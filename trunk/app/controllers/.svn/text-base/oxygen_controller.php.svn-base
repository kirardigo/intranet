<?php
	class OxygenController extends AppController
	{
		var $uses = array(
			'Oxygen',
			'Customer',
			'CustomerCarrier',
			'Invoice',
			'Lookup',
			'Physician',
			'Purchase',
			'Rental',
			'Setting',
			'Transaction',
			'Vendor'
		);
		
		/**
		 * Display the Oxygen information for a customer.
		 * @param string $accountNumber The account number to lookup.
		 */
		function module_oxygenForCustomer($accountNumber)
		{
			$this->helpers[] = 'ajax';
			
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->Oxygen->find('count', array(
					'conditions' => array(
						'account_number' => $accountNumber,
						'record_code' => 'M'
					)
				));
				
				return $count > 0;
			}
			
			if (isset($this->data))
			{
				$this->data['Oxygen']['account_number'] = $accountNumber;
				$this->data['Oxygen']['record_code'] = 'M';
				
				// Format dates for database consumption
				$this->data['Oxygen']['lab_initial_date_ordered_or_renewal'] = databaseDate($this->data['Oxygen']['lab_initial_date_ordered_or_renewal']);
				$this->data['Oxygen']['date_test_performed'] = databaseDate($this->data['Oxygen']['date_test_performed']);
								
				$result = $this->Oxygen->save($this->data);
				$success = ($result !== false) ? true : false;
				
				//postback results will be in JSON indicating the success of the operation
				$this->layout = 'json';
				$this->params['json'] = true;
				$this->set('json', array('success' => $success));
				return;
			}
			
			//find the oxygen record
			$this->data = $this->Oxygen->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber,
					'record_code' => 'M'
				)
			));
			
			if (isset($this->data['Oxygen']))
			{
				formatDatesInArray($this->data['Oxygen'], array('date_test_performed', 'lab_initial_date_ordered_or_renewal'));
			}
			
			//fetch the active rentals
			$rentals = $this->Rental->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber,
					'returned_date' => null,
					'6_point_classification' => 'OXY'
				),
				'order' => 'setup_date desc'
			));
			
			//fetch the dropdown lookups
			$equipmentTypes = $this->Lookup->get('oxygen_equipment_types');
			$deliveryMethods = $this->Lookup->get('oxygen_delivery_methods');
			$usageRequirements = $this->Lookup->get('oxygen_usage_requirements');
			$testConditions = $this->Lookup->get('oxygen_test_conditions');
			$clinicalFindings = $this->Lookup->get('oxygen_clinical_findings');
			
			$this->set(compact('equipmentTypes', 'deliveryMethods', 'usageRequirements',
				'testConditions', 'clinicalFindings', 'rentals', 'accountNumber'
			));
		}
		
		/**
		 * Display the sleep information for a customer.
		 * @param string $accountNumber The account number to lookup.
		 */
		function module_radForCustomer($accountNumber)
		{
			$this->helpers[] = 'ajax';
			
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->Oxygen->find('count', array(
					'conditions' => array(
						'account_number' => $accountNumber,
						'osa_status !=' => ''
					)
				));
				
				return $count > 0;
			}
			
			if (isset($this->data))
			{
				$this->data['Oxygen']['account_number'] = $accountNumber;
				
				// Format dates for database consumption
				$this->data['Oxygen']['osa_setup_date'] = databaseDate($this->data['Oxygen']['osa_setup_date']);
				$this->data['Oxygen']['osa_status_date'] = databaseDate($this->data['Oxygen']['osa_status_date']);
				$this->data['Oxygen']['first_night_sleep_study_date'] = databaseDate($this->data['Oxygen']['first_night_sleep_study_date']);
				
				// Update status date automatically
				if ($this->data['Oxygen']['id'] != '')
				{
					$oldStatus = $this->Oxygen->field('osa_status', array('id' => $this->data['Oxygen']['id']));
					
					if ($this->data['Oxygen']['osa_status'] != $oldStatus)
					{
						$this->data['Oxygen']['osa_status_date'] = databaseDate('now');
					}
				}
				
				// Automatically fill last update fields
				$this->data['Oxygen']['date_last_updated'] = databaseDate('now');
				if (class_exists('User'))
				{
					$this->data['Oxygen']['last_updated_ini'] = User::current();
				}
				
				$result = $this->Oxygen->save($this->data);
				$success = ($result !== false) ? true : false;
				
				//postback results will be in JSON indicating the success of the operation
				$this->layout = 'json';
				$this->params['json'] = true;
				$this->set('json', array('success' => $success));
				return;
			}
			
			//fetch sleep data
			$this->data = $this->Oxygen->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber,
					'osa_status !=' => ''
				)
			));
			
			$chargeTransactionType = $this->Setting->get('charge_transaction_type_id');
			
			if (isset($this->data['Oxygen']))
			{
				$lastTransaction = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array('transaction_date_of_service'),
					'conditions' => array(
						'account_number' => $accountNumber,
						'transaction_date_of_service >' => databaseDate('-24 months'),
						'transaction_type' => $chargeTransactionType,
						'general_ledger_code' => array('455S', '456S')
					),
					'order' => array('transaction_date_of_service desc')
				));
				
				$this->data['Virtual']['last_trx_date'] = ifset($lastTransaction['Transaction']['transaction_date_of_service']);
				
				formatDatesInArray($this->data['Oxygen'], array('date_last_updated', 'osa_setup_date', 'osa_status_date', 'first_night_sleep_study_date'));
			}
			
			//fetch the active rentals
			$rentals = $this->Rental->find('all', array(
				'contain' => array('Inventory'),
				'conditions' => array(
					'account_number' => $accountNumber,
					'Inventory.general_ledger_rental_code' => array('450R', '455R', '456R'),
				),
				'order' => 'setup_date desc'
			));
			
			//order the rentals with oxygen rentals last
			$sleepRentals = array();
			$oxygenRentals = array();
			
			foreach ($rentals as $row)
			{
				if ($row['Inventory']['general_ledger_rental_code'] == '450R')
				{
					$oxygenRentals[] = $row;
				}
				else
				{
					$sleepRentals[] = $row;
				}
			}
			$rentals = array_merge($sleepRentals, $oxygenRentals);
			
			//fetch the purchased supplies
			$purchases = $this->Purchase->find('all', array(
				'contain' => array('Inventory'),
				'conditions' => array(
					'account_number' => $accountNumber,
					'Inventory.general_ledger_sales_code' => array('455S', '456S'),
					'date_of_service >=' => databaseDate('-30 months')
				),
				'order' => 'date_of_service desc'
			));
			
			$aaaModel = ClassRegistry::init('AaaReferral');
			$aaa = false;
			if ($this->data['Oxygen']['osa_aaa_referral_code'] != '')
			{
				$aaa = $aaaModel->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'aaa_number' => $this->data['Oxygen']['osa_aaa_referral_code']
					)
				));
			}
			
			$lab = false;
			if ($this->data['Oxygen']['osa_aaa_lab_code'] != '')
			{
				$lab = $aaaModel->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'aaa_number' => $this->data['Oxygen']['osa_aaa_lab_code']
					)
				));
			}
			
			$oxygenTypes = $this->Lookup->get('oxygen_types');
			$oxygenStatuses = $this->Lookup->get('oxygen_sleep_status', true);
			
			$this->set(compact('accountNumber', 'rentals', 'purchases', 'aaa', 'lab', 'oxygenTypes', 'oxygenStatuses'));
		}
		
		/**
		 * Container for oxygen reporting modules.
		 */
		function reporting()
		{
			$this->pageTitle = 'Respiratory Management';
		}
		
		/**
		 * Generate summary report of oxygen records.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'OxygenModuleSummaryFilter';
			$postDataName = 'OxygenModuleSummaryPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Oxygen']['is_export']))
				{
					$isExport = $this->data['Oxygen']['is_export'];
					unset($this->data['Oxygen']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if (isset($filters['Oxygen.osa_status']) && $filters['Oxygen.osa_status'] == 'notBlank')
					{
						unset($filters['Oxygen.osa_status']);
						$filters['Oxygen.osa_status !='] = '';
					}
					
					if (isset($filters['Oxygen.osa_setup_date_start']))
					{
						$filters['Oxygen.osa_setup_date >='] = databaseDate($filters['Oxygen.osa_setup_date_start']);
						unset($filters['Oxygen.osa_setup_date_start']);
					}
					
					if (isset($filters['Oxygen.osa_setup_date_end']))
					{
						$filters['Oxygen.osa_setup_date <='] = databaseDate($filters['Oxygen.osa_setup_date_end']);
						unset($filters['Oxygen.osa_setup_date_end']);
					}
					
					$conditions = array_merge($conditions, $filters);
					
					$this->Session->write($filterName, $conditions);
				}
				else if ($this->Session->check($filterName))
				{
					$conditions = $this->Session->read($filterName);
					$this->data = $this->Session->read($postDataName);
				}
				else
				{
					$this->Session->delete($filterName);
					$this->Session->delete($postDataName);
				}
				
				$results = $this->Oxygen->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$chargeTransactionType = $this->Setting->get('charge_transaction_type_id');
				
				$customerModel = ClassRegistry::init('Customer');
				
				foreach ($results as $key => $row)
				{
					formatDatesInArray($results[$key]['Oxygen'], array('osa_setup_date', 'osa_status_date', 'first_night_sleep_study_date'));
					
					$customer = $customerModel->find('first', array(
						'contain' => array(),
						'fields' => array('name', 'profit_center_number'),
						'conditions' => array('account_number' => $row['Oxygen']['account_number'])
					));
					
					if ($customer !== false)
					{
						//filter by profit center number
						if (isset($filters['Customer.profit_center_number']) && $filters['Customer.profit_center_number'] != $customer['Customer']['profit_center_number'])
						{
							unset($results[$key]);
							continue;
						}
						
						$lastTransaction = $this->Transaction->find('first', array(
							'contain' => array(),
							'fields' => array('transaction_date_of_service'),
							'conditions' => array(
								'account_number' => $row['Oxygen']['account_number'],
								'transaction_date_of_service >' => databaseDate('-24 months'),
								'transaction_type' => $chargeTransactionType,
								'general_ledger_code' => array('455S', '456S')
							),
							'order' => array('transaction_date_of_service desc')
						));
						
						$results[$key]['Virtual']['last_trx_date'] = ifset($lastTransaction['Transaction']['transaction_date_of_service']);
						
						$results[$key]['Customer']['name'] = $customer['Customer']['name'];
						$results[$key]['Customer']['profit_center_number'] = $customer['Customer']['profit_center_number'];
					}
				}
				
				$oxygenTypes = $this->Lookup->get('oxygen_types');
				
				$this->set(compact('results', 'oxygenTypes'));
				
				if ($isExport)
				{
					$this->render('/oxygen/csv_summary');
				}
			}
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$sleepStatuses = $this->Lookup->get('oxygen_sleep_status', true);
			$sleepStatuses = array_merge($sleepStatuses, array('notBlank' => 'NOT BLANK'));
			$this->set(compact('isUpdate', 'profitCenters', 'sleepStatuses'));
		}
	}
?>