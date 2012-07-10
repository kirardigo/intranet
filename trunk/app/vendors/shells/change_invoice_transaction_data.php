<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Utility that changes the data for an invoice or transaction on an account.
	 */
	class ChangeInvoiceTransactionDataShell extends Shell 
	{
		var $uses = array(
			'Customer',
			'CustomerCarrier',
			'GeneralLedger',
			'Invoice',
			'Process',
			'Transaction',
			'TransactionType'
		);
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $processID;
		var $customerID;
		var $transactionTypes = array();
		var $messages = false;
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to use for the eMRS background task.'
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
				'type'			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'balance',
				'flag' 			=> 'balance',
				'description'	=> 'Indicates whether to just sort & balance the specified account.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'id',
				'flag'			=> 'invoice',
				'description' 	=> 'The ID of the invoice to update. (Cannot be combined with transaction flag.)'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'id',
				'flag'			=> 'transaction',
				'description' 	=> 'The ID of the transaction to update. (Cannot be combined with invoice flag.)'
			),
			array(
				'type'			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'delete',
				'flag'			=> 'delete',
				'description'	=> 'Indicates whether to delete the specified record.'
			),
			
			// Shared by invoices & transactions
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'account_number',
				'flag'			=> 'accountNumber',
				'description' 	=> 'The new account number.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'invoice_number',
				'flag'			=> 'invoiceNumber',
				'description' 	=> 'The new invoice number.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'department_code',
				'flag'			=> 'dept',
				'description' 	=> 'The new department code.'
			),
			
			// Invoice specific
			array(
				'type'			=> 'date',
				'model' 		=> 'Invoice',
				'field'			=> 'date_of_service',
				'flag'			=> 'invDateOfService',
				'description' 	=> 'The new date of service.'
			),
			array(
				'type'			=> 'date',
				'model' 		=> 'Invoice',
				'field'			=> 'billing_date',
				'flag'			=> 'invBillDate',
				'description' 	=> 'The new invoice billing date.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'amount',
				'flag'			=> 'invAmt',
				'description' 	=> 'The new invoice amount.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'carrier_1_code',
				'flag'			=> 'invCarr1',
				'description' 	=> 'The new invoice code for carrier 1.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'carrier_2_code',
				'flag'			=> 'invCarr2',
				'description' 	=> 'The new invoice code for carrier 2.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Invoice',
				'field'			=> 'carrier_3_code',
				'flag'			=> 'invCarr3',
				'description' 	=> 'The new invoice code for carrier 3.'
			),
			
			// Transaction specific
			array(
				'type'			=> 'date',
				'model' 		=> 'Transaction',
				'field'			=> 'transaction_date_of_service',
				'flag'			=> 'transDateOfService',
				'description' 	=> 'The new date of service.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'general_ledger_description',
				'flag'			=> 'transDesc',
				'description' 	=> 'The new transaction G/L description.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'amount',
				'flag'			=> 'transAmt',
				'description' 	=> 'The new transaction amount.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'general_ledger_code',
				'flag'			=> 'transGLCode',
				'description' 	=> 'The new transaction G/L code.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'transaction_type',
				'flag'			=> 'transType',
				'description' 	=> 'The new transaction type.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'carrier_number',
				'flag'			=> 'transCarrier',
				'description' 	=> 'The new transaction carrier code.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'Transaction',
				'field'			=> 'serial_number',
				'flag'			=> 'transSerial',
				'description' 	=> 'The new transaction serial number.'
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			
			// Parse arguments
			$parameters = $this->ReportParameters->parse($this->parameters);
			
			$accountNumber = $parameters['Customer']['account_number'];
			$this->customerID = $this->Customer->field('id', array('account_number' => $accountNumber));
			
			// Begin impersonation
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			// Cache the transaction types for future reference
			$types = $this->TransactionType->find('all', array('contain' => array()));
			$this->transactionTypes = array_combine(Set::extract('/TransactionType/code', $types), $types);
			
			// Just sort and balance
			if ($parameters['Virtual']['balance'])
			{
				$this->sortAndBalance($accountNumber);
				return;
			}
			
			// Validate arguments
			if (!isset($parameters['Invoice']['id']) && !isset($parameters['Transaction']['id']))
			{
				$this->Logging->write('Must specify either -invoice or -transaction.');
				$this->_stop();
			}
			
			if (isset($parameters['Invoice']['id']) && isset($parameters['Transaction']['id']))
			{
				$this->Logging->write('May not specify both -invoice and -transaction.');
				$this->_stop();
			}
			
			// Set message for process
			$action = ($parameters['Virtual']['delete']) ? 'Deleting' : 'Updating';
			
			if (isset($parameters['Invoice']['id']))
			{
				$invoiceNumber = $this->Invoice->field('invoice_number', array('id' => $parameters['Invoice']['id']));
				$processMessage = "{$action} invoice {$invoiceNumber} for account {$accountNumber}";
			}
			else
			{
				$processMessage = "{$action} transaction {$parameters['Transaction']['id']} for account {$accountNumber}";
			}
			
			// Initialize the process
			$this->processID = $this->Process->createProcess('Invoice / Transaction Utility', false);
			
			$this->Logging->write($processMessage);
			$this->Process->updateProcess($this->processID, 0, $processMessage);
			
			// Lock the customer record
			if ($customerID === false || !$this->Customer->lock($this->customerID))
			{
				$this->message("Customer record {$accountNumber} could not be locked");
				$this->Process->updateProcess($this->processID, 0, $processMessage . ': LOCKING ISSUE');
				$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			if (isset($parameters['Invoice']['id']))
			{
				$oldInvoiceData = $this->Invoice->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $parameters['Invoice']['id'])
				));
				
				if ($oldInvoiceData === false)
				{
					$this->message("Invoice ID# {$parameters['Invoice']['id']} does not exist.", true);
				}
				
				if ($parameters['Virtual']['delete'])
				{
					$this->deleteInvoice($parameters['Invoice']['id']);
					$this->balanceChains($accountNumber);
				}
				else if ($oldInvoiceData !== false)
				{
					$record = $parameters['Invoice'];
					
					for ($i = 1; $i <= 3; $i++)
					{
						if (isset($record["carrier_{$i}_code"]))
						{
							if ($record["carrier_{$i}_code"] == '')
							{
								if (!$this->removeInvoiceCarrier($accountNumber, $invoiceNumber, $record["carrier_{$i}_code"]))
								{
									unset($record["carrier_{$i}_code"]);
								}
							}
							else
							{
								if (!$this->changeInvoiceCarrier($accountNumber, $invoiceNumber, $record["carrier_{$i}_code"], $oldInvoiceData['Invoice']["carrier_{$i}_code"]))
								{
									unset($record["carrier_{$i}_code"]);
								}
							}
						}
					}
					
					// Save invoice record
					$this->Invoice->create();
					$this->Invoice->save(array('Invoice' => $record));
					
					// Loop through transactions to update any overlapping fields
					$transactionPointer = $this->Customer->field('transaction_pointer', array(
						'account_number' => $accountNumber
					));
					
					while ($transactionPointer != 0)
					{
						$transactionRecord = $this->Transaction->find('first', array(
							'contain' => array(),
							'conditions' => array('id' => $transactionPointer)
						));
						
						if ($transactionRecord === false)
						{
							$this->message("Transaction chain for {$accountNumber} broken at {$transactionPointer}", true);
						}
						
						if ($transactionRecord['Transaction']['invoice_number'] == $invoiceNumber)
						{
							if (isset($record['account_number']))
							{
								$transactionRecord['Transaction']['account_number'] = $record['account_number'];
							}
							if (isset($record['invoice_number']))
							{
								$transactionRecord['Transaction']['invoice_number'] = $record['invoice_number'];
							}
							if (isset($record['date_of_service']) && databaseDate($oldInvoiceData['Invoice']['date_of_service']) == databaseDate($transactionRecord['Transaction']['transaction_date_of_service']))
							{
								$transactionRecord['Transaction']['transaction_date_of_service'] = $record['date_of_service'];
							}
							if (isset($record['department_code']) && $oldInvoiceData['Invoice']['department_code'] == $transactionRecord['Transaction']['department_code'])
							{
								$transactionRecord['Transaction']['department_code'] = $record['department_code'];
							}
							if (isset($record['carrier_1_code']) && $oldInvoiceData['Invoice']['carrier_1_code'] == $transactionRecord['Transaction']['carrier_number'])
							{
								$transactionRecord['Transaction']['carrier_number'] = $record['carrier_1_code'];
							}
							if (isset($record['carrier_2_code']) && $oldInvoiceData['Invoice']['carrier_2_code'] == $transactionRecord['Transaction']['carrier_number'])
							{
								$transactionRecord['Transaction']['carrier_number'] = $record['carrier_2_code'];
							}
							if (isset($record['carrier_3_code']) && $oldInvoiceData['Invoice']['carrier_3_code'] == $transactionRecord['Transaction']['carrier_number'])
							{
								$transactionRecord['Transaction']['carrier_number'] = $record['carrier_3_code'];
							}
							
							$this->Transaction->create();
							$this->Transaction->save($transactionRecord);
						}
						
						$transactionPointer = $transactionRecord['Transaction']['next_record_pointer'];
					}
					
					$this->Process->updateProcess($this->processID, 30);
					
					// Sort the chains if necessary
					if (isset($record['date_of_service']))
					{
						$this->sortChains($accountNumber, true);
					}
					
					$this->Process->updateProcess($this->processID, 60);
					$this->balanceChains($accountNumber);
				}
			}
			else if (isset($parameters['Transaction']['id']))
			{
				$oldTransactionData = $this->Transaction->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $parameters['Transaction']['id'])
				));
				
				if ($oldTransactionData === false)
				{
					$this->message("Transaction ID# {$parameters['Transaction']['id']} does not exist.", true);
				}
				
				if ($parameters['Virtual']['delete'])
				{
					// Deleting a charge effects the invoice amount
					if ($oldTransactionData['Transaction']['transaction_type'] == 1)
					{
						// Get adjusted amount
						$adjustedAmount = round($this->TransactionType->getAdjustedAmount($oldTransactionData['Transaction']['amount'], $this->transactionTypes[$oldTransactionData['Transaction']['transaction_type']]), 2);
						
						$invoice = $this->Invoice->find('first', array(
							'contain' => array(),
							'conditions' => array(
								'account_number' => $accountNumber,
								'invoice_number' => $oldTransactionData['Transaction']['invoice_number']
							)
						));
						
						// Adjust the amount to account for removed charge
						$invoice['Invoice']['amount'] = $invoice['Invoice']['amount'] - $adjustedAmount;
						
						$this->Invoice->create();
						$this->Invoice->save($invoice);
					}
					
					$this->deleteTransaction($parameters['Transaction']['id']);
					$this->balanceChains($accountNumber);
				}
				else if ($oldTransactionData !== false)
				{
					if (!isset($parameters['Invoice']))
					{
						$parameters['Invoice'] = array();
					}
					
					$record = array_merge($parameters['Invoice'], $parameters['Transaction']);
					
					if (isset($record['carrier_number']) && trim($record['carrier_number']) === '')
					{
						$this->message('Cannot remove carrier from transaction');
						unset($record['carrier_number']);
					}
					else
					{
						if (!$this->changeTransactionCarrier($accountNumber, $parameters['Transaction']['id'], $record['carrier_number']))
						{
							unset($record['carrier_number']);
						}
					}
					
					if (isset($record['general_ledger_code']))
					{
						$id = $this->GeneralLedger->field('id', array('general_ledger_code' => $record['general_ledger_code']));
						
						if ($id === false)
						{
							$this->message("G/L code {$record['general_ledger_code']} is not valid");
							unset($record['general_ledger_code']);
						}
					}
					
					if (isset($record['transaction_type']))
					{
						if ($oldTransactionData['Transaction']['transaction_type'] == 1 && $record['transaction_type'] != 1)
						{
							// Get adjusted amount
							$adjustedAmount = round($this->TransactionType->getAdjustedAmount($oldTransactionData['Transaction']['amount'], $this->transactionTypes[$oldTransactionData['Transaction']['transaction_type']]), 2);
							
							$invoice = $this->Invoice->find('first', array(
								'contain' => array(),
								'conditions' => array(
									'account_number' => $accountNumber,
									'invoice_number' => $oldTransactionData['Transaction']['invoice_number']
								)
							));
							
							// Adjust the amount to account for removed charge
							$invoice['Invoice']['amount'] = $invoice['Invoice']['amount'] - $adjustedAmount;
							
							$this->Invoice->create();
							$this->Invoice->save($invoice);
						}
						else if ($oldTransactionData['Transaction']['transaction_type'] != 1 && $record['transaction_type'] == 1)
						{
							// If amount was updated, use new amount, otherwise fallback to existing amount
							$amount = isset($record['amount']) ? $record['amount'] : $oldTransactionData['Transaction']['amount'];
							
							// Get adjusted amount
							$adjustedAmount = round($this->TransactionType->getAdjustedAmount($amount, $this->transactionTypes[$oldTransactionData['Transaction']['transaction_type']]), 2);
							
							$invoice = $this->Invoice->find('first', array(
								'contain' => array(),
								'conditions' => array(
									'account_number' => $accountNumber,
									'invoice_number' => $oldTransactionData['Transaction']['invoice_number']
								)
							));
							
							// Adjust the amount to account for new charge
							$invoice['Invoice']['amount'] = $invoice['Invoice']['amount'] + $adjustedAmount;
							
							$this->Invoice->create();
							$this->Invoice->save($invoice);
						}
					}
					
					// Save transaction record
					$this->Transaction->create();
					$this->Transaction->save(array('Transaction' => $record));
					
					$this->Process->updateProcess($this->processID, 30);
					
					// Sort the chain if necessary
					if (isset($record['transaction_date_of_service']))
					{
						$this->sortChains($accountNumber);
					}
					
					$this->Process->updateProcess($this->processID, 60);
					$this->balanceChains($accountNumber);
				}
			}
			
			$this->Logging->write('Finished');
			$completedText = $this->messages ? ': MESSAGES' : ': DONE';
			$this->Process->updateProcess($this->processID, 100, $processMessage . $completedText);
			$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
			$this->Customer->unlock($this->customerID);
		}
		
		/**
		 * Carrier can be removed from invoice if the following rules are met:
		 *   - There are no transactions for that invoice & carrier with a non-zero balance
		 * @param string $accountNumber The account number of the record.
		 * @param int $invoiceNumber The invoice number of the record.
		 * @param string $carrierNumber The carrier number to remove.
		 * @return bool Determines whether carrier was removed
		 */
		function removeInvoiceCarrier($accountNumber, $invoiceNumber, $carrierNumber)
		{
			$transactionPointer = $this->Customer->field('transaction_pointer', array('account_number' => $accountNumber));
			
			// Loop through transactions to determine if carrier can be removed
			while ($transactionPointer != 0)
			{
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'invoice_number',
						'carrier_number',
						'amount',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				if ($record['Transaction']['invoice_number'] == $invoiceNumber &&
					$record['Transaction']['carrier_number'] == $carrierNumber &&
					$record['Transaction']['amount'] != 0)
				{
					$this->message("Cannot remove {$carrierNumber} from invoice {$invoiceNumber}");
					return false;
				}
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			// Lookup the invoice record
			$invoice = $this->Invoice->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber,
					'invoice_number' => $invoiceNumber
				)
			));
			
			// Find and remove the carrier from the invoice
			for ($i = 1; $i <= 3; $i++)
			{
				if ($invoice['Invoice']["carrier_{$i}_code"] == $carrierNumber)
				{
					$invoice['Invoice']["carrier_{$i}_code"] = '';
					
					$this->Invoice->create();
					$this->Invoice->save($invoice);
				}
			}
			
			return true;
		}
		
		/**
		 * Carrier can be changed on invoice if the following rules are met:
		 *   - Carrier exists in CustomerCarrier chain
		 *   - Carrier doesn't already exist on Invoice
		 * @param string $accountNumber The account number of the record.
		 * @param int $invoiceNumber The invoice number of record.
		 * @param string $carrierNumber The carrier number to change to.
		 * @param string $oldCarrierNumber The carrier number to change from.
		 * @return bool Indicates if carrier should be changed. 
		 */
		function changeInvoiceCarrier($accountNumber, $invoiceNumber, $carrierNumber, $oldCarrierNumber)
		{
			$isCarrierActive = false;
			$carrierExists = false;
			
			$carrierPointer = $this->Customer->field('carrier_pointer', array('account_number' => $accountNumber));
			
			while ($carrierPointer != 0)
			{
				$record = $this->CustomerCarrier->find('first', array(
					'contain' => array(),
					'fields' => array(
						'carrier_number',
						'is_active',
						'next_record_pointer'
					),
					'conditions' => array('id' => $carrierPointer)
				));
				
				if ($record['CustomerCarrier']['carrier_number'] == $carrierNumber)
				{
					if ($record['CustomerCarrier']['is_active'])
					{
						$isCarrierActive = true;
					}
					
					$carrierExists = true;
					break;
				}
				
				$carrierPointer = $record['CustomerCarrier']['next_record_pointer'];
			}
			
			// Not in CustomerCarrier chain
			if (!$carrierExists)
			{
				$this->message("{$carrierNumber} is not in CustomerCarrier");
				return false;
			}
			
			// Warn user when setting a non-active carrier
			if (!$isCarrierActive)
			{
				$this->message("{$carrierNumber} is not active");
			}
			
			$invoice = $this->Invoice->find('first', array(
				'contain' => array(),
				'fields' => array(
					'carrier_1_code',
					'carrier_2_code',
					'carrier_3_code'
				),
				'conditions' => array(
					'account_number' => $accountNumber,
					'invoice_number' => $invoiceNumber
				)
			));
			
			// No need to add if new carrier already belongs to the invoice
			if (in_array($carrierNumber, $invoice['Invoice']))
			{
				$this->message("{$carrierNumber} was already on invoice {$invoiceNumber}");
				return false;
			}
			
			return true;
		}
		
		/**
		 * Carrier can be changed on transaction if the following rules are met:
		 *   - Carrier exists in CustomerCarrier chain
		 *   - Carrier already exists on Invoice or has room to be added
		 * @param string $accountNumber The account number of the Customer.
		 * @param int $transactionID The ID of the transaction record.
		 * @param string $carrierNumber The carrier number to change to.
		 * @return bool Indicates if carrier should be changed. 
		 */
		function changeTransactionCarrier($accountNumber, $transactionID, $carrierNumber)
		{
			$isCarrierActive = false;
			$carrierExists = false;
			$addedToInvoice = false;
			
			$carrierPointer = $this->Customer->field('carrier_pointer', array('account_number' => $accountNumber));
			
			while ($carrierPointer != 0)
			{
				$record = $this->CustomerCarrier->find('first', array(
					'contain' => array(),
					'fields' => array(
						'carrier_number',
						'is_active',
						'next_record_pointer'
					),
					'conditions' => array('id' => $carrierPointer)
				));
				
				if ($record['CustomerCarrier']['carrier_number'] == $carrierNumber)
				{
					if ($record['CustomerCarrier']['is_active'])
					{
						$isCarrierActive = true;
					}
					
					$carrierExists = true;
					break;
				}
				
				$carrierPointer = $record['CustomerCarrier']['next_record_pointer'];
			}
			
			// Not in CustomerCarrier chain
			if (!$carrierExists)
			{
				$this->message("{$carrierNumber} is not in CustomerCarrier");
				return false;
			}
			
			// Warn user when setting a non-active carrier
			if (!$isCarrierActive)
			{
				$this->message("{$carrierNumber} is not active");
			}
			
			$invoiceNumber = $this->Transaction->field('invoice_number', array('id' => $transactionID));
			
			$invoice = $this->Invoice->find('first', array(
				'contain' => array(),
				'fields' => array(
					'carrier_1_code',
					'carrier_2_code',
					'carrier_3_code'
				),
				'conditions' => array(
					'account_number' => $accountNumber,
					'invoice_number' => $invoiceNumber
				)
			));
			
			if (in_array($carrierNumber, $invoice['Invoice']))
			{
				return true;
			}
			
			// If there is space to add the carrier to the invoice, we need to do so
			for ($i = 1; $i <= 3; $i++)
			{
				if ($invoice['Invoice']["carrier_{$i}_code"] == '')
				{
					$invoice['Invoice']["carrier_{$i}_code"] = $carrierNumber;
					
					$this->Invoice->create();
					$this->Invoice->save($invoice);
					
					$addedToInvoice = true;
					break;
				}
			}
			
			if (!$addedToInvoice)
			{
				$this->message("No room to add {$carrierNumber} to invoice {$invoiceNumber}");
				return false;
			}
			
			return true;
		}
		
		/**
		 * Adjust transaction balances.
		 * @param string $accountNumber The account number to sort.
		 * @param bool $includeInvoices Determines whether just transactions are sorted.
		 */
		function sortChains($accountNumber, $includeInvoices = false)
		{
			if ($includeInvoices)
			{
				$invoicePointer = $this->Customer->field('invoice_pointer', array(
					'account_number' => $accountNumber
				));
				
				$invoices = array();
				
				// Walk the invoice chain to build array for sorting
				while ($invoicePointer != 0)
				{
					$record = $this->Invoice->find('first', array(
						'contain' => array(),
						'fields' => array(
							'date_of_service',
							'next_record_pointer'
						),
						'conditions' => array('id' => $invoicePointer)
					));
					
					if ($record === false)
					{
						$this->message("Invoice ID# {$invoicePointer} could not be found.", true);
					}
					
					$invoices[$invoicePointer] = databaseDate($record['Invoice']['date_of_service']) . str_pad($invoicePointer, 10, "0", STR_PAD_LEFT);
					
					$invoicePointer = $record['Invoice']['next_record_pointer'];
				}
				
				asort($invoices);
				
				$nextRecord = 0;
				
				// Update individual records to create sorted chain
				foreach ($invoices as $key => $scrap)
				{
					$saveData['Invoice'] = array(
						'id' => $key,
						'next_record_pointer' => $nextRecord
					);
					
					$this->Invoice->create();
					$this->Invoice->save($saveData);
					
					$nextRecord = $key;
				}
				
				// Set the start of the chain from the customer record
				$saveData['Customer'] = array(
					'id' => $this->Customer->field('id', array('account_number' => $accountNumber)),
					'invoice_pointer' => $nextRecord
				);
				
				$this->Customer->create();
				$this->Customer->save($saveData);
			}
			
			$transactionPointer = $this->Customer->field('transaction_pointer', array(
				'account_number' => $accountNumber
			));
			
			$transactions = array();
			
			// Walk the transaction chain to create array for sorting
			while ($transactionPointer != 0)
			{
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'transaction_date_of_service',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				if ($record === false)
				{
					$this->message("Transaction ID# {$transactionPointer} could not be found.", true);
				}
				
				$transactions[$transactionPointer] = databaseDate($record['Transaction']['transaction_date_of_service']) . str_pad($transactionPointer, 10, "0", STR_PAD_LEFT);
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			asort($transactions);
			
			$nextRecord = 0;
			
			// Update individual records to create sorted chain
			foreach ($transactions as $key => $scrap)
			{
				$saveData['Transaction'] = array(
					'id' => $key,
					'next_record_pointer' => $nextRecord
				);
				
				$this->Transaction->create();
				$this->Transaction->save($saveData);
				
				$nextRecord = $key;
			}
			
			// Set the start of chain from the customer record
			$saveData['Customer'] = array(
				'id' => $this->Customer->field('id', array('account_number' => $accountNumber)),
				'transaction_pointer' => $nextRecord
			);
			
			$this->Customer->create();
			$this->Customer->save($saveData);
		}
		
		/**
		 * Adjust balances.
		 * @param string $accountNumber The account number to balance.
		 */
		function balanceChains($accountNumber)
		{
			$accountBalance = 0;
			$carrierBalances = array();
			$invoiceBalances = array();
			
			$customerRecord = $this->Customer->find('first', array(
				'contain' => array(),
				'fields' => array(
					'carrier_pointer',
					'invoice_pointer',
					'transaction_pointer'
				),
				'conditions' => array('account_number' => $accountNumber)
			));
			
			$carrierPointer = $customerRecord['Customer']['carrier_pointer'];
			
			$customerCarriers = array();
			$totalCarriers = 0;
			$activeCarriers = 0;
			$activePrimary = 0;
			$activeSecondary = 0;
			$totalInvoices = 0;
			$totalTransactions = 0;
			
			while ($carrierPointer != 0)
			{
				$record = $this->CustomerCarrier->find('first', array(
					'contain' => array(),
					'fields' => array(
						'carrier_number',
						'carrier_type',
						'is_active',
						'next_record_pointer'
					),
					'conditions' => array('id' => $carrierPointer)
				));
				
				if (in_array($record['CustomerCarrier']['carrier_number']))
				{
					$this->message("Duplicate customer carrier {$record['CustomerCarrier']['carrier_number']}.");
				}
				
				if ($record['CustomerCarrier']['carrier_type'] == 'P' && !$record['CustomerCarrier']['is_active'])
				{
					$this->message("Account has non-active primary customer carrier.");
				}
				
				if ($record['CustomerCarrier']['carrier_type'] == 'S' && !$record['CustomerCarrier']['is_active'])
				{
					$this->message("Account has non-active secondary customer carrier.");
				}
				
				$customerCarriers[] = $record['CustomerCarrier']['carrier_number'];
				$totalCarriers++;
				
				if ($record['CustomerCarrier']['is_active'])
				{
					$activeCarriers++;
					
					if ($record['CustomerCarrier']['carrier_type'] == 'P')
					{
						$activePrimary++;
					}
					else if ($record['CustomerCarrier']['carrier_type'] == 'S')
					{
						$activeSecondary++;
					}
				}
				
				if ($activeCarriers > 3)
				{
					$this->message('More than 3 active customer carriers.');
				}
				
				if ($activePrimary > 1)
				{
					$this->message('More than 1 active primary customer carrier.');
				}
				
				if ($activeSecondary > 1)
				{
					$this->message('More than 1 active secondary customer carrier.');
				}
				
				if ($totalCarriers > 5)
				{
					$this->message('More than 5 customer carriers on account.');
				}
				
				$carrierPointer = $record['CustomerCarrier']['next_record_pointer'];
			}
			
			if ($activeSecondary > 0 && $activePrimary == 0)
			{
				$this->message('Secondary carrier without primary.');
			}
			
			if ($activeCarriers > 0 && $activePrimary == 0 && $activeSecondary == 0)
			{
				$this->message('Active N carrier without P and/or S.');
			}
			
			$transactionPointer = $customerRecord['Customer']['transaction_pointer'];
			
			// Loop through transactions to track running balances
			while ($transactionPointer != 0)
			{
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'amount',
						'transaction_type',
						'carrier_number',
						'invoice_number',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				$invoiceNumber = $record['Transaction']['invoice_number'];
				$carrierNumber = $record['Transaction']['carrier_number'];
				$adjustedAmount = round($this->TransactionType->getAdjustedAmount($record['Transaction']['amount'], $this->transactionTypes[$record['Transaction']['transaction_type']]), 2);
				
				// Add entry for new carrier if necessary
				if (!isset($carrierBalances[$carrierNumber]))
				{
					if (!in_array($carrierNumber, $customerCarriers))
					{
						$this->message("{$carrierNumber} from transaction not in CustomerCarrier.");
					}
					
					$carrierBalances[$carrierNumber] = 0;
				}
				
				// Add entry for new invoice if necessary
				if (!isset($invoiceBalances[$invoiceNumber]))
				{
					$invoiceBalances[$invoiceNumber] = array();
				}
				
				// Add entry for new invoice carrier if necessary
				if (!isset($invoiceBalances[$invoiceNumber][$carrierNumber]))
				{
					$invoiceBalances[$invoiceNumber][$carrierNumber] = 0;
				}
				
				// Track running balances
				$accountBalance = round($accountBalance + $adjustedAmount, 2);
				$carrierBalances[$carrierNumber] = round($carrierBalances[$carrierNumber] + $adjustedAmount, 2);
				$invoiceBalances[$invoiceNumber][$carrierNumber] = round($invoiceBalances[$invoiceNumber][$carrierNumber] + $adjustedAmount, 2);
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			$transactionPointer = $customerRecord['Customer']['transaction_pointer'];
			
			// Loop through transaction chain to set updated balances
			while ($transactionPointer != 0)
			{
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'amount',
						'transaction_type',
						'carrier_number',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				$totalTransactions++;
				
				$adjustedAmount = round($this->TransactionType->getAdjustedAmount($record['Transaction']['amount'], $this->transactionTypes[$record['Transaction']['transaction_type']]), 2);
				
				// Set updated running balances
				$record['Transaction']['account_balance'] = $accountBalance;
				$record['Transaction']['carrier_balance_due'] = $carrierBalances[$record['Transaction']['carrier_number']];
				
				$this->Transaction->create();
				$this->Transaction->save($record);
				
				// Unwind the balances for the next record in the chain
				$accountBalance = round($accountBalance - $adjustedAmount, 2);
				$carrierBalances[$record['Transaction']['carrier_number']] = round($carrierBalances[$record['Transaction']['carrier_number']] - $adjustedAmount, 2);
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			$invoicePointer = $customerRecord['Customer']['invoice_pointer'];
			
			// Loop through invoice chain to set updated balances
			while ($invoicePointer != 0)
			{
				$record = $this->Invoice->find('first', array(
					'contain' => array(),
					'fields' => array(
						'invoice_number',
						'carrier_1_code',
						'carrier_2_code',
						'carrier_3_code',
						'next_record_pointer'
					),
					'conditions' => array('id' => $invoicePointer)
				));
				
				$totalInvoices++;
				
				$invoiceNumber = $record['Invoice']['invoice_number'];
				
				for ($i = 1; $i <= 3; $i++)
				{
					if ($record['Invoice']["carrier_{$i}_code"] != '' && !isset($invoiceBalances[$invoiceNumber][$record['Invoice']["carrier_{$i}_code"]]))
					{
						$this->message("Carrier " . $record['Invoice']["carrier_{$i}_code"] . " from invoice {$invoiceNumber} has no transaction activity.");
						$record['Invoice']["carrier_{$i}_balance"] = 0;
					}
					else if ($record['Invoice']["carrier_{$i}_code"] == '' &&
						isset($invoiceBalances[$invoiceNumber][$record['Invoice']["carrier_{$i}_code"]]) &&
						$invoiceBalances[$invoiceNumber][$record['Invoice']["carrier_{$i}_code"]] != 0)
					{
						$this->message("Invoice {$invoiceNumber} carrier {$i} is blank but amount is non-zero.");
						$record['Invoice']["carrier_{$i}_balance"] = $invoiceBalances[$invoiceNumber][$record['Invoice']["carrier_{$i}_code"]];
					}
					else
					{
						$record['Invoice']["carrier_{$i}_balance"] = ifset($invoiceBalances[$invoiceNumber][$record['Invoice']["carrier_{$i}_code"]], 0);
					}
				}
				
				$this->Invoice->create();
				$this->Invoice->save($record);
				
				$invoicePointer = $record['Invoice']['next_record_pointer'];
			}
			
			if ($totalTransactions > 0 && $totalInvoices == 0)
			{
				$this->message("Account has transactions but no invoices");
			}
			else if ($totalInvoices > 0 && $totalTransactions == 0)
			{
				$this->message("Account has invoices but no transactions");
			}
		}
		
		/**
		 * Remove an invoice.
		 * @param int $invoiceID The ID of the invoice to delete.
		 */
		function deleteInvoice($invoiceID)
		{
			// Lookup the invoice
			$invoiceData = $this->Invoice->find('first', array(
				'contain' => array(),
				'fields' => array(
					'account_number',
					'invoice_number'
				),
				'conditions' => array('id' => $invoiceID)
			));
			
			if ($invoiceData === false)
			{
				$this->message("Invoice ID# {$invoiceID} could not be found.", true);
			}
			
			// Get start of chain from Customer record
			$transactionPointer = $this->Customer->field('transaction_pointer', array(
				'account_number' => $invoiceData['Invoice']['account_number']
			));
			
			// Delete all transactions for invoice in the chain
			while ($transactionPointer != 0)
			{
				// Pull information from record
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'invoice_number',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				if ($record === false)
				{
					$this->message("Transaction chain for {$invoiceData['Invoice']['account_number']} is broken.", true);
				}
				
				// Only delete matching transactions
				if ($record['Transaction']['invoice_number'] == $invoiceData['Invoice']['invoice_number'])
				{
					$this->Transaction->delete($transactionPointer);
				}
				
				// Advance to next record
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			// Delete invoice
			$this->Invoice->delete($invoiceID);
		}
		
		/**
		 * Remove a transaction.
		 * @param int $transactionID The ID of the transaction to delete.
		 */
		function deleteTransaction($transactionID)
		{
			// Delete transaction
			$this->Transaction->delete($transactionID);
		}
		
		/**
		 * Sort and balance account.
		 * @param string $accountNumber The account number to balance.
		 */
		function sortAndBalance($accountNumber)
		{
			// Log task, output is already being buffered before this function is called
			$this->processID = $this->Process->createProcess('Invoice / Transaction Utility', false);
			$processMessage = "Sorting and balancing account {$accountNumber}";
			$this->Logging->write($processMessage);
			$this->Process->updateProcess($this->processID, 0, $processMessage);
			
			// Lock the customer record
			if ($customerID === false || !$this->Customer->lock($this->customerID))
			{
				$this->message("Customer record {$accountNumber} could not be locked");
				$this->Process->updateProcess($this->processID, 0, $processMessage . ': LOCKING ISSUE');
				$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
				$this->_stop();
			}
			
			// Sort the invoice & transaction chains
			$this->sortChains($accountNumber, true);
			$this->Process->updateProcess($this->processID, 40, $processMessage);
			
			// Balance the invoice & transaction chains
			$this->balanceChains($accountNumber);
			$this->Process->updateProcess($this->processID, 90, $processMessage);
			
			$this->Logging->write('Finished');
			$completedText = $this->messages ? ': MESSAGES' : ': DONE';
			$this->Process->updateProcess($this->processID, 100, $processMessage . $completedText);
			$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
			$this->Customer->unlock($this->customerID);
		}
		
		/**
		 * Add a message to the ouput.
		 * @param string $msg The message to log.
		 * @param bool $halt Determines whether to continue execution.
		 */
		function message($msg, $halt = false)
		{
			$this->Logging->write($msg);
			$this->messages = true;
			
			if ($halt)
			{
				$this->Process->updateProcess($this->processID, 100, "ERROR: {$msg}");
				$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
				$this->Customer->unlock($this->customerID);
				$this->_stop();
			}
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>