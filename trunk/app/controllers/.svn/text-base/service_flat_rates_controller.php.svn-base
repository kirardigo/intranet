<?php
	class ServiceFlatRatesController extends AppController
	{
		var $pageTitle = 'Service Flat Rates';
		var $helpers = array('Ajax');
		var $uses = array('ServiceFlatRate', 'Inventory');
		
		/**
		 * Action to list all available service rates in a pageable table.
		 */ 
		function summary()
		{
			$postDataName = 'ServiceFlatRatePost';
			$filterName = 'ServiceFlatRateFilter';
			$conditions = array();
			$isExport = 0;
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Virtual.flat_rate_export']))
				{
					$isExport = $conditions['Virtual.flat_rate_export'];
					unset($conditions['Virtual.flat_rate_export']);
				}
				
				if (isset($conditions['ServiceFlatRate.hcpc_code']))
				{
					$conditions['ServiceFlatRate.hcpc_code like'] = $conditions['ServiceFlatRate.hcpc_code'] . '%';
					unset($conditions['ServiceFlatRate.hcpc_code']);
				}
				
				if (isset($conditions['ServiceFlatRate.description']))
				{
					$conditions['ServiceFlatRate.description like'] = '%' . $conditions['ServiceFlatRate.description'] . '%';
					unset($conditions['ServiceFlatRate.description']);
				}
				
				$this->Session->write($postDataName, $this->data);
				$this->Session->write($filterName, $conditions);
			}
			else if ($this->Session->check($filterName))
			{
				//if we're not on a postback but we have a saved search, filter by it
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			
			if ($isExport)
			{
				$records = $this->ServiceFlatRate->find('all', array(
					'contain' => array(),
					'conditions' => $conditions,
					'order' => 'hcpc_code'
				));
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/service_flat_rates/csv_summary');
				return;
			}
			
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'hcpc_code'
			);
			
			$this->set('records', $this->paginate('ServiceFlatRate'));
		}
		
		/**
		 * Creates or edits a service flat rate record.
		 * @param int $id The id of the record to edit. Omit to create a new record.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				if ($this->ServiceFlatRate->save($this->data) !== false)
				{
					$this->set('close', true);
				}
			}
			else
			{
				$this->data = $this->ServiceFlatRate->find('first', array('conditions' => array('id' => $id), 'contain' => array()));
			}
			
			$this->set(compact('id'));
		}
	
		function json_selectCarrierCode($id)
		{			
			$record = $this->ServiceFlatRate->find('first', array(
				'contain' => array(),
				'fields' => array(
					'description',
					'mrs_flat_rate',
					'cms_flat_rate'
				),
				'conditions' => array('id' => $id)
			));
			
			$this->set('json', $record['ServiceFlatRate']);
		}

	}
?>