<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * This program was never fully finished to work as quickly as necessary. Now
	 * that eMRS is running on Linux with a newer version of filePro, the 2 gigabyte
	 * file limit is no longer an issue. It seems that we will no longer need this.
	 */
	class PurgeTransactionShell extends Shell 
	{
		var $tasks = array('ReportParameters', 'Logging');
		
		var $uses = array(
			'Customer',
			'Invoice',
			'InvoiceTemp',
			'InvoiceArchive',
			'Transaction',
			'TransactionTemp',
			'TransactionArchive'
		);
		
		var $parameters = array(
			array(
				'type' => 'string',
				'model' => 'Invoice',
				'field' => 'start_date',
				'flag' => 'start',
				'description' => 'Starting date of service for invoices to keep',
				'required' => true
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			die('Not necessary. See notes in code.');
			
			$this->Logging->write('STARTED');
			
			// Get a reference to the driver
			$db = ConnectionManager::getDataSource($this->TransactionTemp->useDbConfig);
			
			// Use the driver to find the schemas for the files
			$schemas['TransactionTemp'] = $db->describe($this->TransactionTemp, 'all');
			$schemas['TransactionArcive'] = $db->describe($this->TransactionArchive, 'all');
			$schemas['InvoiceTemp'] = $db->describe($this->InvoiceTemp, 'all');
			$schemas['InvoiceArchive'] = $db->describe($this->InvoiceArchive, 'all');
			
			// Open the files for appending and truncate them
			$transactionTempFd = dio_open($schemas['TransactionTemp']['data_path'], O_WRONLY | O_APPEND | O_TRUNC);
			$transactionArchiveFd = dio_open($schemas['TransactionArcive']['data_path'], O_WRONLY | O_APPEND | O_TRUNC);
			$invoiceTempFd = dio_open($schemas['InvoiceTemp']['data_path'], O_WRONLY | O_APPEND | O_TRUNC);
			$invoiceArchiveFd = dio_open($schemas['InvoiceArchive']['data_path'], O_WRONLY | O_APPEND | O_TRUNC);
			
			// Parse parameters
			$data = $this->ReportParameters->parse($this->parameters);
			$data['Invoice']['start_date'] = databaseDate($data['Invoice']['start_date']);
			
			// Setup information for customer iteration
			$batchSize = 1;
			$currentOffset = 0;
			$remainingCustomers = $this->Customer->find('count');
			
			// Loop until we have iterated over all of the customers
			while ($remainingCustomers > 0)
			{
				$this->Logging->write('Remaining Customers: ' . $remainingCustomers);
				
				// Get next batch of customers along with information for non-archived invoices
				unset($customers);
				$customers = $this->Customer->find('all', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'account_number',
						'invoice_pointer',
						'transaction_pointer'
					),
					'conditions' => array(
						'id >' => $currentOffset
					),
					'chains' => array(
						'Invoice' => array(
							'contain' => array(),
							'required' => false
						)
					),
					'limit' => $batchSize
				));
				
				if ($customers !== false)
				{
					foreach ($customers as $customer)
					{
						$nextInvoice = $customer['Customer']['invoice_pointer'];
						$nextTransaction = $customer['Customer']['transaction_pointer'];
						unset($previousTransaction);
						unset($previousInvoice);
						unset($keepInvoices);
						$keepInvoices = array();
						
						// Clear the pointers
						$customer['Customer']['transaction_pointer'] = 0;
						$customer['Customer']['invoice_pointer'] = 0;
						$this->Customer->create();
						$this->Customer->save($customer);
						
						// Rewrite invoices for customer and make array of invoice numbers to keep
						foreach ($customer['Invoice'] as $invoice)
						{
							if ($invoice['date_of_service'] >= $data['Invoice']['start_date'])
							{
								$keepInvoices[] = $invoice['invoice_number'];
								
								// Write to InvoiceTemp
								$saveData['InvoiceTemp'] = $invoice;
								unset($saveData['InvoiceTemp']['id']);
								$saveData['InvoiceTemp']['next_record_pointer'] = 0;
								
								// Insert the new record to the file
								$buffer = $db->_createRecordBuffer($this->InvoiceTemp, array_keys($saveData['InvoiceTemp']), array_values($saveData['InvoiceTemp']));
								dio_write($invoiceTempFd, $buffer);
								
								// Figure out what record we inserted
								$position = dio_seek($invoiceTempFd, 0, SEEK_CUR);
								$currentInvoice = $position / $schemas['InvoiceTemp']['record_length'];
								
								if (!isset($previousInvoice))
								{
									// Set the invoice pointer
									$customer['Customer']['invoice_pointer'] = $currentInvoice;
									$this->Customer->create();
									$this->Customer->save($customer);
								}
								else
								{
									$lastInvoice['InvoiceTemp'] = array(
										'id' => $previousInvoice,
										'next_record_pointer' => $currentInvoice
									);
									
									$this->InvoiceTemp->create();
									$this->InvoiceTemp->save($lastInvoice);
								}
								
								$previousInvoice = $currentInvoice;
							}
							else
							{
								// Write to InvoiceArchive
								$saveData['InvoiceArchive'] = $invoice;
								unset($saveData['InvoiceArchive']['id']);
								
								// Insert the new record to the file
								$buffer = $db->_createRecordBuffer($this->InvoiceArchive, array_keys($saveData['InvoiceArchive']), array_values($saveData['InvoiceArchive']));
								dio_write($invoiceArchiveFd, $buffer);
							}
						}
						
						while ($nextTransaction != 0)
						{
							unset($transaction);
							$transaction = $this->Transaction->find('first', array(
								'contain' => array(),
								'conditions' => array('id' => $nextTransaction)
							));
							
							if ($transaction === false)
							{
								$this->err("ERROR: Transaction chain broken for {$customer['Customer']['account_number']} for transaction id: {$nextTransaction}");
								break;
							}
							
							// Set the pointer for the next transaction
							$nextTransaction = $transaction['Transaction']['next_record_pointer'];
							
							// Save the transaction to either the archive or new transaction file
							if (in_array($transaction['Transaction']['invoice_number'], $keepInvoices))
							{
								// Write to TransactionTemp
								unset($transaction['Transaction']['id']);
								$transaction['Transaction']['next_record_pointer'] = 0;
								$transaction['TransactionTemp'] = $transaction['Transaction'];
								unset($transaction['Transaction']);
								
								// Insert the new record to the file
								$buffer = $db->_createRecordBuffer($this->TransactionTemp, array_keys($transaction['TransactionTemp']), array_values($transaction['TransactionTemp']));
								dio_write($transactionTempFd, $buffer);
								
								// Figure out what record we inserted
								$position = dio_seek($transactionTempFd, 0, SEEK_CUR);
								$currentTransaction = $position / $schemas['TransactionTemp']['record_length'];
								
								if (!isset($previousTransaction))
								{
									// Set the transaction pointer
									$customer['Customer']['transaction_pointer'] = $currentTransaction;
									$this->Customer->create();
									$this->Customer->save($customer);
								}
								else
								{
									$saveData['TransactionTemp'] = array(
										'id' => $previousTransaction,
										'next_record_pointer' => $currentTransaction
									);
									
									$this->TransactionTemp->create();
									$this->TransactionTemp->save($saveData);
								}
								
								$previousTransaction = $currentTransaction;
							}
							else
							{
								// Write to TransactionArchive
								unset($transaction['Transaction']['id']);
								$transaction['TransactionArchive'] = $transaction['Transaction'];
								unset($transaction['Transaction']);
								
								// Insert the new record to the file
								$buffer = $db->_createRecordBuffer($this->TransactionArchive, array_keys($transaction['TransactionArchive']), array_values($transaction['TransactionArchive']));
								dio_write($transactionArchiveFd, $buffer);
							}
						} // End of transaction chain iteration
						
						$currentOffset = $customer['Customer']['id'];
						$remainingCustomers--;
					} // End of customer batch loop
				}
			} // End of customer loop
			
			// Close the file descriptors
			dio_close($transactionTempFd);
			dio_close($transactionArchiveFd);
			dio_close($invoiceTempFd);
			dio_close($invoiceArchiveFd);
			
			$this->Logging->writeElapsedTime();
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup()
		{
			$this->Logging->startTimer();
			$this->Logging->setLogFile('purge_transactions');
		}
	}
?>