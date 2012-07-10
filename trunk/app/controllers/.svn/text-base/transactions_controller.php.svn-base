<?php
	class TransactionsController extends AppController
	{
		var $uses = array(
			'Transaction',
			'Lookup',
			'TransactionType',
			'Department',
			'Setting',
			'Customer',
			'CustomerBilling'
		);
		
		var $components = array('DefaultFile');
		var $helpers = array('Paginator');
		
		/**
		 * Module to display the detail about a particular transaction.
		 * @param int $transactionID The ID of the transaction to examine.
		 */
		function module_detail($transactionID)
		{
			$this->data = $this->Transaction->find('first', array(
				'conditions' => array('id' => $transactionID),
				'contain' => array()
			));
		}
		
		/**
		 * Container action for transaction modules.
		 */
		function management()
		{
			$this->pageTitle = 'Transaction Management';
		}
		
		/**
		 * Displays the transaction management module.
		 */
		function module_management()
		{
			//this can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_managementTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			//the user posted a new search
			if (!empty($this->data))
			{
				$competitiveBidZipsModel = ClassRegistry::init('CompetitiveBidZipCode');
				$competitiveBidHcpcsModel = ClassRegistry::init('CompetitiveBidHcpc');
				$customerModel = ClassRegistry::init('Customer');
				
				//create the temp table in MySQL
				//grab transaction schema so we can make the columns the right size if we need to craate the table
				$schema = $this->Transaction->schema();
				
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						transaction_type varchar({$schema['transaction_type']['length']}) not null,
						unique_identification_number varchar({$schema['unique_identification_number']['length']}) not null,
						transaction_date_of_service date not null,
						general_ledger_code varchar({$schema['general_ledger_code']['length']}) not null,
						general_ledger_description varchar({$schema['general_ledger_description']['length']}) not null,
						account_number varchar({$schema['account_number']['length']}) not null,
						setup_date date null,
						competitive_bid_zip_code_flag varchar(1) not null,
						invoice_number varchar({$schema['invoice_number']['length']}) not null,
						carrier_1_number varchar({$schema['carrier_number']['length']}) not null,
						carrier_1_amount decimal(13, 2) not null,
						carrier_2_number varchar({$schema['carrier_number']['length']}) null,
						carrier_2_amount decimal(13, 2) null,
						carrier_3_number varchar({$schema['carrier_number']['length']}) null,
						carrier_3_amount decimal(13, 2) null,
						total_amount decimal(13, 2) not null,
						quantity int null,
						inventory_number varchar({$schema['inventory_number']['length']}) not null,
						inventory_description varchar({$schema['inventory_description']['length']}) not null,
						inventory_group_code varchar({$schema['inventory_group_code']['length']}) not null,
						healthcare_procedure_code varchar({$schema['healthcare_procedure_code']['length']}) not null,
						competitive_bid_hcpc_flag varchar(1) not null,
						profit_center_number varchar({$schema['profit_center_number']['length']}) not null,
						salesman_number varchar({$schema['salesman_number']['length']}) not null,
						department_code varchar({$schema['department_code']['length']}) not null,
						period_posting_date date not null,
						transaction_control_number varchar({$schema['transaction_control_number']['length']}) not null,
						transaction_control_number_file varchar({$schema['transaction_control_number_file']['length']}) not null,
						rental_or_purchase varchar({$schema['rental_or_purchase']['length']}) not null,
						serial_number varchar({$schema['serial_number']['length']}) not null,
						physician_number varchar({$schema['physician_number']['length']}) not null,
						client_zip_code varchar({$schema['client_zip_code']['length']}) not null,
						long_term_care_facility_number int,
						long_term_care_facility_type varchar(6),
						referral_number_from_aaa_file int,
						client_name varchar(30) not null,
						address_1 varchar(30) not null,
						address_2 varchar(30) not null,
						city_state varchar(30) not null,
						zip_code varchar(9) not null,
						phone_number varchar(14) not null,
						is_deceased bool not null,
						unique key (transaction_type, unique_identification_number, general_ledger_code)
					)
				");
				
				//create our search conditions
				$conditions = Set::filter($this->postConditions($this->data));
				
				$useDOS = false;
				
				//figure out our date range. We are going to batch records in one month intervals
				//so that we don't pull too much data at once
				if (isset($conditions['Transaction.transaction_date_of_service_start']))
				{
					$start = strtotime($conditions['Transaction.transaction_date_of_service_start']);
					$useDOS = true;
				}
				else if (isset($conditions['Transaction.period_posting_date_start']))
				{
					$start = strtotime($conditions['Transaction.period_posting_date_start']);
				}
				else
				{
					$start = strtotime('1/1/1993');
				}
				
				if (isset($conditions['Transaction.transaction_date_of_service_end']))
				{
					$end = strtotime($conditions['Transaction.transaction_date_of_service_end']);
					$useDOS = true;
				}
				else if (isset($conditions['Transaction.period_posting_date_end']))
				{
					$end = strtotime($conditions['Transaction.period_posting_date_end']);
				}
				else
				{
					$end = strtotime('+ 1 week');
				}
				
				unset($conditions['Transaction.transaction_date_of_service_start']);
				unset($conditions['Transaction.transaction_date_of_service_end']);
				unset($conditions['Transaction.period_posting_date_start']);
				unset($conditions['Transaction.period_posting_date_end']);
				
				// Parse fields that allow multiple comma-separated values
				if (array_key_exists('Transaction.invoice_number', $conditions))
				{
					$conditions['Transaction.invoice_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.invoice_number']));
				}
				
				if (array_key_exists('Transaction.referral_number_from_aaa_file', $conditions))
				{
					$conditions['Transaction.referral_number_from_aaa_file'] = explode(',', str_replace(' ', '', $conditions['Transaction.referral_number_from_aaa_file']));
				}
				
				if (array_key_exists('Transaction.long_term_care_facility_number', $conditions))
				{
					$conditions['Transaction.long_term_care_facility_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.long_term_care_facility_number']));
				}
				
				if (array_key_exists('Transaction.physician_number', $conditions))
				{
					$conditions['Transaction.physician_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.physician_number']));
				}
				
				if (array_key_exists('Transaction.inventory_number', $conditions))
				{
					$conditions['Transaction.inventory_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.inventory_number']));
				}
				
				if (array_key_exists('Transaction.general_ledger_code', $conditions))
				{
					$conditions['Transaction.general_ledger_code'] = explode(',', str_replace(' ', '', $conditions['Transaction.general_ledger_code']));
				}
				
				if (array_key_exists('Transaction.healthcare_procedure_code', $conditions))
				{
					$conditions['Transaction.healthcare_procedure_code'] = explode(',', str_replace(' ', '', $conditions['Transaction.healthcare_procedure_code']));
				}
				
				if (array_key_exists('Transaction.carrier_number', $conditions))
				{
					$conditions['Transaction.carrier_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.carrier_number']));
				}
				
				//pull transaction types so we can adjust amounts
				$types = ClassRegistry::init('TransactionType')->find('all', array(
					'fields' => array('code', 'is_amount_subtracted'),
					'contain' => array()
				));
				
				$isSubtracted = Set::combine($types, '{n}.TransactionType.code', '{n}.TransactionType.is_amount_subtracted');
				
				while ($start <= $end)
				{
					//grab from start to 1 month later
					$subsetEnd = mktime(0, 0, 0, date('m', $start) + 1, 0, date('Y', $start));
					
					//if we've gone past the end, cap it 
					if ($subsetEnd > $end)
					{
						$subsetEnd = $end;	
					}
					
					$dateField = $useDOS ? 'Transaction.transaction_date_of_service' : 'Transaction.period_posting_date';
					
					//adjust the date conditions
					$conditions[$dateField . ' between'] = array(
						date('Y-m-d', $start), 
						date('Y-m-d', $subsetEnd)
					);
					
					//pull the data
					$transactions = $this->Transaction->find('all', array(
						'fields' => array(
							'transaction_type',
							'unique_identification_number',
							'transaction_date_of_service',
							'amount',
							'general_ledger_code',
							'general_ledger_description',
							'account_number',
							'invoice_number',
							'carrier_number',
							'quantity',
							'inventory_number',
							'inventory_description',
							'inventory_group_code',
							'healthcare_procedure_code',
							'profit_center_number',
							'salesman_number',
							'department_code',
							'period_posting_date',
							'transaction_control_number',
							'transaction_control_number_file',
							'rental_or_purchase',
							'serial_number',
							'physician_number',
							'client_zip_code',
							'long_term_care_facility_number',
							'referral_number_from_aaa_file'
						),
						'conditions' => $conditions,
						'contain' => array()
					));
					
					//insert the aggregated amounts into the temp table
					foreach ($transactions as $transaction)
					{
						$fields = array_map(array('Sanitize', 'escape'), $transaction['Transaction']);
						$uniqueID = str_replace(' ', '', $fields['unique_identification_number']);
						
						//adjust the amount if necessary
						$fields['amount'] = $isSubtracted[$fields['transaction_type']] ? ($fields['amount'] * -1) : $fields['amount'];
						
						//the unique ID is typically in the form of "<invoice number>.<line item number>". If
						//we don't have one, it's probably a transaction that came from post transactions, in which
						//case we'll just forge our own ID
						if ($uniqueID == '')
						{
							$uniqueID = $fields['invoice_number'] . '.X';
						}
						
						if ($fields['long_term_care_facility_number'] != null)
						{
							$fields['long_term_care_facility_type'] = ClassRegistry::init('AaaReferral')->field('facility_type', array('aaa_number' => $fields['long_term_care_facility_number']));
						}
						
						$isCBZip = $competitiveBidZipsModel->find('count', array(
							'conditions' => array(
								'competitive_bid_zip_code' => $fields['client_zip_code']
							),
							'index' => 'A'
						));
						
						$isCBHcpc = $competitiveBidHcpcsModel->find('count', array(
							'conditions' => array(
								'healthcare_procedure_code' => $fields['healthcare_procedure_code']
							),
							'index' => 'A'
						));
						
						$setupDate = databaseDate($customerModel->field('setup_date', array('account_number' => $fields['account_number'])));
						
						// Get extra fields from the CustomerBilling record
						$billingPointer = $this->Customer->field('billing_pointer', array('account_number' => $fields['account_number']));
						$billingRecord = $this->CustomerBilling->find('first', array(
							'contain' => array(),
							'conditions' => array('id' => $billingPointer)
						));
						$billingFields = array_map(array('Sanitize', 'escape'), $billingRecord['CustomerBilling']);
						
						$db->query("
							insert into {$table} (
								transaction_type,
								unique_identification_number,
								transaction_date_of_service,
								general_ledger_code,
								general_ledger_description,
								account_number,
								setup_date,
								competitive_bid_zip_code_flag,
								invoice_number,
								carrier_1_number,
								carrier_1_amount,
								total_amount,
								quantity,
								inventory_number,
								inventory_description,
								inventory_group_code,
								healthcare_procedure_code,
								competitive_bid_hcpc_flag,
								profit_center_number,
								salesman_number,
								department_code,
								period_posting_date,
								transaction_control_number,
								transaction_control_number_file,
								rental_or_purchase,
								serial_number,
								physician_number,
								client_zip_code,
								long_term_care_facility_number,
								long_term_care_facility_type,
								referral_number_from_aaa_file,
								client_name,
								address_1,
								address_2,
								city_state,
								zip_code,
								phone_number,
								is_deceased
							)
							values (
								'{$fields['transaction_type']}',
								'" . $uniqueID . "',
								'" . databaseDate($fields['transaction_date_of_service']) . "',
								'{$fields['general_ledger_code']}',
								'{$fields['general_ledger_description']}',
								'{$fields['account_number']}',
								" . ($setupDate != '' ? "'" . $setupDate . "'" : 'null') . ",
								'" . ($isCBZip ? 'B' : '') . "',
								'{$fields['invoice_number']}',
								'{$fields['carrier_number']}',
								{$fields['amount']},
								{$fields['amount']},
								" . (($fields['quantity'] != null && is_numeric($fields['quantity'])) ? $fields['quantity'] : 'null') . ",
								'{$fields['inventory_number']}',
								'{$fields['inventory_description']}',
								'{$fields['inventory_group_code']}',
								'{$fields['healthcare_procedure_code']}',
								'" . ($isCBZip && $isCBHcpc ? 'H' : '') . "',
								'{$fields['profit_center_number']}',
								'{$fields['salesman_number']}',
								'{$fields['department_code']}',
								'" . databaseDate($fields['period_posting_date']) . "',
								'{$fields['transaction_control_number']}',
								'{$fields['transaction_control_number_file']}',
								'{$fields['rental_or_purchase']}',
								'{$fields['serial_number']}',
								'{$fields['physician_number']}',
								'{$fields['client_zip_code']}',
								" . ($fields['long_term_care_facility_number'] != null ? $fields['long_term_care_facility_number'] : 'null') . ",
								" . (isset($fields['long_term_care_facility_type']) && $fields['long_term_care_facility_type'] != false ? "'{$fields['long_term_care_facility_type']}'" : 'null') . ",
								" . ($fields['referral_number_from_aaa_file'] != null ? $fields['referral_number_from_aaa_file'] : 'null') . ",
								'{$billingFields['billing_name']}',
								'{$billingFields['address_1']}',
								'{$billingFields['address_2']}',
								'{$billingFields['city']}',
								'{$billingFields['zip_code']}',
								'{$billingFields['phone_number']}',
								'{$billingFields['is_deceased']}'
							)
							on duplicate key update
							carrier_1_amount = case when carrier_1_number = '{$fields['carrier_number']}' then carrier_1_amount + {$fields['amount']} else carrier_1_amount end,
							carrier_2_number = case when carrier_1_number <> '{$fields['carrier_number']}' and carrier_2_number is null then '{$fields['carrier_number']}' else carrier_2_number end,
							carrier_2_amount = case when carrier_2_number = '{$fields['carrier_number']}' then ifnull(carrier_2_amount, 0) + {$fields['amount']} else carrier_2_amount end,
							carrier_3_number = case when carrier_1_number <> '{$fields['carrier_number']}' and carrier_2_number <> '{$fields['carrier_number']}' and carrier_3_number is null then '{$fields['carrier_number']}' else carrier_3_number end,
							carrier_3_amount = case when carrier_3_number = '{$fields['carrier_number']}' then ifnull(carrier_3_amount, 0) + {$fields['amount']} else carrier_3_amount end,
							total_amount = total_amount + {$fields['amount']}
						", false);
					}
					
					//advance the start to one day past the subset end
					$start = mktime(0, 0, 0, date('m', $subsetEnd), date('d', $subsetEnd) + 1, date('Y', $subsetEnd));
				}
			}
			else if (!$isPostback)
			{
				//new search
				$this->DefaultFile->load();
				//$this->data['Transaction']['period_posting_date_start'] = formatU05Date($this->DefaultFile->data['current_post_period']);
				//$this->data['Transaction']['period_posting_date_end'] = date('m/d/Y', strtotime($this->data['Transaction']['period_posting_date_start'] . ' + 1 month - 1 day'));
				
				//pull lookup data
				$this->set(array(
					'profitCenters' => $this->Lookup->get('profit_centers', true, true),
					'rentalPurchase' => array('R' => 'R', 'P' => 'P'),
					'departments' => $this->Department->getCodeList()
				));
			}
			
			//pull transaction types into a hash
			$this->set('transactionTypes', Set::combine(
				$this->TransactionType->find('all', array('fields' => array('code', 'description'), 'contain' => array())),
				'/TransactionType/code',
				'/TransactionType/description'
			));
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 50,
					'page' => 1,
					'order' => 'period_posting_date desc'
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$transactions = $this->paginate($modelName);
				
				$this->set('transactions', $transactions);
			}
			
			$this->set('isPostback', $isPostback);
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
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull transaction types into a hash
			$this->set('transactionTypes', Set::combine(
				$this->TransactionType->find('all', array('fields' => array('code', 'description'), 'contain' => array())),
				'/TransactionType/code',
				'/TransactionType/description'
			));
			
			//pull the transactions
			$query = array('order' => 'period_posting_date desc');
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('transactions', $model->find('all', $query));
		}
		
		/**
		 * Exports the contact results to CSV.
		 */
		function ajax_exportContactResults()
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
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull transaction types into a hash
			$this->set('transactionTypes', Set::combine(
				$this->TransactionType->find('all', array('fields' => array('code', 'description'), 'contain' => array())),
				'/TransactionType/code',
				'/TransactionType/description'
			));
			
			//pull the transactions
			$query = array('order' => 'period_posting_date desc');
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$results = $model->find('all', $query);
			$output = array();
			
			foreach ($results as $row)
			{
				$account = $row['Transaction']['account_number'];
				$invoice = $row['Transaction']['invoice_number'];
				
				// Group records by invoice on the account, showing the oldest transaction
				if (!isset($output[$account][$invoice]) || $row['Transaction']['transaction_date_of_service'] < $output[$account][$invoice]['transaction_date_of_service'])
				{
					$output[$account][$invoice] = $row['Transaction'];
				}
			}
			
			$this->set('output', $output);
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the management module.
		 * @param string $username The users username.
		 * @return string The unique table name.
		 */
		function _managementTempTableName($username)
		{
			return 'temp_transaction_management_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the management module.
		 * @param string $username The users username.
		 * @return string The unique table name.
		 */
		function _relatedTempTableName($username)
		{
			return 'temp_transaction_related_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Displays all transactions that appear on the invoices that get matched by the filtered transactions.
		 */
		function module_related()
		{
			//this can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_relatedTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			$costOfGoodsSoldModel = ClassRegistry::init('CostOfGoodsSold');
			$transactionCOGSModel = ClassRegistry::init('TransactionCostOfGoodsSold');
			$invoiceModel = ClassRegistry::init('Invoice');
			
			//the user posted a new search
			if (!empty($this->data))
			{
				//create the temp table in MySQL
				//grab transaction schema so we can make the columns the right size if we need to craate the table
				$schema = $this->Transaction->schema();
				
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						transaction_type varchar({$schema['transaction_type']['length']}) not null,
						unique_identification_number varchar({$schema['unique_identification_number']['length']}) not null,
						transaction_date_of_service date not null,
						general_ledger_code varchar({$schema['general_ledger_code']['length']}) not null,
						general_ledger_description varchar({$schema['general_ledger_description']['length']}) not null,
						account_number varchar({$schema['account_number']['length']}) not null,
						invoice_number varchar({$schema['invoice_number']['length']}) not null,
						carrier_number varchar({$schema['carrier_number']['length']}) not null,
						amount decimal(13, 2) not null,
						inventory_number varchar({$schema['inventory_number']['length']}) not null,
						inventory_description varchar({$schema['inventory_description']['length']}) not null,
						healthcare_procedure_code varchar({$schema['healthcare_procedure_code']['length']}) not null,
						profit_center_number varchar({$schema['profit_center_number']['length']}) not null,
						department_code varchar({$schema['department_code']['length']}) not null,
						period_posting_date date not null,
						transaction_control_number varchar({$schema['transaction_control_number']['length']}) not null,
						transaction_control_number_file varchar({$schema['transaction_control_number_file']['length']}) not null,
						cost_of_goods_sold text not null,
						rental_or_purchase char(1) not null,
						unique key (transaction_type, unique_identification_number, general_ledger_code)
					)
				");
				
				//create our search conditions
				$conditions = Set::filter($this->postConditions($this->data));
				
				$useDOS = false;
				
				//figure out our date range. We are going to batch records in one month intervals
				//so that we don't pull too much data at once
				if (isset($conditions['Transaction.period_posting_date_start']))
				{
					$start = strtotime($conditions['Transaction.period_posting_date_start']);
				}
				else
				{
					$start = strtotime('1/1/1993');
				}
				
				if (isset($conditions['Transaction.period_posting_date_end']))
				{
					$end = strtotime($conditions['Transaction.period_posting_date_end']);
				}
				else
				{
					$end = strtotime('+ 1 week');
				}
				
				unset($conditions['Transaction.period_posting_date_start']);
				unset($conditions['Transaction.period_posting_date_end']);
				
				// Parse fields that allow multiple comma-separated values
				if (array_key_exists('Transaction.inventory_number', $conditions))
				{
					$conditions['Transaction.inventory_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.inventory_number']));
				}
				
				if (array_key_exists('Transaction.general_ledger_code', $conditions))
				{
					$conditions['Transaction.general_ledger_code'] = explode(',', str_replace(' ', '', $conditions['Transaction.general_ledger_code']));
				}
				
				if (array_key_exists('Transaction.healthcare_procedure_code', $conditions))
				{
					$conditions['Transaction.healthcare_procedure_code'] = explode(',', str_replace(' ', '', $conditions['Transaction.healthcare_procedure_code']));
				}
				
				if (array_key_exists('Transaction.carrier_number', $conditions))
				{
					$conditions['Transaction.carrier_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.carrier_number']));
				}
				
				if (array_key_exists('Transaction.invoice_number', $conditions))
				{
					$conditions['Transaction.invoice_number'] = explode(',', str_replace(' ', '', $conditions['Transaction.invoice_number']));
				}
				
				//pull transaction types so we can adjust amounts
				$types = ClassRegistry::init('TransactionType')->find('all', array(
					'fields' => array('code', 'is_amount_subtracted'),
					'contain' => array()
				));
				
				$isSubtracted = Set::combine($types, '{n}.TransactionType.code', '{n}.TransactionType.is_amount_subtracted');
				$costOfGoods = array();
				
				while ($start <= $end)
				{
					//grab from start to 1 month later
					$subsetEnd = mktime(0, 0, 0, date('m', $start) + 1, 0, date('Y', $start));
					
					//if we've gone past the end, cap it 
					if ($subsetEnd > $end)
					{
						$subsetEnd = $end;	
					}
					
					$dateField = $useDOS ? 'Transaction.transaction_date_of_service' : 'Transaction.period_posting_date';
					
					//adjust the date conditions
					$conditions[$dateField . ' between'] = array(
						date('Y-m-d', $start), 
						date('Y-m-d', $subsetEnd)
					);
					
					//pull the invoices based on the filters
					$invoices = $this->Transaction->find('all', array(
						'contain' => array(),
						'fields' => array(
							'invoice_number'
						),
						'conditions' => $conditions
					));
					
					if ($invoices !== false)
					{
						$invoices = array_values(array_unique(Set::extract('/Transaction/invoice_number', $invoices)));
						
						//pull all transactions for matching invoices
						$transactions = $this->Transaction->find('all', array(
							'fields' => array(
								'transaction_type',
								'unique_identification_number',
								'transaction_date_of_service',
								'amount',
								'cost_1',
								'general_ledger_code',
								'general_ledger_description',
								'account_number',
								'invoice_number',
								'carrier_number',
								'inventory_number',
								'inventory_description',
								'healthcare_procedure_code',
								'profit_center_number',
								'department_code',
								'period_posting_date',
								'transaction_control_number',
								'transaction_control_number_file'
							),
							'conditions' => array(
								'invoice_number' => $invoices
							),
							'contain' => array()
						));
						
						//insert the aggregated amounts into the temp table
						if ($transactions !== false)
						{
							//iterate to build the COGS values
							foreach ($transactions as $key => $transaction)
							{
								$fields = array_map(array('Sanitize', 'escape'), $transaction['Transaction']);
								$uniqueID = str_replace(' ', '', $fields['unique_identification_number']);
								
								//lookup the cost of goods sold, if we haven't already for the invoice
								if (!isset($costOfGoods[$fields['account_number']][$fields['invoice_number']]))
								{
									$cogsRecords = $costOfGoodsSoldModel->find('all', array(
										'contain' => array(),
										'fields' => array(
											'manufacturer_code',
											'manufacturer_invoice_amount'
										),
										'conditions' => array(
											'account_number' => $fields['account_number'],
											'invoice_number' => $fields['invoice_number']
										),
										'index' => 'D'
									));
									
									foreach ($cogsRecords as $cogsRow)
									{
										$costOfGoods[$fields['account_number']][$fields['invoice_number']]['total'] = ifset($costOfGoods[$fields['account_number']][$fields['invoice_number']]['total'], 0) + $cogsRow['CostOfGoodsSold']['manufacturer_invoice_amount'];
										$costOfGoods[$fields['account_number']][$fields['invoice_number']][] = $cogsRow;
									}
								}
								
								//get the transaction cost of goods sold by uniqueID
								$buCogsRecords = $transactionCOGSModel->find('all', array(
									'contain' => array(),
									'fields' => array(
										'cogs_1'
									),
									'conditions' => array(
										'unique_identification_number' => $uniqueID
									),
									'index' => 'E'
								));
								
								foreach ($buCogsRecords as $cogsRow)
								{
									if (isset($costOfGoods[$fields['account_number']][$fields['invoice_number']]['buTotal']))
									{
										$costOfGoods[$fields['account_number']][$fields['invoice_number']]['buTotal'] += $cogsRow['TransactionCostOfGoodsSold']['cogs_1'];
									}
									else
									{
										$costOfGoods[$fields['account_number']][$fields['invoice_number']]['buTotal'] = $cogsRow['TransactionCostOfGoodsSold']['cogs_1'];
									}
								}
							}
							
							//iterate again to insert final values into temp table
							foreach ($transactions as $key => $transaction)
							{
								$fields = array_map(array('Sanitize', 'escape'), $transaction['Transaction']);
								$uniqueID = str_replace(' ', '', $fields['unique_identification_number']);
								
								//adjust the amount if necessary
								$fields['amount'] = $isSubtracted[$fields['transaction_type']] ? ($fields['amount'] * -1) : $fields['amount'];
								
								if (!isset($invoiceRP[$fields['invoice_number']]))
								{
									$invoiceRP[$fields['invoice_number']] = $invoiceModel->field('rental_or_purchase', array('invoice_number' => $transaction['Transaction']['invoice_number']));
								}
								
								$fields['rental_or_purchase'] = $invoiceRP[$fields['invoice_number']];
								
								//the unique ID is typically in the form of "<invoice number>.<line item number>". If
								//we don't have one, it's probably a transaction that came from post transactions, in which
								//case we'll just forge our own ID
								if ($uniqueID == '')
								{
									$uniqueID = $fields['invoice_number'] . '.X';
								}
								
								$db->query("
									insert into {$table} (
										transaction_type,
										unique_identification_number,
										transaction_date_of_service,
										general_ledger_code,
										general_ledger_description,
										account_number,
										invoice_number,
										carrier_number,
										amount,
										inventory_number,
										inventory_description,
										healthcare_procedure_code,
										profit_center_number,
										department_code,
										period_posting_date,
										transaction_control_number,
										transaction_control_number_file,
										rental_or_purchase,
										cost_of_goods_sold
									)
									values (
										'{$fields['transaction_type']}',
										'" . $uniqueID . "',
										'" . databaseDate($fields['transaction_date_of_service']) . "',
										'{$fields['general_ledger_code']}',
										'{$fields['general_ledger_description']}',
										'{$fields['account_number']}',
										'{$fields['invoice_number']}',
										'{$fields['carrier_number']}',
										{$fields['amount']},
										'{$fields['inventory_number']}',
										'{$fields['inventory_description']}',
										'{$fields['healthcare_procedure_code']}',
										'{$fields['profit_center_number']}',
										'{$fields['department_code']}',
										'" . databaseDate($fields['period_posting_date']) . "',
										'{$fields['transaction_control_number']}',
										'{$fields['transaction_control_number_file']}',
										'{$fields['rental_or_purchase']}',
										'" . serialize(ifset($costOfGoods[$fields['account_number']][$fields['invoice_number']])) . "'
									)
									on duplicate key update
									amount = amount + {$fields['amount']}
								", false);
							}
						}
					}
					
					//advance the start to one day past the subset end
					$start = mktime(0, 0, 0, date('m', $subsetEnd), date('d', $subsetEnd) + 1, date('Y', $subsetEnd));
				}
			}
			else if (!$isPostback)
			{
				//new search
				//$this->DefaultFile->load();
				//$this->data['Transaction']['period_posting_date_start'] = formatU05Date($this->DefaultFile->data['current_post_period']);
				//$this->data['Transaction']['period_posting_date_end'] = date('m/d/Y', strtotime($this->data['Transaction']['period_posting_date_start'] . ' + 1 month - 1 day'));
				
				//pull lookup data
				$this->set(array(
					'profitCenters' => $this->Lookup->get('profit_centers', true, true),
					'rentalPurchase' => array('R' => 'R', 'P' => 'P'),
					'departments' => $this->Department->getCodeList()
				));
			}
			
			//pull transaction types into a hash
			$transactionTypes = $this->TransactionType->find('all', array(
				'fields' => array('code', 'description', 'is_transfer', 'is_amount_subtracted'),
				'order' => 'code',
				'contain' => array()
			));
			
			$transactionTypes = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}');
			$transactionTypeList = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}.TransactionType.description');
			
			$chargeType = $this->Setting->get('charge_transaction_type_id');
			$paymentType = $this->Setting->get('payment_transaction_type_id');
			$creditType = $this->Setting->get('credit_transaction_type_id');
			
			$this->set(compact('transactionTypes', 'chargeType', 'paymentType', 'creditType', 'transactionTypeList'));
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 50,
					'page' => 1,
					'order' => array(
						'profit_center_number',
						'account_number',
						'invoice_number',
						'transaction_date_of_service'
					)
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$transactions = $this->paginate($modelName);
				
				$this->set('transactions', $transactions);
			}
			
			$this->set('isPostback', $isPostback);
		}
		
		/**
		 * Exports the related results to CSV.
		 */
		function ajax_exportRelatedResults()
		{
			set_time_limit(0);
			$this->autoRenderAjax = false;
			
			//figure out the table to grab the results from
			$table = $this->_relatedTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			//create the model
			$db = ConnectionManager::getDataSource('default');
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'Transaction', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull transaction types into a hash
			$transactionTypes = $this->TransactionType->find('all', array(
				'fields' => array('code', 'description', 'is_transfer', 'is_amount_subtracted'),
				'order' => 'code',
				'contain' => array()
			));
			
			$transactionTypes = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}');
			
			$chargeType = $this->Setting->get('charge_transaction_type_id');
			$paymentType = $this->Setting->get('payment_transaction_type_id');
			$creditType = $this->Setting->get('credit_transaction_type_id');
			
			$this->set(compact('transactionTypes', 'chargeType', 'paymentType', 'creditType'));
			
			//pull the transactions
			$query = array(
				'order' => array(
					'profit_center_number',
					'account_number',
					'invoice_number',
					'transaction_date_of_service'
				)
			);
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('transactions', $model->find('all', $query));
		}
		
		/**
		 * Screen to list the transactions for a chosen account to modify through the utility.
		 */
		function utilityList()
		{
			$this->pageTitle = 'Transaction Utility';
			
			$filterName = 'transactionUtilityData';
			
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
					'conditions' => array('account_number' => $this->data['Transaction']['account_number'])
				));
				
				$conditions = array(
					'account_number' => $this->data['Transaction']['account_number']
				);
				
				if (isset($this->data['Transaction']['invoice_number']) && $this->data['Transaction']['invoice_number'] != '')
				{
					$conditions['invoice_number'] = $this->data['Transaction']['invoice_number'];
				}
				
				// Paginate transactions
				$this->paginate = array(
					'contain' => array(),
					'fields' => array(
						'id',
						'invoice_number',
						'transaction_date_of_service',
						'amount',
						'general_ledger_code',
						'transaction_type',
						'department_code',
						'carrier_number'
					),
					'conditions' => $conditions,
					'order' => 'transaction_date_of_service desc'
				);
				
				$records = $this->paginate('Transaction');
				
				$results = $this->TransactionType->find('all', array(
					'code' => array(),
					'fields' => array('code', 'description')
				));
				foreach ($results as $row)
				{
					$transactionTypes[$row['TransactionType']['code']] = $row['TransactionType']['description'];
				}
				
				$this->set(compact('customer', 'records', 'transactionTypes'));
			}
		}
		
		/**
		 * Screen to edit the fields of the transaction.
		 * @param int $id The ID of the invoice to edit.
		 */
		function utilityEdit($id)
		{
			$this->pageTitle = 'Transaction Utility';
			
			$original = $this->Transaction->find('first', array(
				'contain' => array(),
				'fields' => array(
					'id',
					'account_number',
					'invoice_number',
					'transaction_date_of_service',
					'amount',
					'general_ledger_code',
					'general_ledger_description',
					'transaction_type',
					'department_code',
					'serial_number',
					'carrier_number'
				),
				'conditions' => array('id' => $id)
			));
			
			$original['Transaction']['amount'] = number_format($original['Transaction']['amount'], 2, '.', '');
			
			formatDatesInArray($original['Transaction'], array('billing_date', 'date_of_service'));
			
			if (isset($this->data))
			{
				$original['Transaction']['transaction_date_of_service'] = databaseDate($original['Transaction']['transaction_date_of_service']);
				$this->data['Transaction']['transaction_date_of_service'] = databaseDate($this->data['Transaction']['transaction_date_of_service']);
				
				$changes = array_diff_assoc($this->data['Transaction'], $original['Transaction']);
				
				if (count($changes) == 0)
				{
					$this->redirect('/transactions/utilityList');
				}
				
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
						"-impersonate %s -account %s -transaction %s " .
						"%s %s %s %s %s %s %s %s %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						escapeshellarg($original['Transaction']['account_number']),
						escapeshellarg($id),
						isset($changes['invoice_number']) ? '-invoiceNumber ' . escapeshellarg($changes['invoice_number']) : '',
						isset($changes['transaction_date_of_service']) ? '-transDateOfService ' . escapeshellarg($changes['transaction_date_of_service']) : '',
						isset($changes['general_ledger_code']) ? '-transGLCode ' . escapeshellarg($changes['general_ledger_code']) : '',
						isset($changes['general_ledger_description']) ? '-transDesc ' . escapeshellarg($changes['general_ledger_description']) : '',
						isset($changes['transaction_type']) ? '-transType ' . escapeshellarg($changes['transaction_type']) : '',
						isset($changes['serial_number']) ? '-transSerial ' . escapeshellarg($changes['serial_number']) : '',
						isset($changes['department_code']) ? '-dept ' . escapeshellarg($changes['department_code']) : '',
						isset($changes['amount']) ? '-transAmt ' . escapeshellarg(' ' . $changes['amount']) : '',
						isset($changes['carrier_number']) ? '-transCarrier ' . escapeshellarg($changes['carrier_number']) : ''
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			else
			{
				$this->data = $original;
			}
						
			$results = $this->TransactionType->find('all', array(
				'code' => array(),
				'fields' => array('code', 'description')
			));
			foreach ($results as $row)
			{
				$transactionTypes[$row['TransactionType']['code']] = $row['TransactionType']['description'];
			}
			
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'transactionTypes', 'departments'));
		}
		
		/**
		 * Launch the process to delete a transaction.
		 * @param string $accountNumber The account number of the transaction.
		 * @param int $transactionID The ID of the transaction to remove.
		 */
		function utilityDelete($accountNumber, $transactionID)
		{
			$this->autoRender = false;
			
			shell_exec(
				sprintf(
					"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
					"-impersonate %s -account %s -transaction %s -delete > /dev/null 2>&1 &",
					escapeshellarg(ROOT),
					escapeshellarg($this->Session->read('user')),
					escapeshellarg($accountNumber),
					escapeshellarg($transactionID)
				)
			);
			
			$this->redirect('/processes/manager/reset:1');
		}
		
		/**
		 * Front end screen to launch sort and balance on account.
		 */
		function utilitySortBalance()
		{
			$this->pageTitle = 'Sort & Balance Utility';
			
			if (isset($this->data['Transaction']['account_number']))
			{
				$this->redirect("/transactions/utilityBalance/{$this->data['Transaction']['account_number']}");
			}
		}
		
		/**
		 * Launch the process to sort and balance an account.
		 * @param string $accountNumber The account number to balance.
		 */
		function utilityBalance($accountNumber)
		{
			$this->autoRender = false;
			
			shell_exec(
				sprintf(
					"cd %s; nohup ./cake/console/cake change_invoice_transaction_data " .
					"-impersonate %s -account %s -balance > /dev/null 2>&1 &",
					escapeshellarg(ROOT),
					escapeshellarg($this->Session->read('user')),
					escapeshellarg($accountNumber)
				)
			);
			
			$this->redirect('/processes/manager/reset:1');
		}
	}
?>