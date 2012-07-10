<?php
	class CustomersController extends AppController
	{
		var $pageTitle = 'Client';
		var $uses = array(
			'Customer',
			'CustomerBilling',
			'CustomerCarrier',
			'CustomerStatus',
			'Lookup',
			'PlaceOfResidence',
			'ProfitCenter'
		);
		var $helpers = array('Ajax');
		var $filterName = 'customersFilter';
		
		/**
		 * List the customer records
		 */
		function index()
		{
//			TODO: Consider making this work with the new driver
//			$this->paginate = array(
//				'contain' => array(),
//				'order' => array(
//					"case Customer.account_number when '' then 'zzzzzzz' else Customer.account_number end",
//					'Customer.account_number'
//				)
//			);
			
			$this->paginate = array(
				'fields' => array('account_number', 'profit_center_number', 'name', 'address_1', 'city', 'setup_date'),
				'contain' => array(),
				'order' => array(
					'Customer.account_number'
				),
				'page' => 1,
				'limit' => 25
			);
			
			// Set or erase the filter
			if (isset($this->data))
			{
				if ($this->data['Search']['account_number'] == '' && $this->data['Search']['name'] == '')
				{
					$this->Session->del($this->filterName);
				}
				else
				{
					$this->Session->write($this->filterName, $this->data['Search']);
				}
			}
			
			// Filter the results based on the filter
			if ($this->Session->check($this->filterName))
			{
				$filter = $this->Session->read($this->filterName);
				
				if ($filter['account_number'] != '')
				{
					$this->paginate['conditions']['Customer.account_number'] = $filter['account_number'];
				}
				
				if ($filter['name'] != '')
				{
					$this->paginate['conditions']['Customer.name like'] = $filter['name'] . '%';
				}
				
				$this->data['Search'] = $filter;
			}

			$this->set('pagedData', $this->paginate('Customer'));
		}
		
		/**
		 * Edit a customer record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id = null)
		{
			// Process posted data
			if (isset($this->data))
			{
				pr($this->data);exit;
				try
				{
					$this->CustomerBilling->set($this->data);
					$this->Customer->set($this->data);
					
					if (!$this->CustomerBilling->validate())
					{
						throw new Exception('Unable to validate customer billing record.');
					}
					
					if (!$this->Customer->validate())
					{
						throw new Exception('Unable to validate customer record.');
					}
					
					// Validate the customer carrier if one is present
					if (isset($this->data['CustomerCarrier']))
					{
						if (!$this->CustomerCarrier->save($this->data))
						{
							throw new Exception('Unable to save customer carrier.');
						}
					}
					
					$this->CustomerBilling->save();
					
					$this->data['Customer']['billing_pointer'] = $this->CustomerBilling->id;
					$this->Customer->save();
					
					$this->redirect('index');
				}
				catch (Exception $ex) { }
			}
			// Find existing record if specified
			else if ($id !== null)
			{
				$this->data = $this->Customer->find('first', array(
					'contain' => array('CustomerBilling'),
					'conditions' => array(
						'id' => $id
					),
					'chains' => array(
						'CustomerCarrier'
					)
				));
				
				if ($this->data !== false)
				{
					$this->data['Customer']['setup_date'] = formatDate($this->data['Customer']['setup_date']);
					$this->data['CustomerBilling']['date_of_birth'] = formatDate($this->data['CustomerBilling']['date_of_birth']);
					$this->data['Customer']['hipaa_information_provided_date'] = formatDate($this->data['Customer']['hipaa_information_provided_date']);
				}
			}
			
			$this->set('id', $id);
			
			$this->helpers[] = 'Ajax';
		}
		
		/**
		 * Delete a customer record.
		 * @param int $id The ID of the record to delete.
		 * @param int $page The page number of the index to return to.
		 */
		function delete($id, $page = 1)
		{
			$this->Customer->delete($id);
			$this->redirect("index/page:{$page}");
		}
		
		/**
		 * Ajax action to find an account number for a customer by account number. It expects
		 * $this->data['Customer']['account_number'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (trim($this->data['Customer']['account_number']) == '')
			{
				die();
			}
			
			$matches = $this->Customer->find('all', array(
				'fields' => array('id', 'account_number', 'name'),
				'conditions' => array('account_number like' => $this->data['Customer']['account_number'] . '%'),
				'order' => array('name'),
				'contain' => array()
			));

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Customer.id', 
				'id_prefix' => 'customer_',
				'value_fields' => array('Customer.account_number'),
				'informal_fields' => array('Customer.name')
			));
		}
		
		function ajax_autoCompleteForPurchaseDetail()
		{
			if (!isset($this->data['PurchaseOrderDetail']['search']))
			{
				exit;
			}
			
			$matches = $this->Customer->find('all', array(
				'fields' => array('id', 'account_number', 'name'),
				'conditions' => array('account_number like' => $this->data['PurchaseOrderDetail']['search'] . '%'),
				'order' => array('name'),
				'contain' => array()
			));

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Customer.id', 
				'id_prefix' => 'customer_',
				'value_fields' => array('Customer.account_number'),
				'informal_fields' => array('Customer.name')
			));	
		}
		
		/**
		 * Ajax action to find an account number for a customer by name. It expects
		 * $this->data['Customer']['name'] to be set.
		 */
		function ajax_autoCompleteByName()
		{
			if (trim($this->data['Customer']['name']) == '')
			{
				die();
			}
			
			$matches = $this->Customer->find('all', array(
				'fields' => array('id', 'account_number', 'name'),
				'conditions' => array('name like' => $this->data['Customer']['name'] . '%'),
				'order' => array('name'),
				'contain' => array()
			));

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Customer.id', 
				'id_prefix' => 'customer_',
				'value_fields' => array('Customer.name'),
				'informal_fields' => array('Customer.account_number'),
				'informal_format' => '- <span class="AccountNumber">%s</span>'
			));
		}
		
		/**
		 * AJAX action to get a customer's name.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		accountNumber The account number to find the name for.
		 */
		function ajax_name()
		{
			$match = $this->Customer->field('name', array('account_number' => $this->params['form']['accountNumber']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * JSON action to get a customer's name.
		 *
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		accountNumber The account number to find the name for.
		 * 
		 * The returned JSON will contain a "name" variable with the name, or false it the customer couldn't be found.
		 */
		function json_name()
		{
			$match = $this->Customer->find('first', array(
				'fields' => array('name'),
				'conditions' => array('account_number' => $this->params['form']['accountNumber'])
			));
			
			$name = $match !== false ? $match['Customer']['name'] : false;			
			$this->set('json', compact('name'));
		}
		
		function json_informationById($id)
		{
			$record = $this->Customer->find('first', array(
				'contain' => array(),
				'fields' => array(
					'name',
				),
				'conditions' => array(
					'id' => $id
				)
			));
			
			$this->set('json', $record['Customer']);
		}		
		
		/**
		 * JSON action to get whether a customer is in a competitive bid area.
		 *
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		accountNumber The account number to find the status for.
		 */
		function json_competitive()
		{
			$competitiveBidZipCodeModel = ClassRegistry::init('CompetitiveBidZipCode');
			
			$isCBZip = $competitiveBidZipCodeModel->find('count', array(
				'conditions' => array(
					'competitive_bid_zip_code' => $this->Customer->field('zip_code', array('account_number' => $this->params['form']['accountNumber']))
				),
				'index' => 'A'
			));
			
			$primaryCarrierCode = $this->Customer->getPrimaryCarrierCode($this->params['form']['accountNumber']);
			
			$isCompetitive = $isCBZip > 0 && $primaryCarrierCode == 'MC20' ? 1 : 0;
			
			$this->set('json', array('isCompetitive' => $isCompetitive));
		}
		
		/**
		 * JSON action to get a customer's status.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		accountNumber The account number to find the status for.
		 * 
		 * The returned JSON will contain a "status" variable with the status and a "description" variable
		 * With the status description.
		 */
		function json_status()
		{			
			$match = $this->Customer->find('first', array(
				'fields' => array('account_status_code'),
				'conditions' => array('account_number' => $this->params['form']['accountNumber'])
			));
			
			if ($match === false)
			{
				die();
			}
			 
			//look up the description if we can
			$description = $this->CustomerStatus->field('description', array('code' => $match['Customer']['account_status_code']));
			
			$this->set('json', array('status' => $match['Customer']['account_status_code'], 'description' => $description !== false ? $description : ''));
		}
		
		/**
		 * JSON action to get a customer's phone number.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		accountNumber The account number to find the status for.
		 * 
		 * The returned JSON will contain a "phone" variable with the phone number.
		 */
		function json_phone()
		{
			$this->set('json', array('phone' => $this->Customer->field('phone_number', array('account_number' => $this->params['form']['accountNumber']))));
		}
		
		/**
		 * Module that allows a user to change the status on the customer.
		 * @param string $accountNumber The account number of the customer to modify.
		 */
		function module_changeStatus($accountNumber)
		{
			//default the two fields we need
			$this->data['Customer']['account_number'] = $accountNumber;
			$this->data['Customer']['account_status_code'] = $this->Customer->field('account_status_code', array('account_number' => $accountNumber));
			
			//grab the statuses
			$this->set('statuses', $this->CustomerStatus->getList());
		}
		
		/**
		 * Create a new customer record.
		 */
		function create()
		{
			$this->pageTitle = 'New Client';
			
			if (isset($this->data))
			{
				$this->Customer->set($this->data);
				$this->CustomerBilling->set($this->data);
				
				$valid = true;
				
				if (!$this->Customer->validates())
				{
					$valid = false;
				}
				
				if (!$this->CustomerBilling->validates())
				{
					$valid = false;
				}
				
				if ($valid)
				{
					$accountNumber = $this->ProfitCenter->nextFreeAccountNumber($this->data['Customer']['profit_center_number']);
					$this->data['CustomerBilling']['account_number'] = $accountNumber;
					
					$this->CustomerBilling->create();
					$this->CustomerBilling->saveViaFilepro($this->data);
					
					$this->data['Customer']['account_number'] = $accountNumber;
					$this->data['Customer']['billing_pointer'] = $this->CustomerBilling->id;
					$this->data['Customer']['setup_date'] = databaseDate('now');
					$this->data['Customer']['account_status_code'] = 10;
					
					$this->Customer->create();
					$this->Customer->saveViaFilepro($this->data);
					
					$this->redirect("/customers/inquiry/accountNumber:{$accountNumber}");
				}
			}
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			unset($profitCenters['000']); // No customers in Admin
			
			$this->set(compact('profitCenters'));
		}
		
		/**
		 * Action for the customer inquiry.
		 */
		function inquiry() 
		{
			$this->helpers[] = 'ajax';
			
			if (count($this->params['named']) > 0)
			{
				if (isset($this->params['named']['accountNumber']))
				{
					$this->set('accountNumber', $this->params['named']['accountNumber']);
				}
				
				$this->Session->write('inquiryParameters', $this->params['named']);
			}
			else
			{
				$this->Session->delete('inquiryParameters');
			}
			
			$this->set('initialTab', isset($this->params['named']['tab']) ? $this->params['named']['tab'] : 0);
		}
		
		/**
		 * Determine which tabs on inquiry screen contain data.
		 */
		function json_inquiryCheckForData($accountNumber)
		{
			$CustomerCoreTab = true;
			$CustomerCarriersTab = $this->requestAction("/modules/customerCarriers/forCustomer/{$accountNumber}/checkForData:1");
			$AaaReferralsTab = true;
			$COETab = $this->requestAction("/modules/customerOwnedEquipment/forCustomer/{$accountNumber}/checkForData:1");
			$RentalEquipmentTab = $this->requestAction("/modules/rentals/forCustomer/{$accountNumber}/checkForData:1");
			$PurchasesTab = $this->requestAction("/modules/purchases/forCustomer/{$accountNumber}/checkForData:1");
			$OnOrderTab = $this->requestAction("/modules/purchaseOrders/forCustomer/{$accountNumber}/checkForData:1");
			$InvoicesTab = $this->requestAction("/modules/invoices/forCustomer/{$accountNumber}/checkForData:1");
			$LedgerTab = $this->requestAction("/modules/invoices/ledger/{$accountNumber}/checkForData:1");
			$DocPopTab = $this->requestAction("/modules/documents/forCustomer/{$accountNumber}/checkForData:1");
			$AuthsTab = $this->requestAction("/modules/priorAuthorizations/forCustomer/{$accountNumber}/checkForData:1");
			$VOBTab = $this->requestAction("/modules/eligibilityRequests/forCustomer/{$accountNumber}/checkForData:1");
			$CCLTab = $this->requestAction("/modules/clientCommunicationLog/forCustomer/{$accountNumber}/checkForData:1");
			$eFNTab = true;
			$OxygenTab = $this->requestAction("/modules/oxygen/oxygenForCustomer/{$accountNumber}/checkForData:1");
			$RadTab = $this->requestAction("/modules/oxygen/radForCustomer/{$accountNumber}/checkForData:1");
			
			$this->set('json', compact(
				'CustomerCoreTab',
				'CustomerCarriersTab',
				'AaaReferralsTab',
				'COETab',
				'RentalEquipmentTab',
				'PurchasesTab',
				'OnOrderTab',
				'InvoicesTab',
				'LedgerTab',
				'DocPopTab',
				'AuthsTab',
				'VOBTab',
				'CCLTab',
				'eFNTab',
				'OxygenTab',
				'RadTab'
			));
		}
		
		/**
		 * Get customer information via JSON request.
		 * @param string $accountNumber The account number to fetch data for.
		 */
		function json_information($accountNumber)
		{
			$record = $this->Customer->find('first', array(
				'contain' => array(),
				'fields' => array(
					'name',
					'address_1',
					'address_2',
					'city',
					'zip_code',
					'phone_number'
				),
				'conditions' => array('account_number' => $accountNumber)
			));
			
			$billingRecord = $this->Customer->CustomerBilling->find('first', array(
				'contain' => array(),
				'fields' => array(
					'sex',
					'date_of_birth'
				),
				'conditions' => array('account_number' => $accountNumber)
			));
			
			$this->set('json', array('record' => $record['Customer'], 'billing' => $billingRecord['CustomerBilling']));
		}
		
		/**
		 * Save the customer core information from module_core.
		 * @param string $accountNumber The account number of the customer to view.
		 */
		function json_saveCore($accountNumber)
		{
			$id = $this->Customer->field('id', array('account_number' => $accountNumber));
			$billingID = $this->Customer->field('billing_pointer', array('account_number' => $accountNumber));
			
			$result = array(
				'success' => true,
				'message' => '',
				'customerErrors' => array(),
				'billingErrors' => array()
			);
			
			if (isset($this->data))
			{
				if ($id === false || $billingID === false)
				{
					$result['success'] = false;
					$result['message'] = "Could not find existing record.";
				}
				else
				{
					$customerRecord = $this->Customer->find('first', array(
						'contain' => array(),
						'conditions' => array('id' => $id)
					));
					
					if ($customerRecord['Customer']['address_verification_date'] != $this->data['Customer']['address_verification_date'] &&
						$this->data['Customer']['address_verification_user'] == '')
					{
						$this->data['Customer']['address_verification_user'] = User::current();
					}
					
					$billingRecord = $this->Customer->CustomerBilling->find('first', array(
						'contain' => array(),
						'conditions' => array('id' => $billingID)
					));
					
					if ($billingRecord['CustomerBilling']['stats_seat_width'] != $this->data['CustomerBilling']['stats_seat_width'] ||
						$billingRecord['CustomerBilling']['stats_hip_width'] != $this->data['CustomerBilling']['stats_hip_width'] ||
						$billingRecord['CustomerBilling']['stats_hip_shoulder'] != $this->data['CustomerBilling']['stats_hip_shoulder'] ||
						$billingRecord['CustomerBilling']['stats_hip_knee_right'] != $this->data['CustomerBilling']['stats_hip_knee_right'] ||
						$billingRecord['CustomerBilling']['stats_hip_knee_left'] != $this->data['CustomerBilling']['stats_hip_knee_left'] ||
						$billingRecord['CustomerBilling']['stats_knee_foot'] != $this->data['CustomerBilling']['stats_knee_foot'])
					{
						$this->data['CustomerBilling']['stats_updated'] = databaseDate('now');
						$this->data['CustomerBilling']['stats_ini'] = User::current();
					}
					
					// Clean up the data before saving
					$this->data['Customer']['id'] = $id;
					$this->data['CustomerBilling']['id'] = $billingID;
					$this->data['CustomerBilling']['home_health_agency_date'] = databaseDate($this->data['CustomerBilling']['home_health_agency_date']);
					$this->data['CustomerBilling']['date_of_birth'] = databaseDate($this->data['CustomerBilling']['date_of_birth']);
					$this->data['CustomerBilling']['date_of_injury'] = databaseDate($this->data['CustomerBilling']['date_of_injury']);
					$this->data['Customer']['setup_date'] = databaseDate($this->data['Customer']['setup_date']);
					$this->data['CustomerBilling']['stats_updated'] = databaseDate($this->data['CustomerBilling']['stats_updated']);
					$this->data['Customer']['archive_date'] = databaseDate($this->data['Customer']['archive_date']);
					$this->data['Customer']['hipaa_information_provided_date'] = databaseDate($this->data['Customer']['hipaa_information_provided_date']);
					$this->data['Customer']['address_verification_date'] = databaseDate($this->data['Customer']['address_verification_date']);
					$this->data['Customer']['address_1'] = preg_replace('/[\.\-#,]/', '', $this->data['Customer']['address_1']);
					$this->data['CustomerBilling']['address_1'] = preg_replace('/[\.\-#,]/', '', $this->data['CustomerBilling']['address_1']);
					
					$this->Customer->set($this->data);
					$this->CustomerBilling->set($this->data);
					
					if (!$this->Customer->validates())
					{
						$result['success'] = false;
						$result['customerErrors'] = $this->Customer->invalidFields();
						
						foreach ($result['customerErrors'] as $key => $row)
						{
							$result['customerErrors']['Customer' . Inflector::camelize($key)] = $row;
							unset($result['customerErrors'][$key]);
						}
					}
					
					if (!$this->CustomerBilling->validates())
					{
						$result['success'] = false;
						$result['billingErrors'] = $this->CustomerBilling->invalidFields();
						
						foreach ($result['billingErrors'] as $key => $row)
						{
							$result['billingErrors']['CustomerBilling' . Inflector::camelize($key)] = $row;
							unset($result['billingErrors'][$key]);
						}
					}
					
					if ($result['success'])
					{
						$this->Customer->save($this->data);
						$this->CustomerBilling->save($this->data);
						
						$isDeceased = $this->CustomerBilling->field('is_deceased', array('id' => $this->data['CustomerBilling']['id']));
						
						if ($isDeceased)
						{
							$coeModel = ClassRegistry::init('CustomerOwnedEquipment');
							
							$coeRecords = $coeModel->find('all', array(
								'contain' => array(),
								'fields' => array(
									'id',
									'is_active'
								),
								'conditions' => array(
									'account_number' => $customerRecord['Customer']['account_number'],
									'is_active' => 1
								),
								'index' => 'G'
							));
							
							foreach ($coeRecords as $record)
							{
								$coeModel->create();
								$record['CustomerOwnedEquipment']['is_active'] = 0;
								$coeModel->save($record);
							}
						}
					}
				}
			}
			
			$this->set('suppressJsonHeader', true);
			$this->set('json', $result);
		}
		
		/**
		 * Module that allows a user to see the customer core information.
		 * @param string $accountNumber The account number of the customer to view.
		 */
		function module_core($accountNumber)
		{
			$this->data = $this->Customer->find('first', array(
				'contain' => array('CustomerBilling'),
				'conditions' => array('account_number' => $accountNumber)
			));
			
			// Lookup diagnoses
			$diagnosisModel = ClassRegistry::init('Diagnosis');
			for ($i = 1; $i <= 6; $i++)
			{
				$fieldName = 'diagnosis_code_' . $i;
				
				if ($this->data['CustomerBilling'][$fieldName] === '')
				{
					$data = false;
				}
				else
				{
					$data = $diagnosisModel->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'number' => $this->data['CustomerBilling'][$fieldName]
						),
						'index' => 'C'
					));
				}
				
				$this->data['Diagnosis'][$i]['id'] = ($data !== false) ? $data['Diagnosis']['id'] : false;
				$this->data['Diagnosis'][$i]['code'] = ($data !== false) ? $data['Diagnosis']['code'] : false;
				$this->data['Diagnosis'][$i]['description'] = ($data !== false) ? $data['Diagnosis']['description'] : false;
			}
			
			// Look up physician records if specified
			$physicians = array(
				1 => 'physician_number',
				2 => 'physician_number_2'
			);
			
			foreach ($physicians as $key => $physicianField)
			{
				if (ifset($this->data['CustomerBilling'][$physicianField]) != '')
				{
					$physicianModel = ClassRegistry::init('Physician');
					$physician = $physicianModel->find('first', array(
						'contain' => array(),
						'conditions' => array('physician_number' => $this->data['CustomerBilling'][$physicianField])
					));
					
					$this->data['Physician'][$key] = $physician['Physician'];
				}
			}
			
			// Look up AAA records if specified
			$aaaFields = array(
				1 => array(
					'field' => 'referral_number_from_aaa_file',
					'label' => 'AAA#'
				),
				2 => array(
					'field' => 'long_term_care_facility_number',
					'label' => 'LTCF#'
				),
				3 => array(
					'field' => 'school_or_program_number_from_aaa_file',
					'label' => 'Program#'
				)
			);
			
			foreach ($aaaFields as $key => $aaaField)
			{
				if (ifset($this->data['CustomerBilling'][$aaaField['field']]) != '')
				{
					$aaaModel = ClassRegistry::init('AaaReferral');
					$aaaRecord = $aaaModel->find('first', array(
						'contain' => array(),
						'fields' => array(
							'id',
							'aaa_number',
							'facility_name',
							'contact_name',
							'phone_number'
						),
						'conditions' => array('aaa_number' => $this->data['CustomerBilling'][$aaaField['field']])
					));
					
					$this->data['AaaReferral'][$key] = $aaaRecord['AaaReferral'];
				}
			}
			
			// Lookup salesman name from staff table, if specified
			if (ifset($this->data['CustomerBilling']['salesman_number']) != '')
			{
				$staffModel = ClassRegistry::init('Staff');
				$this->data['CustomerBilling']['salesman_name'] = $staffModel->getStaffName($this->data['CustomerBilling']['salesman_number']);
			}
			
			$sexes = $this->Lookup->get('sex');
			$profileNumbers = $this->Lookup->get('profile_number', true);
			$advanceDirectives = $this->Lookup->get('advance_directive', true);
			$relationships = $this->Lookup->get('relationship', true);
			$placesOfResidence = $this->PlaceOfResidence->getList();
			$customerStatuses = $this->CustomerStatus->getList();
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			
			$this->set(compact('sexes', 'profileNumbers', 'advanceDirectives', 'relationships',
				'accountNumber', 'placesOfResidence', 'customerStatuses', 'aaaFields', 'profitCenters'));
		}
		
		/**
		 * Module for advanced customer searching.
		 */
		function module_search()
		{
			if (!empty($this->data))
			{				
				$accountNumbers = array();
				$hasOtherCriteria = false;
				
				if (trim($this->data['CustomerBilling']['social_security_number']) != '')
				{
					$social = trim(str_replace('*', '%', $this->data['CustomerBilling']['social_security_number']));
					
					//go find account numbers matching socials
					$matches = $this->Customer->CustomerBilling->find('all', array(
						'fields' => array('account_number'),
						'conditions' => array('social_security_number LIKE' => $social . '%'),
						'contain' => array()
					));
					
					$accountNumbers = array_unique(Set::extract('/CustomerBilling/account_number', $matches));
					$hasOtherCriteria = true;
				}
				
				if (trim($this->data['CustomerCarrier']['claim_number']) != '')
				{
					$claimNumber = trim(str_replace('*', '%', $this->data['CustomerCarrier']['claim_number']));
					
					//go find account numbers matching the claim number and array merge & unique to existing
					//notice the lack of a LIKE clause on this field. Only exact matches are done for claim numbers
					//since it's a chain
					$matches = $this->CustomerCarrier->find('all', array(
						'fields' => array('account_number'),
						'conditions' => array('claim_number' => $claimNumber),
						'contain' => array()
					));
					
					$accountNumbers = array_unique(array_merge($accountNumbers, Set::extract('/CustomerCarrier/account_number', $matches)));
					$hasOtherCriteria = true;
				}
				
				$postedConditions = $this->postConditions(array('Customer' => array_filter($this->data['Customer'])));
				$conditions = array();
				
				//TODO:
				//1. and/or support, add drop down for AND/OR choice
				//2. sortable columns in view
				//4. popup help dialog to explain use of %
				//5. show a warning on the screen when they select OR
				
				//$conditions['or'] = array();
				
				foreach ($postedConditions as $key => $condition)
				{
					$conditions[$key . ' LIKE'] = str_replace('*', '%', $condition) . '%';
				}
				
				if (!empty($accountNumbers))
				{
					$conditions['Customer.account_number'] = $accountNumbers;
				}
				else if ($hasOtherCriteria && count($postedConditions) == 0)
				{
					//if the user specified criteria from other models and those found no matches,
					//and furthermore, we don't have any other posted conditions to filter out the results,
					//we're going to use a bogus criteria that will find no records really fast.
					$conditions['Customer.id'] = -1;
				}

				$matches = $this->Customer->find('all', array(
					'fields' => array(
						'Customer.account_number', 
						'Customer.name', 
						'Customer.address_1', 
						'Customer.phone_number',
						'Customer.billing_pointer',
						'CustomerBilling.social_security_number'
					),
					'conditions' => $conditions,
					'order' => array('account_number'),
					'contain' => array('CustomerBilling')
				));
								
				$this->set('matches', $matches);
			}
		}
		
		/**
		 * Container for client reporting modules.
		 */
		function reporting()
		{
			$this->pageTitle = 'Client Management';
		}
		
		/**
		 * Generate summary report of clients.
		 */
		function module_summary($isUpdate = 0)
		{
			set_time_limit(0);
			
			$filterName = 'CustomersModuleSummaryFilter';
			$postDataName = 'CustomersModuleSummaryPost';
			
			$isExport = 0;
			
			// Initialize filter options
			$ltcfNumbers = array();
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Customer']['is_export']))
				{
					$isExport = $this->data['Customer']['is_export'];
					unset($this->data['Customer']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if ($filters['Customer.profit_center_number'] == 'ALL')
					{
						$includedProfitCenters = $this->Lookup->getMedicalProfitCenters();
						$filters['Customer.profit_center_number'] = $includedProfitCenters;
					}
					
					if (isset($filters['Customer.setup_date']))
					{
						$filters['Customer.setup_date >='] = databaseDate($filters['Customer.setup_date']);
						unset($filters['Customer.setup_date']);
					}
					
					if (isset($filters['Customer.setup_date_end']))
					{
						$filters['Customer.setup_date <='] = databaseDate($filters['Customer.setup_date_end']);
						unset($filters['Customer.setup_date_end']);
					}
					
					if (array_key_exists('Customer.zip_code', $filters))
					{
						$filters['Customer.zip_code'] = explode(',', str_replace(' ', '', $filters['Customer.zip_code']));
					}
					
					if (array_key_exists('CustomerBilling.long_term_care_facility_number', $filters))
					{
						$filters['CustomerBilling.long_term_care_facility_number'] = explode(',', str_replace(' ', '', $filters['CustomerBilling.long_term_care_facility_number']));
					}
					
					if (array_key_exists('CustomerBilling.referral_number_from_aaa_file', $filters))
					{
						$filters['CustomerBilling.referral_number_from_aaa_file'] = explode(',', str_replace(' ', '', $filters['CustomerBilling.referral_number_from_aaa_file']));
					}
					
					if (array_key_exists('CustomerBilling.school_or_program_number_from_aaa_file', $filters))
					{
						$filters['CustomerBilling.school_or_program_number_from_aaa_file'] = explode(',', str_replace(' ', '', $filters['CustomerBilling.school_or_program_number_from_aaa_file']));
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
				
				$results = $this->Customer->find('all', array(
					'contain' => array('CustomerBilling'),
					'conditions' => $conditions,
					'limit' => 500
				));
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/customers/csv_summary');
				}
			}
			
			$this->helpers[] = 'ajax';
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$profitCenters['ALL'] = 'All Medical';
			$this->set(compact('profitCenters', 'isUpdate'));
		}
		
		/**
		 * Utility for changing the profit center number of a given account. Since the account numbering is tied to the profit center, this also
		 * causes the account number to change.
		 */
		function utilityChangeProfitCenter()
		{
			$this->pageTitle = 'Change Profit Center Utility';
			$profitCenters = $this->Lookup->getMedicalProfitCenters();
			$exists = true;
			$alreadyInProfitCenter = false;
			
			if (!empty($this->data))
			{
				//grab the customer
				$customer = $this->Customer->find('first', array('fields' => array('profit_center_number'), 'conditions' => array('account_number' => $this->data['Customer']['account_number'])));
				
				//make sure they exist
				if ($customer === false)
				{
					$exists = false;
				}
				else if ($customer['Customer']['profit_center_number'] == $profitCenters[$this->data['Customer']['profit_center_number']])
				{
					//if the customer is already in the chosen profit center, we aren't going to migrate anything
					$alreadyInProfitCenter = true;
				}
				
				//only migrate if everything checked out ok
				if ($exists && !$alreadyInProfitCenter)
				{
					shell_exec(
						sprintf(
							"cd %s; nohup ./cake/console/cake change_customer_profit_center " .
							"-impersonate %s -account %s -pc %s > /dev/null 2>&1 &",
							escapeshellarg(ROOT),
							escapeshellarg($this->Session->read('user')),
							escapeshellarg($this->data['Customer']['account_number']),
							escapeshellarg($profitCenters[$this->data['Customer']['profit_center_number']])
						)
					);
				
					$this->redirect('/processes/manager/reset:1');
				}
			}
			
			$this->set(compact('profitCenters', 'exists', 'alreadyInProfitCenter'));
		}
		
		/**
		 * Utility for recovering any failures that may have occurred during a profit center migration. Any recoverable failures are written to the migration_recoveries
		 * table. This action then invokes the shell that can read those records and attempt to migrate them again.
		 */
		function utilityMigrationRecovery()
		{
			$this->pageTitle = 'Change Profit Center Recovery Utility';
			
			if (!empty($this->data))
			{
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake migration_recovery " .
						"-impersonate %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user'))
					)
				);
			
				$this->redirect('/processes/manager/reset:1');
			}
		}
	}
?>