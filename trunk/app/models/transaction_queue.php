<?php
	class TransactionQueue extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BW';
		
		var $actsAs = array(
			'Defraggable',
			'Lockable',
			'FormatDates',
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
		
		var $belongsTo = array(
			'Customer' => array(
				'foreignKey' => array('field' => 'account_number', 'parent_field' => 'account_number')
			)
		);
		
		/**
		 * Post a single record from the transaction queue to the transaction file and the invoice file.
		 * @param array $queueRecord Record to post from transaction queue.
		 * @param array $carrierRecord Customer Carrier record referenced by the transaction queue.
		 * @param array $invoiceRecord Invoice record referenced by the transaction queue.
		 * @param array $transactionType Transaction Type record referenced by the transaction queue.
		 * @param string $currentPostingPeriod Database-formatted current posting period date from Default File.
		 */
		function postTransaction($customerID, $queueRecord, $carrierRecord, $invoiceRecord, $transactionType, $currentPostingPeriod)
		{
			// Setup the necessary models
			$transactionTypeModel = ClassRegistry::init('TransactionType');
			$transactionModel = ClassRegistry::init('Transaction');
			$customerModel = ClassRegistry::init('Customer');
			$invoiceModel = ClassRegistry::init('Invoice');
			
			// Error if the customer cannot be locked
			if (!$customerModel->lock($customerID))
			{
				throw new Exception('Could not lock the customer');
			}
			
			// Error if date of service is blank
			if ($queueRecord['transaction_date_of_service'] == null)
			{
				$customerModel->unlock($customerID);
				throw new Exception('Service date is blank');
			}
			
			// Error if transaction type not valid
			if ($transactionType == null)
			{
				$customerModel->unlock($customerID);
				throw new Exception('Transaction type error');
			}
			
			// Adjust the transaction amount per the transaction type
			$adjustedAmount = $transactionTypeModel->getAdjustedAmount($queueRecord['amount'], $transactionType);
			
			// Default to purchase if not specified
			if (trim($queueRecord['rental_or_purchase']) == '')
			{
				$queueRecord['rental_or_purchase'] = 'P';
			}
			
			if ($invoiceRecord != null) // Update existing invoice
			{
				// Lock invoice record
				if (!$invoiceModel->lock($invoiceRecord['id']))
				{
					$customerModel->unlock($customerID);
					throw new Exception('Existing invoice could not be locked');
				}
				
				if ($adjustedAmount != 0)
				{
					// Add carrier to invoice if not already present
					if (!in_array($queueRecord['carrier_number'], array($invoiceRecord['carrier_1_code'], $invoiceRecord['carrier_2_code'], $invoiceRecord['carrier_3_code'])))
					{
						if (trim($invoiceRecord['carrier_1_code']) == '')
						{
							$invoiceRecord['carrier_1_code'] = $queueRecord['carrier_number'];
						}
						else if (trim($invoiceRecord['carrier_2_code']) == '')
						{
							$invoiceRecord['carrier_2_code'] = $queueRecord['carrier_number'];
						}
						else if (trim($invoiceRecord['carrier_3_code']) == '')
						{
							$invoiceRecord['carrier_3_code'] = $queueRecord['carrier_number'];
						}
						else
						{
							// Unlock invoice record before throwing error
							$invoiceModel->unlock($invoiceRecord['id']);
							$customerModel->unlock($customerID);
							throw new Exception("Invoice already has 3 carriers");
						}
					}
					
					// Update the appropriate carrier balance on the invoice
					if ($invoiceRecord['carrier_1_code'] == $queueRecord['carrier_number'])
					{
						$invoiceRecord['carrier_1_balance'] = round($invoiceRecord['carrier_1_balance'] + $adjustedAmount, 2);
					}
					else if ($invoiceRecord['carrier_2_code'] == $queueRecord['carrier_number'])
					{
						$invoiceRecord['carrier_2_balance'] = round($invoiceRecord['carrier_2_balance'] + $adjustedAmount, 2);
					}
					else if ($invoiceRecord['carrier_3_code'] == $queueRecord['carrier_number'])
					{
						$invoiceRecord['carrier_3_balance'] = round($invoiceRecord['carrier_3_balance'] + $adjustedAmount, 2);
					}
				}
				
				// Update the invoice information based on the transaction type
				if ($transactionType['TransactionType']['invoice_update_function_name'] != '')
				{
					$invoiceRecord = call_user_func(array($this, $transactionType['TransactionType']['invoice_update_function_name']), $queueRecord, $invoiceRecord, $adjustedAmount);
				}
				
				$invoiceRecord['account_balance'] = round($invoiceRecord['carrier_1_balance'] + $invoiceRecord['carrier_2_balance'] + $invoiceRecord['carrier_3_balance'], 2);
				
				$invoiceModel->create();
				$invoiceModel->save(array('Invoice' => $invoiceRecord));
								
				// Unlock invoice record
				$invoiceModel->unlock($invoiceRecord['id']);
			}
			else // Create new invoice
			{
				$invoiceRecord = array(				
					'invoice_number' 		=> $queueRecord['invoice_number'],
					'billing_date' 			=> $queueRecord['billing_date'],
					'date_of_service' 		=> databaseDate($queueRecord['transaction_date_of_service']),
					'account_number' 		=> $queueRecord['account_number'],
					'amount' 				=> ($transactionType['TransactionType']['is_amount_set_on_new_invoice']) ? $adjustedAmount : 0,
					'carrier_1_balance' 	=> $adjustedAmount,
					'carrier_2_code' 		=> '',
					'carrier_2_balance' 	=> 0,
					'carrier_3_code' 		=> '',
					'carrier_3_balance' 	=> 0,
					'posting_period_date' 	=> databaseDate($currentPostingPeriod),
					'creation_date' 		=> databaseDate($queueRecord['data_entry_date']),
					'rental_or_purchase' 	=> $queueRecord['rental_or_purchase'],
					'line_1_initials' 		=> $queueRecord['user_id'],
					'line_1_date' 			=> databaseDate($queueRecord['data_entry_date']),
					'profit_center_number' 	=> $queueRecord['profit_center_number']
				);
				
				if ($adjustedAmount != 0)
				{
					$invoiceRecord['carrier_1_code'] = $queueRecord['carrier_number'];
				}
				
				// Create the invoice information based on the transaction type
				if ($transactionType['TransactionType']['invoice_create_function_name'] != '')
				{
					// Array exists so that the function is referenced as a method of the current object
					$invoiceRecord = call_user_func(array($this, $transactionType['TransactionType']['invoice_create_function_name']), $invoiceRecord, $adjustedAmount);
				}
				
				// Set certain values based on whether the transaction on the new invoice is a payment
				if ($transactionType['TransactionType']['is_payment'])
				{
					$invoiceRecord['line_1_status'] = 'P';
				}
				else
				{
					$invoiceRecord['salesman_number'] = $queueRecord['salesman_number'];
					$invoiceRecord['department_code'] = $queueRecord['department_code'];
					$invoiceRecord['transaction_control_number'] = $queueRecord['transaction_control_number'];
					$invoiceRecord['transaction_control_number_file'] = $queueRecord['transaction_control_number_file'];
					
					// If amount is zero or the carrier is a patient paying 100%, set special status
					if ($adjustedAmount == 0 || ($carrierRecord['carrier_group_code'] == 'PAT' && $carrierRecord['gross_charge_percentage'] == 0))
					{
						$invoiceRecord['line_1_status'] = 'B';
					}
					else
					{
						$invoiceRecord['line_1_status'] = 'C';
					}
				}
				
				$invoiceModel->addToChain($customerID, array('Invoice' => $invoiceRecord));
			}
			
			// Insert transaction
			$transactionRecord = array(
				'invoice_number' 					=> $queueRecord['invoice_number'],
				'transaction_date_of_service' 		=> databaseDate($queueRecord['transaction_date_of_service']),
				'general_ledger_description' 		=> $queueRecord['general_ledger_description'],
				'amount' 							=> round($queueRecord['amount'], 2),
				'general_ledger_code' 				=> $queueRecord['general_ledger_code'],
				'transaction_type' 					=> $queueRecord['transaction_type'],
				'carrier_number' 					=> $queueRecord['carrier_number'],
				'account_number' 					=> $queueRecord['account_number'],
				'quantity' 							=> $queueRecord['quantity'],
				'inventory_number' 					=> $queueRecord['inventory_number'],
				'inventory_description' 			=> ($transactionType['TransactionType']['is_amount_subtracted']) ? $queueRecord['general_ledger_description'] : $queueRecord['inventory_description'],
				'healthcare_procedure_code' 		=> $queueRecord['healthcare_procedure_code'],
				'inventory_group_code' 				=> $queueRecord['inventory_group_code'],
				'profit_center_number' 				=> $queueRecord['profit_center_number'],
				'salesman_number' 					=> $queueRecord['salesman_number'],
				'department_code' 					=> $queueRecord['department_code'],
				'unique_identification_number'	 	=> $queueRecord['unique_identification_number'],
				'physical_creation_date' 			=> databaseDate($queueRecord['data_entry_date']),
				'period_posting_date' 				=> $currentPostingPeriod,
				'transaction_control_number' 		=> $queueRecord['transaction_control_number'],
				'transaction_control_number_file'	=> $queueRecord['transaction_control_number_file'],
				'rental_or_purchase' 				=> $queueRecord['rental_or_purchase'],
				'serial_number' 					=> $queueRecord['serial_number'],
				'cash_reference_number'				=> $queueRecord['cash_reference_number']
			);
						
			$result = $transactionModel->addToChain($customerID, array('Transaction' => $transactionRecord));
						
			$previousAccountBalance = 0;
			$previousCarrierBalance = 0;
						
			// Update account_balance & carrier_balance_due for the newly added record
			// Note that the account & carrier balances are NOT invoice specific
			if ($result['after'] !== false)
			{
				$previousAccountBalance = $transactionModel->field('account_balance', array('id' => $result['after']));
				
				$next = $result['after'];
				while ($next != 0)
				{
					$nextTransaction = $transactionModel->find('first', array(
						'contain' => array(),
						'fields' => array(
							'next_record_pointer',
							'carrier_number',
							'carrier_balance_due'
						),
						'conditions' => array('id' => $next)
					));
					
					if ($nextTransaction['Transaction']['carrier_number'] == $queueRecord['carrier_number'])
					{
						$previousCarrierBalance = $nextTransaction['Transaction']['carrier_balance_due'];
						break;
					}
					else
					{
						$next = $nextTransaction['Transaction']['next_record_pointer'];
					}
				}
			}
			
			$transactionModel->create();
			$transactionModel->save(array('Transaction' => array(
				'id' => $result['current'],
				'account_balance' => round($previousAccountBalance + $adjustedAmount, 2),
				'carrier_balance_due' => round($previousCarrierBalance + $adjustedAmount, 2)
			)));

			//now, for all transactions that are before the transaction we just inserted into the chain (those that have a
			//more recent DOS - the Transaction chain is stored in DOS desc order), add the adjusted amount to the account 
			//balance, and if the carrier matches, add it to the carrier balance due as well.
			$pointer = $customerModel->field('transaction_pointer', array('account_number' => $queueRecord['account_number']));
			
			while ($pointer != $result['current'])
			{
				$entry = $transactionModel->find('first', array(
					'fields' => array(
						'id',
						'carrier_number',
						'account_balance',
						'carrier_balance_due',
						'next_record_pointer'
					),
					'conditions' => array(
						'id' => $pointer
					),
					'contain' => array()
				));
				
				if ($entry['Transaction']['carrier_number'] == $queueRecord['carrier_number'])
				{
					$entry['Transaction']['carrier_balance_due'] += $adjustedAmount;
				}
				
				$entry['Transaction']['account_balance'] += $adjustedAmount;
				
				$transactionModel->create();
				$transactionModel->save($entry);
					
				$pointer = $entry['Transaction']['next_record_pointer'];
			}
			
			$customerModel->unlock($customerID);
			return;
		}
		
		/**
		 * Create suggested credits after batch posting transactions.
		 * @param array
		 */
		function createSuggestedCredits($data)
		{
			$customerModel = ClassRegistry::init('Customer');
			$transactionTypeModel = ClassRegistry::init('TransactionType');
			$generalLedgerModel = ClassRegistry::init('GeneralLedger');
			
			$currentDate = date('Y-m-d');
			
			// Structure: array[account#][invoice#][carrier#]
			foreach ($data as $accountNumber => $accountData)
			{
				$customerID = $customerModel->field('id', array('account_number' => $accountNumber));
				
				if (!$customerModel->lock($customerID))
				{
					//throw new Exception("Suggested Credit Error. Cannot lock account. (Acct#: {$accountNumber})");
					continue;
				}
				
				foreach ($accountData as $invoiceNumber => $invoiceData)
				{
					$chargeTransactionType = $transactionTypeModel->field('code', array('description' => 'Charge'));
					
					// Find account & invoice
					$result = $customerModel->find('first', array(
						'contain' => array(),
						'conditions' => array('account_number' => $accountNumber),
						'chains' => array(
							'Invoice' => array(
								'contain' => array(),
								'conditions' => array('invoice_number' => $invoiceNumber),
								'limit' => 1,
								'required' => false
							),
							'Transaction' => array(
								'contain' => array(),
								'conditions' => array(
									'invoice_number' => $invoiceNumber,
									'transaction_type' => $chargeTransactionType
								),
								'limit' => 1,
								'required' => false
							)
						)
					));
					
					if ($result === false)
					{
						//throw new Exception("Suggested Credit Error. Cannot find account. (Acct#: {$accountNumber})");
						break; // Break out of the invoice loop & back to the account loop
					}
					
					if (!isset($result['Invoice'][0]))
					{
						//throw new Exception("Suggested Credit Error. Cannot find invoice. (Acct#: {$accountNumber}, Invoice#: {$invoiceNumber})");
						continue; // Skip this invoice
					}
					
					if (!isset($result['Transaction'][0]))
					{
						//throw new Exception("Suggested Credit Error. Cannot find charge transaction. (Acct#: {$accountNumber}, Invoice#: {$invoiceNumber})");
						continue; // Skip this invoice
					}
					
					// Determine whether rental or purchase based on GL code
					$invoiceType = $generalLedgerModel->determineInvoiceType($result['Transaction'][0]['general_ledger_code']);
					
					if ($invoiceType == 'purchase')
					{
						$generalLedgerCode = '3304';
						$rentalOrPurchase = 'P';
					}
					else if ($invoiceType == 'rental')
					{
						$generalLedgerCode = '3512';
						$rentalOrPurchase = 'R';
					}
					else
					{
						//throw new Exception("Suggested Credit Error. Cannot determine if invoice is sale or rental. (Acct#: {$accountNumber}, Invoice#: {$invoiceNumber})");
						continue; // Skip this invoice
					}
					
					foreach ($invoiceData as $carrierNumber => $row)
					{
						// Lookup carrier balance from invoice
						if ($result['Invoice'][0]['carrier_1_code'] == $carrierNumber)
						{
							$amount = $result['Invoice'][0]['carrier_1_balance'];
						}
						else if ($result['Invoice'][0]['carrier_2_code'] == $carrierNumber)
						{
							$amount = $result['Invoice'][0]['carrier_2_balance'];
						}
						else if ($result['Invoice'][0]['carrier_3_code'] == $carrierNumber)
						{
							$amount = $result['Invoice'][0]['carrier_3_balance'];
						}
						else
						{
							$amount = 0;
						}
						
						// If balance != zero, create suggested credit record in TransactionQueue
						if ($amount == 0)
						{
							continue;
						}
						
						$suggestedData['TransactionQueue'] = array(
							'account_number' => $accountNumber,
							'transaction_date_of_service' => $currentDate,
							'general_ledger_description' => 'Computer Generated Credit',
							'amount' => $amount,
							'general_ledger_code' => $generalLedgerCode,
							'transaction_type' => 3,
							'carrier_number' => $carrierNumber,
							'invoice_number' => $invoiceNumber,
							'billing_date' => $currentDate,
							'post_status' => 'Z',
							'quantity' => 1,
							'unique_identification_number' => $invoiceNumber,
							'rental_or_purchase' => $rentalOrPurchase,
							'data_entry_date' => $currentDate,
							'user_id' => 'zzz',
							'created_by' => 'zzz'
						);
						
						$this->create();
						$this->save($suggestedData);
					}
				}
				
				$customerModel->unlock($customerID);
			}
		}
		
		/**
		 * Calculates the current total amount of all transactions in the queue for a given invoice and carrier.
		 * @param string $invoiceNumber The invoice number to search for.
		 * @param string $carrierNumber The carrier number to search for.
		 * @return numeric The total amount of all of the transactions in the queue for the specified invoice and carrier.
		 */
		function currentQueueTotal($invoiceNumber, $carrierNumber)
		{
			$transactions = $this->find('all', array(
				'fields' => array('amount', 'transaction_type'),
				'conditions' => array(
					'invoice_number' => $invoiceNumber,
					'carrier_number' => $carrierNumber
				),
				'contain' => array()
			));
			
			$types = ClassRegistry::init('TransactionType')->find('all', array(
				'fields' => array('code', 'is_amount_subtracted'),
				'contain' => array()
			));
			
			$isSubtracted = Set::combine($types, '{n}.TransactionType.code', '{n}.TransactionType.is_amount_subtracted');
			$total = 0;
			
			//notice the hard casts to floats here - that's to fix up potential bad data (leading spaces and period amounts)
			//in the queue that can sometimes happen. Hard-casting allows them to correctly be used in calculations instead
			//of being coersed to zero.
			foreach ($transactions as $transaction)
			{
				$total += ($isSubtracted[$transaction['TransactionQueue']['transaction_type']]) ? (float)$transaction['TransactionQueue']['amount'] * -1 : (float)$transaction['TransactionQueue']['amount'];
			}
			
			return $total;
		}
		
		/**
		 * Makes a transaction queue entry that will result in a zero'ed out balance on an invoice for the particular
		 * carrier.
		 * @param string $invoiceNumber The number of the invoice to use.
		 * @param string $carrierNumber The number of the carrier to use.
		 * @param string $dateOfServiceDate The date of service to use on the transaction.
		 * @param string $dataEntryDate The data entry date to use on the transaction.
		 * @param string $cashReferenceNumber The cash reference number to use on the transaction.
		 * @param string $createdBy The username of the user that should be credited with the creation of the transaction.
		 * @return True if successful, false otherwise.
		 */
		function adjustRemainingInvoiceBalanceToZero($invoiceNumber, $carrierNumber, $dateOfServiceDate, $dataEntryDate, $cashReferenceNumber, $createdBy)
		{
			$invoiceModel = ClassRegistry::init('Invoice');
			
			//grab the invoice
			$invoice = $invoiceModel->find('first', array(
				'fields' => array(
					'account_number',
					'salesman_number',
					'profit_center_number',
					'department_code',
					'transaction_control_number', 
					'transaction_control_number_file', 
					'rental_or_purchase'
				),
				'conditions' => array('invoice_number' => $invoiceNumber),
				'contain' => array()
			));
			
			//figure out the remaining balance on the invoice
			$remaining = $invoiceModel->currentPendingBalance($invoiceNumber, $carrierNumber);
					
			if ($remaining != 0)
			{
				//go grab the credit transaction type code
				$transactionType = ClassRegistry::init('TransactionType')->field('code', array(
					'id' => ClassRegistry::init('Setting')->get('credit_transaction_type_id')
				));
				
				//determine the GL code
				$glModel = ClassRegistry::init('GeneralLedger');
				$glCode = $glModel->determineGLCodeForInvoice($invoiceNumber, $carrierNumber, $transactionType);
				$glDescription = '';
				
				if ($glCode != null)
				{
					$glDescription = $glModel->field('description', array('general_ledger_code' => $glCode));
				}
				
				$this->create();
				
				//coersce return value to a boolean value
				return !!$this->save(array(
					'account_number' => $invoice['Invoice']['account_number'],
					'transaction_date_of_service' => databaseDate($dateOfServiceDate),
					'general_ledger_description' => 'AUTOMATIC CREDIT POSTING',
					'amount' => $remaining,
					'general_ledger_code' => $glCode,
					'transaction_type' => $transactionType, 
					'carrier_number' => $carrierNumber,
					'invoice_number' => $invoiceNumber,
					'billing_date' => databaseDate($dataEntryDate),
					'salesman_number' => $invoice['Invoice']['salesman_number'],
					'cost_of_sales_pointer' => '0',
					'post_status' => 'A',
					'profit_center_number' => $invoice['Invoice']['profit_center_number'],
					'department_code' => $invoice['Invoice']['department_code'],
					'transaction_control_number_file' => $invoice['Invoice']['transaction_control_number_file'],
					'transaction_control_number' => $invoice['Invoice']['transaction_control_number'],
					'rental_or_purchase' => $invoice['Invoice']['rental_or_purchase'],
					'data_entry_date' => databaseDate($dataEntryDate),
					'cash_reference_number' => $cashReferenceNumber,
					'user_id' => $createdBy,
					'created_by' => $createdBy
				));
			}
			
			return true;
		}
		
		/**
		 *
		 */
		function _updateInvoiceForCharge($queueRecord, $invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['amount'] = round($invoiceRecord['amount'] + $adjustedAmount, 2);
			$invoiceRecord['billing_date'] = databaseDate($queueRecord['billing_date']);
			$invoiceRecord['date_of_service'] = databaseDate($queueRecord['transaction_date_of_service']);
			$invoiceRecord['salesman_number'] = $queueRecord['salesman_number'];
			$invoiceRecord['department_code'] = $queueRecord['department_code'];
			$invoiceRecord['transaction_control_number'] = $queueRecord['transaction_control_number'];
			$invoiceRecord['transaction_control_number_file'] = $queueRecord['transaction_control_number_file'];
			$invoiceRecord['line_1_status'] = 'C';
			$invoiceRecord['line_1_initials'] = $queueRecord['user_id'];
			$invoiceRecord['line_1_date'] = databaseDate($queueRecord['data_entry_date']);
			
			return $invoiceRecord;
		}
		
		/**
		 *
		 */
		function _updateInvoiceForPayment($queueRecord, $invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['payments'] = round($invoiceRecord['payments'] + $adjustedAmount, 2);
			
			return $invoiceRecord;
		}
		
		/**
		 *
		 */
		function _updateInvoiceForCredit($queueRecord, $invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['credits'] = round($invoiceRecord['credits'] + $adjustedAmount, 2);
			
			return $invoiceRecord;
		}
		
		/**
		 *
		 */
		function _updateInvoiceForTransfer($queueRecord, $invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['move_balances'] = round($invoiceRecord['move_balances'] + $adjustedAmount, 2);
			
			return $invoiceRecord;
		}
		
		/**
		 *
		 */
		function _createInvoiceForPayment($invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['payments'] = $adjustedAmount;
			
			return $invoiceRecord;
		}
		
		/**
		 *
		 */
		function _createInvoiceForCredit($invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['credits'] = $adjustedAmount;
			
			return $invoiceRecord;
		}
		
		/**
		 * 
		 */
		function _createInvoiceForTransfer($invoiceRecord, $adjustedAmount)
		{
			$invoiceRecord['move_balances'] = $adjustedAmount;
			
			return $invoiceRecord;
		}
		
	}
?>