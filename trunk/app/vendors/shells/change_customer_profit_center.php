<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Utility that changes the profit center on a customer.
	 */
	class ChangeCustomerProfitCenterShell extends Shell 
	{
		var $uses = array(
			'Process',
			'Customer',
			'ProfitCenter',
			'Document',
			'Setting',
			'FileNote',
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
				'type'			=> 'string',
				'model' 		=> 'Customer',
				'field'			=> 'account_number',
				'flag'			=> 'account',
				'required' 		=> true,
				'description' 	=> 'The account number to process.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Customer',
				'field'			=> 'profit_center_number',
				'flag'			=> 'pc',
				'required'		=> true,
				'description' 	=> 'The profit center number to change the account to.'
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$success = true;
			$this->Logging->maintainBuffer();
			
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			//initialize the process
			$processID = $this->Process->createProcess('Change Customer Profit Center', false);
			
			$this->Logging->write('Starting');
			$this->Process->updateProcess($processID, 0, 'Starting');
			
			//find the customer
			$customerID = $this->Customer->field('id', array('account_number' => $parameters['Customer']['account_number']));
			
			//lock the customer record
			if ($customerID === false || !$this->Customer->lock($customerID))
			{
				$this->Logging->write("Customer record {$parameters['Customer']['account_number']} could not be locked.");
				$this->Process->updateProcess($processID, 0, 'Locking issue');
				$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			//get the next free account number
			$newAccount = $this->ProfitCenter->nextFreeAccountNumber($parameters['Customer']['profit_center_number']);

			//make sure we got a new number			
			if ($newAccount == false)
			{
				//unlock the customer before finishing
				$this->Customer->unlock($customerID);
				
				//give up since we couldn't get a new account number
				$this->Logging->write("Unable to assign a account number for profit center number {$parameters['Customer']['profit_center_number']}! Check to make sure that there are numbers available.");
				$this->Process->updateProcess($processID, 0, 'Unable to assign account number');	
				$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			//prep the data we're going to change (mrs_account_number is the field name for some of the files, so we have to have both in the array)
			$data = array('account_number' => $newAccount, 'mrs_account_number' => $newAccount, 'profit_center_number' => $parameters['Customer']['profit_center_number']);
			
			$models = Configure::listObjects('model');

			//x DONE - fp import - update AUTH_PRIORS (new)
			//x DONE - fp import - update CMN_mngmt (new)
			//x DONE - fp import - update CMN_detail (new)
			//x DONE - fp import (customer_owned_equipment) - update INFO_EQUIP (new)
			//x DONE - update PROD_RECALL (account & p/c)
			//x DONE - update ORD (account & p/c)			
			//x DONE - update 02_DIST (account)
			//x DONE - update MAINTLOG (account)
			//x DONE - update FU05BW (transaction queue) (account & p/c)
			//x DONE - update ORD_MEMO (account)
			//x DONE - update FU05BX (billing queue) (account)
			//x DONE - update FU05DH (certificate medical necessity equipment) (account)
			//x DONE - update FU05DM (oxygen) (account)
			//x DONE - update FU05DR (home_therapy_narrative) (account)
			//x DONE - update FU05DW (extra_narrative) (account)
			//x DONE - update FU05BT (invoice) (account & p/c)			
			//x DONE - update COGS (account & p/c)			
			//x DONE - update FU05BU (transaction) (account & p/c)
			//x DONE - update ORDER (account & p/c)
			//x DONE - update FU05BR (customer carrier) (account)
			//x DONE - update FU05CK (purchase order detail) (account & p/c)
			//x DONE - update FU05BQ (customer billing) (account)
			//x DONE - update FU05BZ (purchase) (account & p/c)
			//x DONE - update FU05BY (rental) (account & p/c)
			//x DONE - update FU05EZ_Claim (account)
			//x DONE - update FU05FA_ServiceLine (account)
			//x DONE - update FU05FB_Adjustments (account)
			//x DONE - update FU05FC_Remarks (account)
			//x DONE - update CMN_answers (new) (account)
			//x DONE - update CREDITS (new) (account)
			//x DONE - update Collection_LTRS (new) (account)
			//x DONE - update STATEMENT (new) (account)
			//x DONE - update FORM1500 (new) (account & p/c)
			//x DONE - fp import - update SCHED (account & p/c)
			
			//N/A - Doesn't have field - update CMN_totals (new)
			//N/A - Doesn't have field - update AAA (aaa referral) (account)			
			
			//No Longer Used - update DIST.LOG (account & p/c)
			//No Longer Used - update INFO (account)
			//No Longer Used - update INV.TRK (account & p/c)
			//No Longer Used - update PRICEADJLOG (account)
			//No Longer Used - update LOG (account)
			//No Longer Used - update FU05BS (patient oxygen) (account)
			//No Longer Used - update CMN_questions (new)

			try
			{
				//run through all models looking for ones that can be migrated
				foreach ($models as $i => $name)
				{
					$model = ClassRegistry::init($name);
						
					//make sure the model is migratable for the account number
					if ($model->Behaviors->enabled('Migratable') && in_array($model->Behaviors->Migratable->settings[$model->alias]['key'], array('account_number', 'mrs_account_number')))
					{
						$this->Logging->write("Migrating {$model->useTable}...");
						$this->Process->updateProcess($processID, floor($i / count($models) * 90), "Migrating {$model->useTable}");
						$migrated = 0;
					
						//migrate the account
						if (($migrated = $model->migrate($parameters['Customer']['account_number'], $data)) === false)
						{
							//log the fact that the model didn't migrate completely
							$this->Logging->write("ERROR: {$model->useTable} failed to migrate successfully.");
							
							//write it to our recovery table to be re-migrated later
							$this->MigrationRecovery->create();
							
							$this->MigrationRecovery->save(array(
								'model' => $name,
								'old_account_number' => $parameters['Customer']['account_number'],
								'new_account_number' => $newAccount,
								'new_profit_center_number' => $parameters['Customer']['profit_center_number']
							));
							
							$success = false;
						}
						else
						{
							$this->Logging->write("Successfully migrated {$migrated} records in {$model->useTable}.");
						}
					}
				}
				
				//update DocPop accounts
				$this->Logging->write('Updating DocPop...');
				$this->Process->updateProcess($processID, 95, 'Updating DocPop');
				$this->Document->migrateAccount($parameters['Customer']['account_number'], $newAccount);
				
				//update account, p/c in FU05BL
				$this->Logging->write('Updating the Account...');
				$this->Process->updateProcess($processID, 96, 'Updating the Account');
				
				//unlock the customer first so we can write via filepro (yes this presents a potential race condition with someone else
				//getting a lock on the record and our save will fail, but it's the best we can do to ensure the filepro indexes
				//are up to date). Also, saving the record through the U05 driver, then unlocking, then saving again via filepro doesn't work
				//as filePro gets confused when an index doesn't match the data in the record when trying to update the index (see task 17877).
				$this->Customer->unlock($customerID);

				$customerSaved = $this->Customer->saveViaFilepro(array('Customer' => array(
					'id' => $customerID,
					'account_number' => $newAccount,
					'profit_center_number' => $parameters['Customer']['profit_center_number']
				)));
				
				if ($customerSaved === false)
				{
					//log the fact that the we couldn't update filepro indexes
					$this->Logging->write('WARNING: Failed to update the main account record. The account will still be listed under the old account number until the account is manually updated. Please contact the IT department.');
					
					//set success to false so the log shows that it completed with errors
					$success = false;
				}
				
				//send an email to people who are supposed to be alerted about an account migration
				$this->Logging->write('Sending Account Migration Emails...');
				$this->Process->updateProcess($processID, 98, 'Sending Account Migration Emails');

				App::import('Component', 'Email');
				$email = new EmailComponent();
				
				$addresses = $this->Setting->get(array('default_mail_reply', 'tech_support_email', 'account_migration_email'));
				
				$email->to = Configure::read('live') == 1 ? $addresses['account_migration_email'] : $addresses['tech_support_email'];
				$email->subject = 'Account Migration';
				$email->from = $addresses['default_mail_reply'];
				$email->sendAs = 'html';
				$email->send(date('m/d/Y h:i:s') . ": Account number {$parameters['Customer']['account_number']} has been moved to profit center {$parameters['Customer']['profit_center_number']} by {$parameters['Virtual']['impersonate']} and is now account number {$newAccount}.");
				
				$this->Logging->write("Account number {$parameters['Customer']['account_number']} has been moved to profit center {$parameters['Customer']['profit_center_number']} by {$parameters['Virtual']['impersonate']} and is now account number {$newAccount}.");
				
				//create an eFN about the change
				$this->FileNote->createNote(
					array('FileNote' => array(
						'account_number' => $newAccount,
						'action_code' => 'CL900',
						'department_code' => 'C',
						'priority_code' => 'M',
						'memo' => "Account Change. New #: {$newAccount} - Old #: {$parameters['Customer']['account_number']}"
					)),
					$parameters['Virtual']['impersonate']
				);
			}
			catch (Exception $ex)
			{
				//unlock the customer before finishing
				$this->Customer->unlock($customerID);
				
				//give up since something went wrong
				$this->Logging->write('Unexpected error: ' . $ex->getMessage());
				$this->Process->updateProcess($processID, 95, 'Unexpected error');	
				$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			$this->Logging->write('Finished' . ($success ? '' : ' with Errors'));
			$this->Process->updateProcess($processID, 100, 'Finished' . ($success ? '' : ' with Errors'));
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>