<?php
	class PhysiciansController extends AppController
	{
		var $pageTitle = 'Physicians';
		
		var $uses = array('Physician', 'Note');
		
		var $filterName = 'physiciansFilter';
		
		/**
		 * AJAX action to get a physician name.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		physician_number The carrier number to find the name for.
		 */
		function ajax_name()
		{
			$match = $this->Physician->field('name', array('physician_number' => $this->params['form']['physician_number']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * Ajax action to find a physician by name.
		 * Expects $this->data['Physician']['search'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (!isset($this->data['Physician']['search']))
			{
				exit;
			}
			
			$value = $this->data['Physician']['search'];
			
			$single = $this->Physician->find('first', array(
				'contain' => array(),
				'fields' => array('id', 'name', 'address_1', 'phone_number'),
				'conditions' => array('physician_number' => $value),
				'order' => array('name')
			));
			
			if ($single === false)
			{
				$matches = $this->Physician->find('all', array(
					'contain' => array(),
					'fields' => array('id', 'name', 'address_1', 'phone_number'),
					'conditions' => array('name like' => $value . '%'),
					'order' => array('name')
				));
			}
			else
			{
				$matches[0] = $single;
			}
			
			$this->set('output', array(
				'data' => $matches,
				'id_field' => 'Physician.id', 
				'id_prefix' => '',
				'value_fields' => array('Physician.name'),
				'informal_fields' => array('Physician.address_1', 'Physician.phone_number'),
				'informal_format' => ' | %s | %s'
			));
		}
		
		/**
		 * Get information about the physician via JSON by the ID.
		 * @param int $id The ID of the record to fetch.
		 */
		function json_information($id)
		{
			$record = $this->Physician->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			$data = array(
				'name' => ifset($record['Physician']['name']),
				'physician_number' => ifset($record['Physician']['physician_number']),
				'phone_number' => ifset($record['Physician']['phone_number'])
			);
			
			$this->set('json', $data);
		}
		
		/**
		 * Check to see if there are outstanding rentals on an account for a physician.
		 * @param string $physicianNumber The physician number.
		 * @param string $accountNumber The account number of the customer.
		 */
		function json_outstandingCustomerRental($physicianNumber, $accountNumber)
		{
			$warning = false;
			$message = '';
			$customerModel = ClassRegistry::init('Customer');
			$rentalModel = ClassRegistry::init('Rental');
			
			$rentalPointer = $customerModel->field('rental_equipment_pointer', array('account_number' => $accountNumber));
			
			while ($rentalPointer != 0)
			{
				$record = $rentalModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'next_record_pointer',
						'physician_equipment_code',
						'returned_date'
					),
					'conditions' => array('id' => $rentalPointer)
				));
				
				if ($record === false)
				{
					$warning = true;
					$message = 'Rental chain is broken. Please fix before proceeding.';
				}
				
				if ($record['Rental']['physician_equipment_code'] == $physicianNumber &&
					$record['Rental']['returned_date'] == '')
				{
					$warning = true;
					$message = 'Outstanding rental equipment exists. Consider adding additional physician.';
					break;
				}
				
				$rentalPointer = $record['Rental']['next_record_pointer'];
			}
			
			$this->set('json', array('warning' => $warning, 'message' => $message));
		}
		
		/**
		 * List the physician records
		 */
		function index()
		{
//			TODO: Consider making this work with the new driver
//			$this->paginate = array(
//				'contain' => array(),
//				'order' => array(
//					"case Physician.physician_number when '' then 'zzzzzzz' else Physician.physician_number end",
//					'Physician.physician_number'
//				)
//			);
			
			$this->paginate = array(
				'fields' => array('physician_number', 'name', 'address_1', 'city', 'unique_identification_number'),
				'contain' => array(),
				'order' => array(
					'Physician.physician_number'
				)
			);
			
			// Set or erase the filter
			if (isset($this->data))
			{
				if ($this->data['Search']['physician_number'] == '' && $this->data['Search']['name'] == '')
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
				
				if ($filter['physician_number'] != '')
				{
					$this->paginate['conditions']['Physician.physician_number'] = $filter['physician_number'];
				}
				
				if ($filter['name'] != '')
				{
					$this->paginate['conditions']['Physician.name like'] = $filter['name'] . '%';
				}
				
				$this->data['Search'] = $filter;
			}
			
			$this->set('pagedData', $this->paginate('Physician'));
		}
		
		/**
		 * Edit a physician record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id = null)
		{
			// Process posted data
			if (isset($this->data))
			{
				$this->Physician->set($this->data);
				
				if ($this->Physician->validates())
				{
					$oldLicenseNumber = $this->Physician->field('license_number', array('id' => $id));
					
					if ($oldLicenseNumber === false || $oldLicenseNumber != $this->data['Physician']['license_number'])
					{
						$this->data['Physician']['license_number_update_date'] = databaseDate('now');
					}
					else
					{
						$this->data['Physician']['license_number_update_date'] = databaseDate($this->data['Physician']['license_number_update_date']);
					}
					
					// We need to create a physician number if it is a new record
					if ($id == null)
					{
						$this->data['Physician']['physician_number'] = $this->Physician->getSequenceNumber($this->data['Physician']['name']);
					}
					
					if ($this->Physician->saveViaFilepro($this->data))
					{
						$id = $this->Physician->id;
						
						$this->Note->saveNote($this->Physician->generateTargetUri($id), 'comments', $this->data['Note']['note']);
						
						$this->set('close', true);
					}
				}
			}
			// Find existing record if specified
			else if ($id !== null)
			{
				$this->data = $this->Physician->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					$this->data['Physician']['license_number_update_date'] = formatDate($this->data['Physician']['license_number_update_date']);
				}
			}
			
			// Needs to be in separate section to ensure it is loaded if validation fails
			if ($id !== null)
			{	
				$this->set('noteRecord', $this->Note->getNotes($this->Physician->generateTargetUri($id), 'comments'));
			}
			
			$this->set('id', $id);
		}
		
		/**
		 * Delete a physician record.
		 * @param int $id The ID of the record to delete.
		 * @param int $page The page number of the index to return to.
		 */
		function delete($id, $page = 1)
		{
			//$this->Physician->delete($id);
			$userID = $this->Session->read('user');
			$this->log("Physician Deletion Attempt: User: {$userID}, Record: {$id}");
			$this->redirect("index/page:{$page}");
		}
		
		/**
		 * Container for Physician reporting tabs.
		 */
		function reporting()
		{
			
		}
		
		/**
		 * Generate summary report of Physician records.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'PhysiciansModuleSummaryFilter';
			$postDataName = 'PhysiciansModuleSummaryPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['Physician']['is_export']))
				{
					$isExport = $this->data['Physician']['is_export'];
					unset($this->data['Physician']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					if (isset($filters['Physician.name']))
					{
						$filters['Physician.name LIKE'] = $filters['Physician.name'] . '%';
						unset($filters['Physician.name']);
					}
					
					if (isset($filters['Physician.city']))
					{
						$filters['Physician.city LIKE'] = $filters['Physician.city'] . '%';
						unset($filters['Physician.city']);
					}
					
					if (isset($filters['Physician.unique_identification_number']))
					{
						$filters['Physician.unique_identification_number LIKE'] = $filters['Physician.unique_identification_number'] . '%';
						unset($filters['Physician.unique_identification_number']);
					}
					
					if (isset($filters['Physician.medicaid_provider_number']))
					{
						$filters['Physician.medicaid_provider_number LIKE'] = $filters['Physician.medicaid_provider_number'] . '%';
						unset($filters['Physician.medicaid_provider_number']);
					}
					
					if (isset($filters['Physician.license_number']))
					{
						$filters['Physician.license_number LIKE'] = $filters['Physician.license_number'] . '%';
						unset($filters['Physician.license_number']);
					}
					
					if ($filters['Physician.unique_identification_number_blank'])
					{
						$filters['Physician.unique_identification_number'] = '';
					}
					
					if ($filters['Physician.medicaid_provider_number_blank'])
					{
						$filters['Physician.medicaid_provider_number'] = '';
					}
					
					if ($filters['Physician.national_provider_identification_number_blank'])
					{
						$filters['Physician.national_provider_identification_number'] = '';
					}
					
					if ($filters['Physician.license_number_blank'])
					{
						$filters['Physician.license_number'] = '';
					}
					
					unset($filters['Physician.unique_identification_number_blank']);
					unset($filters['Physician.medicaid_provider_number_blank']);
					unset($filters['Physician.national_provider_identification_number_blank']);
					unset($filters['Physician.license_number_blank']);
					
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
				
				$results = $this->Physician->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/physicians/csv_summary');
				}
			}
			
			$this->helpers[] = 'ajax';
			$this->set(compact('isUpdate'));
		}
	}
?>