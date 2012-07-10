<?php
	class AaaProfilesController extends AppController
	{
		var $pageTitle = 'Aaa Profiles';
	
		var $uses = array(
			'AaaProfile',
			'AaaProfileFacts',
			'AaaReferral',
			'County',
			'Department',
			'Lookup',
			'Note'
		);
		
		/**
		 * List the AAA Profile records.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'AaaProfileSummaryFilter';
			$postDataName = 'AaaProfileSummaryPost';
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
				
				$filters = Set::filter($this->postConditions(array('AaaProfile' => $this->data['AaaProfile'])));
				
				if (!is_array($filters))
				{
					$filters = array();
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$this->Session->write($filterName, $conditions);
			}
			
			if ($this->Session->check($filterName))
			{
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}

			$findArray = array(
				'contain' => array('AaaProfileFact'),
				'conditions' => $conditions,
				'order' => 'aaa_number'
			);
			
			if ($isExport)
			{
				$records = $this->AaaProfile->find('all', $findArray);
				$this->_buildFinalResultSet($records, $this->data);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/aaa_profiles/csv_summary');
				return;
			}
			
			$records = $this->AaaProfile->find('all', $findArray);
			$this->_buildFinalResultSet($records, $this->data);
			
			$profitCenters = $this->Lookup->get('profit_centers');
			
			$this->helpers[] = 'ajax';
			$this->set(compact('records', 'profitCenters', 'isUpdate'));
		}
		
		/**
		 * Perform post processing on the result set.
		 * @param array $records The array or results to process.
		 * @param array $data The posted AAA search criteria.
		 */
		function _buildFinalResultSet(&$records, &$data)
		{		
			foreach ($records as $key => $record)
			{
				$aaa = $this->AaaReferral->find('first', array(
					'contain' => array(),
					'fields' => array(
						'aaa_number',
						'facility_name',
						'county_code',
						'homecare_salesman',
						'homecare_market_code'
					),
					'conditions' => array('aaa_number' => $record['AaaProfile']['aaa_number'])
				));
				
				if ($aaa !== false)
				{
					$records[$key]['AaaReferral'] = $aaa['AaaReferral'];					
					$records[$key]['AaaReferral']['profit_center_number'] = $this->County->field('default_profit_center', array('code' => $aaa['AaaReferral']['county_code']));

					//remove non-matching records if we have a filter to apply
					if (!empty($data))
					{
						if ($data['AaaReferral']['profit_center_number'] != ''
							&& $data['AaaReferral']['profit_center_number'] != $records[$key]['AaaReferral']['profit_center_number'])
						{
							unset($records[$key]);
							continue;
						}
						
						if ($data['AaaReferral']['homecare_salesman'] != ''
							&& $data['AaaReferral']['homecare_salesman'] != $records[$key]['AaaReferral']['homecare_salesman'])
						{
							unset($records[$key]);
							continue;
						}
						
						if ($data['AaaReferral']['homecare_market_code'] != ''
							&& $data['AaaReferral']['homecare_market_code'] != $records[$key]['AaaReferral']['homecare_market_code'])
						{
							unset($records[$key]);
							continue;
						}
					}
				}
			}
		}
		
		/**
		 * Edit a AAA Profile record.
		 * @param int $id The ID of the record to edit or null to create.
		 */
		function edit($id = null)
		{
			$noteRecord = array();
			
			if (isset($this->data))
			{
				if ($this->AaaProfile->save($this->data))
				{
					$id = $this->AaaProfile->id;
					$uri = $this->AaaProfile->generateTargetUri($id);
					
					if (isset($this->data['Note']['opportunities']['note']))
					{
						$this->Note->saveNote($uri, 'opportunities', $this->data['Note']['opportunities']['note']);
					}
					
					if (isset($this->data['Note']['history']['note']))
					{
						$this->Note->saveNote($uri, 'history', $this->data['Note']['history']['note']);
					}
					
					if (isset($this->data['Note']['inservice']['note']))
					{
						$this->Note->saveNote($uri, 'inservice', $this->data['Note']['inservice']['note']);
					}
					
					if (isset($this->data['Note']['generael']['note']))
					{
						$this->Note->saveNote($uri, 'general', $this->data['Note']['general']['note']);
					}
					
					$this->set('close', true);
				}
				
				$this->data = $this->AaaProfile->find('first', array(
					'contain' => array('AaaProfileFact'),
					'conditions' => array('id' => $id)
				));
				
				$noteRecord = $this->Note->getNotes($this->AaaProfile->generateTargetUri($id));
			}
			else
			{
				$this->data = $this->AaaProfile->find('first', array(
					'contain' => array('AaaProfileFact'),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					$this->data['AaaReferral']['facility_name'] = $this->AaaReferral->field('facility_name', array('aaa_number' => $this->data['AaaProfile']['aaa_number']));
					$noteRecord = $this->Note->getNotes($this->AaaProfile->generateTargetUri($id));
				}
			}
			
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'departments', 'noteRecord'));
		}
	}
?>