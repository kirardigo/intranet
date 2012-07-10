<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Shell that implements the old U05 EMC Claims (CU05EK) program.
	 */
	class ElectronicMedicalClaimsBillingShell extends Shell
	{
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		var $uses = array('Process', 'Setting', 'EmcBillingBatch', 'BillingQueue');
		
		/** This will hold our process ID that will be used to report our progress as we invoice. */
		var $processID;
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model'			=> 'Virtual',
				'field'			=> 'form_code',
				'flag'			=> 'emc',
				'description'	=> 'The EMC form code.',
				'required'		=> true
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'profit_center_number',
				'flag' 			=> 'pc',
				'description'	=> 'The profit center number.',
				'required'		=> true
			),
			array(
				'type' 			=> 'date',
				'model' 		=> 'Virtual',
				'field' 		=> 'billing_date',
				'flag' 			=> 'date',
				'description'	=> 'The date to update the billing date to.',
				'required' 		=> true
			),
			array(
				'type'			=> 'array',
				'model'			=> 'Virtual',
				'field'			=> 'carrier_code',
				'flag'			=> 'carrier',
				'description'	=> 'Up to five carrier codes to process, delimited by commas.'
			),
			array(
				'type'			=> 'string',
				'model'			=> 'Virtual',
				'field'			=> 'username',
				'flag'			=> 'username',
				'description'	=> 'The user the process should run as.',
				'required'		=> true
			)
		);
		
		function main()
		{
			$parameters = $this->ReportParameters->parse($this->parameters);
			
			//we need to maintain the log to store in the process at the end
			$this->Logging->maintainBuffer();
			
			//impersonate the user
			$this->Impersonate->impersonate($parameters['Virtual']['username']);
			
			//start a process
			$this->processID = $this->Process->createProcess('EMC Billing', true);
			
			//output all the parameters we're using for this run
			$this->Logging->write('Parameters:');
			
			foreach (Set::flatten($parameters) as $parameter => $value)
			{
				$this->Logging->write($parameter . ' => ' . $value);
			}
			
			$this->Logging->write('');
			$this->Logging->write('Starting process.');
			
			//load the default file
			$this->Logging->write('Loading default file.');

			App::import('Component', 'DefaultFile');
			$this->DefaultFile = new DefaultFileComponent();
			
			if (!$this->DefaultFile->load())
			{
				$this->Logging->write('Failed to load default file. Quitting.');
				$this->Process->updateProcess($this->processID, 0, 'Could not open default file');
				$this->Process->interruptProcess($this->processID);	
				$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
				return;
			}
			
			//figure out and prep the carriers
			$carriers = isset($parameters['Virtual']['carrier_code']) ? $parameters['Virtual']['carrier_code'] : array();
			
			//only take 5 max
			while (count($carriers) > 5)
			{
				$carriers = array_pop($carriers);
			}
			
			//make sure we have 5 in the array
			while (count($carriers) < 5)
			{
				$carriers[] = '';
			}
			
			//create the batch record
			$batchData = array(
				'form_code' => $parameters['Virtual']['form_code'],
				'receiver_code' => $this->DefaultFile->data['receiver_id'],
				'profit_center_number' => $parameters['Virtual']['profit_center_number'],
				'billing_date' => databaseDate($parameters['Virtual']['billing_date']),
				'carrier_code_1' => $carriers[0],
				'carrier_code_2' => $carriers[1],
				'carrier_code_3' => $carriers[2],
				'carrier_code_4' => $carriers[3],
				'carrier_code_5' => $carriers[4],
				'run_as' => $parameters['Virtual']['username']
			);
			
			if ($this->EmcBillingBatch->save(array('EmcBillingBatch' => $batchData)) !== false)
			{
				//pull and normalize the data we need
				$this->pullData($batchData, $carriers);
			}
			

			//finish the process
			$this->Process->updateProcess($this->processID, 100, 'Finished');
			$this->Logging->write('Done.');
			$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
		}
		
		function pullData($batchData, $carriers)
		{
			//figure out the carrier conditions, if any
			$carriers = array_filter($carriers);
			$carrierConditions = array();
			
			if (count($carriers) > 0)
			{
				$carrierConditions = array(
					'or' => array(
						'carrier_1_code' => $carriers,
						'carrier_2_code' => $carriers,
						'carrier_3_code' => $carriers
					)
				);
			}
			
			//find all matching billing queue records based on the form code
			$data = $this->BillingQueue->find('all', array(
				'conditions' => array_merge(
					array(
						'form_code' => $batchData['form_code'],
					),
					$carrierConditions
				)),
				'contain' => array()
			));
			
			//go through each billing queue record and see if we need to bring it over
			foreach ($data as $record)
			{
				//make sure we find the customer
				if (($customer = $this->Customer->find('first', array('conditions' => array('account_number' => $data['BillingQueue']['account_number']), 'contain' => array()))) !== false)
				{
					//make sure the customer is for the specified profit center
					if ($customer['Customer']['profit_center_number'] == $batchData['profit_center_number'])
					{
						//TODO - now we're set to bring over the necessary data
					}
				}
			}
		}
	}
?>