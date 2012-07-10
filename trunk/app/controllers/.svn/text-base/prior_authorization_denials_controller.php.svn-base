<?php
	class PriorAuthorizationDenialsController extends AppController
	{
		var $pageTitle = 'Prior Authorization Denials';
		
		/**
		 * Show a summary view of the records.
		 */
		function summary()
		{			
			$filterName = 'PriorAuthDenialSummaryFilter';
			$postDataName = 'PriorAuthDenialSummaryPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['PriorAuthorizationDenial.description']))
				{
					$filters['PriorAuthorizationDenial.description like'] = $filters['PriorAuthorizationDenial.description'] . '%';
					unset($filters['PriorAuthorizationDenial.description']);
					$index = 'B';
				}
				
				if (isset($filters['PriorAuthorizationDenial.code']))
				{
					$filters['PriorAuthorizationDenial.code like'] = $filters['PriorAuthorizationDenial.code'] . '%';
					unset($filters['PriorAuthorizationDenial.code']);
					$index = 'A';
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
				'contain' => array(),
				'conditions' => $conditions
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport)
			{
				$records = $this->PriorAuthorizationDenial->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/priorAuthorizationDenials/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				//$findArray['conditions']['PriorAuthorizationDenial.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('PriorAuthorizationDenial');
			
			$this->set(compact('records'));
		}
		
		/**
		 * Edit a record.
		 * @param int $id The ID of the record or null to create a new one.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->PriorAuthorizationDenial->set($this->data);
				
				if ($this->PriorAuthorizationDenial->validates())
				{
					if (!$this->PriorAuthorizationDenial->save($this->data))
					{
						$this->set('message', 'The record failed to save.');
					}
					
					$this->set('close', true);
				}
			}
			else if ($id != null)
			{
				$this->data = $this->PriorAuthorizationDenial->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->PriorAuthorizationDenial->delete($id);
			
			$this->redirect('summary');
		}
	}
?>