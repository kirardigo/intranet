<?php
	class InvoicesController extends AppController
	{
		var $uses = array(
			'Invoice', 
			'Transaction', 
			'TransactionType', 
			'Rental', 
			'Customer', 
			'Lookup', 
			'Department',
			'Process'
		);
		
		var $components = array('DefaultFile');
		var $helpers = array('Ajax', 'Paginator');
		
		/**
		 * Looks up invoices by invoice number matching. Expects $this->data['Invoice']['invoice_number'] to be set.
		 * @param string $accountNumber The optional account number to filter invoice selection by.
		 */
		function ajax_autoComplete($accountNumber = null)
		{
			if (trim($this->data['Invoice']['invoice_number']) == '')
			{
				die();
			}
			
			$conditions = array(
				'Invoice.invoice_number like' => $this->data['Invoice']['invoice_number'] . '%', 
				'Customer.name <>' => ''
			);
			
			//filter by the account if we have one
			if ($accountNumber != null)
			{
				$conditions['Invoice.account_number'] = $accountNumber;
			}
			
			$matches = $this->Invoice->find('all', array(
				'fields' => array(
					'Invoice.id',
					'Invoice.invoice_number',
					'Invoice.account_number',
					'Invoice.date_of_service',
					'Customer.name'
				),
				'conditions' => $conditions,
				'contain' => array('Customer')
			));
			
			//format dates
			foreach ($matches as $i => $match)
			{
				$matches[$i]['Invoice']['date_of_service'] = formatDate($match['Invoice']['date_of_service']);
			}

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Invoice.id', 
				'id_prefix' => 'invoice_',
				'value_fields' => array('Invoice.invoice_number'),
				'informal_fields' => array('Invoice.date_of_service', 'Customer.name', 'Invoice.account_number'),
				'informal_format' => '<span class="DateOfService">(%s)</span> - <span class="CustomerName">%s</span> <span class="AccountNumber" style="display:none;">%s</span>'
			));
		}
		
		/**
		 * Looks up invoices by TCN matching. Expects $this->data['Invoice']['transaction_control_number'] to be set.
		 */
		function ajax_autoCompleteByTcn()
		{
			if (trim($this->data['Invoice']['transaction_control_number']) == '')
			{
				die();
			}
			
			$matches = $this->Invoice->find('all', array(
				'fields' => array('Invoice.id', 'Invoice.invoice_number', 'Invoice.transaction_control_number', 'Invoice.transaction_control_number_file', 'Invoice.account_number', 'Invoice.date_of_service', 'Customer.name'),
				'conditions' => array(
					'Invoice.transaction_control_number' => $this->data['Invoice']['transaction_control_number'], 
					'Customer.name <>' => ''
				),
				'contain' => array('Customer')
			));
			
			//format dates
			foreach ($matches as $i => $match)
			{
				$matches[$i]['Invoice']['date_of_service'] = formatDate($match['Invoice']['date_of_service']);
			}

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Invoice.id', 
				'id_prefix' => 'invoice_',
				'value_fields' => array('Invoice.invoice_number'),
				'informal_fields' => array('Invoice.transaction_control_number_file', 'Invoice.date_of_service', 'Customer.name', 'Invoice.transaction_control_number', 'Invoice.account_number'),
				'informal_format' => '<span class="DateOfService">(%s - %s)</span> - <span class="CustomerName">%s</span><span class="TcnNumber" style="display:none;">%s</span><span class="AccountNumber" style="display:none;">%s</span>'
			));
		}
		
		/**
		 * Module to display invoices for a particular customer.
		 * 
		 * Client Events: 
		 * 		invoice:carrierSelected - fired when a user clicks a carrier. The event memo contains the 
		 * 								  following keys: invoice, tcn, date, amount, carrier, balance.
		 * 		invoice:ledgerRequested - fired when a user clicks the ledger link for an invoice. It is up
		 * 								  to the consumer to actually show the ledger in some way. The memo
		 * 								  will contain the following key: invoice
		 * 		invoice:editL1Requested - fired when a user clicks the edit L1 link for an invoice. It is up
		 * 								  to the consumer to actually allow the user to edit L1 info in some way. 
		 * 								  The memo will contain the following keys: row (the DOM row of the selected table) and
		 * 								  invoice. The reason the row is passed is so the consumer can invoke
		 * 								  Modules.Invoices.ForCustomer.updateL1Information() if an edit is actually made.
		 * 
		 * Optionally, this method can accept the following named parameters:
		 * 		bool closedInvoices - Specifies whether or not to get closed invoices. False by default.
		 * 		bool clickableCarriers - Specifies whether or not to have the carriers clickable in the output table. 
		 * 		bool showPurchasesLink - Specifies whether or not to have a purchases link in each row of the output table.
		 * 		int rentalID - Specify to filter down to invoices that match the HCPC of the rental that fall
		 * 					   between the rental's setup and return dates.
		 * 		string carrierNumber The carrier number of the carrier to filter invoices for.
		 * 		
		 * 
		 * @param string $accountNumber The account number of the customer to get invoices for.
		 */
		function module_forCustomer($accountNumber)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$pointer = $this->Customer->field('invoice_pointer', array('account_number' => $accountNumber));
				
				return ($pointer != 0);
			}
			
			$invoices = array();
			$hasInvoices = true;
			
			$closedInvoices = ifset($this->params['named']['closedInvoices'], false);
			$clickableCarriers = ifset($this->params['named']['clickableCarriers'], false);
			$showPurchasesLink = ifset($this->params['named']['showPurchasesLink'], false);
			$showEditL1InformationLink = ifset($this->params['named']['showEditL1InformationLink'], false);
			$rentalID = ifset($this->params['named']['rentalID'], null);
			$carrierNumber = ifset($this->params['named']['carrierNumber'], null);
			
			$fields = array(				
				'Invoice.invoice_number', 
				'Invoice.transaction_control_number', 
				'Invoice.department_code', 
				'Invoice.date_of_service', 
				'Invoice.amount',
				'Invoice.carrier_1_code',
				'Invoice.carrier_1_balance',
				'Invoice.carrier_2_code',
				'Invoice.carrier_2_balance',
				'Invoice.carrier_3_code',
				'Invoice.carrier_3_balance',
				'Invoice.line_1_status',
				'Invoice.line_1_date',
				'Invoice.line_1_amount'
			);
			
			//determine our base conditions for open or closed invoices
			$conditions = $closedInvoices
				? array(
					'Invoice.carrier_1_balance' => 0,
					'Invoice.carrier_2_balance' => 0,
					'Invoice.carrier_3_balance' => 0
				)
				: array('or' => array(
					'Invoice.carrier_1_balance <>' => array(0, ''),
					'Invoice.carrier_2_balance <>' => array(0, ''),
					'Invoice.carrier_3_balance <>' => array(0, '')
				));
				
			if ($carrierNumber != null)
			{
				$conditions['and']['or'] = array(
					'Invoice.carrier_1_code' => $carrierNumber,
					'Invoice.carrier_2_code' => $carrierNumber,
					'Invoice.carrier_3_code' => $carrierNumber
				);
			}
						
			//if we have a rental, we need to filter the results down
			if ($rentalID != null)
			{
				//find the rental in question
				$rental = $this->Rental->find('first', array(
					'fields' => array('healthcare_procedure_code', 'setup_date', 'returned_date'),
					'conditions' => array('id' => $rentalID),
					'contain' => array()
				));
				
				if ($rental !== false)
				{
					$dateCondition = null;
					
					//figure out what dates we need to filter down to
					if ($rental['Rental']['returned_date'] != null)
					{
						$dateCondition = array('Transaction.transaction_date_of_service BETWEEN' => array(databaseDate($rental['Rental']['setup_date']), databaseDate($rental['Rental']['returned_date'])));
					}
					else
					{
						$dateCondition = array('Transaction.transaction_date_of_service >=' => databaseDate($rental['Rental']['setup_date']));
					}
			
					//grab the list of invoices that match the account number, HCPC, and date ranges
					$matches = $this->Transaction->find('all', array(
						'fields' => array('invoice_number'),
						'conditions' => array_merge(
							array(
								'Transaction.account_number' => $accountNumber,
								'Transaction.healthcare_procedure_code' => $rental['Rental']['healthcare_procedure_code']
							),
							$dateCondition
						),
						'contain' => array()
					));	
					
					//unique the invoice numbers so we can pull them all
					$matches = array_unique(Set::extract($matches, '{n}.Transaction.invoice_number'));
					
					//if we couldn't find any invoices, we'll short circuit the search
					if (count($matches) == 0)
					{
						$hasInvoices = false;
					}
					else
					{
						$conditions['Invoice.invoice_number'] = $matches;
					}
					
					//set the rental so we can show the filter in the view
					$this->set('rental', $rental);
				}
				else
				{
					//if we didn't find the rental, there's no reason to pull invoices
					$hasInvoices = false;
				}
			}
				
			//as long as we haven't short-circuited, go grab the invoices
			if ($hasInvoices)
			{
				$invoices = $this->Invoice->find('all', array(
					'fields' => $fields,
					'conditions' => array_merge(array('Invoice.account_number' => $accountNumber), $conditions),
					'contain' => array()
				));
			}

			$this->set(compact('invoices', 'accountNumber', 'closedInvoices', 'clickableCarriers', 'showPurchasesLink', 'showEditL1InformationLink', 'rentalID', 'carrierNumber'));
		}
		
		/**
		 * Ajax action to get aged open invoices for a given customer.
		 * Optionally, this method can accept the following named parameters:
		 * 		int rentalID - Specify to filter down to invoices that match the HCPC of the rental that fall
		 * 					   between the rental's setup and return dates.
		 * 		string carrierNumber The carrier number of the carrier to filter invoices for.
		 * 		
		 * @param string $accountNumber The account number to get the open invoices for.
		 */
		function ajax_agedOpenInvoices($accountNumber)
		{
			$this->autoRenderAjax = false;
			
			$rentalID = ifset($this->params['named']['rentalID'], null);
			$carrierNumber = ifset($this->params['named']['carrierNumber'], null);
			
			$invoices = array();
			$hasInvoices = true;
			
			$conditions = array(
				'Invoice.account_number' => $accountNumber,
				'or' => array(
					'Invoice.carrier_1_balance <>' => array(0, ''),
					'Invoice.carrier_2_balance <>' => array(0, ''),
					'Invoice.carrier_3_balance <>' => array(0, '')
				)
			);
			
			if ($carrierNumber != null)
			{
				$conditions['and']['or'] = array(
					'Invoice.carrier_1_code' => $carrierNumber,
					'Invoice.carrier_2_code' => $carrierNumber,
					'Invoice.carrier_3_code' => $carrierNumber
				);
			}
			
			//if we have a rental, we need to filter the results down
			if ($rentalID != null)
			{
				//find the rental in question
				$rental = $this->Rental->find('first', array(
					'fields' => array('healthcare_procedure_code', 'setup_date', 'returned_date'),
					'conditions' => array('id' => $rentalID),
					'contain' => array()
				));
				
				if ($rental !== false)
				{
					$dateCondition = null;
					
					//figure out what dates we need to filter down to
					if ($rental['Rental']['returned_date'] != null)
					{
						$dateCondition = array('Transaction.transaction_date_of_service BETWEEN' => array(databaseDate($rental['Rental']['setup_date']), databaseDate($rental['Rental']['returned_date'])));
					}
					else
					{
						$dateCondition = array('Transaction.transaction_date_of_service >=' => databaseDate($rental['Rental']['setup_date']));
					}
			
					//grab the list of invoices that match the account number, HCPC, and date ranges
					$matches = $this->Transaction->find('all', array(
						'fields' => array('invoice_number'),
						'conditions' => array_merge(
							array(
								'Transaction.account_number' => $accountNumber,
								'Transaction.healthcare_procedure_code' => $rental['Rental']['healthcare_procedure_code']
							),
							$dateCondition
						),
						'contain' => array()
					));	
					
					//unique the invoice numbers so we can pull them all
					$matches = array_unique(Set::extract($matches, '{n}.Transaction.invoice_number'));
					
					//if we couldn't find any invoices, we'll short circuit the search
					if (count($matches) == 0)
					{
						$hasInvoices = false;
					}
					else
					{
						$conditions['Invoice.invoice_number'] = $matches;
					}
				}
				else
				{
					//if we didn't find the rental, there's no reason to pull invoices
					$hasInvoices = false;
				}
			}
			
			//as long as we haven't short-circuited, go grab the invoices
			if ($hasInvoices)
			{
				//grab all open invoices
				$invoices = $this->Invoice->find('all', array(
					'fields' => array('date_of_service', 'carrier_1_code', 'carrier_1_balance', 'carrier_2_code', 'carrier_2_balance', 'carrier_3_code', 'carrier_3_balance'),
					'conditions' => $conditions,
					'contain' => array()
				));
			}
			
			$open = array();
			
			foreach ($invoices as $invoice)
			{
				//skip invoices with no date
				if ($invoice['Invoice']['date_of_service'] == null)
				{
					continue;
				}
				
				//determine how old the invoice is and what bucket it falls in - we're going to
				//have 6 buckets - 0-30 days, 30-60 days, 60-90 days, 90-120 days, 120-150 days, and 150+ days
				$age = (time() - strtotime($invoice['Invoice']['date_of_service'])) / 60 / 60 / 24;
				$bucket = min(floor($age / 30), 5);
				
				//go through each carrier and for those that have balances, put the balance in the proper bucket,
				//grouped by carrier
				foreach (array('1', '2', '3') as $carrier)
				{
					$code = $invoice['Invoice']["carrier_{$carrier}_code"];
					$amount = $invoice['Invoice']["carrier_{$carrier}_balance"];
					
					//if we have a carrier filter, skip any carrier that's not the chosen one
					if ($carrierNumber != null && $carrierNumber != $code)
					{
						continue;
					}
					
					if (!empty($amount))
					{
						if (!isset($open[$code][$bucket]))
						{
							$open[$code][$bucket] = 0;
						}
						
						$open[$code][$bucket] += $amount;
					}
				}
			}
			
			$this->set(compact('open'));
		}
		
		/**
		 * Module to display a ledger for a particular invoice.
		 *
		 * @param string $accountNumber The account number to show a ledger for.
		 * @param bool $isUpdate Optional. Determines whether we are merely updating the paginated table.
		 * @param bool $forceRefresh Optional. Because of the nature of how the ledger is calculated, temporary
		 * tables are used in the database to capture the results so they can later be paginated via that table.
		 * Passing true for this parameter forces the table to be recreated.
		 */
		function module_ledger($accountNumber, $isUpdate = false, $forceRefresh = false)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$pointer = $this->Customer->field('invoice_pointer', array('account_number' => $accountNumber));
				
				return ($pointer != 0);
			}
			
			// Initialize variables
			$conditions = array();
			$carriers = array();
			$invoiceNumber = null;
			$carrierNumber = null;
			$transactionType = null;
			
			$inquiryParameters = $this->Session->read('inquiryParameters');
			
			if (isset($inquiryParameters['ledgerInvoice']))
			{
				$this->params['named']['invoiceNumber'] = $inquiryParameters['ledgerInvoice'];
				unset($inquiryParameters['ledgerInvoice']);
				$this->Session->write('inquiryParameters', $inquiryParameters);
			}
			
			// Parse named parameters
			if (isset($this->params['named']['invoiceNumber']))
			{
				$invoiceNumber = $this->params['named']['invoiceNumber'];
				$this->data['Transaction']['invoice_number'] = $invoiceNumber;
			}
			
			if (isset($this->params['named']['carrierNumber']))
			{
				$carrierNumber = $this->params['named']['carrierNumber'];
				$this->data['Transaction']['carrier_number'] = $carrierNumber;
			}
			
			if (isset($this->params['named']['transactionType']))
			{
				$transactionType = $this->params['named']['transactionType'];
				$this->data['Transaction']['transaction_type'] = $transactionType;
				$conditions = array('transaction_type' => $transactionType);
			}
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_ledgerTempTableName($this->Session->read('user'), $accountNumber, $invoiceNumber, $carrierNumber); 
			$modelName = Inflector::classify($table);
			
			//grab transaction types
			$transactionTypes = $this->TransactionType->find('all', array(
				'fields' => array('code', 'description', 'is_transfer', 'is_amount_subtracted'),
				'order' => 'code',
				'contain' => array()
			));
			$transactionTypeList = Set::combine($transactionTypes, '/TransactionType/code', '/TransactionType/description');
			
			//if this is not a pagination postback, we need to pull more information and potentially
			//build the table of results for the user
			if (!$isUpdate)
			{
				//these are the conditions and contain that will be applied to the 
				//transactions search if an invoice is not specified
				$transactionConditions = array();
				$transactionContain = array(
					'Invoice' => array(
						'fields' => array(
							'Invoice.invoice_number', 
							'Invoice.transaction_control_number',
							'Invoice.carrier_1_code',
							'Invoice.carrier_2_code',
							'Invoice.carrier_3_code',
							'Invoice.carrier_1_balance',
							'Invoice.carrier_2_balance',
							'Invoice.carrier_3_balance'
						)
					)
				);
				
				$invoice = null;
				
				//if we have an invoice, go get it
				if ($invoiceNumber != null)
				{
					$invoice = $this->Invoice->find('first', array(
						'fields' => array(
							'Invoice.invoice_number', 
							'Invoice.transaction_control_number', 
							'Invoice.account_number',
							'Invoice.carrier_1_code',
							'Invoice.carrier_2_code',
							'Invoice.carrier_3_code',
							'Invoice.carrier_1_balance',
							'Invoice.carrier_2_balance',
							'Invoice.carrier_3_balance'
						),
						'conditions' => array('Invoice.invoice_number' => $invoiceNumber),
						'contain' => array()
					));
					
					if (!$invoice)
					{
						die();
					}
					
					//update the transaction search parameters
					$transactionConditions['Transaction.invoice_number'] = $invoiceNumber;
					$transactionContain = array();
					
					$this->set('invoice', $invoice);
				}
				
				//see if the temp table exists
				$exists = Set::extract($db->query("select count(1) as the_count from information_schema.tables where table_schema = '{$db->config['database']}' and table_name = '{$table}'"), '0.0.the_count');
				
				if (!$exists || $forceRefresh)
				{	
					//grab transaction schema so we can make the columns the right size if we need to craate the table
					$schema = $this->Transaction->schema();
					
					//ditch any existing table as well as any more specific versions of the table since we're refreshing 
					//(the pattern is to escape underscores for a 'like' clause, and removing the trailing pluralized 's'
					//on the table that we needed for Cake)
					$pattern = str_replace('_', '\_', substr($table, 0 , strlen($table) - 1));
					$tables = Set::flatten($db->query("show tables like '{$pattern}%'"));
					
					foreach ($tables as $tableToDrop)
					{
						$db->query("drop table if exists {$tableToDrop}");
					}
	
					//create the temp table in MySQL
					$db->query("
						create table {$table} (
							id int not null auto_increment primary key,
							transaction_id int not null,
							invoice_number varchar({$schema['invoice_number']['length']}) not null,
							transaction_control_number varchar({$schema['transaction_control_number']['length']}) null,
							transaction_date_of_service date, 
							carrier_number varchar({$schema['carrier_number']['length']}) not null, 
							transaction_type varchar({$schema['transaction_type']['length']}) not null, 
							general_ledger_description varchar({$schema['general_ledger_description']['length']}) not null,
							inventory_description varchar({$schema['inventory_description']['length']}) not null,
							healthcare_procedure_code varchar({$schema['healthcare_procedure_code']['length']}) not null,
							amount decimal(13, 2),
							carrier_balance_due decimal(13, 2),
							is_invalid_invoice bool not null,
							index (is_invalid_invoice)
						)
					");
	
					//create the model dynamically - we have to tell the driver to stop caching
					//temporarily so that it will pick up the new table that was just created
					$cacheSources = $db->cacheSources;
					$db->cacheSources = false;
					$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
					$db->cacheSources = $cacheSources;
					
					//filter by carrier if we have one
					if ($carrierNumber != null)
					{
						$transactionConditions['Transaction.carrier_number'] = $carrierNumber;
					}
					else
					{
						//otherwise make sure we only get transactions with a carrier
						$transactionConditions['Transaction.carrier_number <>'] = '';
					}
					
					//if we don't have a particular invoice number, make sure we only get
					//transactions that actually have an invoice number
					if ($invoiceNumber == null)
					{
						$transactionConditions['Transaction.invoice_number <>'] = '';
					}
					
					//go grab matching transactions
					$transactions = $this->Invoice->Customer->find('first', array(
						'fields' => array('Customer.id', 'Customer.transaction_pointer'),
						'conditions' => array('Customer.account_number' => $accountNumber),
						'chains' => array(
							'Transaction' => array(
								'fields' => array_merge(
									array(
										'Transaction.id',
										'Transaction.invoice_number',
										'Transaction.transaction_date_of_service', 
										'Transaction.carrier_number', 
										'Transaction.transaction_type',
										'Transaction.general_ledger_description',
										'Transaction.inventory_description',
										'Transaction.healthcare_procedure_code',
										'Transaction.amount',
										'Transaction.carrier_balance_due'
									)
								),
								'conditions' => $transactionConditions,
								'order' => array('Transaction.carrier_number', 'Transaction.transaction_date_of_service desc', 'Transaction.id desc'),
								'contain' => $transactionContain,
								'required' => false
							)
						)
					));
	
					//massage types for O(1) lookup
					$transactionTypeLookups = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}');
					
					$balances = array();
					
					//go through each transaction
					if ($transactions !== false)
					{
						foreach ($transactions['Transaction'] as $transaction)
						{
							//save some typing later
							$currentInvoice = $invoiceNumber != null ? $invoiceNumber : $transaction['invoice_number'];
							$carrier = $transaction['carrier_number'];
							
							//make sure the transaction isn't referring to an invoice that doesn't exist (or
							//at least isn't in customer's invoice chain... imagine that)
							if ($invoice == null && empty($transaction['Invoice']))
							{
								$transaction['is_invalid_invoice'] = 1;
							}
							else
							{
								//if we're filtered by invoice, we need to have the balance due field become the running
								//balance due on that invoice. To do this, we need to "roll back" the transactions as we read
								//them to keep a running balance. The reason we need to do this is because normally, the 
								//carrier_balance_due field on the invoice is the running balance for the entire carrier,
								//not the running balance for just that invoice
								if ($invoice != null)
								{
									//if we don't have a record yet of this invoice, set the running balance to the balance
									//for the specified carrier balance in the Invoice file
									if (!isset($balances[$currentInvoice][$carrier]))
									{
										//grab the invoice fields
										$data = $invoice != null ? $invoice['Invoice'] : $transaction['Invoice'];
										$invoiceBalance = null;
										
										//find the balance on the invoice for this carrier
										foreach (array('1', '2', '3') as $number)
										{
											if ($data["carrier_{$number}_code"] == $carrier)
											{
												$invoiceBalance = $data["carrier_{$number}_balance"];
												break;
											}
										}
										
										$balances[$currentInvoice][$carrier]['balance'] = $invoiceBalance;
										$transaction['carrier_balance_due'] = $invoiceBalance;
										$balances[$currentInvoice][$carrier]['previous'] = $transaction;
									}
									else
									{
										//otherwise, we need "undo" the previous transaction amount by applying it in reverse in 
										//order to keep a proper running balance
										$previous = $balances[$currentInvoice][$carrier]['previous'];
										
										$isSubtracted = $transactionTypeLookups[$previous['transaction_type']]['TransactionType']['is_amount_subtracted'];
										$balances[$currentInvoice][$carrier]['balance'] -= $isSubtracted ? ($previous['amount'] * -1) : $previous['amount'];
										
										$transaction['carrier_balance_due'] = $balances[$currentInvoice][$carrier]['balance'];
										$balances[$currentInvoice][$carrier]['previous'] = $transaction;
									}
								}
							
								//set the one field that we need that isn't directly in the transaction
								$transaction['transaction_control_number'] = $invoice != null ? $invoice['Invoice']['transaction_control_number'] : $transaction['Invoice']['transaction_control_number'];
							}
							
							//then move and clear the record ID
							$transaction['transaction_id'] = $transaction['id'];
							unset($transaction['id']);
							
							//format the date for MySQL
							$transaction['transaction_date_of_service'] = databaseDate($transaction['transaction_date_of_service']);
		
							//insert the transaction into our temp table
							$tempModel->create();
							$tempModel->save(array('Transaction' => $transaction));
						}
					}
				}
			}
			
			//create the temp model
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull the total amounts grouped by transaction type
			$totals = $tempModel->find('all', array(
				'fields' => array('transaction_type', 'sum(amount) as total'),
				'conditions' => $conditions,
				'group' => 'transaction_type'
			));
			
			$this->paginate = array(
				'limit' => 20,
				'conditions' => $conditions,
				'page' => 1,
				'order' => 'id'
			);
			
			//paginate the current page
			$this->{$modelName} = $tempModel;
			$transactions = $this->paginate($modelName);
			
			if ($transactions !== false)
			{
				foreach ($transactions as $row)
				{
					$carriers[$row['Transaction']['carrier_number']] = $row['Transaction']['carrier_number'];
				}
			}
			
			$carrierList = $tempModel->find('all', array(
				'contain' => array(),
				'fields' => array('DISTINCT carrier_number')
			));
			
			$carriers = array();
			foreach ($carrierList as $row)
			{
				$carriers[$row['Transaction']['carrier_number']] = $row['Transaction']['carrier_number'];
			}
			
			//pull invalid invoices
			$invalidInvoices = $tempModel->find('all', array(
				'fields' => array('invoice_number'),
				'conditions' => array('is_invalid_invoice' => 1)
			));

			$this->set(compact('accountNumber', 'invoiceNumber', 'carrierNumber', 'transactions', 'transactionTypes', 'transactionTypeList', 'invalidInvoices', 'totals', 'carriers', 'isUpdate'));
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the ledger module.
		 * @param string $username The users username.
		 * @param string $accountNumber The account number.
		 * @param string $invoiceNumber The invoice number, if any.
		 * @param string $carrierNumber The carrier number, if any.
		 * @return string The unique table name.
		 */
		function _ledgerTempTableName($username, $accountNumber, $invoiceNumber = null, $carrierNumber = null)
		{
			return 'temp_ledger' . 
				'_u' . strtolower(Inflector::slug($username)) . 
				'_a' . strtolower(Inflector::slug($accountNumber)) 
				. ($invoiceNumber != null ? ('_i' . strtolower(Inflector::slug($invoiceNumber))) : '')
				. ($carrierNumber != null ? ('_c' . strtolower(Inflector::slug($carrierNumber))) : '')
				. 's';
		}
		
		/**
		 * Module to allow the user to edit the L1 information on an invoice.
		 * @param string $invoiceNumber The invoice number of the invoice to edit.
		 */
		function module_editL1Information($invoiceNumber)
		{
			//load the invoice
			$this->data = $this->Invoice->find('first', array(
				'fields' => array('account_number', 'invoice_number', 'line_1_status', 'line_1_initials', 'line_1_date', 'line_1_carrier_number'),
				'conditions' => array('invoice_number' => $invoiceNumber),
				'contain' => array()
			));
			
			$this->data['Invoice']['line_1_date'] = formatDate($this->data['Invoice']['line_1_date']);
		}
		
		/**
		 * Module to allow the user to select a series of open invoices.
		 * @param string $accountNumber The account number to filter invoices by.
		 * @param string $carrierNumber Optional. Specify to filter invoices for a given carrier.
		 */
		function module_selection($accountNumber, $carrierNumber = null)
		{
			$conditions = array(
				'Invoice.account_number' => $accountNumber,
				'or' => array(
					'Invoice.carrier_1_balance <>' => array(0, ''),
					'Invoice.carrier_2_balance <>' => array(0, ''),
					'Invoice.carrier_3_balance <>' => array(0, '')
				)
			);
			
			if ($carrierNumber != null)
			{
				$conditions['and']['or'] = array(
					'Invoice.carrier_1_code' => $carrierNumber,
					'Invoice.carrier_2_code' => $carrierNumber,
					'Invoice.carrier_3_code' => $carrierNumber
				);
			}
			
			$invoices = $this->Invoice->find('all', array(
				'fields' => array(
					'Invoice.invoice_number', 
					'Invoice.date_of_service', 
					'Invoice.carrier_1_code',
					'Invoice.carrier_1_balance', 
					'Invoice.carrier_2_code',
					'Invoice.carrier_2_balance', 
					'Invoice.carrier_3_code',
					'Invoice.carrier_3_balance'
				),
				'conditions' => $conditions,
				'order' => array('date_of_service desc'),
				'contain' => array()
			));
			
			$this->set(compact('accountNumber', 'carrierNumber', 'invoices'));
		}
		
		/**
		 * Looks up the current pending balance on an invoice for a carrier with transactions
		 * in the queue applied. Expects $this->params['form']['invoiceNumber'] and 
		 * $this->params['form']['carrierNumber'] to be set.
		 */
		function json_currentPendingBalance()
		{
			$this->set('json', array('balance' => $this->Invoice->currentPendingBalance($this->params['form']['invoiceNumber'], $this->params['form']['carrierNumber'])));
		}
		
		/**
		 * Looks up the balances for all carriers on an invoice.
		 * Expects:
		 * 		$this->params['form']['accountNumber']
		 * 		$this->params['form']['invoiceNumber']
		 * The JSON will contain the following keys:
		 * 		id - The ID of the invoice, or false if it couldn't be found.
		 * 		amount - The amount of the invoice.
		 * 		date_of_service - The invoice date.
		 * 		carrier_1_code, carrier_2_code, carrier_3_code - Each carrier on the invoice.
		 * 		carrier_1_balance, carrier_2_balance, carrier_3_balance - The balance of each carrier on the invoice.
		 */
		function json_balances()
		{
			$balances = $this->Invoice->find('first', array(
				'fields' => array(
					'amount',
					'date_of_service',
					'carrier_1_code',
					'carrier_1_balance',
					'carrier_2_code',
					'carrier_2_balance',
					'carrier_3_code',
					'carrier_3_balance'
				),
				'conditions' => array(
					'account_number' => $this->params['form']['accountNumber'],
					'invoice_number' => $this->params['form']['invoiceNumber']
				),
				'contain' => array()
			));
			
			$this->set('json', $balances !== false ? $balances['Invoice'] : array('id' => false));
		}
		
		/**
		 * Verifies that an invoice and carrier combination is actually valid.
		 * Expects:
		 * 		$this->params['form']['accountNumber'] 
		 * 		$this->params['form']['invoiceNumber'] 
		 * 		$this->params['form']['carrierNumber']
		 * The JSON will contain 3 values:
		 * 		bool exists - states whether or not the invoice exists.
		 * 		bool verified - states whether or not the carrier was valid for the invoice.
		 * 		array carriers - an array of carrier codes that are allowed to be selected for the invoice.
		 */
		function json_verify()
		{
			//grab the invoice
			$invoice = $this->Invoice->find('first', array(
				'fields' => array('Invoice.carrier_1_code', 'Invoice.carrier_2_code', 'Invoice.carrier_3_code'),
				'conditions' => array(
					'Invoice.account_number' => $this->params['form']['accountNumber'],
					'Invoice.invoice_number' => $this->params['form']['invoiceNumber']
				),
				'contain' => array()
			));
			
			$exists = $invoice !== false;
			$verified = false;
			$carriers = array();
			
			//if the invoice exists, grab and normalize (i.e. uppercase) the carrier codes from it
			if ($exists)
			{
				$carriers = array_map('strtoupper', array_filter(array($invoice['Invoice']['carrier_1_code'], $invoice['Invoice']['carrier_2_code'], $invoice['Invoice']['carrier_3_code'])));
			}
			
			//grab the active carrier codes
			$activeCarriers = Set::extract('/carrier_number', $this->Customer->activeCarriers($this->params['form']['accountNumber']));
			
			//add those to the list of allowable carriers
			if ($activeCarriers != false)
			{
				$carriers = array_unique(array_merge($carriers, array_map('strtoupper', $activeCarriers)));
			}
			
			//see if the specified carrier number is valid
			$verified = in_array(strtoupper($this->params['form']['carrierNumber']), $carriers);			
			$this->set('json', array('exists' => $exists, 'verified' => $verified, 'carriers' => $carriers));
		}
		
		/** Container action for the batch invoicing module. */
		function batch() 
		{
			$this->pageTitle = 'Batch Invoicing';
		}

		/**
		 * This is the batch invoicing module that is a port of the rental invoices CU05DG.TXT and MU05FT.TXT U05 
		 * programs. The actual invoicing occurs in the batch_invoicing shell.
		 */
		function module_batch()
		{
			$invoicingTypes = array(
				1 => 'ReRents - Profit Center',
				2 => 'ReRents - Account Number',
				3 => 'Maintenance'
			);
			
			if (!empty($this->data))
			{
				//prep the parameters to pass to the shell
				$parameters = array(
					'begin' => databaseDate($this->data['Invoice']['begin_date']),
					'end' => databaseDate($this->data['Invoice']['end_date']),
					'username' => $this->Session->read('user'),
					'printer' => $this->data['Invoice']['printer']
				);
				
				//add extra ones based on the type of invoicing
				switch ($this->data['Invoice']['invoicing_type'])
				{
					case '1':
						if (trim($this->data['Invoice']['profit_center_number']) != '')
						{
							$parameters['pc'] = $this->data['Invoice']['profit_center_number'];
						}
						
						break;
					case '2':
						if (trim($this->data['Invoice']['account_number']) != '')
						{
							$parameters['account'] = $this->data['Invoice']['account_number'];
						}
						
						break;
					case '3':
						if (trim($this->data['Invoice']['profit_center_number']) != '')
						{
							$parameters['pc'] = $this->data['Invoice']['profit_center_number'];
						}
						
						$parameters['maint'] = '';
						break;
				}
				
				//suppress printing if we're supposed to
				if ($this->data['Invoice']['should_suppress_printing'])
				{
					$parameters['noprint'] = '';
				}
				
				$args = '';
				
				//collapse the args for the command line
				foreach ($parameters as $key => $value)
				{
					$args .= "-{$key} " . escapeshellarg($value) . ' ';
				}
				
				//kick off the invoicing
				exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake batch_invoicing %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						$args
					), 
					$output
				);
				
				$this->redirect('/processes/manager');
			}
			
			//see if another batch invoicing is running
			$isBatchInvoicingRunning = $this->Process->isProcessRunning('Batch Invoicing');
			
			//grab all available printers
			exec('lpstat -p | grep ^printer | grep enabled | cut -f2 -d\ ', $printers);
			$printers = array_combine($printers, $printers);
						
			$periodPostingDate = formatDate($this->DefaultFile->getCurrentPostingPeriod());
			$this->set(compact('invoicingTypes', 'periodPostingDate', 'isBatchInvoicingRunning', 'printers'));
		}
		
		/**
		 * Container action for transaction modules.
		 */
		function management()
		{
			$this->pageTitle = 'Invoice Management';
		}
		
		/**
		 * Displays the invoice management module.
		 */
		function module_management()
		{
			//this sucker can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_managementTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			//the user posted a new search
			if (!empty($this->data))
			{
				//create the temp table in MySQL
				//grab invoice schema so we can make the columns the right size
				$schema = $this->Invoice->schema();
				
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						profit_center_number varchar({$schema['profit_center_number']['length']}) not null,
						account_number varchar({$schema['account_number']['length']}) not null,
						department_code varchar({$schema['department_code']['length']}) not null,
						transaction_control_number varchar({$schema['transaction_control_number']['length']}) not null,
						invoice_number varchar({$schema['invoice_number']['length']}) not null,
						rental_or_purchase varchar({$schema['rental_or_purchase']['length']}) not null,
						date_of_service date null,
						billing_date date null,
						line_1_status varchar({$schema['line_1_status']['length']}) not null,
						line_1_initials varchar({$schema['line_1_initials']['length']}) not null,
						line_1_date date null,
						line_1_carrier_code varchar({$schema['carrier_1_code']['length']}) not null,
						line_1_amount decimal(13, 2) null,
						team varchar({$schema['team']['length']}) not null,
						efn_followup_date date null,
						carrier_1_code varchar({$schema['carrier_1_code']['length']}) not null,
						carrier_1_balance decimal(13, 2) not null,
						carrier_2_code varchar({$schema['carrier_2_code']['length']}) null,
						carrier_2_balance decimal(13, 2) null,
						carrier_3_code varchar({$schema['carrier_3_code']['length']}) null,
						carrier_3_balance decimal(13, 2) null,
						amount decimal(13, 2) null,
						payments decimal(13, 2) null,
						credits decimal(13, 2) null,
						account_balance decimal(13, 2) null,
						reimbursement_memo varchar({$schema['reimbursement_memo']['length']}) not null
					)
				");
				
				//create our search conditions
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Invoice.profit_center_number']))
				{
					if ($conditions['Invoice.profit_center_number'] == 'Medical')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$conditions['Invoice.profit_center_number'] = $includedProfitCenters;
					}
					
					if ($conditions['Invoice.profit_center_number'] == 'blank')
					{
						// show blank or values that are not used
						$profitCenters = $this->Lookup->get('profit_centers');
						$conditions['Invoice.profit_center_number !='] = array_keys($profitCenters);
						unset($conditions['Invoice.profit_center_number']);
					}
				}
				
				switch ($conditions['Invoice.account_balance'])
				{
					case 0: // Non-zero Balance
						$conditions['Invoice.account_balance <>'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 1: // Credit Balance
						$conditions['Invoice.account_balance <'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 2: // Balance Due
						$conditions['Invoice.account_balance >'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 3: // All Balances
						unset($conditions['Invoice.account_balance']);
						break;
				}
				
				if (isset($conditions['Invoice.team']) && $conditions['Invoice.team'] == 'blank')
				{
					$conditions['Invoice.team'] = array('TEAM_??', '');
				}
				
				if (isset($conditions['Invoice.line_1_status']) && $conditions['Invoice.line_1_status'] == 'blank')
				{
					$conditions['Invoice.line_1_status'] = '';
				}
				
				if (isset($conditions['Invoice.line_1_carrier_code']))
				{
					$L1CarrierFilter = $conditions['Invoice.line_1_carrier_code'];
					unset($conditions['Invoice.line_1_carrier_code']);
				}
				
				if (isset($conditions['Invoice.carrier_code']))
				{
					$conditions['or'] = array(
						'carrier_1_code' => $conditions['Invoice.carrier_code'],
						'carrier_2_code' => $conditions['Invoice.carrier_code'],
						'carrier_3_code' => $conditions['Invoice.carrier_code']
					);
					unset($conditions['Invoice.carrier_code']);
				}
				
				//figure out our date range. We are going to batch records in one month intervals
				//so that we don't pull too much data at once
				if (isset($conditions['Invoice.date_of_service_start']))
				{
					$start = strtotime($conditions['Invoice.date_of_service_start']);
					unset($conditions['Invoice.date_of_service_start']);
				}
				else
				{
					$start = strtotime('1/1/1993');
				}
				
				if (isset($conditions['Invoice.date_of_service_end']))
				{
					$end = strtotime($conditions['Invoice.date_of_service_end']);
					unset($conditions['Invoice.date_of_service_end']);
				}
				else
				{
					$end = strtotime('+ 1 week');
				}
				
				while ($start <= $end)
				{
					//grab from start to 1 month later
					$subsetEnd = mktime(0, 0, 0, date('m', $start) + 1, 0, date('Y', $start));
					
					//if we've gone past the end, cap it 
					if ($subsetEnd > $end)
					{
						$subsetEnd = $end;	
					}
					
					//adjust the date conditions
					$conditions['Invoice.date_of_service between'] = array(
						date('Y-m-d', $start), 
						date('Y-m-d', $subsetEnd)
					);
					
					//pull the data
					$invoices = $this->Invoice->find('all', array(
						'fields' => array(
							'profit_center_number',
							'account_number',
							'department_code',
							'transaction_control_number',
							'invoice_number',
							'rental_or_purchase',
							'date_of_service',
							'billing_date',
							'line_1_status',
							'line_1_initials',
							'line_1_date',
							'line_1_carrier_number',
							'line_1_amount',
							'team',
							'carrier_1_code',
							'carrier_1_balance',
							'carrier_2_code',
							'carrier_2_balance',
							'carrier_3_code',
							'carrier_3_balance',
							'amount',
							'payments',
							'credits',
							'account_balance',
							'reimbursement_memo'
						),
						'conditions' => $conditions,
						'contain' => array()
					));
					
					$efnModel = ClassRegistry::init('ElectronicFileNote');
					
					//insert the aggregated amounts into the temp table
					foreach ($invoices as $invoice)
					{
						$fields = array_map(array('Sanitize', 'escape'), $invoice['Invoice']);
						
						// Find the mapped carrier code
						if ($fields['line_1_carrier_number'] != '')
						{
							$L1Carrier = $fields["carrier_{$fields['line_1_carrier_number']}_code"];
						}
						else
						{
							$L1Carrier = '';
						}
						
						// Filter out records that don't match the L1 Carrier filter
						if (isset($L1CarrierFilter) && ($L1CarrierFilter != $L1Carrier))
						{
							continue;
						}
						
						$efnFollowupDate = $efnModel->getOldestFollowupDateByInvoice($fields['account_number'], $fields['invoice_number'], $fields['transaction_control_number']);
						
						$db->query("
							insert into {$table} (
								profit_center_number,
								account_number,
								department_code,
								transaction_control_number,
								invoice_number,
								rental_or_purchase,
								date_of_service,
								billing_date,
								line_1_status,
								line_1_initials,
								line_1_date,
								line_1_carrier_code,
								line_1_amount,
								team,
								efn_followup_date,
								carrier_1_code,
								carrier_1_balance,
								carrier_2_code,
								carrier_2_balance,
								carrier_3_code,
								carrier_3_balance,
								amount,
								payments,
								credits,
								account_balance,
								reimbursement_memo
							)
							values (
								'{$fields['profit_center_number']}',
								'{$fields['account_number']}',
								'{$fields['department_code']}',
								'{$fields['transaction_control_number']}',
								'{$fields['invoice_number']}',
								'{$fields['rental_or_purchase']}',
								'" . databaseDate($fields['date_of_service']) . "',
								" . ($fields['billing_date'] != '' ? "'" . databaseDate($fields['billing_date']) . "'" : 'null') . ",
								'{$fields['line_1_status']}',
								'{$fields['line_1_initials']}',
								" . ($fields['line_1_date'] != '' ? "'" . databaseDate($fields['line_1_date']) . "'" : 'null') . ",
								'{$L1Carrier}',
								" . ($fields['line_1_amount'] != '' ? $fields['line_1_amount'] : 'null') . ",
								'{$fields['team']}',
								" . ($efnFollowupDate != '' ? "'" . databaseDate($efnFollowupDate) . "'" : 'null') . ",
								'{$fields['carrier_1_code']}',
								'{$fields['carrier_1_balance']}',
								'{$fields['carrier_2_code']}',
								" . ($fields['carrier_2_balance'] != '' ? $fields['carrier_2_balance'] : 'null') . ",
								'{$fields['carrier_3_code']}',
								" . ($fields['carrier_3_balance'] != '' ? $fields['carrier_3_balance'] : 'null') . ",
								" . ($fields['amount'] != '' ? $fields['amount'] : 'null') . ",
								" . ($fields['payments'] != '' ? $fields['payments'] : 'null') . ",
								" . ($fields['credits'] != '' ? $fields['credits'] : 'null') . ",
								" . ($fields['account_balance'] != '' ? $fields['account_balance'] : 'null') . ",
								'{$fields['reimbursement_memo']}'
							)
						", false);
					}
					
					//advance the start to one day past the subset end
					$start = mktime(0, 0, 0, date('m', $subsetEnd), date('d', $subsetEnd) + 1, date('Y', $subsetEnd));
				}
			}
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Invoice', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 50,
					'page' => 1,
					'order' => 'date_of_service'
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$invoices = $this->paginate($modelName);
				
				$this->set('invoices', $invoices);
			}
			
			$profitCenters = array_merge(
				array('Medical' => 'All Medical'),
				$this->Lookup->get('profit_centers', true, true),
				array('blank' => 'BLANK')
			);
			$departments = $this->Department->getCodeList();
			$line1Statuses = $this->Lookup->get('line_1_statuses', true);
			$line1Statuses['blank'] = 'BLANK';
			$teamOptions = array(
				'Team_1' => 'Team_1',
				'Team_2' => 'Team_2',
				'Team_3' => 'Team_3',
				'Team_4' => 'Team_4',
				'blank' => 'BLANK'
			);
			$this->set(compact('profitCenters', 'departments', 'line1Statuses', 'teamOptions', 'isPostback'));
		}
		
		/**
		 * Displays the invoice management for claims module.
		 */
		function module_management_for_claims()
		{
			//this sucker can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_managementTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			//the user posted a new search
			if (!empty($this->data))
			{
				//create the temp table in MySQL
				//grab invoice schema so we can make the columns the right size
				$schema = $this->Invoice->schema();
				
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						profit_center_number varchar({$schema['profit_center_number']['length']}) not null,
						account_number varchar({$schema['account_number']['length']}) not null,
						department_code varchar({$schema['department_code']['length']}) not null,
						transaction_control_number varchar({$schema['transaction_control_number']['length']}) not null,
						invoice_number varchar({$schema['invoice_number']['length']}) not null,
						rental_or_purchase varchar({$schema['rental_or_purchase']['length']}) not null,
						date_of_service date null,
						billing_date date null,
						line_1_status varchar({$schema['line_1_status']['length']}) not null,
						line_1_initials varchar({$schema['line_1_initials']['length']}) not null,
						line_1_date date null,
						line_1_carrier_code varchar({$schema['carrier_1_code']['length']}) not null,
						line_1_amount decimal(13, 2) null,
						team varchar({$schema['team']['length']}) not null,
						efn_followup_date date null,
						carrier_1_code varchar({$schema['carrier_1_code']['length']}) not null,
						carrier_1_balance decimal(13, 2) not null,
						carrier_2_code varchar({$schema['carrier_2_code']['length']}) null,
						carrier_2_balance decimal(13, 2) null,
						carrier_3_code varchar({$schema['carrier_3_code']['length']}) null,
						carrier_3_balance decimal(13, 2) null,
						amount decimal(13, 2) null,
						payments decimal(13, 2) null,
						credits decimal(13, 2) null,
						account_balance decimal(13, 2) null,
						reimbursement_memo varchar({$schema['reimbursement_memo']['length']}) not null
					)
				");
				
				//create our search conditions
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Invoice.profit_center_number']))
				{
					if ($conditions['Invoice.profit_center_number'] == 'Medical')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$conditions['Invoice.profit_center_number'] = $includedProfitCenters;
					}
					
					if ($conditions['Invoice.profit_center_number'] == 'blank')
					{
						// show blank or values that are not used
						$profitCenters = $this->Lookup->get('profit_centers');
						$conditions['Invoice.profit_center_number !='] = array_keys($profitCenters);
						unset($conditions['Invoice.profit_center_number']);
					}
				}
				
				switch ($conditions['Invoice.account_balance'])
				{
					case 0: // Non-zero Balance
						$conditions['Invoice.account_balance <>'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 1: // Credit Balance
						$conditions['Invoice.account_balance <'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 2: // Balance Due
						$conditions['Invoice.account_balance >'] = 0;
						unset($conditions['Invoice.account_balance']);
						break;
					case 3: // All Balances
						unset($conditions['Invoice.account_balance']);
						break;
				}
				
				if (isset($conditions['Invoice.line_1_carrier_code']))
				{
					$L1CarrierFilter = $conditions['Invoice.line_1_carrier_code'];
					unset($conditions['Invoice.line_1_carrier_code']);
				}
				
				if (isset($conditions['Invoice.line_1_date']))
				{
					$conditions['Invoice.line_1_date <='] = databaseDate($conditions['Invoice.line_1_date']);
					unset($conditions['Invoice.line_1_date']);
				}
				
				if (isset($conditions['Invoice.team']) && $conditions['Invoice.team'] == 'blank')
				{
					$conditions['Invoice.team'] = array('TEAM_??', '');
				}
				
				if (isset($conditions['Invoice.line_1_status']) && $conditions['Invoice.line_1_status'] == 'blank')
				{
					$conditions['Invoice.line_1_status'] = '';
				}
				
				if (isset($conditions['Invoice.carrier_code']))
				{
					$conditions['or'] = array(
						'carrier_1_code' => $conditions['Invoice.carrier_code'],
						'carrier_2_code' => $conditions['Invoice.carrier_code'],
						'carrier_3_code' => $conditions['Invoice.carrier_code']
					);
					unset($conditions['Invoice.carrier_code']);
				}
				
				//figure out our date range. We are going to batch records in one month intervals
				//so that we don't pull too much data at once
				if (isset($conditions['Invoice.date_of_service_start']))
				{
					$start = strtotime($conditions['Invoice.date_of_service_start']);
					unset($conditions['Invoice.date_of_service_start']);
				}
				else
				{
					$start = strtotime('1/1/1993');
				}
				
				if (isset($conditions['Invoice.date_of_service_end']))
				{
					$end = strtotime($conditions['Invoice.date_of_service_end']);
					unset($conditions['Invoice.date_of_service_end']);
				}
				else
				{
					$end = strtotime('+ 1 week');
				}
				
				while ($start <= $end)
				{
					//grab from start to 1 month later
					$subsetEnd = mktime(0, 0, 0, date('m', $start) + 1, 0, date('Y', $start));
					
					//if we've gone past the end, cap it 
					if ($subsetEnd > $end)
					{
						$subsetEnd = $end;	
					}
					
					//adjust the date conditions
					$conditions['Invoice.date_of_service between'] = array(
						date('Y-m-d', $start), 
						date('Y-m-d', $subsetEnd)
					);
					
					//pull the data
					$invoices = $this->Invoice->find('all', array(
						'fields' => array(
							'profit_center_number',
							'account_number',
							'department_code',
							'transaction_control_number',
							'invoice_number',
							'rental_or_purchase',
							'date_of_service',
							'billing_date',
							'line_1_status',
							'line_1_initials',
							'line_1_date',
							'line_1_carrier_number',
							'line_1_amount',
							'team',
							'carrier_1_code',
							'carrier_1_balance',
							'carrier_2_code',
							'carrier_2_balance',
							'carrier_3_code',
							'carrier_3_balance',
							'amount',
							'payments',
							'credits',
							'account_balance',
							'reimbursement_memo'
						),
						'conditions' => $conditions,
						'contain' => array()
					));
					
					$efnModel = ClassRegistry::init('ElectronicFileNote');
					
					//insert the aggregated amounts into the temp table
					foreach ($invoices as $invoice)
					{
						$fields = array_map(array('Sanitize', 'escape'), $invoice['Invoice']);
						
						// Find the mapped carrier code
						if ($fields['line_1_carrier_number'] != '')
						{
							$L1Carrier = $fields["carrier_{$fields['line_1_carrier_number']}_code"];
						}
						else
						{
							$L1Carrier = '';
						}
						
						// Filter out records that don't match the L1 Carrier filter
						if (isset($L1CarrierFilter) && ($L1CarrierFilter != $L1Carrier))
						{
							continue;
						}
						
						$efnFollowupDate = $efnModel->getOldestFollowupDateByInvoice($fields['account_number'], $fields['invoice_number'], $fields['transaction_control_number']);
						
						$db->query("
							insert into {$table} (
								profit_center_number,
								account_number,
								department_code,
								transaction_control_number,
								invoice_number,
								rental_or_purchase,
								date_of_service,
								billing_date,
								line_1_status,
								line_1_initials,
								line_1_date,
								line_1_carrier_code,
								line_1_amount,
								team,
								efn_followup_date,
								carrier_1_code,
								carrier_1_balance,
								carrier_2_code,
								carrier_2_balance,
								carrier_3_code,
								carrier_3_balance,
								amount,
								payments,
								credits,
								account_balance,
								reimbursement_memo
							)
							values (
								'{$fields['profit_center_number']}',
								'{$fields['account_number']}',
								'{$fields['department_code']}',
								'{$fields['transaction_control_number']}',
								'{$fields['invoice_number']}',
								'{$fields['rental_or_purchase']}',
								'" . databaseDate($fields['date_of_service']) . "',
								" . ($fields['billing_date'] != '' ? "'" . databaseDate($fields['billing_date']) . "'" : 'null') . ",
								'{$fields['line_1_status']}',
								'{$fields['line_1_initials']}',
								" . ($fields['line_1_date'] != '' ? "'" . databaseDate($fields['line_1_date']) . "'" : 'null') . ",
								'{$L1Carrier}',
								" . ($fields['line_1_amount'] != '' ? $fields['line_1_amount'] : 'null') . ",
								'{$fields['team']}',
								" . ($efnFollowupDate != '' ? "'" . databaseDate($efnFollowupDate) . "'" : 'null') . ",
								'{$fields['carrier_1_code']}',
								'{$fields['carrier_1_balance']}',
								'{$fields['carrier_2_code']}',
								" . ($fields['carrier_2_balance'] != '' ? $fields['carrier_2_balance'] : 'null') . ",
								'{$fields['carrier_3_code']}',
								" . ($fields['carrier_3_balance'] != '' ? $fields['carrier_3_balance'] : 'null') . ",
								" . ($fields['amount'] != '' ? $fields['amount'] : 'null') . ",
								" . ($fields['payments'] != '' ? $fields['payments'] : 'null') . ",
								" . ($fields['credits'] != '' ? $fields['credits'] : 'null') . ",
								" . ($fields['account_balance'] != '' ? $fields['account_balance'] : 'null') . ",
								'{$fields['reimbursement_memo']}'
							)
						", false);
					}
					
					//advance the start to one day past the subset end
					$start = mktime(0, 0, 0, date('m', $subsetEnd), date('d', $subsetEnd) + 1, date('Y', $subsetEnd));
				}
			}
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Invoice', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 50,
					'page' => 1,
					'order' => 'date_of_service'
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$invoices = $this->paginate($modelName);
				
				$this->set('invoices', $invoices);
			}
			
			$profitCenters = array_merge(
				array('Medical' => 'All Medical'),
				$this->Lookup->get('profit_centers', true, true),
				array('blank' => 'BLANK')
			);
			$departments = $this->Department->getCodeList();
			$line1Statuses = $this->Lookup->get('line_1_statuses', true);
			$line1Statuses['blank'] = 'BLANK';
			$teamOptions = array(
				'TEAM1' => 'Team_1',
				'TEAM2' => 'Team_2',
				'TEAM3' => 'Team_3',
				'TEAM4' => 'Team_4',
				'blank' => 'Team_??'
			);
			$this->set(compact('profitCenters', 'departments', 'line1Statuses', 'teamOptions', 'isPostback'));
		}
		
		/**
		 * Exports the management results to CSV.
		 */
		function ajax_exportManagementResults()
		{
			set_time_limit(0);
			$this->autoRenderAjax = false;
			
			//figure out the table to grab the results from
			$table = $this->_managementTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			//create the model
			$db = ConnectionManager::getDataSource('default');
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Invoice', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull the transactions
			$query = array('order' => 'date_of_service');
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('invoices', $model->find('all', $query));
		}
		
		/**
		 * Exports the management results to CSV for claims.
		 */
		function ajax_exportManagementResultsForClaims()
		{
			set_time_limit(0);
			$this->autoRenderAjax = false;
			
			//figure out the table to grab the results from
			$table = $this->_managementTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			//create the model
			$db = ConnectionManager::getDataSource('default');
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Invoice', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull the transactions
			$query = array('order' => 'date_of_service');
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('invoices', $model->find('all', $query));
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the management module.
		 * @param string $username The users username.
		 * @return string The unique table name.
		 */
		function _managementTempTableName($username)
		{
			return 'temp_invoice_management_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Show extended details for a specified invoice.
		 * @param int $accountNumber The customer's account number.
		 * @param int $invoiceNumber The invoice number.
		 */
		function module_details($accountNumber, $invoiceNumber)
		{
			$this->data = $this->Invoice->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber,
					'invoice_number' => $invoiceNumber
				)
			));
			
			if ($this->data !== false)
			{
				$this->data['Invoice']['date_of_service'] = formatDate($this->data['Invoice']['date_of_service']);
				$this->data['Invoice']['billing_date'] = formatDate($this->data['Invoice']['billing_date']);
				$this->data['Invoice']['posting_period_date'] = formatDate($this->data['Invoice']['posting_period_date']);
				$this->data['Invoice']['creation_date'] = formatDate($this->data['Invoice']['creation_date']);
				$this->data['Invoice']['line_1_date'] = formatDate($this->data['Invoice']['line_1_date']);
				$this->data['Invoice']['line_2_date'] = formatDate($this->data['Invoice']['line_2_date']);
				$this->data['Invoice']['line_3_date'] = formatDate($this->data['Invoice']['line_3_date']);
				$this->data['Invoice']['line_4_date'] = formatDate($this->data['Invoice']['line_4_date']);
				$this->data['Invoice']['remittance_date'] = formatDate($this->data['Invoice']['remittance_date']);
				
				$customerCarrierModel = ClassRegistry::init('CustomerCarrier');
				
				$carriers[1] = $customerCarrierModel->find('first', array(
					'contain' => array('Carrier'),
					'conditions' => array(
						'account_number' => $accountNumber,
						'carrier_number' => $this->data['Invoice']['carrier_1_code']
					)
				));
				
				$carriers[2] = $customerCarrierModel->find('first', array(
					'contain' => array('Carrier'),
					'conditions' => array(
						'account_number' => $accountNumber,
						'carrier_number' => $this->data['Invoice']['carrier_2_code']
					)
				));
				
				$carriers[3] = $customerCarrierModel->find('first', array(
					'contain' => array('Carrier'),
					'conditions' => array(
						'account_number' => $accountNumber,
						'carrier_number' => $this->data['Invoice']['carrier_3_code']
					)
				));
				
				$this->set(compact('carriers'));
			}
		}
		
		/**
		 * Screen to list the invoices for a chosen account to modify through the utility.
		 */
		function utilityList()
		{
			$this->pageTitle = 'Invoice Utility';
			
			$filterName = 'invoiceUtilityData';
			
			if (!isset($this->data) && $this->Session->check($filterName))
			{
				$this->data = $this->Session->read($filterName);
			}
			
			if (isset($this->data))
			{
				$this->Session->write($filterName, $this->data);
				
				// Look up customer
				$customer = $this->Customer->find('first', array(
					'contain' => array(),
					'fields' => array('name'),
					'conditions' => array('account_number' => $this->data['Invoice']['account_number'])
				));
				
				// Paginate invoices
				$this->paginate = array(
					'contain' => array(),
					'fields' => array(
						'id',
						'invoice_number',
						'date_of_service',
						'transaction_control_number',
						'amount'
					),
					'conditions' => array('account_number' => $this->data['Invoice']['account_number']),
					'order' => 'date_of_service desc'
				);
				
				$records = $this->paginate('Invoice');
				
				$this->set(compact('customer', 'records'));
			}
		}
		
		/**
		 * Screen to edit the fields of the invoice.
		 * @param int $id The ID of the invoice to edit.
		 */
		function utilityEdit($id)
		{
			$this->pageTitle = 'Invoice Utility';
			
			$original = $this->Invoice->find('first', array(
				'contain' => array(),
				'fields' => array(
					'id',
					'account_number',
					'invoice_number',
					'billing_date',
					'date_of_service',
					'amount',
					'department_code',
					'carrier_1_code',
					'carrier_2_code',
					'carrier_3_code'
				),
				'conditions' => array('id' => $id)
			));
			
			$original['Invoice']['amount'] = number_format($original['Invoice']['amount'], 2, '.', '');
			formatDatesInArray($original['Invoice'], array('billing_date', 'date_of_service'));
			
			if (isset($this->data))
			{
				$original['Invoice']['billing_date'] = databaseDate($original['Invoice']['billing_date']);
				$original['Invoice']['date_of_service'] = databaseDate($original['Invoice']['date_of_service']);
				$this->data['Invoice']['billing_date'] = databaseDate($this->data['Invoice']['billing_date']);
				$this->data['Invoice']['date_of_service'] = databaseDate($this->data['Invoice']['date_of_service']);
				
				$changes = array_diff_assoc($this->data['Invoice'], $original['Invoice']);
				
				if (count($changes) == 0)
				{
					$this->redirect('/invoices/utilityList');
				}
				
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
						"-impersonate %s -account %s -invoice %s " .
						"%s %s %s %s %s %s %s %s %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						escapeshellarg($original['Invoice']['account_number']),
						escapeshellarg($id),
						isset($changes['account_number']) ? '-accountNumber ' . escapeshellarg($changes['account_number']) : '',
						isset($changes['invoice_number']) ? '-invoiceNumber ' . escapeshellarg($changes['invoice_number']) : '',
						isset($changes['billing_date']) ? '-invBillDate ' . escapeshellarg($changes['billing_date']) : '',
						isset($changes['date_of_service']) ? '-invDateOfService ' . escapeshellarg($changes['date_of_service']) : '',
						isset($changes['amount']) ? '-invAmt ' . escapeshellarg($changes['amount']) : '',
						isset($changes['department_code']) ? '-dept ' . escapeshellarg($changes['department_code']) : '',
						isset($changes['carrier_1_code']) ? '-invCarr1 ' . escapeshellarg($changes['carrier_1_code']) : '',
						isset($changes['carrier_2_code']) ? '-invCarr2 ' . escapeshellarg($changes['carrier_2_code']) : '',
						isset($changes['carrier_3_code']) ? '-invCarr3 ' . escapeshellarg($changes['carrier_3_code']) : ''
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			else
			{
				$this->data = $original;
			}
			
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'departments'));
		}
		
		/**
		 * Launch the process to delete an invoice.
		 * @param string $accountNumber The account number of the invoice.
		 * @param int $invoiceID The ID of the invoice to remove.
		 */
		function utilityDelete($accountNumber, $invoiceID)
		{
			$this->autoRender = false;
			
			shell_exec(
				sprintf(
					"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
					"-impersonate %s -account %s -invoice %s -delete > /dev/null 2>&1 &",
					escapeshellarg(ROOT),
					escapeshellarg($this->Session->read('user')),
					escapeshellarg($accountNumber),
					escapeshellarg($invoiceID)
				)
			);
			
			$this->redirect('/processes/manager/reset:1');
		}
		
		/**
		 * Move transactions on invoice to a new carrier.
		 */
		function utilitySwitchCarrier()
		{
			$this->pageTitle = 'Switch Carrier Utility';
			
			if (isset($this->data['Invoice']['carrier_code']))
			{
				$invoiceID = $this->Invoice->field('id', array(
					'account_number' => $this->data['Invoice']['account_number'],
					'invoice_number' => $this->data['Invoice']['invoice_number']
				));
				
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
						"-impersonate %s -account %s -invoice %s %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						escapeshellarg($this->data['Invoice']['account_number']),
						escapeshellarg($invoiceID),
						"-invCarr{$this->data['Invoice']['carrier_number']} " . escapeshellarg($this->data['Invoice']['carrier_code'])
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			else if (isset($this->data['Invoice']['account_number']))
			{
				$invoice = $this->Invoice->find('first', array(
					'contain' => array(),
					'fields' => array(
						'carrier_1_code',
						'carrier_1_balance',
						'carrier_2_code',
						'carrier_2_balance',
						'carrier_3_code',
						'carrier_3_balance'
					),
					'conditions' => array(
						'account_number' => $this->data['Invoice']['account_number'],
						'invoice_number' => $this->data['Invoice']['invoice_number']
					)
				));
				
				$this->set(compact('invoice'));
			}
		}
	}
?>