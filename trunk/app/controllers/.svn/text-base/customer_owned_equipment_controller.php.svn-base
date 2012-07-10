<?php
	class CustomerOwnedEquipmentController extends AppController
	{
		var $uses = array(
			'CustomerOwnedEquipment',
			'CustomerOwnedEquipmentNumber',
			'Lookup',
			'Note'
		);
		var $helpers = array('Paginator');
		
		var $pageTitle = 'Customer Owned Equipment';
		
		/**
		 * Lookup the customer owned equipment records for a customer.
		 * @param string $accountNumber The customer's account number.
		 */
		function module_forCustomer($accountNumber)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->CustomerOwnedEquipment->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'account_number' => $accountNumber
					),
					'index' => 'F'
				));
				
				return ($count > 0);
			}
			
			$this->data = $this->CustomerOwnedEquipment->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber
				),
				'order' => array(
					'is_active desc',
					'date_of_purchase desc'
				),
				'index' => 'F'
			));
			
			$inquiryParameters = $this->Session->read('inquiryParameters');
			$this->set('load', ifset($inquiryParameters['load']));
		}
		
		/**
		 * Lookup details for customer owned equipment record.
		 * @param int $id The ID of the COE record.
		 */
		function ajax_detail($id = null)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->CustomerOwnedEquipment->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			if ($this->data !== false)
			{
				$id = $this->data['CustomerOwnedEquipment']['id'];
				$this->set('noteRecord', $this->Note->getNotes($this->CustomerOwnedEquipment->generateTargetUri($id)));
			}
			
			$pmdClasses = $this->Lookup->get('pmd_class');
			$this->set(compact('id', 'pmdClasses'));
		}
		
		/**
		 * Pull additional information for the summary views.
		 */
		function _pullAdditionalData(&$data, $filters)
		{
			$customerModel = ClassRegistry::init('Customer');
			$aaaModel = ClassRegistry::init('AaaReferral');
			$transactionModel = ClassRegistry::init('Transaction');
			
			foreach ($data as $key => $row)
			{
				if (ifset($row['CustomerOwnedEquipment']['account_number']) != '')
				{
					$customerRecord = $customerModel->find('first', array(
						'contain' => array(
							'CustomerBilling' => array(
								'fields' => array(
									'billing_name',
									'address_1',
									'address_2',
									'city',
									'zip_code',
									'phone_number',
									'is_deceased',
									'long_term_care_facility_number',
									'school_or_program_number_from_aaa_file',
									'salesman_number',
									'stats_profile'
								)
							)
						),
						'fields' => array(
							'name',
							'profit_center_number',
							'account_status_code',
							'billing_pointer'
						),
						'conditions' => array('account_number' => $row['CustomerOwnedEquipment']['account_number'])
					));
				}
				
				if ($customerRecord !== false)
				{
					$aaaRecord = $aaaModel->find('first', array(
						'contain' => array(),
						'fields' => array(
							'contact_name',
							'facility_name',
							'rehab_salesman',
							'homecare_salesman'
						),
						'conditions' => array(
							'aaa_number' => $customerRecord['CustomerBilling']['school_or_program_number_from_aaa_file']
						)
					));
					
					$transactionSalesman = $transactionModel->field('salesman_number', array(
						'account_number' => $row['CustomerOwnedEquipment']['account_number'],
						'invoice_number' => $row['CustomerOwnedEquipment']['invoice_number'],
						'salesman_number !=' => ''
					));
					
					if ($transactionSalesman === false)
					{
						$transactionSalesman = $transactionModel->field('salesman_number', array(
							'account_number' => $row['CustomerOwnedEquipment']['account_number'],
							'transaction_control_number' => $row['CustomerOwnedEquipment']['transaction_control_number'],
							'salesman_number !=' => ''
						));
					}
					
					$lastService = $transactionModel->find('first', array(
						'contain' => array(),
						'fields' => array('transaction_date_of_service'),
						'conditions' => array('account_number' => $row['CustomerOwnedEquipment']['account_number']),
						'order' => 'transaction_date_of_service desc'
					));
					
					$lastSleep = $transactionModel->find('first', array(
						'contain' => array(),
						'fields' => array('transaction_date_of_service'),
						'conditions' => array(
							'account_number' => $row['CustomerOwnedEquipment']['account_number'],
							'general_ledger_code' => array('455R', '455S', '456R', '456S')
						),
						'order' => 'transaction_date_of_service desc'
					));
					
					if (isset($filters['Customer.profit_center_number']) && $filters['Customer.profit_center_number'] != $customerRecord['Customer']['profit_center_number'])
					{
						unset($data[$key]);
						continue;
					}
					
					if (isset($filters['CustomerBilling.program_options']))
					{
						// Only show blanks
						if ($filters['CustomerBilling.program_options'] == 1 && $customerRecord['CustomerBilling']['school_or_program_number_from_aaa_file'] != '')
						{
							unset($data[$key]);
							continue;
						}
						
						// Only show non-blanks
						if ($filters['CustomerBilling.program_options'] == 2 && $customerRecord['CustomerBilling']['school_or_program_number_from_aaa_file'] == '')
						{
							unset($data[$key]);
							continue;
						}
					}
					if (isset($filters['CustomerBilling.school_or_program_number_from_aaa_file']) && $filters['CustomerBilling.school_or_program_number_from_aaa_file'] != $customerRecord['CustomerBilling']['school_or_program_number_from_aaa_file'])
					{
						unset($data[$key]);
						continue;
					}
					
					if (isset($filters['CustomerBilling.ltcf_options']))
					{
						// Only show blanks
						if ($filters['CustomerBilling.ltcf_options'] == 1 && $customerRecord['CustomerBilling']['long_term_care_facility_number'] != '')
						{
							unset($data[$key]);
							continue;
						}
						
						// Only show non-blanks
						if ($filters['CustomerBilling.ltcf_options'] == 2 && $customerRecord['CustomerBilling']['long_term_care_facility_number'] == '')
						{
							unset($data[$key]);
							continue;
						}
					}
					if (isset($filters['CustomerBilling.long_term_care_facility_number']) && $filters['CustomerBilling.long_term_care_facility_number'] != $customerRecord['CustomerBilling']['long_term_care_facility_number'])
					{
						unset($data[$key]);
						continue;
					}
					if (isset($filters['CustomerBilling.salesman_number']))
					{
						$salesmen = explode(',', str_replace(';', ',', str_replace(' ', '', strtoupper($filters['CustomerBilling.salesman_number']))));
						
						if (!in_array($customerRecord['CustomerBilling']['salesman_number'], $salesmen))
						{
							unset($data[$key]);
							continue;
						}
					}
					
					$data[$key]['Customer'] = $customerRecord['Customer'];
					$data[$key]['CustomerBilling'] = ifset($customerRecord['CustomerBilling'], array());
					$data[$key]['AaaReferral'] = ifset($aaaRecord['AaaReferral'], array());
					$data[$key]['Transaction']['salesman_number'] = isset($transactionSalesman) && $transactionSalesman !== false ? $transactionSalesman : '';
					$data[$key]['Transaction']['last_service_date'] = $lastService !== false ? $lastService['Transaction']['transaction_date_of_service'] : '';
					$data[$key]['Transaction']['last_sleep_date'] = $lastSleep !== false ? $lastSleep['Transaction']['transaction_date_of_service'] : '';
				}
			}
		}
		
		/**
		 * Container action for modules.
		 */
		function management()
		{
			
		}
		
		/**
		 * Displays the COE management module.
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
				//create the temp table in MySQL
				//grab COE schema so we can make the columns the right size if we need to craate the table
				$schema = $this->CustomerOwnedEquipment->schema();
				
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						original_id int not null,
						account_number varchar({$schema['account_number']['length']}) not null,
						description varchar({$schema['description']['length']}) not null,
						invoice_number varchar({$schema['invoice_number']['length']}) not null,
						transaction_control_number varchar({$schema['transaction_control_number']['length']}) not null,
						date_of_purchase datetime null,
						serial_number varchar({$schema['serial_number']['length']}) not null,
						model_number varchar({$schema['model_number']['length']}) not null,
						manufacturer_frame_code varchar({$schema['manufacturer_frame_code']['length']}) not null,
						is_active bool not null,
						purchase_healthcare_procedure_code varchar({$schema['purchase_healthcare_procedure_code']['length']}) not null,
						initial_carrier_number varchar({$schema['initial_carrier_number']['length']}) not null,
						customer_owned_equipment_id_number int null,
						tilt_manufacturer_code varchar({$schema['tilt_manufacturer_code']['length']}) not null,
						tilt_model_number varchar({$schema['tilt_model_number']['length']}) not null,
						tilt_serial_number varchar({$schema['tilt_serial_number']['length']}) not null,
						customer_name varchar(30) not null,
						profit_center_number varchar(3) not null,
						aaa_program_number varchar(6) not null,
						aaa_ltcf_number varchar(6) not null,
						client_name varchar(30) not null,
						address_1 varchar(30) not null,
						address_2 varchar(30) not null,
						city_state varchar(30) not null,
						zip_code varchar(9) not null,
						phone_number varchar(14) not null,
						is_deceased bool not null,
						account_salesman varchar(4) not null,
						transaction_salesman varchar(3) not null,
						program_rehab_salesman varchar(3) not null,
						program_homecare_salesman varchar(3) not null,
						program_contact_name varchar(30) not null,
						program_facility_name varchar(30) not null,
						stats_profile varchar(1) not null,
						account_status_code varchar(2) not null,
						last_service_date datetime null,
						last_sleep_date datetime null
					)
				");
				
				//create our search conditions
				$conditions = array();
				$filters = Set::filter($this->postConditions($this->data));
				
				// Set the filters
				if (isset($filters['CustomerOwnedEquipment.is_active']))
				{
					$index = 'J';
				}
				if (isset($filters['CustomerOwnedEquipment.initial_carrier_number']))
				{
					$index = 'K';
				}
				if (isset($filters['CustomerOwnedEquipment.date_of_purchase_start']))
				{
					$filters['CustomerOwnedEquipment.date_of_purchase >='] = databaseDate($filters['CustomerOwnedEquipment.date_of_purchase_start']);
					unset($filters['CustomerOwnedEquipment.date_of_purchase_start']);
					$index = 'D';
				}
				if (isset($filters['CustomerOwnedEquipment.date_of_purchase_end']))
				{
					$filters['CustomerOwnedEquipment.date_of_purchase <='] = databaseDate($filters['CustomerOwnedEquipment.date_of_purchase_end']);
					unset($filters['CustomerOwnedEquipment.date_of_purchase_end']);
					$index = 'D';
				}
				if (isset($filters['CustomerOwnedEquipment.account_number']))
				{
					$index = 'A';
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$findArray = array(
					'fields' => array(
						'id',
						'account_number',
						'description',
						'invoice_number',
						'transaction_control_number',
						'date_of_purchase',
						'serial_number',
						'model_number',
						'manufacturer_frame_code',
						'is_active',
						'purchase_healthcare_procedure_code',
						'initial_carrier_number',
						'customer_owned_equipment_id_number',
						'tilt_manufacturer_code',
						'tilt_model_number',
						'tilt_serial_number'
					),
					'conditions' => $conditions,
					'contain' => array()
				);
				
				if (isset($index))
				{
					$findArray['index'] = $index;
				}
				
				//pull the data
				$coeRecords = $this->CustomerOwnedEquipment->find('all', $findArray);
				$this->_pullAdditionalData($coeRecords, $conditions);
				
				//insert the aggregated amounts into the temp table
				foreach ($coeRecords as $coeRecord)
				{
					$fields = array_map(array('Sanitize', 'escape'), $coeRecord['CustomerOwnedEquipment']);
					$customerFields = array_map(array('Sanitize', 'escape'), ifset($coeRecord['Customer'], array()));
					$billingFields = array_map(array('Sanitize', 'escape'), ifset($coeRecord['CustomerBilling'], array()));
					$aaaFields = array_map(array('Sanitize', 'escape'), ifset($coeRecord['AaaReferral'], array()));
					$transactionFields = array_map(array('Sanitize', 'escape'), ifset($coeRecord['Transaction'], array()));
					
					$db->query("
						insert into {$table} (
							original_id,
							account_number,
							description,
							invoice_number,
							transaction_control_number,
							date_of_purchase,
							serial_number,
							model_number,
							manufacturer_frame_code,
							is_active,
							purchase_healthcare_procedure_code,
							initial_carrier_number,
							customer_owned_equipment_id_number,
							tilt_manufacturer_code,
							tilt_model_number,
							tilt_serial_number,
							customer_name,
							profit_center_number,
							aaa_program_number,
							aaa_ltcf_number,
							client_name,
							address_1,
							address_2,
							city_state,
							zip_code,
							phone_number,
							is_deceased,
							account_salesman,
							transaction_salesman,
							program_rehab_salesman,
							program_homecare_salesman,
							program_contact_name,
							program_facility_name,
							stats_profile,
							account_status_code,
							last_service_date,
							last_sleep_date
						)
						values (
							" . $fields['id'] . ",
							'{$fields['account_number']}',
							'{$fields['description']}',
							'{$fields['invoice_number']}',
							'{$fields['transaction_control_number']}',
							'" . databaseDate($fields['date_of_purchase']) . "',
							'{$fields['serial_number']}',
							'{$fields['model_number']}',
							'{$fields['manufacturer_frame_code']}',
							" . ($fields['is_active'] ? 1 : 0) . ",
							'{$fields['purchase_healthcare_procedure_code']}',
							'{$fields['initial_carrier_number']}',
							'{$fields['customer_owned_equipment_id_number']}',
							'{$fields['tilt_manufacturer_code']}',
							'{$fields['tilt_model_number']}',
							'{$fields['tilt_serial_number']}',
							'" . Sanitize::escape(ifset($coeRecord['Customer']['name'])) . "',
							'" . Sanitize::escape(ifset($coeRecord['Customer']['profit_center_number'])) . "',
							'" . ifset($billingFields['school_or_program_number_from_aaa_file']) . "',
							'" . ifset($billingFields['long_term_care_facility_number']) . "',
							'" . ifset($billingFields['billing_name']) . "',
							'" . ifset($billingFields['address_1']) . "',
							'" . ifset($billingFields['address_2']) . "',
							'" . ifset($billingFields['city']) . "',
							'" . ifset($billingFields['zip_code']) . "',
							'" . ifset($billingFields['phone_number']) . "',
							'" . ifset($billingFields['is_deceased']) . "',
							'" . ifset($billingFields['salesman_number']) . "',
							'" . Sanitize::escape(ifset($coeRecord['Transaction']['salesman_number'])) . "',
							'" . ifset($aaaFields['rehab_salesman']) . "',
							'" . ifset($aaaFields['homecare_salesman']) . "',
							'" . ifset($aaaFields['contact_name']) . "',
							'" . ifset($aaaFields['facility_name']) . "',
							'" . ifset($billingFields['stats_profile']) . "',
							'" . ifset($customerFields['account_status_code']) . "',
							" . ($transactionFields['last_service_date'] != '' ? "'" . databaseDate($transactionFields['last_service_date']) . "'" : "null") . ",
							" . ($transactionFields['last_sleep_date'] != '' ? "'" . databaseDate($transactionFields['last_sleep_date']) . "'" : "null") . "
						)
					", false);
				}
			}
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'CustomerOwnedEquipment', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 50,
					'page' => 1
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$records = $this->paginate($modelName);
				
				$this->set('records', $records);
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
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'CustomerOwnedEquipment', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull the transactions
			$query = array();
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('records', $model->find('all', $query));
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the management module.
		 * @param string $username The users username.
		 * @return string The unique table name.
		 */
		function _managementTempTableName($username)
		{
			return 'temp_coe_management_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to delete.
		 */
		function json_delete($id)
		{
			// Remove notes
			$this->Note->deleteNotes($this->CustomerOwnedEquipment->generateTargetUri($id));
			
			// Remove the record itself
			$this->CustomerOwnedEquipment->delete($id);
			
			$this->set('json', array('success' => true));
		}
		
		/**
		 * Save the record.
		 */
		function json_save()
		{
			$success = true;
			$message = '';
			
			if (isset($this->data))
			{
				// Clean up the data before saving
				$this->data['CustomerOwnedEquipment']['date_of_purchase'] = databaseDate($this->data['CustomerOwnedEquipment']['date_of_purchase']);
				$this->data['CustomerOwnedEquipment']['warranty_expiration_date'] = databaseDate($this->data['CustomerOwnedEquipment']['warranty_expiration_date']);
				
				// Perform validation here
				$this->CustomerOwnedEquipment->set($this->data);
				
				if (!$this->CustomerOwnedEquipment->validates())
				{
					$success = false;
					$message = print_r($this->CustomerOwnedEquipment->validationErrors, true);
				}
				else
				{
					// Add ID number if one does not exist
					if ($this->data['CustomerOwnedEquipment']['customer_owned_equipment_id_number'] == '')
					{
						$this->data['CustomerOwnedEquipment']['customer_owned_equipment_id_number'] = $this->CustomerOwnedEquipmentNumber->increment('customer_owned_equipment_id_number');
					}
					
					if (!$this->CustomerOwnedEquipment->save($this->data))
					{
						$success = false;
						$message = 'Record could not be saved. ' . print_r($this->CustomerOwnedEquipment->validationErrors, true);
					}
					else
					{
						// Tie note to the COE record if save was successful
						$id = $this->CustomerOwnedEquipment->id;
						$this->Note->saveNote($this->CustomerOwnedEquipment->generateTargetUri($id), 'general', $this->data['Note']['general']['note']);
					}
				}
			}
			
			$this->set('json', array('success' => $success, 'message' => $message));
		}
		
		/**
		 * Toggle the status of the active flag.
		 * @param int $id The ID of the record to manipulate.
		 */
		function json_toggleActive($id)
		{
			$success = false;
			
			$oldActiveValue = $this->CustomerOwnedEquipment->field('is_active', array('id' => $id));
			$newActiveValue = $oldActiveValue ? 0 : 1;
			
			$saveData['CustomerOwnedEquipment'] = array(
				'id' => $id,
				'is_active' => $newActiveValue
			);
			
			$success = $this->CustomerOwnedEquipment->save($saveData);
			
			$this->set('json', array('success' => ($success != false)));
		}
	}
?>