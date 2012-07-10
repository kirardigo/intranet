<?php
	/**
	 * This utility is another part of the ChangeCustomerProfitCenter shell. Any time that shell fails to migrate a particular model
	 * during the migration process, it writes the information that is needed to perform the migration to the migration_recoveries table
	 * in the database. Then, this shell can be run to read those records and try to migrate them again at a later time, even after the
	 * account itself has already been migrated.
	 */
	class MigrationRecoveryShell extends Shell
	{
		var $uses = array(
			'Process',
			'Customer',
			'Setting',
			'MigrationRecovery'
		);
		
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
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'id',
				'flag' 			=> 'id',
				'description'	=> 'Optionally used to specified an explicit migration recovery record to process.'
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			//initialize the process
			$processID = $this->Process->createProcess('Migration Recovery', false);
			$customerID = null;
			
			$this->Logging->write('Starting');
			$this->Process->updateProcess($processID, 0, 'Starting');
			
			$id = 0;
			$explicitID = isset($parameters['Virtual']['id']) ? $parameters['Virtual']['id'] : false;
			
			try
			{
				//get an initial count of how many records we have to process
				$recordCount = $this->MigrationRecovery->find('count');
				$i = 0;
				
				//go through each record in the migration recovery table (or just the explicit one if specified)
				while (($current = $this->MigrationRecovery->find('first', array('conditions' => array('id' . ($explicitID === false ? ' >' : '')  => ($explicitID === false ? $id : $explicitID))))) !== false)
				{
					//prep the ID for the next time around
					$id = $current['MigrationRecovery']['id'];
					$i++;
					
					//instantiate the model this record is for
					$model = ClassRegistry::init($current['MigrationRecovery']['model']);
					
					//make sure the model is migratable for the account number
					if (!$model->Behaviors->enabled('Migratable') || !in_array($model->Behaviors->Migratable->settings[$model->alias]['key'], array('account_number', 'mrs_account_number')))
					{
						//if not, ditch it
						$this->MigrationRecovery->delete($current['MigrationRecovery']['id']);
						$this->Logging->write("Skipping {$model->useTable}. Table is not configured to be migrated.");
						
						if ($explicitID !== false)
						{
							break;
						}
						
						//keep going to the next record
						continue;
					}
					
					$this->Logging->write("Migrating {$model->useTable}...");
					$this->Process->updateProcess($processID, floor($i / $recordCount * 90), "Migrating {$model->useTable}");
					
					//find the customer under their NEW account number since we're recovering a single model on an account that has already been migrated
					$customerID = $this->Customer->field('id', array('account_number' => $current['MigrationRecovery']['new_account_number']));
					
					//lock the account
					if ($customerID === false || !$this->Customer->lock($customerID))
					{
						//skip the record if we can't lock it
						$this->Logging->write("Customer record {$current['MigrationRecovery']['new_account_number']} could not be locked. Skipping.");
						
						if ($explicitID !== false)
						{
							break;
						}
						
						continue;
					}
					
					//prep the data we're going to change (mrs_account_number is the field name for some of the files, so we have to have both in the array)
					$data = array(
						'account_number' => $current['MigrationRecovery']['new_account_number'], 
						'mrs_account_number' => $current['MigrationRecovery']['new_account_number'], 
						'profit_center_number' => $current['MigrationRecovery']['new_profit_center_number']
					);
										
					//try and migrate the data again
					if (!$model->migrate($current['MigrationRecovery']['old_account_number'], $data))
					{
						//if we can't migrate again just unlock the customer and keep going
						$this->Customer->unlock($customerID);
						$this->Logging->write("Customer record {$current['MigrationRecovery']['new_account_number']} failed to migrate. Skipping.");
						
						if ($explicitID !== false)
						{
							break;
						}
						
						continue;
					}
				
					//delete the migration record since we've migrated the data
					$this->MigrationRecovery->delete($current['MigrationRecovery']['id']);
					
					//unlock the customer
					$this->Customer->unlock($customerID);
					
					if ($explicitID !== false)
					{
						break;
					}
				}
			}
			catch (Exception $ex)
			{
				//unlock the customer if one is locked
				if ($customerID != null)
				{
					$this->Customer->unlock($customerID);
				}
				
				//give up since something went wrong
				$this->Logging->write('Unexpected error: ' . $ex->getMessage());
				$this->Process->updateProcess($processID, 95, 'Unexpected error');	
				$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			$this->Logging->write('Finished');
			$this->Process->updateProcess($processID, 100, 'Finished');
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>