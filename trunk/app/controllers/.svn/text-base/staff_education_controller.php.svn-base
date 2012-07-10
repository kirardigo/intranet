<?php
	class StaffEducationController extends AppController
	{
		var $pageTitle = 'Staff MEU';
		
		var $uses = array(
			'StaffEducation',
			'Lookup',
			'Department',
			'StaffEducationCourse',
			'Staff'
		);
		
		/**
		 * Display a summary of the records.
		 */
		function summary()
		{
			$filterName = 'StaffEducationSummaryFilter';
			$postDataName = 'StaffEducationSummaryPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			
			$confirmationMethods = $this->Lookup->get('meu_confirmation_method');
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$departments = $this->Department->getCodeList();
			$this->set(compact('confirmationMethods', 'profitCenters', 'departments'));
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['StaffEducation.date_start']))
				{
					$filters['StaffEducation.date_completed >='] = databaseDate($filters['StaffEducation.date_start']);
					unset($filters['StaffEducation.date_start']);
				}
				if (isset($filters['StaffEducation.date_end']))
				{
					$filters['StaffEducation.date_completed <='] = databaseDate($filters['StaffEducation.date_end']);
					unset($filters['StaffEducation.date_end']);
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
			
			$findArray = array(
				'contain' => array('StaffEducationCourse'),
				'conditions' => $conditions,
				'order' => 'date_completed desc'
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport)
			{
				$records = $this->StaffEducation->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/staff_education/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				//$findArray['conditions']['StaffEducation.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('StaffEducation');
			
			foreach ($records as $key => $row)
			{
				$records[$key]['StaffEducation']['date_completed'] = formatDate($row['StaffEducation']['date_completed']);
			}
			
			$this->set(compact('records'));
		}
		
		/**
		 * Edit a record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->data['StaffEducation']['id'] = $id;
				$this->data['StaffEducation']['staff_education_course_id'] = $this->StaffEducationCourse->field('id', array('meu_number' => $this->data['StaffEducationCourse']['meu_number']));
				
				if (isset($this->data['Staff']['search']))
				{
					$this->data['StaffEducation']['username'] = strtoupper(ifset($this->data['Staff']['search']));
				}
				else if ($id != null)
				{
					$this->data['StaffEducation']['username'] = $this->StaffEducation->field('username', array('id' => $id));
				}
				
				$this->StaffEducation->set($this->data);
				
				$this->data['StaffEducation']['date_completed'] = databaseDate($this->data['StaffEducation']['date_completed']);
				$users = explode(',', str_replace(', ', ',', str_replace(';', ',', $this->data['StaffEducation']['username'])));
				
				foreach ($users as $user)
				{
					$record = $this->Staff->find('first', array(
						'contain' => array(),
						'fields' => array('profit_center_number', 'department'),
						'conditions' => array('user_id' => $user)
					));
					
					if ($record !== false)
					{
						$this->StaffEducation->create();
						$this->data['StaffEducation']['username'] = $user;
						$this->data['StaffEducation']['profit_center_number'] = $record['Staff']['profit_center_number'];
						$this->data['StaffEducation']['department_code'] = $record['Staff']['department'];
						
						$this->StaffEducation->save($this->data);
					}
				}
				
				$this->set('close', true);
			}
			else if ($id != null)
			{
				$this->data = $this->StaffEducation->find('first', array(
					'contain' => array('StaffEducationCourse'),
					'conditions' => array('StaffEducation.id' => $id)
				));
			}
			
			if (isset($this->data['StaffEducation']['date_completed']))
			{
				$this->data['StaffEducation']['date_completed'] = formatDate($this->data['StaffEducation']['date_completed']);
			}
			
			$confirmationMethods = $this->Lookup->get('meu_confirmation_method');
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'confirmationMethods', 'profitCenters', 'departments', 'courseName'));
			
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to remove.
		 */
		function delete($id)
		{
			$this->StaffEducation->delete($id);
			
			$this->redirect('summary');
		}
	}
?>