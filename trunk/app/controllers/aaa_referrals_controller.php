<?php
	class AaaReferralsController extends AppController
	{
		var $uses = array(
			'AaaCredential',
			'AaaReferral',
			'AaaSalesman',
			'AaaMonthlySummary',
			'AaaProfitCenter',
			'Customer',
			'FacilityType',
			'Lookup',
			'NextFreeNumber',
			'Note'
		);
		
		/**
		 * Edit a AAA referral record.
		 * @param int $id The ID of the AAA record.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				// Set the county information based on the value of the autocomplete field
				$county = $this->AaaProfitCenter->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'county_name' => $this->data['AaaProfitCenter']['county_name']
					),
					'index' => 'B'
				));
				
				$this->data['AaaReferral']['county_code'] = ($county !== false) ? $county['AaaProfitCenter']['county_code'] : '';
				$this->data['AaaReferral']['county_name'] = ($county !== false) ? $county['AaaProfitCenter']['county_name'] : '';
				
				$this->AaaReferral->set($this->data);
				
				if ($this->AaaReferral->validates())
				{
					if ($id === null)
					{
						$this->data['AaaReferral']['aaa_number'] = $this->NextFreeNumber->next('aaa_number');
					}
					
					if ($this->AaaReferral->save($this->data))
					{
						if ($id === null)
						{
							$this->set('new', true);
						}
						
						$id = $this->AaaReferral->id;
						
						$this->Note->saveNote($this->AaaReferral->generateTargetUri($id), 'general', $this->data['Note']['general']['note']);
						
						$this->set('close', true);
					}
				}
			}
			else if ($id !== null)
			{
				$this->data = $this->AaaReferral->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'id' => $id
					)
				));
				
				$this->data['Note'] = $this->Note->getNotes($this->AaaReferral->generateTargetUri($id));
			}
			
			$facilityTypes = $this->FacilityType->getList();
			$credentials = $this->AaaCredential->getList();
			$rehabSalesman = $this->AaaSalesman->getList();
			$communicationMethods = $this->Lookup->get('method_of_communication');
			$marketCodes = $this->Lookup->get('aaa_market_codes', true, true);
			$rehabMarketingCodes = $this->Lookup->get('rehab_marketing_codes', true, true);
			
			$this->set(compact('id', 'communicationMethods', 'facilityTypes', 'credentials',
				'rehabSalesman', 'marketCodes', 'rehabMarketingCodes'));
			$this->helpers[] = 'ajax';
		}
		
		/**
		 * AJAX action to get a AAA facility name.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		aaa_number The AAA number to find the facility name for.
		 */
		function ajax_facility()
		{
			$match = $this->AaaReferral->field('facility_name', array('aaa_number' => $this->params['form']['aaa_number']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * Ajax action to find an AAA number by facility name.
		 * Expects $this->data['AaaReferral']['search'] to be set.
		 */
		function ajax_autoCompleteByFacility()
		{
			if (!isset($this->data['AaaReferral']['search']))
			{
				exit;
			}
			
			$value = $this->data['AaaReferral']['search'];
			
			$matches = $this->AaaReferral->find('all', array(
				'fields' => array('id', 'aaa_number', 'facility_name', 'contact_name'),
				'conditions' => array('facility_name like' => $value . '%'),
				'order' => array('facility_name', 'contact_name'),
				'contain' => array()
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'AaaReferral.id', 
				'id_prefix' => '',
				'value_fields' => array('AaaReferral.aaa_number'),
				'informal_fields' => array('AaaReferral.facility_name', 'AaaReferral.contact_name'),
				'informal_format' => '- <span class="FacilityName">%s</span>: <span class="ContactName">%s</span>'
			));
		}
		
		/**
		 * Ajax action to find an record by facility or contact name.
		 * Expects $this->data['AaaReferral']['search'] to be set.
		 */
		function ajax_autoCompleteByName()
		{
			if (!isset($this->data['AaaReferral']['search']))
			{
				exit;
			}
			
			$value = $this->data['AaaReferral']['search'];
			
			if (is_numeric($value))
			{
				$single = $this->AaaReferral->find('first', array(
					'contain' => array(),
					'fields' => array('id', 'aaa_number', 'facility_name', 'contact_name'),
					'conditions' => array('aaa_number' => $value)
				));
			}
			else
			{
				$single = false;
			}
			
			if ($single === false)
			{
				$matches = $this->AaaReferral->find('all', array(
					'fields' => array('id', 'aaa_number', 'facility_name', 'contact_name'),
					'conditions' => array(
						'facility_name like' => $value . '%'
					),
					'order' => array('facility_name', 'contact_name'),
					'contain' => array()
				));
				
				$matches2 = $this->AaaReferral->find('all', array(
					'fields' => array('id', 'aaa_number', 'facility_name', 'contact_name'),
					'conditions' => array(
						'contact_name like' => $value . '%'
					),
					'order' => array('facility_name', 'contact_name'),
					'contain' => array()
				));
				
				foreach ($matches2 as $row)
				{
					$matches[] = $row;
				}
			}
			else
			{
				$matches[0] = $single;
			}
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'AaaReferral.id', 
				'id_prefix' => '',
				'value_fields' => array('AaaReferral.facility_name'),
				'informal_fields' => array('AaaReferral.contact_name', 'AaaReferral.aaa_number'),
				'informal_format' => ': <span class="ContactName">%s</span> (%s)'
			));
		}
		
		/**
		 * Get information about the record via JSON by the ID.
		 * @param int $id The ID of the record to fetch.
		 */
		function json_information($id)
		{
			$record = $this->AaaReferral->find('first', array(
				'contain' => array(),
				'fields' => array(
					'facility_name',
					'contact_name',
					'aaa_number',
					'phone_number',
					'method_of_communication',
					'mail_address_1',
					'mail_address_2',
					'mail_city_state_zip'
				),
				'conditions' => array('id' => $id)
			));
			
			$this->set('json', $record['AaaReferral']);
		}
		
		/**
		 * Module to display referrals for a particular customer.
		 * @param string $accountNumber The account number of the customer.
		 */
		function module_forCustomer($accountNumber)
		{
			$aaaReferral = false;
			$ltcfReferral = false;
			$programReferral = false;
			
			$this->data = $this->Customer->find('first', array(
				'contain' => array('CustomerBilling'),
				'conditions' => array(
					'Customer.account_number' => $accountNumber
				)
			));
			
			if ($this->data['CustomerBilling']['physician_number'] != '')
			{
				$physicianModel = ClassRegistry::init('Physician');
				$data = $physicianModel->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'physician_number' => $this->data['CustomerBilling']['physician_number']
					)
				));
				
				$this->data['Physician'] = $data['Physician'];
			}
			
			if ($this->data['CustomerBilling']['referral_number_from_aaa_file'] != '')
			{
				$data = Set::extract('/AaaReferral/.', $this->AaaReferral->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'AaaReferral.aaa_number' => $this->data['CustomerBilling']['referral_number_from_aaa_file']
					)
				)));
				
				$this->data['AaaReferral'][0] = $data[0];
			}
			
			if ($this->data['CustomerBilling']['long_term_care_facility_number'] != '')
			{
				$data = Set::extract('/AaaReferral/.', $this->AaaReferral->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'AaaReferral.aaa_number' => $this->data['CustomerBilling']['long_term_care_facility_number']
					)
				)));
				
				$this->data['AaaReferral'][1] = $data[0];
			}
			
			if ($this->data['CustomerBilling']['school_or_program_number_from_aaa_file'] != '')
			{
				$data = Set::extract('/AaaReferral/.', $this->AaaReferral->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'AaaReferral.aaa_number' => $this->data['CustomerBilling']['school_or_program_number_from_aaa_file']
					)
				)));
				
				$this->data['AaaReferral'][2] = $data[0];
			}
		}
		
		/**
		 * Container for AAA reporting tabs.
		 */
		function reporting()
		{
			$this->pageTitle = 'AAA Referrals';
		}
		
		/**
		 * Generate summary report of AAA records.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'AaaReferralsModuleSummaryFilter';
			$postDataName = 'AaaReferralsModuleSummaryPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['AaaReferral']['is_export']))
				{
					$isExport = $this->data['AaaReferral']['is_export'];
					unset($this->data['AaaReferral']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if (isset($filters['AaaReferral.is_active_for_rehab']) && $filters['AaaReferral.is_active_for_rehab'] == 0)
					{
						unset($filters['AaaReferral.is_active_for_rehab']);
						$filters['AaaReferral.is_active_for_rehab !='] = 1;
					}
					if (isset($filters['AaaReferral.is_active_for_homecare']) && $filters['AaaReferral.is_active_for_homecare'] == 0)
					{
						unset($filters['AaaReferral.is_active_for_homecare']);
						$filters['AaaReferral.is_active_for_homecare !='] = 1;
					}
					if (isset($filters['AaaReferral.is_active_for_access']) && $filters['AaaReferral.is_active_for_access'] == 0)
					{
						unset($filters['AaaReferral.is_active_for_access']);
						$filters['AaaReferral.is_active_for_access !='] = 1;
					}
					if (isset($filters['AaaReferral.aaa_number']))
					{
						$filters['AaaReferral.aaa_number'] = explode(',', str_replace(', ', ',', $filters['AaaReferral.aaa_number']));
					}
					if (isset($filters['AaaReferral.county_code']))
					{
						$filters['AaaReferral.county_code'] = explode(',', str_replace(', ', ',', $filters['AaaReferral.county_code']));
					}
					if (isset($filters['AaaReferral.rehab_salesman']))
					{
						$filters['AaaReferral.rehab_salesman'] = explode(',', str_replace(', ', ',', $filters['AaaReferral.rehab_salesman']));
					}
					if (isset($filters['AaaReferral.homecare_salesman']))
					{
						$filters['AaaReferral.homecare_salesman'] = explode(',', str_replace(', ', ',', $filters['AaaReferral.homecare_salesman']));
					}
					if (isset($filters['AaaReferral.contact_name LIKE']))
					{
						$filters['AaaReferral.contact_name LIKE'] .= '%';
					}
					if (isset($filters['AaaReferral.facility_name LIKE']))
					{
						$filters['AaaReferral.facility_name LIKE'] .= '%';
					}
					if (isset($filters['AaaReferral.address_1 LIKE']))
					{
						$filters['AaaReferral.address_1 LIKE'] .= '%';
					}
					if (isset($filters['AaaReferral.phone_number LIKE']))
					{
						$filters['AaaReferral.phone_number LIKE'] .= '%';
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
				
				$results = $this->AaaReferral->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/aaa_referrals/csv_summary');
				}
			}
			
			$aaaTypes = $this->FacilityType->getList(true, 'description');
			$rehabMarketingCodes = $this->Lookup->get('rehab_marketing_codes', true, true);
			$homecareMarketingCodes = $this->Lookup->get('aaa_market_codes', true, true);
			
			$this->helpers[] = 'ajax';
			$this->set(compact('isUpdate', 'aaaTypes', 'rehabMarketingCodes', 'homecareMarketingCodes'));
		}
		
		/**
		 * Generate totals report of AAA records.
		 */
		function module_totals($isUpdate = 0)
		{
			$filterName = 'AaaReferralsModuleTotalsFilter';
			$postDataName = 'AaaReferralsModuleTotalsPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['AaaMonthlySummary']['is_export']))
				{
					$isExport = $this->data['AaaMonthlySummary']['is_export'];
					unset($this->data['AaaMonthlySummary']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$conditionsMonthly['AaaMonthlySummary'] = $this->data['AaaMonthlySummary'];
					$conditionsReferral['AaaReferral'] = $this->data['AaaReferral'];
					$conditionsVirtual['Virtual'] = $this->data['Virtual'];
					
					$filters = Set::filter($this->postConditions($conditionsMonthly));
					$afterFilters = Set::filter($this->postConditions($conditionsReferral));
					$virtualFilters = Set::filter($this->postConditions($conditionsVirtual));
					
					if (isset($filters['AaaMonthlySummary.aaa_number']))
					{
						$filters['AaaMonthlySummary.aaa_number'] = explode(',', str_replace(', ', ',', $filters['AaaMonthlySummary.aaa_number']));
					}
					
					if (isset($filters['AaaMonthlySummary.date_month_start']))
					{
						$filters['AaaMonthlySummary.date_month >='] = databaseDate($filters['AaaMonthlySummary.date_month_start']);
						unset($filters['AaaMonthlySummary.date_month_start']);
					}
					
					if (isset($filters['AaaMonthlySummary.date_month_end']))
					{
						$filters['AaaMonthlySummary.date_month <='] = databaseDate($filters['AaaMonthlySummary.date_month_end']);
						unset($filters['AaaMonthlySummary.date_month_end']);
					}
					
					if (isset($filters['AaaMonthlySummary.order_salesman']))
					{
						$filters['AaaMonthlySummary.order_salesman'] = explode(',', str_replace(', ', ',', $filters['AaaMonthlySummary.order_salesman']));
					}
					else
					{
						// By default we want to make sure we use these order records & not the rehab records
						$filters['AaaMonthlySummary.order_salesman !='] = '';
					}
					
					if (isset($filters['AaaMonthlySummary.rehab_salesman']))
					{
						// Clear the order filter if we have specified a rehab filter
						$filters['AaaMonthlySummary.rehab_salesman'] = explode(',', str_replace(', ', ',', $filters['AaaMonthlySummary.rehab_salesman']));
						unset($filters['AaaMonthlySummary.order_salesman']);
						unset($filters['AaaMonthlySummary.order_salesman !=']);
						unset($this->data['AaaMonthlySummary']['order_salesman']);
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
				
				$results = $this->AaaMonthlySummary->find('all', array(
					'contain' => array(),
					'fields' => array(
						'aaa_number',
						'date_month',
						'order_salesman',
						'rehab_salesman',
						'sum(total_revenue_month) as sum_revenue_month',
						'sum(total_revenue_12months) as sum_revenue_12months',
						'sum(total_quotes_month) as sum_quotes_month',
						'sum(total_quotes_12months) as sum_quotes_12months',
					),
					'conditions' => $conditions,
					'group' => array('aaa_number', 'date_month'),
					'order' => array('aaa_number', 'date_month')
				));

				$countyCache = array();
				$aaaCache = array();
				$countyModel = ClassRegistry::init('County');
					
				foreach ($results as $key => $result)
				{
					$aaaReferral = $this->AaaReferral->find('first', array(
						'contain' => array(),
						'fields' => array(
							'contact_name',
							'facility_name',
							'facility_type',
							'group_code',
							'rehab_market_code',
							'county_code'
						),
						'conditions' => array_merge(
							array('AaaReferral.aaa_number' => $result['AaaMonthlySummary']['aaa_number']), 
							$afterFilters
						)
					));
					
					if ($aaaReferral !== false)
					{
						// If we are going to show AAA records that were not in the records, we first need to build a set of numbers that were
						if ($virtualFilters['Virtual.show_all_aaa_records'])
						{
							$aaaNumber = $result['AaaMonthlySummary']['aaa_number'];
							if (!isset($aaaCache[$aaaNumber]))
							{
								$aaaCache[$aaaNumber] = 1;
							}
						}
						
						// Exclude records with county that does not default to selected profit center
						if (isset($virtualFilters['Virtual.profit_center_number']))
						{
							$countyCode = $aaaReferral['AaaReferral']['county_code'];
							
							if (!isset($countyCache[$countyCode]))
							{
								$countyCache[$countyCode] = $countyModel->field('default_profit_center', array('code' => $countyCode));
							}
							
							if ($virtualFilters['Virtual.profit_center_number'] != $countyCache[$countyCode])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						$results[$key]['AaaReferral'] = $aaaReferral['AaaReferral'];
					}
					else
					{
						unset($results[$key]);
						continue;
					}
				}
				
				// Show AAA records that had no orders but otherwise qualify.
				if ($virtualFilters['Virtual.show_all_aaa_records'])
				{
					// Add conditions to the afterFilters array that reference the AAA records
					if (isset($filters['AaaMonthlySummary.aaa_number']))
					{
						$afterFilters['AaaReferral.aaa_number'] = $filters['AaaMonthlySummary.aaa_number'];
					}
					
					if (isset($filters['AaaMonthlySummary.rehab_salesman']))
					{
						$afterFilters['AaaReferral.rehab_salesman'] = $filters['AaaMonthlySummary.rehab_salesman'];
					}
					
					$matchingAaaRecords = $this->AaaReferral->find('all', array(
						'contain' => array(),
						'fields' => array(
							'aaa_number',
							'contact_name',
							'facility_name',
							'facility_type',
							'group_code',
							'rehab_market_code',
							'rehab_salesman'
						),
						'conditions' => $afterFilters,
						'order' => 'aaa_number'
					));
					
					foreach ($matchingAaaRecords as $row)
					{
						if (!array_key_exists($row['AaaReferral']['aaa_number'], $aaaCache))
						{
							$results[] = array(
								'AaaMonthlySummary' => array(
									'aaa_number' => $row['AaaReferral']['aaa_number'],
									'date_month' => '',
									'order_salesman' => '',
									'rehab_salesman' => $row['AaaReferral']['rehab_salesman']
								),
								'0' => array(
									'sum_revenue_month' => 0,
									'sum_revenue_12months' => 0,
									'sum_quotes_month' => 0,
									'sum_quotes_12months' => 0
								),
								'AaaReferral' => $row['AaaReferral']
							);
						}
					}
				}
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/aaa_referrals/csv_totals');
				}
			}
			else
			{
				$this->data['AaaMonthlySummary']['date_month_start'] = '1/1/2009';
				$this->data['AaaMonthlySummary']['date_month_end'] = formatDate('today');
			}
			
			$this->helpers[] = 'ajax';
			$rehabMarketingCodes = $this->Lookup->get('rehab_marketing_codes', true, true);
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$this->set(compact('isUpdate', 'rehabMarketingCodes', 'profitCenters'));
		}
	}
?>