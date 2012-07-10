<?php
	class RentalsController extends AppController
	{
		var $uses = array('Rental', 'Customer', 'Lookup', 'Oxygen');
		
		/**
		 * Show all rental information for a customer.
		 * @param string $accountNumber The customer's account number.
		 * @param bool $isUpdate Specifies whether paginated table is being updated.
		 */
		function module_forCustomer($accountNumber, $isUpdate = 0)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$pointer = $this->Customer->field('rental_equipment_pointer', array('account_number' => $accountNumber));
				
				return ($pointer != 0);
			}
			
			if (isset($this->params['named']['sort']))
			{
				$chainSort = $this->params['named']['sort'] . ' ' . strtoupper($this->params['named']['direction']);
			}
			else
			{
				$chainSort = array(
					"ifnull(returned_date, '9999-12-31') desc",
					'setup_date desc'
				);
			}
			
			$this->data = $this->Customer->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber
				),
				'chains' => array(
					'Rental' => array(
						'contain' => array(),
						'fields' => array(
							'healthcare_procedure_code',
							'inventory_number',
							'inventory_description',
							'serial_number',
							'carrier_1_code',
							'carrier_1_net_amount',
							'carrier_1_gross_amount',
							'carrier_2_code',
							'carrier_2_net_amount',
							'carrier_2_gross_amount',
							'carrier_3_code',
							'carrier_3_net_amount',
							'carrier_3_gross_amount',
							'setup_date',
							'returned_date',
							'form_code'
						),
						'order' => $chainSort
					)
				)
			));
			
			// Set chain pagination parameters
			$this->helpers[] = 'paginator';
			$page = ifset($this->params['named']['page'], 1);
			$limit = 20;
			$totalCount = count($this->data['Rental']);
			
			// Filter the data to the current page
			$rentals = array();
			$start = $page * $limit - $limit;
			$end = $page * $limit - 1;
			
			for ($i = $start; $i <= $end; $i++)
			{
				if (isset($this->data['Rental'][$i]))
				{
					$rentals[$i] = $this->data['Rental'][$i];
				}
			}
			
			$this->data['Rental'] = $rentals;
			
			// Set paginator variables
			$this->params['paging']['Rental'] = array(
				'page'		=> $page,
				'current'	=> count($this->data['Rental']),
				'count'		=> $totalCount,
				'prevPage'	=> ($page > 1),
				'nextPage'	=> ($totalCount > ($page * $limit)),
				'pageCount'	=> ceil($totalCount / $limit),
				'defaults'	=> array(),
				'options'	=> array()
			);
			
			// Set parameters for sortable headers
			if (isset($this->params['named']['sort']))
			{
				$this->params['paging']['Rental']['options'] = array(
					'sort' => $this->params['named']['sort'],
					'direction' => $this->params['named']['direction']
				);
			}
			
			$this->set(compact('accountNumber', 'isUpdate'));
		}
		
		/**
		 * Retrieve the line item details for a rental.
		 * @param string $id The rental ID.
		 */
		function ajax_rentalDetail($id)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->Rental->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			// Lookup diagnoses
			$diagnosisModel = ClassRegistry::init('Diagnosis');
			for ($i = 1; $i <= 4; $i++)
			{
				$fieldName = 'equipment_diagnosis_code_' . $i;
				
				$data = $diagnosisModel->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'number' => $this->data['Rental'][$fieldName]
					),
					'index' => 'C'
				));
				
				$this->data['Diagnosis'][$i]['code'] = ($data !== false) ? $data['Diagnosis']['code'] : false;
				$this->data['Diagnosis'][$i]['description'] = ($data !== false) ? $data['Diagnosis']['description'] : false;
			}
			
			// If HCPC is specified, look up the description
			if (ifset($this->data['Rental']['healthcare_procedure_code']) !== '')
			{
				$hcpcModel = ClassRegistry::init('HealthcareProcedureCode');
				$this->data['HealthcareProcedureCode']['description'] = $hcpcModel->field('description', array(
					'code' => $this->data['Rental']['healthcare_procedure_code']
				));
			}
			
			// If physician is specified, look up the record
			if (isset($this->data['Rental']['physician_equipment_code']) && $this->data['Rental']['physician_equipment_code'] != '')
			{
				$physicianModel = ClassRegistry::init('Physician');
				$physician = $physicianModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'name'
					),
					'conditions' => array(
						'physician_number' => $this->data['Rental']['physician_equipment_code']
					)
				));
				
				$this->data['Physician'] = $physician['Physician'];
			}
		}
		
		/**
		 * Container for rental reporting modules.
		 */
		function reporting()
		{
			$this->pageTitle = 'Rental Management';
		}
		
		/**
		 * Generate summary report of rentals.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'RentalsModuleSummaryFilter';
			$postDataName = 'RentalsModuleSummaryPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Rental']['is_export']))
				{
					$isExport = $this->data['Rental']['is_export'];
					unset($this->data['Rental']['is_export']);
				}
				
				if (isset($this->data['Rental']['is_mrs_export']))
				{
					$isMrsExport = $this->data['Rental']['is_mrs_export'];
					unset($this->data['Rental']['is_mrs_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if (!isset($filters['Rental.profit_center_number']))
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$filters['Rental.profit_center_number'] = $includedProfitCenters;
					}
					
					if (isset($filters['Rental.healthcare_procedure_code']))
					{
						$filters['Rental.healthcare_procedure_code'] = explode(',', str_replace(', ', ',', $filters['Rental.healthcare_procedure_code']));
					}
					
					if (!isset($filters['Rental.setup_date']) && !isset($filters['Rental.setup_date_end']) &&
						!isset($filters['Rental.returned_date']) &&	!isset($filters['Rental.returned_date_end']))
					{
						// By default, find records that have setup dates but have not been returned
						$filters['Rental.setup_date <>'] = null;
						$filters['Rental.returned_date'] = null;
					}
					else
					{
						if (isset($filters['Rental.setup_date']))
						{
							$filters['Rental.setup_date >='] = databaseDate($filters['Rental.setup_date']);
							unset($filters['Rental.setup_date']);
						}
						
						if (isset($filters['Rental.setup_date_end']))
						{
							$filters['Rental.setup_date <='] = databaseDate($filters['Rental.setup_date_end']);
							unset($filters['Rental.setup_date_end']);
						}
						
						if (isset($filters['Rental.returned_date']))
						{
							$filters['Rental.returned_date >='] = databaseDate($filters['Rental.returned_date']);
							unset($filters['Rental.returned_date']);
						}
						
						if (isset($filters['Rental.returned_date_end']))
						{
							$filters['Rental.returned_date <='] = databaseDate($filters['Rental.returned_date_end']);
							unset($filters['Rental.returned_date_end']);
						}
					}
					
					if (isset($filters['Rental.carrier_code']))
					{
						$filters['or'] = array(
							'carrier_1_code' => $filters['Rental.carrier_code'],
							'carrier_2_code' => $filters['Rental.carrier_code'],
							'carrier_3_code' => $filters['Rental.carrier_code']
						);
						
						unset($filters['Rental.carrier_code']);
					}
					
					if (isset($filters['Rental.diagnosis_pointer']))
					{
						$icd9Filter = $filters['Rental.diagnosis_pointer'];
						unset($filters['Rental.diagnosis_pointer']);
					}
					
					if (isset($filters['Rental.general_ledger_code']))
					{
						$generalLedgerFilter = $filters['Rental.general_ledger_code'];
						unset($filters['Rental.general_ledger_code']);
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
				
				$results = $this->Rental->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$customerModel = ClassRegistry::init('Customer');
				$customerBillingModel = ClassRegistry::init('CustomerBilling');
				$inventoryModel = ClassRegistry::init('Inventory');
				$competitiveBidHcpcModel = ClassRegistry::init('CompetitiveBidHcpc');
				$competitiveBidZipCodeModel = ClassRegistry::init('CompetitiveBidZipCode');
				$serializedEquipmentModel = ClassRegistry::init('SerializedEquipment');
				
				foreach ($results as $key => $row)
				{
					$icd9Codes = array();
					
					for ($i = 1; $i <= 4; $i++)
					{
						if ($row['Rental']["diagnosis_{$i}_pointer"] != '')
						{
							$value = $row['Rental']["diagnosis_{$i}_pointer"];
							$billingRecord = $customerModel->field('billing_pointer', array('account_number' => $row['Rental']['account_number']));
							$icd9 = $customerBillingModel->field("diagnosis_code_{$value}", array('id' => $billingRecord));
							$results[$key]['Rental']["icd9_{$i}"] = $icd9;
							$icd9Codes[] = $icd9;
						}
					}
					
					//filter out row if it doesn't match ICD9 filter
					if (isset($icd9Filter) && !in_array($icd9Filter, $icd9Codes))
					{
						unset($results[$key]);
						continue;
					}
					
					$inventoryGeneralLedgerCode = $inventoryModel->field('general_ledger_rental_code', array('inventory_number' => $row['Rental']['inventory_number']));
					
					//filter out row if it doesn't match GL code filter
					if (isset($generalLedgerFilter) && $generalLedgerFilter != $inventoryGeneralLedgerCode)
					{
						unset($results[$key]);
						continue;
					}
					
					$results[$key]['Rental']['general_ledger_code'] = $inventoryGeneralLedgerCode;
					
					if ($isMrsExport)
					{
						$record = $serializedEquipmentModel->find('first', array(
							'contain' => array(),
							'fields' => array(
								'date_of_sale',
								'mrs_invoice_number',
								'product_description'
							),
							'conditions' => array(
								'mrs_serial_number' => $row['Rental']['serial_number']
							),
							'index' => 'A'
						));
						
						$results[$key]['SerializedEquipment'] = $record['SerializedEquipment'];
					}
					else
					{
						$results[$key]['Rental']['competitive_bid_zip'] = false;
						$results[$key]['Rental']['competitive_bid_hcpc'] = false;
						
						$isCBZip = $competitiveBidZipCodeModel->find('count', array(
							'conditions' => array(
								'competitive_bid_zip_code' => $customerModel->field('zip_code', array('account_number' => $row['Rental']['account_number']))
							),
							'index' => 'A'
						));
						
						if ($isCBZip > 0 && $row['Rental']['carrier_1_code'] == 'MC20')
						{
							$results[$key]['Rental']['competitive_bid_zip'] = true;
							
							$isCBHcpc = $competitiveBidHcpcModel->find('count', array(
								'conditions' => array(
									'healthcare_procedure_code' => $row['Rental']['healthcare_procedure_code']
								),
								'index' => 'A'
							));
							
							if ($isCBHcpc > 0)
							{
								$results[$key]['Rental']['competitive_bid_hcpc'] = true;
							}
						}
					}
					
					//cross-reference to see if records exist in FU05DM
					$oxygenCount = $this->Oxygen->find('count', array(
						'contain' => array(),
						'conditions' => array(
							'account_number' => $row['Rental']['account_number'],
							'record_code' => 'M'
						)
					));
					
					$sleepCount = $this->Oxygen->find('count', array(
						'contain' => array(),
						'conditions' => array(
							'account_number' => $row['Rental']['account_number'],
							'osa_status !=' => ''
						)
					));
					
					if ($oxygenCount > 0 && $sleepCount > 0)
					{
						$results[$key]['Oxygen']['respiratory_code'] = 'B';
					}
					else if ($oxygenCount > 0)
					{
						$results[$key]['Oxygen']['respiratory_code'] = 'O';
					}
					else if ($sleepCount > 0)
					{
						$results[$key]['Oxygen']['respiratory_code'] = 'S';
					}
					else
					{
						$results[$key]['Oxygen']['respiratory_code'] = '';
					}
				}
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/rentals/csv_summary');
				}
				else if ($isMrsExport)
				{
					$this->render('/rentals/csv_summary_mrs');
				}
			}
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$this->set(compact('profitCenters', 'isUpdate'));
		}		
		
		function totalRentedForInventoryItem($inventoryNumber)
		{
			$rentalCount = $this->Rental->find('count', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number' => $inventoryNumber,
					'returned_date <>' => '' 
				)
			));
		}
		
	}
?>