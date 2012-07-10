<?php
	class StaffEducationCoursesController extends AppController
	{
		var $pageTitle = 'Staff MEU Courses';
		
		var $uses = array(
			'Department',
			'Lookup',
			'NextFreeNumber',
			'StaffEducationCourse'
		);
		
		/**
		 * Display a summary of the records.
		 */
		function summary()
		{
			$filterName = 'StaffEducationCourseSummaryFilter';
			$postDataName = 'StaffEducationCourseSummaryPost';
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
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array('id desc')
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport)
			{
				$records = $this->StaffEducationCourse->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/staff_education_courses/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				//$findArray['conditions']['StaffEducationCourse.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('StaffEducationCourse');
			
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
				$this->StaffEducationCourse->set($this->data);
				
				if ($this->StaffEducationCourse->validates())
				{
					if ($this->data['StaffEducationCourse']['course_type'] == 'MIL' && $this->data['StaffEducationCourse']['meu_number'] == '')
					{
						$this->data['StaffEducationCourse']['meu_number'] = $this->NextFreeNumber->next('staff_education_course_number');
					}
					
					$this->StaffEducationCourse->save($this->data);
					$this->set('close', true);
				}
			}
			else
			{
				$this->data = $this->StaffEducationCourse->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$confirmationMethods = $this->Lookup->get('meu_confirmation_method');
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$staffCourseTypes = $this->Lookup->get('staff_education_course_types');
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'confirmationMethods', 'profitCenters', 'departments', 'staffCourseTypes'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to remove.
		 */
		function delete($id)
		{
			$this->StaffEducationCourse->delete($id);
			
			$this->redirect('summary');
		}
		
		/**
		 * Ajax action to find a education course.
		 * Expects $this->data['StaffEducationCourse']['search'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (!isset($this->data['StaffEducationCourse']['search']))
			{
				exit;
			}
			
			$value = strtoupper($this->data['StaffEducationCourse']['search']);
			
			$matches = $this->StaffEducationCourse->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'meu_number', 'title'),
				'conditions' => array('meu_number like' => $value . '%'),
				'order' => 'meu_number'
			));
			
			if (count($matches) == 0)
			{
				$matches = $this->StaffEducationCourse->find('all', array(
					'contain' => array(),
					'fields' => array('id', 'meu_number', 'title'),
					'conditions' => array('title like' => $value . '%'),
					'order' => 'meu_number'
				));
			}
			
			$this->set('output', array(
				'data' => $matches,
				'id_field' => 'StaffEducationCourse.id', 
				'id_prefix' => '',
				'value_fields' => array('StaffEducationCourse.meu_number'),
				'informal_fields' => array('StaffEducationCourse.title'),
				'informal_format' => ' - %s'
			));
		}
	}
?>