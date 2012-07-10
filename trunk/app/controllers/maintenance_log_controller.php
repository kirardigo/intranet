<?php
	class MaintenanceLogController extends AppController
	{
		var $uses = array('MaintenanceLog', 'MaintenanceLogAction', 'SerializedEquipment', 'Customer', 'Lookup');
		var $pageTitle = 'Maintenance Log';
		
		/**
		 * List and filter the records.
		 */
		function index()
		{
			$filterName = 'MaintenanceLogIndexFilter';
			$postDataName = 'MaintenanceLogIndexPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			$findArray = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'date_of_service desc'
			);
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['MaintenanceLog.comment']))
				{
					$filters['MaintenanceLog.comment'] = trim($filters['MaintenanceLog.comment']);
				}
				if (isset($filters['MaintenanceLog.maintenance_type']))
				{
					$index = 'C';
				}
				if (isset($filters['MaintenanceLog.date_of_service_start']))
				{
					$filters['MaintenanceLog.date_of_service >='] = databaseDate($filters['MaintenanceLog.date_of_service_start']);
					unset($filters['MaintenanceLog.date_of_service_start']);
					$index = 'G';
				}
				if (isset($filters['MaintenanceLog.date_of_service_end']))
				{
					$filters['MaintenanceLog.date_of_service <='] = databaseDate($filters['MaintenanceLog.date_of_service_end']);
					unset($filters['MaintenanceLog.date_of_service_end']);
					$index = 'G';
				}
				if (isset($filters['MaintenanceLog.serialized_equipment_number']))
				{
					$index = 'A';
				}
				
				$findArray['conditions'] = array_merge($conditions, $filters);
				
				if (isset($index))
				{
					$findArray['index'] = $index;
				}
				
				$this->Session->write($filterName, $findArray);
			}
			else if ($this->Session->check($filterName))
			{
				$findArray = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			else
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
			}
			
			if ($isExport)
			{
				$records = $this->MaintenanceLog->find('all', $findArray);
				$maintenanceTypes = $this->Lookup->get('maintenance_types');
				
				$this->set(compact('records', 'maintenanceTypes'));
				
				$this->autoLayout = false;
				$this->render('/maintenance_log/csv_index');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				$findArray['conditions']['MaintenanceLog.id'] = 0;
			}
			
			$this->paginate = $findArray;
			$records = $this->paginate('MaintenanceLog');
			
			$maintenanceTypes = $this->Lookup->get('maintenance_types');
			$maintenanceActions = $this->MaintenanceLogAction->get();
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			
			$this->set(compact('records', 'maintenanceTypes', 'maintenanceActions', 'profitCenters'));
		}
		
		/**
		 * Edit a record.
		 * @param int @id The ID of the record to edit or null to add.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->MaintenanceLog->set($this->data);
				
				if ($this->MaintenanceLog->validates())
				{
					$record = $this->data;
					
					unset($record['MaintenanceLog']['created']);
					unset($record['MaintenanceLog']['created_by']);
					$record['MaintenanceLog']['date_of_service'] = databaseDate($this->data['MaintenanceLog']['date_of_service']);
					
					if ($this->MaintenanceLog->save($record))
					{
						if (!isset($this->data['Virtual']['is_continue']))
						{
							$this->set('close', true);
						}
						else
						{
							unset($this->data);
						}
					}
					else
					{
						$this->set('message', 'Validate but no save.');
					}
				}
			}
			else
			{
				$this->data = $this->MaintenanceLog->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					formatDatesInArray($this->data['MaintenanceLog'], array(
						'date_of_service',
						'next_date_of_service',
						'created'
					));
					
					$this->data['Customer']['name'] = $this->Customer->field('name', array('account_number' => $this->data['MaintenanceLog']['account_number']));
					
					$mrsEquipment = $this->SerializedEquipment->find('first', array(
						'contain' => array(),
						'fields' => array('product_description', 'date_of_sale'),
						'conditions' => array('mrs_serial_number' => $this->data['MaintenanceLog']['serialized_equipment_number']),
						'index' => 'A'
					));
					
					if ($mrsEquipment !== false)
					{
						$this->data['SerializedEquipment']['product_description'] = $mrsEquipment['SerializedEquipment']['product_description'];
						$this->data['SerializedEquipment']['date_of_sale'] = formatDate($mrsEquipment['SerializedEquipment']['date_of_sale']);
					}
				}
			}
			
			if ($id == null)
			{
				$userID = $this->Session->read('user');
				$staffModel = ClassRegistry::init('Staff');
				$profitCenter = $staffModel->field('profit_center_number', array('user_id' => $userID));
				
				$defaultAccounts = array(
					'010' => 'C99010',
					'020' => 'A99020',
					'050' => 'Y99050',
					'060' => 'N99060'
				);
				
				$this->data['MaintenanceLog']['account_number'] = ifset($defaultAccounts[$profitCenter], $defaultAccounts['020']);
				$this->data['Customer']['name'] = $this->Customer->field('name', array('account_number' => $this->data['MaintenanceLog']['account_number']));
			}
			
			$maintenanceTypes = $this->Lookup->get('maintenance_types');
			$maintenanceActions = $this->MaintenanceLogAction->get();
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			
			$this->set(compact('id', 'maintenanceTypes', 'maintenanceActions', 'profitCenters'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The record to delete.
		 */
		function delete($id)
		{
			//$this->MaintenanceLog->delete($id);
			
			$this->flash('Not yet available', 'index');
		}
	}
?>