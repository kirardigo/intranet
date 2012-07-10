<?php
	Configure::write('Cache.disable', true);
	
	class PurgeBillingQueueShell extends Shell 
	{
		var $uses = array('BillingQueue', 'Process', 'Invoice', 'Customer');
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to mark as the creator for generated records.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'BillingQueue',
				'field'			=> 'date_of_service',
				'flag'			=> 'date',
				'default'		=> '',
				'description' 	=> 'Program will purge all records prior to this date.'
			),
			array(
				'type' 			=> 'string',
				'model'			=> 'BillingQueue',
				'field'			=> 'form_code',
				'flag'			=> 'formCode',
				'default'		=> '',
				'description'	=> 'Limit purge to a specified form code.'
			),
			array(
				'type' 			=> 'flag',
				'model'			=> 'BillingQueue',
				'field'			=> 'zero_balance_required',
				'flag'			=> 'zeroBalanceRequired',
				'description'	=> ''
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			
			// Parse the report parameters
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			$processID = $this->Process->createProcess('Purge Billing Queue', false);
			
			$this->Process->updateProcess($processID, 0, 'Purging...');
			$this->Logging->write('Purging the Billing Queue...');
			$this->Logging->write('Purge Date: ' . formatDate($parameters['BillingQueue']['date_of_service']));
			$this->Logging->write('Form Code: ' . $parameters['BillingQueue']['form_code']);
			$this->Logging->write('Zero Balance Required: ' . ($parameters['BillingQueue']['zero_balance_required'] ? 'Y' : 'N'));
			
			$db = ConnectionManager::getDataSource($this->BillingQueue->useDbConfig);
			$fileInfo = stat($db->dataPath($this->BillingQueue));
			$recordLength = $db->recordLength($this->BillingQueue);
			$totalRecords = $fileInfo['size'] / $recordLength;
			$processedRecords = 0;
			$purgeDate = databaseDate($parameters['BillingQueue']['date_of_service']);
			$id = 0;

			$this->Logging->write("Original Record Count: {$totalRecords}");
			
			// Prepare the temp file for writing
			$tempFile = TMP . $this->BillingQueue->useTable . '.TMP';
			$f = fopen($tempFile, 'w');
			
			while (($data = $this->BillingQueue->find('first', array('contain' => array(), 'conditions' => array('id >' => $id)))) !== false)
			{
				$processedRecords++;
				$this->Process->updateProcess($processID, min(($processedRecords / $totalRecords) * 100, 100));
				
				$keep = false;
				
				// Do not purge if the form code does not match
				if ($parameters['BillingQueue']['form_code'] != '' && $data['BillingQueue']['form_code'] != $parameters['BillingQueue']['form_code'])
				{
					$keep = true;
				}
				else if ($purgeDate != '' && databaseDate($data['BillingQueue']['date_of_service']) >= $purgeDate)
				{
					// Do not purge records that are more recent than the cut-off date
					$keep = true;
				}
				else if ($parameters['BillingQueue']['zero_balance_required'])
				{
					// Do not purge if zero balance is required but the invoice is showing a non-zero balance
					
					//see if the invoice has a zero balance
					$invoice = $this->Invoice->find('first', array(
						'fields' => array('id'),
						'conditions' => array(
							'account_number' => $data['BillingQueue']['account_number'],
							'invoice_number' => $data['BillingQueue']['invoice_number'],
							'carrier_1_balance' => 0,
							'carrier_2_balance' => 0,
							'carrier_3_balance' => 0
						),
						'contain' => array()
					));
					
					//if the invoice wasn't zero balance, we can't purge it
					if ($invoice === false)
					{
						$keep = true;
					}
				}
				
				if ($keep)
				{
					//we're going to cheat and use a private method of the FU05 driver that can create the
					//buffer that gets written for the record
					$buffer = $db->_createRecordBuffer($this->BillingQueue, array_keys($data['BillingQueue']), array_values($data['BillingQueue']));
					
					//write the buffer
					fwrite($f, $buffer);
				}
				
				$id = $data['BillingQueue']['id'];
			}
			
			fclose($f);
			
			$fileInfo = stat($tempFile);
			$purgedRecords = $totalRecords - ($fileInfo['size'] / $recordLength);
			
			// Copy the file over the original (we copy and unlink instead of move to retain file attributes)
			if (copy($tempFile, $db->dataPath($this->BillingQueue)))
			{
				$this->Logging->write("Purged Record Count: {$purgedRecords}");
				$this->Process->updateProcess($processID, 100, "Finished purging {$purgedRecords} records");
			}
			else
			{
				$this->Logging->write("Unable to copy the purged file live. Please check that the file is not locked.");
				$this->Process->updateProcess($processID, 100, "Unable to copy the purged file live");
			}
			
			unlink($tempFile);
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());			
			$this->out('');
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>