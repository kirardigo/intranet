<?php
	class PriorAuthorizationsController extends AppController
	{
		var $uses = array(
			'PriorAuthorization',
			'PriorAuthorizationDenial',
			'PriorAuthorizationDenialMapping',
			'PriorAuthorizationNumber',
			'Customer',
			'Department',
			'Lookup',
			'Note',
			'FileNote'
		);
		var $pageTitle = 'Prior Authorizations';
		
		/**
		 * Lookup the prior auth records for a customer.
		 * @param string $accountNumber The customer's account number.
		 */
		function module_forCustomer($accountNumber, $isUpdate = 0)
		{
			$conditions = array(
				'account_number' => $accountNumber
			);
			
			if (isset($this->params['named']['invoiceNumber']))
			{
				$conditions['invoice_number'] = $this->params['named']['invoiceNumber'];
			}
			
			$inquiryParameters = $this->Session->read('inquiryParameters');
			$this->set('load', ifset($inquiryParameters['load']));
			
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->PriorAuthorization->find('count', array(
					'contain' => array(),
					'conditions' => $conditions,
					'index' => 'A'
				));
				
				return ($count > 0);
			}
			
			$this->paginate = array(
				'contain' => array(),
				'fields' => array(
					'id',
					'authorization_id_number',
					'department_code',
					'carrier_number',
					'status',
					'transaction_control_number',
					'invoice_number',
					'authorization_cmn',
					'date_requested',
					'date_approved',
					'date_denied',
					'date_expiration',
					'description'
				),
				'conditions' => $conditions,
				'index' => 'A'
			);
			
			$this->data = $this->paginate('PriorAuthorization');
			
			$this->set(compact('accountNumber', 'isUpdate'));
		}
		
		/**
		 * Lookup details for prior auth record.
		 * @param int $id The ID of the record.
		 */
		function ajax_detail($id = null)
		{
			$this->autoRenderAjax = false;
			
			if ($id != null)
			{
				$conditions = array('id' => $id);
			}
			else if (isset($this->params['named']['auth_num']))
			{
				$conditions = array('authorization_id_number' => $this->params['named']['auth_num']);
				$index = 'B';
			}
			else if (isset($this->params['named']['new']))
			{
				$inquiryParams = $this->Session->read('inquiryParameters');
				$this->data['PriorAuthorization']['account_number'] = ifset($inquiryParams['accountNumber']);
				$this->data['PriorAuthorization']['department_code'] = ifset($inquiryParams['dept']);
				$this->data['PriorAuthorization']['transaction_control_number'] = ifset($inquiryParams['tcn']);
				$this->data['PriorAuthorization']['transaction_control_number_file'] = ifset($inquiryParams['tcnFile']);
				$this->data['PriorAuthorization']['invoice_number'] = ifset($inquiryParams['invoice']);
				$this->data['PriorAuthorization']['date_of_service'] = formatU05Date(ifset($inquiryParams['dos']));
				$this->data['PriorAuthorization']['date_requested'] = formatU05Date(ifset($inquiryParams['requested']));
				$this->data['PriorAuthorization']['description'] = urldecode(ifset($inquiryParams['description']));
				$this->data['PriorAuthorization']['amount_requested'] = ifset($inquiryParams['amount']);
			}
			else
			{
				die("Filters not correctly specified.");
			}
			
			if (isset($conditions))
			{
				$findArray = array(
					'contain' => array(),
					'conditions' => $conditions
				);
				
				if (isset($index))
				{
					$findArray['index'] = $index;
				}
				
				$this->data = $this->PriorAuthorization->find('first', $findArray);
				
				if ($this->data !== false)
				{
					$id = $this->data['PriorAuthorization']['id'];
					$this->data['PriorAuthorization']['amount_requested'] = formatNumber($this->data['PriorAuthorization']['amount_requested'], 2);
					$this->data['PriorAuthorization']['amount_approved'] = formatNumber($this->data['PriorAuthorization']['amount_approved'], 2);
					$this->data['PriorAuthorization']['appeals_amount'] = formatNumber($this->data['PriorAuthorization']['appeals_amount'], 2);	
					
					$this->set('noteRecord', $this->Note->getNotes($this->PriorAuthorization->generateTargetUri($id)));
				}
			}
			
			$types = $this->Lookup->get('prior_authorization_types', true);
			$statuses = $this->Lookup->get('prior_authorization_statuses', true);
			$tcnFileTypes = $this->Lookup->get('tcn_file_types');
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'types', 'statuses', 'departments', 'tcnFileTypes'));
		}
		
		/**
		 * Show a summary view of the records.
		 */
		function summary()
		{
			$filterName = 'PriorAuthSummaryFilter';
			$postDataName = 'PriorAuthSummaryPost';
			$indexName = 'PriorAuthSummaryIndex';
			$conditions = array();
			$isExport = 0;
			$isMitsExport = 0;
			$records = array();
			
			$types = $this->Lookup->get('prior_authorization_types', true);
			$statuses = $this->Lookup->get('prior_authorization_statuses', true);
			
			$this->set(compact('types', 'statuses'));
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				if (isset($this->data['Virtual']['is_mits_export']))
				{
					$isMitsExport = $this->data['Virtual']['is_mits_export'];
					unset($this->data['Virtual']['is_mits_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				// Process DOS range
				if (isset($filters['PriorAuthorization.date_of_service_start']))
				{
					$filters['PriorAuthorization.date_of_service >='] = databaseDate($filters['PriorAuthorization.date_of_service_start']);
					unset($filters['PriorAuthorization.date_of_service_start']);
				}
				if (isset($filters['PriorAuthorization.date_of_service_end']))
				{
					$filters['PriorAuthorization.date_of_service <='] = databaseDate($filters['PriorAuthorization.date_of_service_end']);
					unset($filters['PriorAuthorization.date_of_service_end']);
				}
				
				// Process requested date range
				if (isset($filters['PriorAuthorization.date_requested_start']))
				{
					$filters['PriorAuthorization.date_requested >='] = databaseDate($filters['PriorAuthorization.date_requested_start']);
					unset($filters['PriorAuthorization.date_requested_start']);
				}
				
				if (isset($filters['PriorAuthorization.date_requested_end']))
				{
					$filters['PriorAuthorization.date_requested <='] = databaseDate($filters['PriorAuthorization.date_requested_end']);
					unset($filters['PriorAuthorization.date_requested_end']);
				}
				
				// Process approved date range
				if (isset($filters['PriorAuthorization.date_approved_start']))
				{
					$filters['PriorAuthorization.date_approved >='] = databaseDate($filters['PriorAuthorization.date_approved_start']);
					unset($filters['PriorAuthorization.date_approved_start']);
				}
				
				if (isset($filters['PriorAuthorization.date_approved_end']))
				{
					$filters['PriorAuthorization.date_approved <='] = databaseDate($filters['PriorAuthorization.date_approved_end']);
					unset($filters['PriorAuthorization.date_approved_end']);
				}
				
				// Process denied date range
				if (isset($filters['PriorAuthorization.date_denied_start']))
				{
					$filters['PriorAuthorization.date_denied >='] = databaseDate($filters['PriorAuthorization.date_denied_start']);
					unset($filters['PriorAuthorization.date_denied_start']);
				}
				
				if (isset($filters['PriorAuthorization.date_denied_end']))
				{
					$filters['PriorAuthorization.date_denied <='] = databaseDate($filters['PriorAuthorization.date_denied_end']);
					unset($filters['PriorAuthorization.date_denied_end']);
				}
				
				// Set index based on criteria
				if (isset($filters['PriorAuthorization.carrier_number']))
				{
					$index = 'D';
				}
				
				if (isset($filters['PriorAuthorization.account_number']))
				{
					$index = 'A';
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$this->Session->write($filterName, $conditions);
				
				if (isset($index))
				{
					$this->Session->write($indexName, $index);
				}
				else
				{
					$this->Session->delete($indexName);
				}
			}
			else if (isset($this->params['named']['reset']))
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
				$this->Session->delete($indexName);
			}
			
			if ($this->Session->check($filterName))
			{
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
				
				if ($this->Session->check($indexName))
				{
					$index = $this->Session->read($indexName);
				}
			}
			
			$findArray = array(
				'contain' => array(),
				'conditions' => $conditions
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport || $isMitsExport)
			{
				$records = $this->PriorAuthorization->find('all', $findArray);
				
				$this->_manipulateResults($records, $filters);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				
				if ($isExport)
				{
					$this->render('/prior_authorizations/csv_summary');
				}
				else if ($isMitsExport)
				{
					$this->render('/prior_authorizations/csv_mits_fup');
				}
				
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				$findArray['conditions']['PriorAuthorization.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('PriorAuthorization');
			
			$this->_manipulateResults($records, $conditions);
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('records', 'profitCenters', 'departments'));
		}
		
		/**
		 * Finish filtering and adding data to the results.
		 */
		function _manipulateResults(&$data, $filters)
		{
			$customer = ClassRegistry::init('Customer');
			$customerCarrier = ClassRegistry::init('CustomerCarrier');
			
			foreach ($data as $key => $row)
			{
				if (isset($filters['Customer.profit_center_number']))
				{
					$profitCenter = $this->Customer->field('profit_center_number', array('account_number' => $row['PriorAuthorization']['account_number']));
					
					if ($profitCenter != $filters['Customer.profit_center_number'])
					{
						unset($data[$key]);
						continue;
					}
				}
				
				$customerData = $customer->find('first', array(
					'contain' => ('CustomerBilling'),
					'conditions' => array(
						'account_number' => $row['PriorAuthorization']['account_number']
					)
				));
				
				if ($customerData !== null)
				{
					$data[$key]['Customer']['name'] = $customerData['Customer']['name'];
					$data[$key]['CustomerBilling']['date_of_birth'] = $customerData['CustomerBilling']['date_of_birth'];
				}
				
				$data[$key]['CustomerCarrier']['claim_number'] = $customerCarrier->field('claim_number', array(
					'account_number' => $row['PriorAuthorization']['account_number'],
					'carrier_number' => $row['PriorAuthorization']['carrier_number']
				));
			}
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to delete.
		 */
		function json_delete($id)
		{
			// Find authorization number and remove mappings
			$authNumber = $this->PriorAuthorization->field('authorization_id_number', array('id' => $id));
			$this->PriorAuthorizationDenialMapping->deleteAll(array(
				'authorization_id_number' => $authNumber
			));
			
			// Remove notes
			$this->Note->deleteNotes($this->PriorAuthorization->generateTargetUri($id));
			
			// Remove the record itself
			$this->PriorAuthorization->delete($id);
			
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
				$this->data['PriorAuthorization']['date_of_service'] = databaseDate($this->data['PriorAuthorization']['date_of_service']);
				$this->data['PriorAuthorization']['date_activated'] = databaseDate($this->data['PriorAuthorization']['date_activated']);
				$this->data['PriorAuthorization']['authorization_start_date'] = databaseDate($this->data['PriorAuthorization']['authorization_start_date']);
				$this->data['PriorAuthorization']['date_requested'] = databaseDate($this->data['PriorAuthorization']['date_requested']);
				$this->data['PriorAuthorization']['date_approved'] = databaseDate($this->data['PriorAuthorization']['date_approved']);
				$this->data['PriorAuthorization']['authorization_end_date'] = databaseDate($this->data['PriorAuthorization']['authorization_end_date']);
				$this->data['PriorAuthorization']['date_expiration'] = databaseDate($this->data['PriorAuthorization']['date_expiration']);
				$this->data['PriorAuthorization']['date_denied'] = databaseDate($this->data['PriorAuthorization']['date_denied']);
				$this->data['PriorAuthorization']['appeals_date'] = databaseDate($this->data['PriorAuthorization']['appeals_date']);

				// Perform validation here
				$this->PriorAuthorization->set($this->data);
				
				if (!$this->PriorAuthorization->validates())
				{
					$success = false;
					$message = print_r($this->PriorAuthorization->validationErrors, true);
				}
				else
				{
					$custCarrierModel = ClassRegistry::init('CustomerCarrier');
					
					// Make sure that the carrier exists for this account
					if (!$custCarrierModel->isCarrierOnAccount($this->data['PriorAuthorization']['account_number'], $this->data['PriorAuthorization']['carrier_number']))
					{
						$success = false;
						$message = 'The carrier does not exist on this account.';
						$this->set('json', array('success' => $success, 'message' => $message));
						return;
					}
					
					// Add Auth ID if one does not exist
					if ($this->data['PriorAuthorization']['authorization_id_number'] == '')
					{
						$this->data['PriorAuthorization']['authorization_id_number'] = 'A' . $this->PriorAuthorizationNumber->increment('authorization_number');
					}
					
					$id = $this->data['PriorAuthorization']['id'];
					
					if ($id != '')
					{
						$oldApproval = $this->PriorAuthorization->field('date_approved', array('id' => $id));
						$oldDenial = $this->PriorAuthorization->field('date_denied', array('id' => $id));
					}
					else
					{
						$oldApproval = '';
						$oldDenial = '';
					}
					
					if (!$this->PriorAuthorization->save($this->data))
					{
						$success = false;
						$message = 'Record could not be saved.';
					}
					else
					{
						if (isset($this->data['Note']['general']['note']))
						{
							$this->Note->saveNote($this->PriorAuthorization->generateTargetUri($this->PriorAuthorization->id), 'general', $this->data['Note']['general']['note']);
						}
						
						$createdBy = $this->Session->read('user');
						
						// If we updated the record, there are situations where we need
						// to generate an eFN.
						if ($id == '')
						{
							$this->FileNote->createNote(
								array('FileNote' => array(
									'account_number' => $this->data['PriorAuthorization']['account_number'],
									'transaction_control_number_file' => $this->data['PriorAuthorization']['transaction_control_number_file'],
									'transaction_control_number' => $this->data['PriorAuthorization']['transaction_control_number'],
									'invoice_number' => $this->data['PriorAuthorization']['invoice_number'],
									'department_code' => $this->data['PriorAuthorization']['department_code'],
									'memo' => "Auth Prior #{$this->data['PriorAuthorization']['authorization_id_number']} submitted to {$this->data['PriorAuthorization']['carrier_number']}",
									'remarks_1' => "Submitted on {$this->data['PriorAuthorization']['date_requested']} for {$this->data['PriorAuthorization']['amount_requested']}",
									'action_code' => 'ORDAP'						
								)),
								$createdBy
							);
						}
						else if ($oldApproval == '' && $this->data['PriorAuthorization']['date_approved'] != '')
						{
							$this->FileNote->createNote(
								array('FileNote' => array(
									'account_number' => $this->data['PriorAuthorization']['account_number'],
									'transaction_control_number_file' => $this->data['PriorAuthorization']['transaction_control_number_file'],
									'transaction_control_number' => $this->data['PriorAuthorization']['transaction_control_number'],
									'invoice_number' => $this->data['PriorAuthorization']['invoice_number'],
									'department_code' => $this->data['PriorAuthorization']['department_code'],
									'memo' => "Auth Prior #{$this->data['PriorAuthorization']['authorization_id_number']} approved by {$this->data['PriorAuthorization']['carrier_number']}",
									'remarks_1' => "Approved on {$this->data['PriorAuthorization']['date_approved']} for {$this->data['PriorAuthorization']['amount_approved']}",
									'action_code' => 'ORDPA'						
								)),
								$createdBy
							);
						}
						else if ($oldDenial == '' && $this->data['PriorAuthorization']['date_denied'] != '')
						{
							$this->FileNote->createNote(
								array('FileNote' => array(
									'account_number' => $this->data['PriorAuthorization']['account_number'],
									'transaction_control_number_file' => $this->data['PriorAuthorization']['transaction_control_number_file'],
									'transaction_control_number' => $this->data['PriorAuthorization']['transaction_control_number'],
									'invoice_number' => $this->data['PriorAuthorization']['invoice_number'],
									'department_code' => $this->data['PriorAuthorization']['department_code'],
									'memo' => "Auth Prior #{$this->data['PriorAuthorization']['authorization_id_number']} denied by {$this->data['PriorAuthorization']['carrier_number']}",
									'remarks_1' => "Denied on {$this->data['PriorAuthorization']['date_denied']} for {$this->data['PriorAuthorization']['amount_requested']}",
									'action_code' => 'ORDDN'						
								)),
								$createdBy
							);
						}
						
						// Store ID if record was saved
						$id = $this->PriorAuthorization->id;
					}
				}
			}
			
			$this->set('json', array('success' => $success, 'message' => $message));
		}
	}
?>