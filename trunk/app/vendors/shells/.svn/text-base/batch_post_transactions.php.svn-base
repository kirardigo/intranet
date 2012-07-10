<?php
	Configure::write('Cache.disable', true);
	
	class BatchPostTransactionsShell extends Shell 
	{
		var $uses = array(
			'Transaction',
			'TransactionQueue',
			'TransactionJournal',
			'TransactionType',
			'Invoice',
			'Customer',
			'GeneralLedger',
			'Process'
		);
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' 			=> 'date',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'transaction_date_of_service >=',
				'flag' 			=> 'start',
				'description'	=> 'The start date.'
			),
			array(
				'type' 			=> 'date',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'transaction_date_of_service <=',
				'flag' 			=> 'end',
				'description'	=> 'The end date.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'cash_reference_number',
				'flag' 			=> 'cashref',
				'description'	=> 'The cash reference number.'
			),
			array(
				'type' 			=> 'flag',
				'model' 		=> 'Virtual',
				'field' 		=> 'include_blank_cash_reference_numbers',
				'flag' 			=> 'blankcashref',
				'description'	=> 'Indicates the system should include records that do not have a cash reference number.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'created_by',
				'flag' 			=> 'user',
				'description'	=> 'The username to process the queue for.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'profit_centers',
				'flag' 			=> 'profit',
				'description'	=> 'A comma-separated list of profit centers.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'post_status',
				'flag' 			=> 'status',
				'description'	=> 'The posting status of the record.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'bank_number',
				'flag' 			=> 'bank',
				'description'	=> 'The bank number.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'TransactionQueue',
				'field' 		=> 'id',
				'flag' 			=> 'record',
				'description'	=> 'The record number.'
			),
			array(
				'type' 			=> 'flag',
				'model' 		=> 'Virtual',
				'field' 		=> 'suggested_credits',
				'flag' 			=> 'suggest',
				'description'	=> 'Indicates the system should create suggested credits.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to mark as the creator for generated records.'
			),
			array(
				'type' 			=> 'flag',
				'model' 		=> 'Virtual',
				'field' 		=> 'is_verbose',
				'flag' 			=> 'verbose',
				'description'	=> 'Indicates that verbose information should be displayed.'
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
			$isVerbose = $parameters['Virtual']['is_verbose'];
			if ($isVerbose) { $this->Logging->write('Starting Batch Post', 1); }
			
			// Kick off process AFTER we are impersonating the proper user
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			$processID = $this->Process->createProcess('Batch Post', true);
			
			if (!isset($parameters['TransactionQueue']))
			{
				$parameters['TransactionQueue'] = array();
			}
			
			// Build the conditions array
			$conditions = Set::flatten($parameters['TransactionQueue']);
			
			//if we have a user to filter by, we need to filter against both the created_by
			//field (used by eMRS) as well as the user_id field (for backwards compatibility with filePro)
			if (isset($conditions['created_by']))
			{
				$conditions['or'] = array(
					'user_id' => $conditions['created_by'],
					'created_by' => $conditions['created_by']
				);
				
				unset($conditions['created_by']);
			}
			
			// Add the profit center numbers to the conditions, if specified
			if (isset($parameters['Virtual']['profit_centers']))
			{
				$conditions['profit_center_number'] = explode(',', $parameters['Virtual']['profit_centers']);
			}
			
			// Add blank option when specifying cash_reference_number
			if (isset($parameters['TransactionQueue']['cash_reference_number']) 
				&& $parameters['Virtual']['include_blank_cash_reference_numbers'])
			{
				$conditions['TransactionQueue.cash_reference_number'] = array(
					$parameters['TransactionQueue']['cash_reference_number'],
					''
				);
			}
			
			// Load settings from the default file
			App::import('Component', 'DefaultFile');
			$this->DefaultFile = new DefaultFileComponent();
			$currentPostingPeriod = $this->DefaultFile->getCurrentPostingPeriod();
			
			// Initialize the totals
			$transactionTypesRaw = $this->TransactionType->find('all', array(
				'contain' => array()
			));
			
			foreach ($transactionTypesRaw as $row)
			{
				$totals[$row['TransactionType']['code']] = 0;
				$transactionTypes[$row['TransactionType']['code']] = $row;
			}

			$conditions['TransactionQueue.id >'] = 0;
			$postedTransactions = array();
			
			$estimatedTotal = $this->TransactionQueue->find('count', array(
				'contain' => array(),
				'conditions' => $conditions
			));
			
			$currentCount = 0;
			$cancelled = false;
			
			if ($isVerbose) { $this->Logging->write('Starting loop of records in queue', 1); }
			
			// Loop through transaction queue records to post
			while (($transactionQueueID = $this->TransactionQueue->field('id', $conditions, 'id')) !== false)
			{
				// Calculate estimated progress, assuming that we still have 5% to do after processing the queue itself
				$currentCount++;
				$estimatedPercentage = round(min(($currentCount / $estimatedTotal) * 95, 95));
				
				// Interrupt process if cancelled by user
				if ($this->Process->isProcessInterrupted($processID))
				{
					$this->Logging->write('Process cancelled by user', $isVerbose);
					$this->Process->updateProcess($processID, $estimatedPercentage, 'Cancelling...');
					$cancelled = true;
					break;
				}
				
				// Lock individual queue record
				if (!$this->TransactionQueue->lock($transactionQueueID))
				{
					$this->Logging->write("ERROR: Transaction Queue record {$transactionQueueID} could not be locked");
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				if ($isVerbose) { $this->Logging->write("Queue record {$transactionQueueID} locked", 1); }
				
				$record = $this->TransactionQueue->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $transactionQueueID)
				));
				
				// Between calls to field & lock, the record could have been deleted.
				if ($record === false)
				{
					$this->Logging->write("ERROR: Transaction Queue record {$transactionQueueID} no longer exists");
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				$this->Process->updateProcess($processID, $estimatedPercentage, "Processing record {$currentCount} of {$estimatedTotal}");
				
				// Fix amount field to always be a float
				$record['TransactionQueue']['amount'] = (float)$record['TransactionQueue']['amount'];
				
				$customerID = $this->Customer->field('id', array('account_number' => $record['TransactionQueue']['account_number']));
				
				// Attempt to lock the customer record
				if ($customerID === false || !$this->Customer->lock($customerID))
				{
					$this->Logging->write("ERROR: Customer record {$record['TransactionQueue']['account_number']} could not be locked");
					$this->TransactionQueue->unlock($transactionQueueID);
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				if ($isVerbose) { $this->Logging->write("Customer record {$record['TransactionQueue']['account_number']} locked", 1); }
				
				// Grab the customer & customer carrier info
				$carriers = $this->Customer->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'Customer.account_number' => $record['TransactionQueue']['account_number']
					),
					'chains' => array(
						'CustomerCarrier' => array(
							'contain' => array(),
							'fields' => array(
								'carrier_number',
								'carrier_group_code',
								'gross_charge_percentage'
							),
							'conditions' => array(
								'CustomerCarrier.carrier_number' => $record['TransactionQueue']['carrier_number']
							),
							'limit' => 1,
							'required' => false
						)
					)
				));
				
				// Grab the invoice record
				$invoice = $this->Invoice->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'account_number' => $record['TransactionQueue']['account_number'],
						'invoice_number' => $record['TransactionQueue']['invoice_number']
					)
				));
				
				if ($invoice !== false && $isVerbose)
				{
					$this->Logging->write("Invoice {$record['TransactionQueue']['invoice_number']} found for customer {$record['TransactionQueue']['account_number']}", 1);
				}
				
				// If customer does not exist, return error
				if (!isset($carriers['Customer']))
				{
					$this->Logging->write("ERROR: Customer {$record['TransactionQueue']['account_number']} does not exist");
					$this->Customer->unlock($customerID);
					$this->TransactionQueue->unlock($transactionQueueID);
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				// If matching carrier does not exist, return error
				if (!isset($carriers['CustomerCarrier'][0]))
				{
					$this->Logging->write("ERROR: Carrier {$record['TransactionQueue']['carrier_number']} does not exist for {$record['TransactionQueue']['account_number']}");
					$this->Customer->unlock($customerID);
					$this->TransactionQueue->unlock($transactionQueueID);
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				// Validate that general ledger code still exists
				if (trim($record['TransactionQueue']['general_ledger_code']) != '')
				{
					$generalLedgerRecord = $this->GeneralLedger->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'GeneralLedger.general_ledger_code' => $record['TransactionQueue']['general_ledger_code']
						)
					));
				}
				
				// If general ledger code does not exist or is blank, return error
				if ($generalLedgerRecord === false || trim($record['TransactionQueue']['general_ledger_code']) == '')
				{
					$this->Logging->write("ERROR: General Ledger code {$record['TransactionQueue']['general_ledger_code']} is not valid");
					$this->Customer->unlock($customerID);
					$this->TransactionQueue->unlock($transactionQueueID);
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				// Update invoice and transaction records
				try
				{
					if ($isVerbose) { $this->Logging->write('Starting post of single record', 1); }
					
					$this->TransactionQueue->postTransaction(
						$carriers['Customer']['id'],
						$record['TransactionQueue'],
						$carriers['CustomerCarrier'][0],
						isset($invoice['Invoice']) ? $invoice['Invoice'] : null,
						isset($transactionTypes[$record['TransactionQueue']['transaction_type']]) ? $transactionTypes[$record['TransactionQueue']['transaction_type']] : null,
						$currentPostingPeriod
					);
					
					if ($isVerbose) { $this->Logging->write('Finishing post of single record', 1); }
				}
				catch (Exception $ex)
				{
					$this->Logging->write('ERROR: ' . $ex->getMessage());
					$this->Customer->unlock($customerID);
					$this->TransactionQueue->unlock($transactionQueueID);
					$conditions['TransactionQueue.id >'] = $transactionQueueID;
					continue;
				}
				
				// Get the adjusted amount based on the transaction type
				$adjustedAmount = $this->TransactionType->getAdjustedAmount($record['TransactionQueue']['amount'], isset($transactionTypes[$record['TransactionQueue']['transaction_type']]) ? $transactionTypes[$record['TransactionQueue']['transaction_type']] : null);
				
				// Update TransactionJournal
				if ($record['TransactionQueue']['amount'] != 0)
				{
					$journalData['TransactionJournal'] = array(
						'account_number' 					=> $record['TransactionQueue']['account_number'],
						'invoice_number' 					=> $record['TransactionQueue']['invoice_number'],
						'transaction_date_of_service' 		=> databaseDate($record['TransactionQueue']['transaction_date_of_service']),
						'general_ledger_code' 				=> $record['TransactionQueue']['general_ledger_code'],
						'profit_center_number' 				=> $carriers['Customer']['profit_center_number'],
						'carrier_number' 					=> $record['TransactionQueue']['carrier_number'],
						'transaction_type' 					=> $record['TransactionQueue']['transaction_type'],
						'amount' 							=> round($adjustedAmount, 2),
						'inventory_group_code' 				=> $generalLedgerRecord['GeneralLedger']['group_code'],
						'bank_number' 						=> $record['TransactionQueue']['bank_number'],
						'quantity' 							=> $record['TransactionQueue']['quantity'],
						'inventory_number' 					=> $record['TransactionQueue']['inventory_number'],
						'inventory_description' 			=> $record['TransactionQueue']['inventory_description'],
						'healthcare_procedure_code' 		=> $record['TransactionQueue']['healthcare_procedure_code'],
						'inventory_group_code_2' 			=> $record['TransactionQueue']['inventory_group_code'],
						'profit_center_number_2' 			=> $record['TransactionQueue']['profit_center_number'],
						'salesman_number' 					=> $record['TransactionQueue']['salesman_number'],
						'department_code' 					=> $record['TransactionQueue']['department_code'],
						'unique_identification_number' 		=> $record['TransactionQueue']['unique_identification_number'],
						'transaction_control_number_file' 	=> $record['TransactionQueue']['transaction_control_number_file'],
						'transaction_control_number' 		=> $record['TransactionQueue']['transaction_control_number'],
						'serial_number' 					=> $record['TransactionQueue']['serial_number'],
						'cash_reference_number'				=> $record['TransactionQueue']['cash_reference_number']
					);
					
					$this->TransactionJournal->create();
					$this->TransactionJournal->save($journalData);
					
					if ($isVerbose) { $this->Logging->write('Transaction Journal saved', 1); }
					
					// Track account# / invoice# / carrier# so we can apply suggested credits to non-zero payments
					$isPayment = $transactionTypes[$record['TransactionQueue']['transaction_type']]['TransactionType']['is_payment'];
					
					if ($isPayment)
					{
						$nonZeroPayments[$record['TransactionQueue']['account_number']][$record['TransactionQueue']['invoice_number']][$record['TransactionQueue']['carrier_number']] = 1;
					}
				}
				
				// Save and total for posted transactions report
				$postedTransactions[] = $record['TransactionQueue'];
				$totals[$record['TransactionQueue']['transaction_type']] += $adjustedAmount;
				
				// Remove the posted transaction from the queue
				$this->TransactionQueue->delete($transactionQueueID);
				
				if ($isVerbose) { $this->Logging->write('Queue record deleted', 1); }
				
				// Unlock records
				$this->Customer->unlock($customerID);
				$this->TransactionQueue->unlock($transactionQueueID);
				
				if ($isVerbose) { $this->Logging->write('Records unlocked', 1); }
			}
			
			// Generate posted transactions report
			$this->out('');
			$this->Logging->write('PARAMETERS:', $isVerbose);
			foreach (Set::flatten($parameters) as $parameter => $value)
			{
				$this->Logging->write($parameter . ' => ' . $value, $isVerbose);
			}
			
			$this->out('');
			$this->Logging->write('POSTED TRANSACTIONS:', $isVerbose);
			
			foreach ($postedTransactions as $postedTransaction)
			{
				$this->Logging->write(vsprintf('%-6s  %-7s  %-4s  %10s  %10s  %-7s  %8.2f  %8s', array(
					$postedTransaction['account_number'],
					$postedTransaction['invoice_number'],
					$postedTransaction['general_ledger_code'],
					$postedTransaction['transaction_date_of_service'],
					$postedTransaction['billing_date'],
					$postedTransaction['carrier_number'],
					$postedTransaction['amount'],
					$postedTransaction['created_by']
				)), $isVerbose);
			}
			
			$this->out('');
			$this->Logging->write('TOTALS:', $isVerbose);
			foreach ($totals as $transactionType => $total)
			{
				$this->Logging->write(sprintf('%-20s $%10.2f', $transactionTypes[$transactionType]['TransactionType']['description'], $total), $isVerbose);
			}
			
			// Create suggested credits, if requested
			if ($parameters['Virtual']['suggested_credits'])
			{
				$this->Process->updateProcess($processID, $estimatedPercentage, 'Creating Suggested Credits');
				if ($isVerbose) { $this->Logging->write('Creating Suggested Credits', 1); }
				
				$this->TransactionQueue->createSuggestedCredits(isset($nonZeroPayments) ? $nonZeroPayments : array());
			}
			
			if ($cancelled)
			{
				$this->Process->updateProcess($processID, $estimatedPercentage, 'Cancelled');
			}
			else
			{
				$this->Process->updateProcess($processID, 100, 'Finished');
			}
			
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
			
			$this->out('');
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>