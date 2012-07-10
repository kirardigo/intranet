<?php
	class OrdersController extends AppController
	{
		var $uses = array(
			'Budget',
			'FacilityType',
			'Invoice',
			'Lookup',
			'Order',
			'TransactionJournal'
		);
		
		var $components = array('DefaultFile');
		
		/**
		 * Container for active work modules.
		 */
		function work()
		{
			$this->pageTitle = 'Rehab';
		}
		
		/**
		 * Show the WIP view for active work.
		 * @param bool $isUpdate Determines whether the response is an update.
		 */
		function module_workInProcess($isUpdate = 0)
		{
			$filterName = 'OrdersModuleWorkInProcessFilter';
			$postDataName = 'OrdersModuleWorkInProcessPost';
			$showSummary = true;
			$staffInitials = array();
			
			if ($isUpdate)
			{
				$isExport = 0;
				
				if (isset($this->data['Order']['is_export']))
				{
					$isExport = $this->data['Order']['is_export'];
					unset($this->data['Order']['is_export']);
				}
				
				// Lookup current post period from Default File
				$this->DefaultFile->load();
				$currentMonth = substr($this->DefaultFile->data['current_post_period'], 0, 2);
				$currentYear = substr($this->DefaultFile->data['current_post_period'], 4, 4);
				
				$conditions = array(
					'Order.work_in_process' => array('W', 'D', 'F'),
					'Order.page_number' => 1,
					'or' => array(
						'Order.work_completed_date' => null,
						'and' => array(
							'month(Order.work_completed_date)' => $currentMonth,
							'year(Order.work_completed_date)' => $currentYear
						)
					)
				);
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if ($filters['Order.profit_center_number'] == 'ALL')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$filters['Order.profit_center_number'] = $includedProfitCenters;
					}
					
					if (isset($filters['Order.work_completed_date_start']) || isset($filters['Order.work_completed_date_end']))
					{
						if (isset($filters['Order.work_completed_date_start']))
						{
							$filters['Order.work_completed_date >='] = databaseDate($filters['Order.work_completed_date_start']);
							unset($filters['Order.work_completed_date_start']);
						}
						
						if (isset($filters['Order.work_completed_date_end']))
						{
							$filters['Order.work_completed_date <='] = databaseDate($filters['Order.work_completed_date_end']);
							unset($filters['Order.work_completed_date_end']);
						}
						
						$showSummary = false;
						unset($conditions['or']);
						unset($filters['Order.is_complete']);
					}
					else if (isset($filters['Order.is_complete']))
					{
						if ($filters['Order.is_complete'])
						{
							$filters['Order.work_completed_date NOT'] = null;
						}
						else
						{
							$filters['Order.work_completed_date'] = null;
						}
						
						unset($filters['Order.is_complete']);
						unset($conditions['or']);
					}
					
					if (isset($filters['Order.is_scheduled']))
					{
						$isScheduled = $filters['Order.is_scheduled'];
						unset($filters['Order.is_scheduled']);
						
						if ($isScheduled)
						{
							$filters['Order.work_scheduled_date NOT'] = null;
						}
						else
						{
							$filters['Order.work_scheduled_date'] = null;
						}
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
				
				$results = $this->Order->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$totals = array(
					'currentScheduled' => 0,
					'currentCompleted' => 0,
					'nextScheduled' => 0,
					'wipTotal' => 0
				);
				
				// Budget should default to certain departments
				if ($conditions['Order.profit_center_number'] == '021')
				{
					$departmentCode = 'A';
				}
				else if ($conditions['Order.profit_center_number'] == '070')
				{
					$departmentCode = 'T';
				}
				else
				{
					$departmentCode = 'R';
				}
				
				// Get the current budget
				$totals['budgetTotal'] = $this->Budget->field('SUM(amount) as budgetTotal', array(
					'profit_center_number' => $conditions['Order.profit_center_number'],
					'department' => $departmentCode
				));
				
				// Get the current net revenue
				$totals['revenueTotal'] = $this->TransactionJournal->getMonthToDateNetRevenue($conditions['Order.profit_center_number']);
				$totals['creditTotal'] = $this->TransactionJournal->getMonthToDateCredits($conditions['Order.profit_center_number']);
				
				$priorAuthModel = ClassRegistry::init('PriorAuthorization');
				$usedTransactionControlNumbers = array();
				
				foreach ($results as $key => $row)
				{
					// Set invoice amounts
					$results[$key]['Invoice']['amount'] = $this->Invoice->field('amount', array('invoice_number' => $row['Order']['invoice_number']));
					
					// Set days aged since approved
					if ($row['Order']['funding_approved_date'] != '')
					{
						$results[$key]['Order']['days_old'] = round((strtotime(date('Y-m-d')) - strtotime($row['Order']['funding_approved_date'])) / 86400);
					}
					
					// Set prior authorization expiration date
					if ($row['Order']['transaction_control_number_type'] == 'R')
					{
						$priorAuthRecord = $priorAuthModel->find('first', array(
							'contain' => array(),
							'fields' => array('date_expiration'),
							'conditions' => array('transaction_control_number' => $row['Order']['transaction_control_number']),
							'index' => 'G'
						));
						
						$results[$key]['PriorAuthorization']['date_expiration'] = ($priorAuthRecord === false) ? false : $priorAuthRecord['PriorAuthorization']['date_expiration'];
					}
					
					// Make sure totals do not contain duplicate TCNs
					if (in_array($row['Order']['transaction_control_number'], $usedTransactionControlNumbers))
					{
						continue;
					}
					else
					{
						$usedTransactionControlNumbers[] = $row['Order']['transaction_control_number'];
					}
					
					// Set scheduled totals
					if ($row['Order']['work_scheduled_date'] != '')
					{
						if ($currentMonth == date('m', strtotime($row['Order']['work_scheduled_date'])))
						{
							$totals['currentScheduled'] += $row['Order']['wip_amount'];
						}
						else if ($currentMonth + 1 == date('m', strtotime($row['Order']['work_scheduled_date'])))
						{
							$totals['nextScheduled'] += $row['Order']['wip_amount'];
						}
					}
					
					// Set completed totals
					if ($row['Order']['work_completed_date'] != '' && $currentMonth == date('m', strtotime($row['Order']['work_completed_date'])))
					{
						$totals['currentCompleted'] += $row['Order']['wip_amount'];
					}
					else
					{
						$totals['wipTotal'] += $row['Order']['wip_amount'];
					}
					
					// Build salesman list
					if (!in_array($row['Order']['staff_user_id'], $staffInitials))
					{
						$staffInitials[$row['Order']['staff_user_id']] = $row['Order']['staff_user_id'];
					}
				}
				
				$this->set(compact('results', 'totals'));
				
				if ($isExport)
				{
					$this->render('/orders/csv_work_in_process');
				}
			}
			
			asort($staffInitials);
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$profitCenters['ALL'] = 'All Medical';
			$rehabOptions = $this->Lookup->get('rehab_hospital', true);
			$this->set(compact('profitCenters', 'rehabOptions', 'isUpdate', 'showSummary', 'staffInitials'));
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function module_management()
		{
			// Lookup current post period from Default File
			$this->DefaultFile->load();
			$currentMonthStart = formatU05Date($this->DefaultFile->data['current_post_period']);
			$currentMonthEnd = date('m/d/Y', strtotime($currentMonthStart . ' + 1 month - 1 day'));
			$nextMonthStart = date('m/d/Y', strtotime($currentMonthEnd . ' + 1 day'));
			$nextMonthEnd = date('m/d/Y', strtotime($nextMonthStart . ' + 1 month - 1 day'));
			
			$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
			
			// Find the records for all profit centers
			$results = $this->Order->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'Order.profit_center_number' => $includedProfitCenters,
					'Order.work_in_process' => array('W', 'D', 'F'),
					'Order.page_number' => 1,
					'or' => array(
						'Order.work_completed_date' => null,
						'Order.work_completed_date between ? and ?' => array(databaseDate($currentMonthStart), databaseDate($currentMonthEnd))
					)
				)
			));
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			
			foreach ($profitCenters as $profitCenter => $profitCenterName)
			{
				if (!in_array($profitCenter, $includedProfitCenters))
				{
					continue; // Skip excluded profit centers
				}
				else
				{
					$departmentCode = 'R';
				}
				
				$totals[$profitCenter]['name'] = $profitCenterName;
				
				// Get the current budget by profit center
				$totals[$profitCenter]['budgetTotal'] = $this->Budget->field('SUM(amount) as budgetTotal', array(
						'profit_center_number' => $profitCenter,
						'department' => $departmentCode
				));
				
				// Get the current revenue & credits
				$totals[$profitCenter]['revenueTotal'] = $this->TransactionJournal->getMonthToDateNetRevenue($profitCenter);
				$totals[$profitCenter]['creditTotal'] = $this->TransactionJournal->getMonthToDateCredits($profitCenter);
				
				// Sum the totals
				$totals['all']['budgetTotal'] = ifset($totals['all']['budgetTotal'], 0) + $totals[$profitCenter]['budgetTotal'];
				$totals['all']['revenueTotal'] = ifset($totals['all']['revenueTotal'], 0) + $totals[$profitCenter]['revenueTotal'];
				$totals['all']['creditTotal'] = ifset($totals['all']['creditTotal'], 0) + $totals[$profitCenter]['creditTotal'];
			}
			
			$usedTransactionControlNumbers = array();
			
			foreach ($results as $key => $row)
			{
				$profitCenter = $row['Order']['profit_center_number'];
				
				// Make sure totals do not contain duplicate TCNs
				if (in_array($row['Order']['transaction_control_number'], $usedTransactionControlNumbers))
				{
					continue;
				}
				else
				{
					$usedTransactionControlNumbers[] = $row['Order']['transaction_control_number'];
				}
				
				// Set current month scheduled totals
				if ($row['Order']['work_scheduled_date'] != '' && date('mY', strtotime($currentMonthStart)) == date('mY', strtotime($row['Order']['work_scheduled_date'])))
				{
					$totals[$profitCenter]['currentScheduled'] = ifset($totals[$profitCenter]['currentScheduled'], 0) + $row['Order']['wip_amount'];
					$totals['all']['currentScheduled'] = ifset($totals['all']['currentScheduled'], 0) + $row['Order']['wip_amount'];
				}
				
				// Set next month scheduled totals
				if ($row['Order']['work_scheduled_date'] != '' && date('mY', strtotime($nextMonthStart)) == date('mY', strtotime($row['Order']['work_scheduled_date'])))
				{
					$totals[$profitCenter]['nextScheduled'] = ifset($totals[$profitCenter]['nextScheduled'], 0) + $row['Order']['wip_amount'];
					$totals['all']['nextScheduled'] = ifset($totals['all']['nextScheduled'], 0) + $row['Order']['wip_amount'];
				}
				
				// Set completed totals
				if ($row['Order']['work_completed_date'] != '' && date('mY', strtotime($currentMonthStart)) == date('mY', strtotime($row['Order']['work_completed_date'])))
				{
					$totals[$profitCenter]['currentCompleted'] = ifset($totals[$profitCenter]['currentCompleted'], 0) + $row['Order']['wip_amount'];
					$totals['all']['currentCompleted'] = ifset($totals['all']['currentCompleted'], 0) + $row['Order']['wip_amount'];
				}
				else
				{
					$totals[$profitCenter]['wipTotal'] = ifset($totals[$profitCenter]['wipTotal'], 0) + $row['Order']['wip_amount'];
					$totals['all']['wipTotal'] = ifset($totals['all']['wipTotal'], 0) + $row['Order']['wip_amount'];
				}
			}
			
			$totals['all']['name'] = 'TOTALS';
			
			$quotationResults = $this->Order->find('all', array(
				'contain' => array(),
				'fields' => array(
					'profit_center_number',
					'grand_total'
				),
				'conditions' => array(
					'Order.profit_center_number' => $includedProfitCenters,
					'Order.page_number' => 1,
					'Order.quote_completed_date between ? and ?' => array(databaseDate($currentMonthStart), databaseDate($currentMonthEnd))
				)
			));
			
			foreach ($quotationResults as $key => $row)
			{
				$profitCenter = $row['Order']['profit_center_number'];
				$totals[$profitCenter]['quotationCompleted'] = ifset($totals[$profitCenter]['quotationCompleted'], 0) + $row['Order']['grand_total'];
				$totals['all']['quotationCompleted'] = ifset($totals['all']['quotationCompleted'], 0) + $row['Order']['grand_total'];
			}
			
			$this->set(compact('totals'));
		}
		
		/**
		 * Show the quotation view for active work.
		 * @param bool $isUpdate Determines whether the response is an update.
		 */
		function module_quotation($isUpdate = 0)
		{
			$filterName = 'OrdersModuleQuotationFilter';
			$postDataName = 'OrdersModuleQuotationPost';
			
			// Lookup current post period from Default File
			$this->DefaultFile->load();
			$postingPeriodStart = formatU05Date($this->DefaultFile->data['current_post_period']);
			$postingPeriodEnd = date('m/d/Y', strtotime($postingPeriodStart . ' + 1 month - 1 day'));
			
			// Set the date range for the completed quotations
			$startRange = ifset($this->data['Order']['start_date'], $postingPeriodStart);
			$endRange = ifset($this->data['Order']['end_date'], $postingPeriodEnd);
			unset($this->data['Order']['start_date']);
			unset($this->data['Order']['end_date']);
			
			$orderTypes = array();
			$staffInitials = array();
			$facilityNames = array();
			$groupCodes = array();
			$contactNames = array();
			$isExport = 0;
			$completedTotal = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Order']['is_export']))
				{
					$isExport = $this->data['Order']['is_export'];
					unset($this->data['Order']['is_export']);
				}
				
				$conditions = array(
					'Order.page_number' => 1,
					'Order.needs_quote_date <>' => null,
					'or' => array(
						array(
							'Order.quote_completed_date' => null,
							'Order.deletion_code' => ''
						),
						array(
							'Order.quote_completed_date >=' => databaseDate($startRange),
							'Order.quote_completed_date <=' => databaseDate($endRange)
						)
					)
				);
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if ($filters['Order.profit_center_number'] == 'ALL')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$filters['Order.profit_center_number'] = $includedProfitCenters;
					}
					
					if (isset($filters['AaaReferral.facility_name']))
					{
						$facilityFilter = $filters['AaaReferral.facility_name'];
						unset($filters['AaaReferral.facility_name']);
					}
					
					if (isset($filters['AaaReferral.contact_name']))
					{
						$contactFilter = $filters['AaaReferral.contact_name'];
						unset($filters['AaaReferral.contact_name']);
					}
					
					if (isset($filters['AaaReferral.rehab_salesman']))
					{
						$salesmanFilter = $filters['AaaReferral.rehab_salesman'];
						unset($filters['AaaReferral.rehab_salesman']);
					}
					
					if (isset($filters['AaaReferral.group_code']))
					{
						$groupFilter = $filters['AaaReferral.group_code'];
						unset($filters['AaaReferral.group_code']);
					}
					
					if (isset($filters['AaaReferral.facility_type']))
					{
						$facilityTypeFilter = $filters['AaaReferral.facility_type'];
						unset($filters['AaaReferral.facility_type']);
					}
					
					if (isset($filters['Order.is_complete']))
					{
						$isCompleted = $filters['Order.is_complete'];
						unset($filters['Order.is_complete']);
						
						if ($isCompleted)
						{
							$filters['Order.quote_completed_date <>'] = null;
						}
						else
						{
							$filters['Order.quote_completed_date'] = null;
						}
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
				
				$results = $this->Order->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$aaaReferralModel = ClassRegistry::init('AaaReferral');
				$aaaLookups = array();
				
				// Loop through results to finish cleaning data and generating filter info
				foreach ($results as $key => $row)
				{
					$results[$key]['Invoice']['amount'] = $this->Invoice->field('amount', array('invoice_number' => $row['Order']['invoice_number']));
					
					if ($row['Order']['program_referral_number'] != '')
					{
						// Prevent redundant lookups of a particular AAA record
						if (!isset($aaaData[$row['Order']['program_referral_number']]))
						{
							$aaaRecord = $aaaReferralModel->find('first', array(
								'contain' => array(),
								'fields' => array(
									'facility_name',
									'contact_name',
									'group_code',
									'facility_type',
									'rehab_salesman'
								),
								'conditions' => array(
									'aaa_number' => $row['Order']['program_referral_number']
								)
							));
							
							$aaaData[$row['Order']['program_referral_number']] = $aaaRecord;
						}
						else
						{
							$aaaRecord = $aaaData[$row['Order']['program_referral_number']];
						}
						
						// Models are not associated so we have to filter out results after the fact
						if (isset($facilityFilter))
						{
							if ($facilityFilter != $aaaRecord['AaaReferral']['facility_name'])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						if (isset($salesmanFilter))
						{
							if (strtoupper($salesmanFilter) != $aaaRecord['AaaReferral']['rehab_salesman'])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						if (isset($contactFilter))
						{
							if ($contactFilter != $aaaRecord['AaaReferral']['contact_name'])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						if (isset($groupFilter))
						{
							if ($groupFilter != $aaaRecord['AaaReferral']['group_code'])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						if (isset($facilityTypeFilter))
						{
							if ($facilityTypeFilter != $aaaRecord['AaaReferral']['facility_type'])
							{
								unset($results[$key]);
								continue;
							}
						}
						
						$aaaLookups[$row['Order']['program_referral_number']] = "{$aaaRecord['AaaReferral']['facility_name']} * {$aaaRecord['AaaReferral']['contact_name']}";
						
						if (!in_array($aaaRecord['AaaReferral']['facility_name'], $facilityNames))
						{
							$facilityNames[$aaaRecord['AaaReferral']['facility_name']] = $aaaRecord['AaaReferral']['facility_name'];
						}
						
						if (!in_array($aaaRecord['AaaReferral']['contact_name'], $contactNames))
						{
							$contactNames[$aaaRecord['AaaReferral']['contact_name']] = $aaaRecord['AaaReferral']['contact_name'];
						}
						
						if (!in_array($aaaRecord['AaaReferral']['group_code'], $groupCodes))
						{
							$groupCodes[$aaaRecord['AaaReferral']['group_code']] = $aaaRecord['AaaReferral']['group_code'];
						}
						
						$results[$key]['Order']['program_referral_name'] = $aaaLookups[$row['Order']['program_referral_number']];
						$results[$key]['AaaReferral']['group_code'] = $aaaRecord['AaaReferral']['group_code'];
						$results[$key]['AaaReferral']['facility_type'] = $aaaRecord['AaaReferral']['facility_type'];
						$results[$key]['AaaReferral']['rehab_salesman'] = $aaaRecord['AaaReferral']['rehab_salesman'];
					}
					else
					{
						// If we are filtering by a AAA value, exclude records without a AAA number.
						if (isset($facilityFilter) || isset($contactFilter) || isset($groupFilter) || isset($facilityTypeFilter) || isset($salesmanFilter))
						{
							unset($results[$key]);
							continue;
						}
					}
					
					if ($row['Order']['quote_completed_date'] != null)
					{
						$completedTotal += $row['Order']['grand_total'];
					}
					
					if ($row['Order']['quote_client_care_specialist_date'] != '' && $row['Order']['evaluation_date'] != '')
					{
						$results[$key]['Order']['rts_days'] = weekdayDiff($row['Order']['evaluation_date'], $row['Order']['quote_client_care_specialist_date']);
					}
					else if ($row['Order']['evaluation_date'] != '')
					{
						$results[$key]['Order']['rts_days'] = weekdayDiff($row['Order']['evaluation_date'], databaseDate('now'));
					}
					
					if ($row['Order']['quote_client_care_specialist_date'] != '' && $row['Order']['quote_completed_date'] != '')
					{
						$results[$key]['Order']['ccs_days'] = weekdayDiff($row['Order']['quote_client_care_specialist_date'], $row['Order']['quote_completed_date']);
					}
					
					if (!in_array($row['Order']['order_type'], $orderTypes))
					{
						$orderTypes[$row['Order']['order_type']] = $row['Order']['order_type'];
					}
					
					if (!in_array($row['Order']['staff_user_id'], $staffInitials))
					{
						$staffInitials[$row['Order']['staff_user_id']] = $row['Order']['staff_user_id'];
					}
				}
				
				// Sort the filter options
				asort($orderTypes);
				asort($facilityNames);
				asort($contactNames);
				asort($groupCodes);
				asort($staffInitials);
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/orders/csv_quotation');
				}
			}
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$profitCenters['ALL'] = 'All Medical';
			$rehabOptions = $this->Lookup->get('rehab_hospital', true);
			$aaaTypes = $this->FacilityType->getList(false, 'description');
			
			$this->set(compact('profitCenters', 'rehabOptions', 'isUpdate', 'orderTypes', 'staffInitials',
				'facilityNames', 'contactNames', 'startRange', 'endRange', 'completedTotal', 'groupCodes', 'aaaTypes'));
		}
		
		/**
		 * Show the funding view for active work.
		 * @param bool $isUpdate Determines whether the response is an update.
		 */
		function module_funding($isUpdate = 0)
		{
			$filterName = 'OrdersModuleFundingFilter';
			$postDataName = 'OrdersModuleFundingPost';
			
			$isExport = 0;
			
			// Initialize filter options
			$staffInitials = array();
			$carrier1Codes = array();
			$carrier2Codes = array();
			$carrier3Codes = array();
			$claimsStatuses = array();
			$authorizationStatuses = array();
			$orderTypes = array();
			$wipCodes = array('Any' => 'Any');
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Order']['is_export']))
				{
					$isExport = $this->data['Order']['is_export'];
					unset($this->data['Order']['is_export']);
				}
				
				$conditions = array(
					'Order.page_number' => 1,
					'Order.status' => 'Y',
					'Order.quote_completed_date <>' => null,
					'Order.deletion_code <>' => 'C'
				);
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if ($filters['Order.profit_center_number'] == 'ALL')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$filters['Order.profit_center_number'] = $includedProfitCenters;
					}
					
					if ($filters['Order.work_in_process'] == 'Any')
					{
						unset($filters['Order.work_in_process']);
					}
					else if ($filters['Order.work_in_process'] == 'Blank')
					{
						$filters['Order.work_in_process'] = '';
					}
					
					switch ($filters['Order.funding_pending_date'])
					{
						case 0: // All
							unset($filters['Order.funding_pending_date']);
							break;
						case 1: // Blank
							$filters['Order.funding_pending_date'] = null;
							break;
						case 2: // Not blank
							unset($filters['Order.funding_pending_date']);
							$filters['Order.funding_pending_date <>'] = null;
							break;
					}
					
					switch ($filters['Order.funding_approved_date'])
					{
						case 0: // All
							unset($filters['Order.funding_approved_date']);
							break;
						case 1: // Blank
							$filters['Order.funding_approved_date'] = null;
							break;
						case 2: // Not blank
							unset($filters['Order.funding_approved_date']);
							$filters['Order.funding_approved_date <>'] = null;
							break;
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
				
				$results = $this->Order->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$carrierModel = ClassRegistry::init('Carrier');
				$efnModel = ClassRegistry::init('ElectronicFileNote');
				
				// Loop through results to finish cleaning data and generating filter info
				foreach ($results as $key => $row)
				{
					// Build filter lists for current result set
					if (!in_array($row['Order']['carrier_1_code'], $carrier1Codes))
					{
						$carrier1Codes[$row['Order']['carrier_1_code']] = $row['Order']['carrier_1_code'];
					}
					
					if (!in_array($row['Order']['carrier_2_code'], $carrier2Codes))
					{
						$carrier2Codes[$row['Order']['carrier_2_code']] = $row['Order']['carrier_2_code'];
					}
					
					if (!in_array($row['Order']['carrier_3_code'], $carrier3Codes))
					{
						$carrier3Codes[$row['Order']['carrier_3_code']] = $row['Order']['carrier_3_code'];
					}
					
					if (!in_array($row['Order']['claims_status'], $claimsStatuses))
					{
						$claimsStatuses[$row['Order']['claims_status']] = $row['Order']['claims_status'];
					}
					
					if (!in_array($row['Order']['authorization_status'], $authorizationStatuses))
					{
						$authorizationStatuses[$row['Order']['authorization_status']] = $row['Order']['authorization_status'];
					}
					
					if (!in_array($row['Order']['staff_user_id'], $staffInitials))
					{
						$staffInitials[$row['Order']['staff_user_id']] = $row['Order']['staff_user_id'];
					}
					
					if (!in_array($row['Order']['order_type'], $orderTypes))
					{
						$orderTypes[$row['Order']['order_type']] = $row['Order']['order_type'];
					}
					
					if (!in_array($row['Order']['work_in_process'], $wipCodes))
					{
						if ($row['Order']['work_in_process'] == '')
						{
							$wipCodes['Blank'] = 'Blank';
						}
						else
						{
							$wipCodes[$row['Order']['work_in_process']] = $row['Order']['work_in_process'];
						}
					}
					
					// Calculate days column as either:
					//   1.) Weekdays between request & approval
					//   2.) Weekdays between request & today
					if ($row['Order']['funding_pending_date'] != '' && $row['Order']['funding_approved_date'] != '')
					{
						$results[$key]['Order']['funding_days'] = weekdayDiff($row['Order']['funding_pending_date'], $row['Order']['funding_approved_date']);
					}
					else if ($row['Order']['funding_pending_date'] != '' && $row['Order']['funding_approved_date'] == '')
					{
						$results[$key]['Order']['funding_days'] = weekdayDiff($row['Order']['funding_pending_date'], date('Y-m-d'));
					}
					
					// Lookup the group code for carrier 1
					if ($row['Order']['carrier_1_code'] != '')
					{
						$results[$key]['Order']['carrier_1_group_code'] = $carrierModel->field('group_code', array('carrier_number' => $row['Order']['carrier_1_code']));
					}
					
					$results[$key]['Order']['oldest_efn_followup_date'] = $efnModel->getOldestFollowupDateByTCN($row['Order']['transaction_control_number']);
				}
				
				// Sort the filter lists
				asort($staffInitials);
				asort($carrier1Codes);
				asort($carrier2Codes);
				asort($carrier3Codes);
				asort($claimsStatuses);
				asort($orderTypes);
				asort($authorizationStatuses);
				asort($wipCodes);
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/orders/csv_funding');
				}
			}
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$profitCenters['ALL'] = 'All Medical';
			$rehabOptions = $this->Lookup->get('rehab_hospital', true);
			$this->set(compact('profitCenters', 'isUpdate', 'claimsStatuses', 'authorizationStatuses', 'orderTypes',
				'carrier1Codes', 'carrier2Codes', 'carrier3Codes', 'wipCodes', 'rehabOptions', 'staffInitials'));
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function module_management_canton()
		{
			$profitCenter = '010';
			
			// Lookup current post period from Default File
			$this->DefaultFile->load();
			$currentMonthStart = formatU05Date($this->DefaultFile->data['current_post_period']);
			$currentMonthEnd = date('m/d/Y', strtotime($currentMonthStart . ' + 1 month - 1 day'));
			$nextMonthStart = date('m/d/Y', strtotime($currentMonthEnd . ' + 1 day'));
			$nextMonthEnd = date('m/d/Y', strtotime($nextMonthStart . ' + 1 month - 1 day'));
			
			// Find the records for all profit centers
			$results = $this->Order->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'Order.profit_center_number' => $profitCenter,
					'Order.work_in_process' => array('W', 'D', 'F'),
					'Order.page_number' => 1,
					'or' => array(
						'Order.work_completed_date' => null,
						'Order.work_completed_date between ? and ?' => array(databaseDate($currentMonthStart), databaseDate($currentMonthEnd))
					)
				)
			));
			
			$departmentCode = 'R';
			
			$totals[$profitCenter]['name'] = 'Canton';
			
			// Get the current budget by profit center
			$totals[$profitCenter]['budgetTotal'] = $this->Budget->field('SUM(amount) as budgetTotal', array(
					'profit_center_number' => $profitCenter,
					'department' => $departmentCode
			));
			
			// Get the current revenue & credits
			$totals[$profitCenter]['revenueTotal'] = $this->TransactionJournal->getMonthToDateNetRevenue($profitCenter);
			$totals[$profitCenter]['creditTotal'] = $this->TransactionJournal->getMonthToDateCredits($profitCenter);
			
			$usedTransactionControlNumbers = array();
			
			foreach ($results as $key => $row)
			{
				// Make sure totals do not contain duplicate TCNs
				if (in_array($row['Order']['transaction_control_number'], $usedTransactionControlNumbers))
				{
					continue;
				}
				else
				{
					$usedTransactionControlNumbers[] = $row['Order']['transaction_control_number'];
				}
				
				// Set current month scheduled totals
				if ($row['Order']['work_scheduled_date'] != '' && date('mY', strtotime($currentMonthStart)) == date('mY', strtotime($row['Order']['work_scheduled_date'])))
				{
					$totals[$profitCenter]['currentScheduled'] = ifset($totals[$profitCenter]['currentScheduled'], 0) + $row['Order']['wip_amount'];
				}
				
				// Set next month scheduled totals
				if ($row['Order']['work_scheduled_date'] != '' && date('mY', strtotime($nextMonthStart)) == date('mY', strtotime($row['Order']['work_scheduled_date'])))
				{
					$totals[$profitCenter]['nextScheduled'] = ifset($totals[$profitCenter]['nextScheduled'], 0) + $row['Order']['wip_amount'];
				}
				
				// Set completed totals
				if ($row['Order']['work_completed_date'] != '' && date('mY', strtotime($currentMonthStart)) == date('mY', strtotime($row['Order']['work_completed_date'])))
				{
					$totals[$profitCenter]['currentCompleted'] = ifset($totals[$profitCenter]['currentCompleted'], 0) + $row['Order']['wip_amount'];
				}
				else
				{
					$totals[$profitCenter]['wipTotal'] = ifset($totals[$profitCenter]['wipTotal'], 0) + $row['Order']['wip_amount'];
				}
			}
			
			$quotationResults = $this->Order->find('all', array(
				'contain' => array(),
				'fields' => array(
					'profit_center_number',
					'grand_total'
				),
				'conditions' => array(
					'Order.profit_center_number' => $profitCenter,
					'Order.page_number' => 1,
					'Order.quote_completed_date between ? and ?' => array(databaseDate($currentMonthStart), databaseDate($currentMonthEnd))
				)
			));
			
			foreach ($quotationResults as $key => $row)
			{
				$totals[$profitCenter]['quotationCompleted'] = ifset($totals[$profitCenter]['quotationCompleted'], 0) + $row['Order']['grand_total'];
			}
			
			$this->set(compact('totals'));
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function json_management_canton()
		{
			$this->autoRenderJson = false;
			
			App::import('Vendor', 'OFC/open_flash_chart', true, array(), 'OFC/open-flash-chart.php');
			
			$data = array(9,8,7,6,5,4,3,2,1);
			$bar = new bar();
			//$bar->colour('#BF3B69');
//			$bar->key('Last year', 12);
			$bar->set_values($data);
			
			$chart = new open_flash_chart();
			$chart->set_title('Test');
			$chart->add_element($bar);
			
			$this->set('json', $chart->toString());
			
			$this->render('/shared/json_direct');
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function module_management_akron()
		{
			
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function module_management_youngstown()
		{
			
		}
		
		/**
		 * Show overview reporting for rehab profit centers
		 */
		function module_management_cleveland()
		{
			
		}
	}
?>