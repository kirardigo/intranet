<?php
	class TransactionQueueController extends AppController
	{
		var $uses = array(
			'TransactionQueue', 
			'Lookup', 
			'TransactionType', 
			'Invoice', 
			'Setting', 
			'BatchPostLog', 
			'FileNote', 
			'Customer'
		);
		
		var $helpers = array('Ajax', 'Paginator');
		
		/**
		 * Purge the transaction queue.
		 */
		function purge()
		{
			$this->pageTitle = 'Purge Transaction Queue';
			
			// Only launch the purge if the user submits the form
			if (isset($this->data))
			{
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake purge_transaction_queue -impersonate %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user'))
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
		}
		
		/**
		 * Main action for the post transactions screen.
		 */
		function postTransactions()
		{
			$this->pageTitle = 'Post Transactions';
			
			//grab lookups
			$this->set('banks', $this->Lookup->get('banks', true, true));
			$this->set('transactionTypes', $this->TransactionType->find('all', array('fields' => array('code', 'description', 'is_payment', 'is_amount_subtracted'), 'contain' => array())));
			$this->set('chargeTransactionType', $this->TransactionType->field('code', array('id' => $this->Setting->get('charge_transaction_type_id'))));
		}
		
		/**
		 * Handles the posting of the transactions from the postTransactions action.
		 *
		 * The JSON response will contain an array called 'success' that contains true or false
		 * for each transaction that was posted that states whether or not the transaction succeeded.
		 */
		function json_postTransactions()
		{
			$success = array();
			
			$createdBy = $this->Session->read('user');
			$dateEntered = date('Y-m-d');
			
			//grab payment transaction types
			$paymentTransactionTypes = Set::extract('/TransactionType/code', $this->TransactionType->find('all', array(
				'fields' => array('code'),
				'conditions' => array('is_payment' => 1),
				'contain' => array()
			)));
			
			//grab the credit transaction type code
			$creditTransactionType = $this->TransactionType->field('code', array(
				'id' => $this->Setting->get('credit_transaction_type_id')
			));
			
			//grab the profit center for the customer
			$profitCenter = $this->Customer->field('profit_center_number', array('account_number' => $this->data['Customer']['account_number']));
			
			//go through each posted transaction
			foreach ($this->data['TransactionQueue']['invoice_number'] as $i => $invoiceNumber)
			{
				//grab the associated invoice if we have one
				$invoice = $this->Invoice->find('first', array(
					'fields' => array(
						'salesman_number',
						'department_code',
						'transaction_control_number', 
						'transaction_control_number_file', 
						'rental_or_purchase'
					),
					'conditions' => array('invoice_number' => $invoiceNumber),
					'contain' => array()
				));
				
				//bank numbers are only applied to payments
				$bankNumber = in_array($this->data['TransactionQueue']['transaction_type'][$i], $paymentTransactionTypes) ? $this->data['TransactionQueue']['bank_number'] : '';
				
				$this->TransactionQueue->create();
				
				//keep track of the success or failure of each transaction				
				$success[] = !!$this->TransactionQueue->save(array(
					'account_number' => $this->data['Customer']['account_number'],
					'transaction_date_of_service' => databaseDate($this->data['TransactionQueue']['transaction_date_of_service']),
					'general_ledger_description' => $this->data['TransactionQueue']['general_ledger_description'][$i],
					'amount' => $this->data['TransactionQueue']['amount'][$i],
					'general_ledger_code' => $this->data['TransactionQueue']['general_ledger_code'][$i],
					'transaction_type' => $this->data['TransactionQueue']['transaction_type'][$i],
					'carrier_number' => $this->data['TransactionQueue']['carrier_number'][$i],
					'invoice_number' => $invoiceNumber,
					'billing_date' => $dateEntered,
					'salesman_number' => $invoice !== false ? $invoice['Invoice']['salesman_number'] : '',
					'cost_of_sales_pointer' => '0',
					'post_status' => 'R',
					'bank_number' => $bankNumber,
					'profit_center_number' => $profitCenter,
					'department_code' => $invoice !== false ? $invoice['Invoice']['department_code'] : '',
					'transaction_control_number_file' => $invoice !== false ? $invoice['Invoice']['transaction_control_number_file'] : '',
					'transaction_control_number' => $invoice !== false ? $invoice['Invoice']['transaction_control_number'] : '',
					'rental_or_purchase' => $invoice !== false ? $invoice['Invoice']['rental_or_purchase'] : '',
					'data_entry_date' => $dateEntered,
					'cash_reference_number' => $this->data['TransactionQueue']['cash_reference_number'],
					'created' => date('Y-m-d'),
					'user_id' => $createdBy,
					'created_by' => $createdBy
				));
				
				/* Disabled 2010-11-17 jberes
				//as long as the transaction went through, check to see if the transaction is a credit, because if so,
				//we need to create an eFN for it
				if ($success[count($success) - 1] && $this->data['TransactionQueue']['transaction_type'][$i] == $creditTransactionType)
				{
					$this->FileNote->createNote(
						array('FileNote' => array(
							'account_number' => $this->data['Customer']['account_number'],
							'invoice_number' => $invoiceNumber,
							'memo' => "Inv {$invoiceNumber} adj, GL Code {$this->data['TransactionQueue']['general_ledger_code'][$i]} for \${$this->data['TransactionQueue']['amount'][$i]}",
							'action_code' => 'CLADJ'						
						)),
						$createdBy
					);
				}
				*/
			}
			
			//as long as every transaction succeeded, we'll go ahead and create
			//the auto transactions if we have any
			if (isset($this->data['AutoTransactions']) && count(array_filter($success)) == count($success))
			{
				foreach ($this->data['AutoTransactions']['invoice_and_carrier_number'] as $value)
				{
					$value = explode('__', $value);
					$invoiceNumber = $value[0];
					$carrierNumber = $value[1];
					
					$this->TransactionQueue->adjustRemainingInvoiceBalanceToZero(
						$invoiceNumber, 
						$carrierNumber,
						$this->data['TransactionQueue']['transaction_date_of_service'],
						$dateEntered,
						$this->data['TransactionQueue']['cash_reference_number'],
						$createdBy
					);
				}
			}

			$this->set('json', array('success' => $success));
		}
		
		/**
		 * Utility method to calculate how much is left to balance out for a post. 
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		batchAmount The amount that the user is planning on posting in the batch.
		 * 		pendingTotal A total amount of transactions that are pending but not yet in the queue.
		 * 		cashReferenceNumber The reference number used to find other transactions in the queue.
		 * 
		 * The JSON response will contain a variable called "balance".
		 */
		function json_balanceToPost()
		{
			//we only consider payments when calculating the balance
			$paymentTypes = Set::extract('/TransactionType/code', $this->TransactionType->find('all', array(
				'fields' => array('code'),
				'conditions' => array('is_payment' => 1),
				'contain' => array()
			)));
			
			//grab the payment transactions for the specified cash reference number
			$transactions = $this->TransactionQueue->find('all', array(
				'fields' => array('amount'),
				'conditions' => array(
					'cash_reference_number' => $this->params['form']['cashReferenceNumber'],
					'transaction_type' => $paymentTypes
				)
			));
			
			//grab all of the amounts out of the queue
			$amounts = Set::extract($transactions, '{n}.TransactionQueue.amount');
			
			//massage potentially bad amounts since the field definition is a string (leading spaces
			//followed by a period screw up math operations)
			foreach ($amounts as $i => $amount)
			{
				$amounts[$i] = (float)$amount;
			}
			
			$this->set('json', array('balance' => $this->params['form']['batchAmount'] - ($this->params['form']['pendingTotal'] + array_sum($amounts))));
		}
		
		/**
		 * JSON action to edit a record in the transaction queue. Expects a standard "data" array from a form post.
		 * The JSON will contain one key called "success" that states whether or not the edit was successful.
		 */
		function json_edit()
		{			
			//grab payment transaction types
			$paymentTransactionTypes = Set::extract('/TransactionType/code', $this->TransactionType->find('all', array(
				'fields' => array('code'),
				'conditions' => array('is_payment' => 1),
				'contain' => array()
			)));
			
			//bank numbers are only applied to payments
			$this->data['TransactionQueue']['bank_number'] = in_array($this->data['TransactionQueue']['transaction_type'], $paymentTransactionTypes) ? $this->data['TransactionQueue']['bank_number'] : '';
			
			//massage dates
			foreach (array('transaction_date_of_service', 'billing_date', 'data_entry_date') as $field)
			{
				if (isset($this->data['TransactionQueue'][$field]))
				{
					$this->data['TransactionQueue'][$field] = databaseDate($this->data['TransactionQueue'][$field]);
				}
			}
			
			//change post status to be ready (R)
			$this->data['TransactionQueue']['post_status'] = 'R';
			
			//save the record
			$this->set('json', array('success' => !!$this->TransactionQueue->save($this->data)));
		}
		
		/**
		 * JSON action to delete a record from the transaction queue.
		 * @param int $id The ID of the record to delete.
		 */
		function json_delete($id)
		{
			$this->TransactionQueue->id = $id;
			$success = $this->TransactionQueue->delete();
			$this->set('json', array('success' => $success));
		}
		
		/**
		 * Module to view the transaction queue for the given combination transaction date and 
		 * cash reference number.
 		 * This method can accept the following named parameters:
		 * 		string beginDate - The beginning of the transaction date range to filter by.
		 * 		string endDate - The ending of the transaction date to filter by.
		 * 		string cashref - The cash reference number to filter by.
		 *		int bankNumber - The bank number to filter by.
		 * 		bool showblank - States whether or not to also show transactions that have a blank cash reference number.
		 * 		string user - The user, if any, to filter the results by.
		 */
		function module_view()
		{
			$beginDate = ifset($this->params['named']['beginDate']);
			$endDate = ifset($this->params['named']['endDate']);
			$cashReferenceNumber = ifset($this->params['named']['cashref']);
			$showBlankCashReferenceNumbers = ifset($this->params['named']['showblank'], false);
			$bankNumber = ifset($this->params['named']['bankNumber']);
			$user = ifset($this->params['named']['user']);
			$isPostback = isset($this->params['named']['isPostback']);
			
			$conditions = array();
			
			if ($beginDate != '' && $endDate != '')
			{
				$conditions['transaction_date_of_service BETWEEN'] = array(databaseDate($beginDate), databaseDate($endDate));
			}
			else if ($beginDate != '')
			{
				$conditions['transaction_date_of_service >='] = databaseDate($beginDate);
			}
			else if ($endDate != '')
			{
				$conditions['transaction_date_of_service <='] = databaseDate($endDate);
			}
			
			if ($cashReferenceNumber != '')
			{
				$conditions['cash_reference_number'] = $showBlankCashReferenceNumbers ? array($cashReferenceNumber, '') : $cashReferenceNumber;
			}
			
			if ($bankNumber != '')
			{
				$conditions['bank_number'] = $bankNumber;
			}
			
			if ($user != '')
			{
				$conditions['or'] = array(
					'user_id' => $user,
					'created_by' => $user
				);
			}
			
			//grab banks
			$banks = $this->Lookup->get('banks', true, true);
			$banksCompact = $this->Lookup->get('banks', true, true, true);
			
			//grab transaction types
			$transactionTypes = $this->TransactionType->find('all', array(
				'fields' => array('code', 'description', 'is_transfer', 'is_amount_subtracted'),
				'order' => 'code',
				'contain' => array()
			));
			
			//grab amounts of the whole queue that match so we can calculate totals (yes this is inefficient since 
			//we're also paging, but the driver doesn't currently support aggregates and we're on a tight 
			//schedule at the moment).
			$queue = $this->TransactionQueue->find('all', array(
				'fields' => array(
					'transaction_type',
					'amount'	
				),
				'conditions' => $conditions,
				'contain' => array(),
			));
			
			//grab the current page of results
			$this->paginate = array(
				'fields' => array(
					'id',
					'account_number',
					'carrier_number',
					'invoice_number',
					'bank_number',
					'transaction_date_of_service',
					'billing_date',					
					'transaction_type',
					'post_status',
					'general_ledger_code',
					'general_ledger_description',
					'equipment_description',
					'amount',
					'user_id',
					'created_by'
				),
				'conditions' => $conditions,
				'contain' => array(),
				'order' => array('id'),
				'limit' => 50,
				'page' => 1
			);
			
			$transactions = $this->paginate('TransactionQueue');
			
			//massage potentially bad amounts since the field definition is a string (leading spaces
			//followed by a period screw up math operations)
			foreach ($queue as $i => $transaction)
			{
				$queue[$i]['TransactionQueue']['amount'] = (float)$transaction['TransactionQueue']['amount'];
			}
			
			foreach ($transactions as $i => $transaction)
			{
				$transactions[$i]['TransactionQueue']['amount'] = (float)$transaction['TransactionQueue']['amount'];
			}
			
			$totals = array();
			
			// Group the transaction type totals
			if ($transactions !== false)
			{
				foreach ($queue as $row)
				{
					$totals[$row['TransactionQueue']['transaction_type']] = ifset($totals[$row['TransactionQueue']['transaction_type']], 0) + $row['TransactionQueue']['amount'];
				}
			}
			
			//set data the forms need in the view
			$this->data['TransactionQueue']['beginning_transaction_date_of_service'] = formatDate($beginDate);
			$this->data['TransactionQueue']['original_beginning_transaction_date_of_service'] = formatDate($beginDate);
			$this->data['TransactionQueue']['ending_transaction_date_of_service'] = formatDate($endDate);
			$this->data['TransactionQueue']['original_ending_transaction_date_of_service'] = formatDate($endDate);
			$this->data['TransactionQueue']['cash_reference_number'] = $cashReferenceNumber;
			$this->data['TransactionQueue']['original_cash_reference_number'] = $cashReferenceNumber;
			$this->data['TransactionQueue']['bank_number'] = $bankNumber;
			$this->data['TransactionQueue']['original_bank_number'] = $bankNumber;
			$this->data['TransactionQueue']['created_by'] = $user;
			$this->data['TransactionQueue']['original_created_by'] = $user;
			$this->data['TransactionQueue']['original_page'] = isset($this->params['named']['page']) ? $this->params['named']['page'] : 1;
			$this->data['TransactionQueue']['original_sort_field'] = isset($this->params['named']['sort']) ? $this->params['named']['sort'] : 'id';
			$this->data['TransactionQueue']['original_sort_direction'] = isset($this->params['named']['direction']) ? $this->params['named']['direction'] : 'asc';
			
			$this->data['TransactionQueue']['show_blank_cash_reference_numbers'] = $showBlankCashReferenceNumbers;
			
			$this->set(compact('banks', 'banksCompact', 'transactions', 'transactionTypes', 'isPostback', 'totals'));
		}
		
		/**
		 * Action to invoke a batch post. Used by the module_view action to begin a batch post process.
		 */
		function json_batchPost()
		{
			set_time_limit(0);
			
			$beginDate = databaseDate($this->data['TransactionQueue']['beginning_transaction_date_of_service']);
			$endDate = databaseDate($this->data['TransactionQueue']['ending_transaction_date_of_service']);
			$runByUser = $this->Session->read('user');
			$cashReferenceNumber = trim($this->data['TransactionQueue']['cash_reference_number']);
			$bankNumber = $this->data['TransactionQueue']['bank_number'];
			$includeBlankCashReferenceNumbers = $this->data['TransactionQueue']['show_blank_cash_reference_numbers'];
			$filteredUser = trim($this->data['TransactionQueue']['created_by']);
			$createSuggested = $this->data['TransactionQueue']['create_suggested_credits'];
			
			//execute the batch post
			shell_exec(
				sprintf(
					"cd %s; nohup ./cake/console/cake batch_post_transactions %s %s -impersonate %s %s %s %s %s %s > /dev/null 2>&1 &",
					escapeshellarg(ROOT),
					$beginDate != '' ? ('-start ' . escapeshellarg($beginDate)) : '',
					$endDate != '' ? ('-end ' . escapeshellarg($endDate)) : '',
					escapeshellarg($runByUser),
					$cashReferenceNumber != '' ? ('-cashref ' . escapeshellarg($cashReferenceNumber)) : '',
					$bankNumber != '' ? ('-bank ' . escapeshellarg($bankNumber)) : '',
					$includeBlankCashReferenceNumbers ? '-blankcashref' : '',
					$filteredUser != '' ? ('-user ' . escapeshellarg($filteredUser)) : '',
					$createSuggested ? '-suggest' : ''
				)
			);
			
			$this->set('json', array('result' => 1));
		}
		
		/**
		 * Used to batch post a single record from filePro. Expects two named parameter:
		 * 		record - The record to post.
		 * 		username - The user doing the batch post
		 */
		function batchPostSingle()
		{
			set_time_limit(0);
			$this->autoRender = false;
			
			//grab the record and user
			$record = ifset($this->params['named']['record'], null);
			$user = ifset($this->params['named']['username'], null);
			
			//make sure we have good ones
			if ($record == null || !is_numeric($record) || $user == null)
			{
				return;
			}
			
			//execute the batch post
			shell_exec(
				sprintf(
					"cd %s; nohup ./cake/console/cake batch_post_transactions -record %s -impersonate %s > /dev/null 2>&1 &", 
					escapeshellarg(ROOT), 					
					escapeshellarg($record),
					escapeshellarg($user)
				)
			);
		}
	}
?>