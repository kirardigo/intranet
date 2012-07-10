<?php
	class CarriersController extends AppController
	{
		var $pageTitle = 'Carriers';
		var $filterName = 'carriersFilter';
		var $uses = array(
			'Carrier',
			'CarrierProviderNumber',
			'CarrierStatementType',
			'Lookup',
			'Note'
		);
		
		/**
		 * Ajax action to find a carrier.
		 * Expects $this->data['Carrier']['search'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (!isset($this->data['Carrier']['search']))
			{
				exit;
			}
			
			$value = strtoupper($this->data['Carrier']['search']);
			
			$matches = $this->Carrier->find('all', array(
				'contain' => array(),
				'fields' => array(
					'id',
					'carrier_number',
					'carrier_name_when_browsing_by_group',
					'address_1',
					'city'
				),
				'conditions' => array('carrier_number like' => $value . '%'),
				'order' => array('carrier_name_when_browsing_by_group')
			));
			
			if (count($matches) == 0)
			{
				$matches = $this->Carrier->find('all', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'carrier_number',
						'carrier_name_when_browsing_by_group',
						'address_1',
						'city'
					),
					'conditions' => array('carrier_name_when_browsing_by_group like' => $value . '%'),
					'order' => array('carrier_name_when_browsing_by_group')
				));
			}
			
			$this->set('output', array(
				'data' => $matches,
				'id_field' => 'Carrier.id',
				'id_prefix' => '',
				'value_fields' => array('Carrier.carrier_number'),
				'informal_fields' => array('Carrier.carrier_name_when_browsing_by_group', 'Carrier.address_1', 'Carrier.city'),
				'informal_format' => '| %s | %s | %s'
			));
		}
		
		/**
		 * List the carrier records
		 */
		function index()
		{
			$filterName = 'CarrierFilter';
			$postDataName = 'CarrierSummaryPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			
			$this->paginate = array(
				'contain' => array(),
				'order' => array('Carrier.carrier_number')
			);
			
			// Filter the list of carriers
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['Carrier.carrier_number']))
				{
					$filters['Carrier.carrier_number like'] = $filters['Carrier.carrier_number'] . '%';
					unset($filters['Carrier.carrier_number']);
				}
				
				if (isset($filters['Carrier.name']))
				{
					$filters['Carrier.name like'] = $filters['Carrier.name'] . '%';
					unset($filters['Carrier.name']);
				}
				
				if (isset($filters['Carrier.carrier_name_when_browsing_by_group']))
				{
					$filters['Carrier.carrier_name_when_browsing_by_group like'] = $filters['Carrier.carrier_name_when_browsing_by_group'] . '%';
					unset($filters['Carrier.carrier_name_when_browsing_by_group']);
				}
				
				if (isset($filters['Carrier.phone_number']))
				{
					$filters['Carrier.phone_number like'] = $filters['Carrier.phone_number'] . '%';
					unset($filters['Carrier.phone_number']);
				}
				
				$conditions = array_merge($conditions, $filters);
				$this->Session->write($filterName, $conditions);
			}
			else if (isset($this->params['named']['reset']))
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
			}
			
			if ($this->Session->check($filterName))
			{
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
				
				$this->paginate['conditions'] = $conditions;
			}
			
			$this->set('pagedData', $this->paginate('Carrier'));
		}
		
		/**
		 * Edit a carrier record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id)
		{
			if (isset($this->data))
			{
				if (isset($this->data['Carrier']['carrier_number']) && $this->data['Carrier']['carrier_number'] == '')
				{
					$this->data['Carrier']['carrier_number'] = $this->Carrier->getSequenceNumber($this->data['Carrier']['name']);
				}
				if (isset($this->data['Carrier']['eft_start_date']))
				{
					$this->data['Carrier']['eft_start_date'] = databaseDate($this->data['Carrier']['eft_start_date']);
				}
				if (isset($this->data['Carrier']['contract_date']))
				{
					$this->data['Carrier']['contract_date'] = databaseDate($this->data['Carrier']['contract_date']);
				}
				if (isset($this->data['Carrier']['recredentialed_date']))
				{
					$this->data['Carrier']['recredentialed_date'] = databaseDate($this->data['Carrier']['recredentialed_date']);
				}
				
				// Save the carrier and then the corresponding notes
				if ($this->Carrier->save($this->data))
				{
					$id = $this->Carrier->id;
					
					if (isset($this->data['Note']['vob']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'vob', $this->data['Note']['vob']['note']);
					}
					if (isset($this->data['Note']['auth']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'auth', $this->data['Note']['auth']['note']);
					}
					if (isset($this->data['Note']['service']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'service', $this->data['Note']['service']['note']);
					}
					if (isset($this->data['Note']['homecare']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'homecare', $this->data['Note']['homecare']['note']);
					}
					if (isset($this->data['Note']['rehab']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'rehab', $this->data['Note']['rehab']['note']);
					}
					if (isset($this->data['Note']['claims']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'claims', $this->data['Note']['claims']['note']);
					}
					if (isset($this->data['Note']['contract']))
					{
						$this->Note->saveNote($this->Carrier->generateTargetUri($id), 'contract', $this->data['Note']['contract']['note']);
					}
					
					$this->redirect("/carriers/edit/{$id}");
				}
			}
			else
			{
				$this->data = $this->Carrier->find('first', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'carrier_number',
						'name'
					),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Display the benefits tab.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function module_benefits($id)
		{
			$noteRecord = array();
			
			$this->data = $this->Carrier->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($this->data !== false)
			{
				$noteRecord = $this->Note->getNotes($this->Carrier->generateTargetUri($id));
			}
			
			$dmeOptions = $this->Lookup->get('dme_auth_required_types');
			
			$this->set(compact('id', 'dmeOptions', 'noteRecord'));
		}
		
		/**
		 * Display the department tab.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function module_department($id)
		{
			$noteRecord = array();
			
			$this->data = $this->Carrier->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($this->data !== false)
			{
				$noteRecord = $this->Note->getNotes($this->Carrier->generateTargetUri($id));
			}
			
			$guidelineTypes = $this->Lookup->get('guideline_types');
			$feeScheduleTypes = $this->Lookup->get('fee_schedule_types');
			
			$this->set(compact('id', 'guidelineTypes', 'feeScheduleTypes', 'noteRecord'));
		}
		
		/**
		 * Display the claims tab.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function module_claims($id)
		{
			$providers = array();
			$noteRecord = array();
			
			$this->data = $this->Carrier->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($this->data !== false)
			{
				formatDatesInArray($this->data['Carrier'], array('eft_start_date', 'contract_date', 'recredentialed_date', 'modified_date'));
				
				$noteRecord = $this->Note->getNotes($this->Carrier->generateTargetUri($id));
				
				$providers = $this->CarrierProviderNumber->find('all', array(
					'contain' => array(),
					'conditions' => array('carrier_number' => $this->data['Carrier']['carrier_number']),
					'order' => 'profit_center'
				));
			}
			
			$remitMethods = $this->Lookup->get('remit_methods');
			$statementTypes = $this->CarrierStatementType->getList(true, true);
			
			$this->set(compact('id', 'providers', 'remitMethods', 'statementTypes', 'noteRecord'));
		}
		
		/**
		 * Create a new record.
		 */
		function create()
		{
			if (isset($this->data))
			{
				$this->data['Carrier']['carrier_number'] = $this->Carrier->getSequenceNumber($this->data['Carrier']['name']);
				$this->data['Carrier']['is_carrier_inactive'] = 0;
				
				if ($this->Carrier->save($this->data))
				{
					$this->redirect("/carriers/edit/{$this->Carrier->id}");
				}
			}
			
			$statementTypes = $this->CarrierStatementType->getList(true, true);
			
			$this->set(compact('statementTypes'));
		}
		
		/**
		 * Lookup the group code for a statement type.
		 * @param string $statementType The statement type code to find the group code for.
		 */
		function json_getStatementTypeGroupCode($statementType)
		{
			$groupCode = $this->CarrierStatementType->field('group_code', array('type' => $statementType));
			
			$this->set('json', array('groupCode' => $groupCode));
		}
	}
?>