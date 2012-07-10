<?php
	class StaffPalTimesController extends AppController
	{
		var $uses = array(
			'Department',
			'Lookup',
			'Staff',
			'StaffPalCode',
			'StaffPalTime'
		);
		
		var $pageTitle = 'Staff PAL';
		
		/**
		 * Show existing records based on filters.
		 */
		function summary()
		{
			$filterName = 'StaffPalTimesFilter';
			$postDataName = 'StaffPalTimesPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$departments = $this->Department->getCodeList();
			$palCodes = $this->StaffPalCode->getList();
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$staffConditions = $this->data['Staff'];
				unset($this->data['Staff']);
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['StaffPalTime.start_date']))
				{
					$filters['StaffPalTime.pal_date >='] = databaseDate($filters['StaffPalTime.start_date']);
					unset($filters['StaffPalTime.start_date']);
				}
				if (isset($filters['StaffPalTime.end_date']))
				{
					$filters['StaffPalTime.pal_date <='] = databaseDate($filters['StaffPalTime.end_date']);
					unset($filters['StaffPalTime.end_date']);
				}
				if (isset($filters['StaffPalTime.staff_user_id']))
				{
					$filters['StaffPalTime.staff_user_id'] = explode(",", str_replace(", ", ",", $filters['StaffPalTime.staff_user_id']));
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$this->Session->write($filterName, $conditions);
				$this->data['Staff'] = $staffConditions;
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
			
			if ($isExport)
			{
				$records = $this->StaffPalTime->find('all', array(
					'contain' => array('StaffPalCode'),
					'conditions' => $conditions,
					'order' => array(
						'StaffPalTime.pal_date desc',
						'StaffPalTime.staff_user_id'
					)
				));
				
				$this->_associateStaff($records, $this->data);
				
				$this->set(compact('records', 'profitCenters', 'departments', 'palCodes'));
				
				$this->autoLayout = false;
				$this->render('/staff_pal_times/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($conditions) == 0)
			{
				$conditions['StaffPalTime.id'] = 0;
			}
			
			$records = $this->StaffPalTime->find('all', array(
				'contain' => array('StaffPalCode'),
				'conditions' => $conditions,
				'order' => array(
					'StaffPalTime.pal_date desc',
					'StaffPalTime.staff_user_id'
				)
			));
			
			$this->_associateStaff($records, $this->data);
			
			$this->set(compact('records', 'profitCenters', 'departments', 'palCodes'));
			
			$this->helpers[] = 'Paginator';
		}
		
		/**
		 * Associate Staff record and perform additional filtering.
		 * @param array $records The array of records from StaffPalTime.
		 * @param array $conditions The filters from the screen.
		 */
		function _associateStaff(&$records, $conditions)
		{
			$filters = Set::filter($this->postConditions($conditions));
			
			foreach ($records as $key => $record)
			{
				$staffRecord = $this->Staff->find('first', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'department',
						'profit_center_number',
						'user_id',
						'full_name'
					),
					'conditions' => array('user_id' => $record['StaffPalTime']['staff_user_id']),
					'index' => 'F'
				));
				
				if (isset($filters['Staff.full_name']) && stripos($staffRecord['Staff']['full_name'], $filters['Staff.full_name']) === false)
				{
					unset($records[$key]);
					continue;
				}
				if (isset($filters['Staff.department']) && $staffRecord['Staff']['department'] != $filters['Staff.department'])
				{
					unset($records[$key]);
					continue;
				}
				if (isset($filters['Staff.profit_center_number']) && $staffRecord['Staff']['profit_center_number'] != $filters['Staff.profit_center_number'])
				{
					unset($records[$key]);
					continue;
				}
				
				$records[$key]['Staff'] = $staffRecord['Staff'];
				$records[$key]['StaffPalTime']['pal_date'] = formatDate($record['StaffPalTime']['pal_date']);
			}
		}
		
		/**
		 * Add or edit a record.
		 * @param mixed $id The record ID or null to create a new record.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				if (isset($this->data['StaffPalTime']['staff_id']))
				{
					$this->data['StaffPalTime']['staff_user_id'] = $this->Staff->field('user_id', array('id' => $this->data['StaffPalTime']['staff_id']));
				}
				
				$this->data['StaffPalTime']['pal_date'] = databaseDate($this->data['StaffPalTime']['pal_date']);
				$this->data['StaffPalTime']['id'] = $id;
				$this->StaffPalTime->save($this->data);
				
				$this->redirect('/staffPalTimes/summary');
			}
			
			if ($id != null)
			{
				$this->data = $this->StaffPalTime->find('first', array(
					'contain' => array('StaffPalCode'),
					'conditions' => array('StaffPalTime.id' => $id)
				));
				
				if (isset($this->data['StaffPalTime']['staff_user_id']))
				{
					$staffRecord = $this->Staff->find('first', array(
						'contain' => array(),
						'fields' => array(
							'id',
							'user_id',
							'full_name'
						),
						'conditions' => array('user_id' => $this->data['StaffPalTime']['staff_user_id']),
						'index' => 'F'
					));
					
					$this->data['Staff'] = $staffRecord['Staff'];
				}
				
				$this->data['StaffPalTime']['pal_date'] = formatDate($this->data['StaffPalTime']['pal_date']);
			}
			
			$staffPalCodes = $this->StaffPalCode->getList();
			
			$this->set(compact('id', 'staffPalCodes'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->autoRender = false;
			$this->StaffPalTime->delete($id);
			$this->redirect('/staffPalTimes/summary');
		}
	}
?>