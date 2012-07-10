<?php
	class OxygenDistributionController extends AppController
	{
		var $uses = array('OxygenDistribution', 'Lookup');
		var $pageTitle = 'Oxygen Distribution';
		
		/**
		 * List and filter the records.
		 */
		function index()
		{
			$filterName = 'OxygenDistributionIndexFilter';
			$postDataName = 'OxygenDistributionIndexPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			$findArray = array(
				'contain' => array(),
				'conditions' => array()
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
				
				if (isset($filters['OxygenDistribution.dispensed_date_start']))
				{
					$filters['OxygenDistribution.dispensed_date >='] = databaseDate($filters['OxygenDistribution.dispensed_date_start']);
					unset($filters['OxygenDistribution.dispensed_date_start']);
					$index = 'C';
				}
				if (isset($filters['OxygenDistribution.dispensed_date_end']))
				{
					$filters['OxygenDistribution.dispensed_date <='] = databaseDate($filters['OxygenDistribution.dispensed_date_end']);
					unset($filters['OxygenDistribution.dispensed_date_end']);
					$index = 'C';
				}
				if (isset($filters['OxygenDistribution.account_number']))
				{
					$index = 'B';
				}
				if (isset($filters['OxygenDistribution.lot_number']))
				{
					$index = 'D';
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
				$records = $this->OxygenDistribution->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/oxygen_distribution/csv_index');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				$findArray['conditions']['OxygenDistribution.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('OxygenDistribution');
			
			$this->set(compact('records'));
		}
		
		/**
		 * Edit a record.
		 * @param int @id The ID of the record to edit or null to add.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				pr($this->data);
				exit;
			}
			else
			{
				$this->data = $this->OxygenDistribution->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					$customerModel = ClassRegistry::init('Customer');
					$this->data['Customer']['name'] = $customerModel->field('name', array('account_number' => $this->data['OxygenDistribution']['account_number']));
					$this->data['OxygenDistribution']['dispensed_date'] = formatDate($this->data['OxygenDistribution']['dispensed_date']);
				}
			}
			
			$tankSizes = $this->Lookup->get('oxygen_distribution_tank_size');
			$this->set(compact('id', 'tankSizes'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The record to delete.
		 */
		function delete($id)
		{
			$this->flash('Not available', 'index');
		}
	}
?>