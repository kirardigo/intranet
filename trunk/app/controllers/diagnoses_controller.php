<?php
	class DiagnosesController extends AppController
	{
		/**
		 * Ajax action to find a diagnosis.
		 * Expects $this->data['Diagnosis']['search'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (!isset($this->data['Diagnosis']['search']))
			{
				exit;
			}
			
			$value = strtoupper($this->data['Diagnosis']['search']);
			
			$matches = $this->Diagnosis->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'description', 'code'),
				'conditions' => array('code like' => $value . '%'),
				'index' => 'A'
			));
			
			if (count($matches) == 0)
			{
				$matches = $this->Diagnosis->find('all', array(
					'contain' => array(),
					'fields' => array('id', 'description', 'code'),
					'conditions' => array('description like' => $value . '%'),
					'order' => array('description'),
					'index' => 'B'
				));
			}
			
			$this->set('output', array(
				'data' => $matches,
				'id_field' => 'Diagnosis.id', 
				'id_prefix' => '',
				'value_fields' => array('Diagnosis.description'),
				'informal_fields' => array('Diagnosis.code'),
				'informal_format' => '| %s'
			));
		}
		
		/**
		 * Get information about the diagnosis via JSON by the ID.
		 * @param int $id The number of the record to fetch.
		 */
		function json_information($id)
		{
			$record = $this->Diagnosis->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			$data = array(
				'code' => ifset($record['Diagnosis']['code']),
				'description' => ifset($record['Diagnosis']['description']),
				'number' => ifset($record['Diagnosis']['number'])
			);
			
			$this->set('json', $data);
		}
		
		/**
		 * Add or edit a diagnosis record.
		 * @param int $id The ID of the record or null to create.
		 */
		function edit($id = null)
		{
			$this->pageTitle = 'Diagnosis';
			
			if (isset($this->data))
			{
				$this->Diagnosis->set($this->data);
				
				if ($this->Diagnosis->validates())
				{
					unset($this->data['Diagnosis']['modified']);
					unset($this->data['Diagnosis']['modified_by']);
					
					$this->data['Diagnosis']['combination'] = $this->data['Diagnosis']['code'] . ', ' . $this->data['Diagnosis']['description'];
					$this->data['Diagnosis']['number'] = str_replace('.', '', $this->data['Diagnosis']['code']);
					
					$this->Diagnosis->save($this->data);
					$this->set('close', true);
				}
			}
			else
			{
				$this->data = $this->Diagnosis->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			if (isset($this->data['Diagnosis']['modified']))
			{
				$this->data['Diagnosis']['modified'] = formatDate($this->data['Diagnosis']['modified']);
			}
			
			$this->set('id', $id);
		}
		
		/**
		 * Show a summary view of the Diagnosis records.
		 */
		function summary()
		{
			$this->pageTitle = 'Diagnoses';
			
			$filterName = 'DiagnosisSummaryFilter';
			$postDataName = 'DiagnosisSummaryPost';
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
				
				if (isset($filters['Diagnosis.description']))
				{
					$filters['Diagnosis.description like'] = $filters['Diagnosis.description'] . '%';
					unset($filters['Diagnosis.description']);
					$index = 'B';
				}
				
				if (isset($filters['Diagnosis.code']))
				{
					$filters['Diagnosis.code like'] = $filters['Diagnosis.code'] . '%';
					unset($filters['Diagnosis.code']);
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
				'conditions' => $conditions,
				'order' => array('code')
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport)
			{
				$records = $this->Diagnosis->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/diagnoses/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				$findArray['conditions']['Diagnosis.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('Diagnosis');
			
			foreach ($records as $key => $row)
			{
				$records[$key]['Diagnosis']['modified'] = formatDate($row['Diagnosis']['modified']);
			}
			
			$this->set(compact('records'));
		}
		
		/**
		 * Delete a diagnosis record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->Diagnosis->delete($id);
			
			$this->redirect('summary');
		}
	}
?>