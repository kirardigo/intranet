<?php
	Configure::write('debug', 1);
	class ManufacturerFormCodesController extends AppController
	{
		var $pageTitle = 'MFG Header Codes';
		
		/**
		 * Container screen.
		 */
		function management()
		{
			
		}
		
		/**
		 * Display summary of manufacturer form codes.
		 */
		function module_summary()
		{
			$postDataName = 'ManufacturerFormCodesPost';
			$filterName = 'ManufacturerFormCodesFilter';
			$conditions = array();
			$isExport = 0;
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Virtual.is_export']))
				{
					$isExport = $conditions['Virtual.is_export'];
					unset($conditions['Virtual.is_export']);
				}
				
				if (isset($conditions['ManufacturerFormCode.sequence_description']))
				{
					$conditions['ManufacturerFormCode.sequence_description like'] = '%' . $conditions['ManufacturerFormCode.sequence_description'] . '%';
					unset($conditions['ManufacturerFormCode.sequence_description']);
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
				$records = $this->ManufacturerFormCode->find('all', array(
					'contain' => array(),
					'conditions' => $conditions,
					'order' => array('form_code', 'sequence_number')
				));
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/manufacturer_form_codes/csv_summary');
				return;
			}
			
			//set up the pagination
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array('form_code', 'sequence_number')
			);
			
			$this->set('records', $this->paginate('ManufacturerFormCode'));
			$this->set(compact('isPostback'));
		}
		
		/**
		 * Edit an manufacturer form code record.
		 * @param int $id The ID of the manufacturer form code record.
		 */
		function edit($id = null)
		{
			if(!empty($this->data))
			{
				if ($this->ManufacturerFormCode->save($this->data))
				{
					$this->set('close', true);
					$id = $this->ManufacturerFormCode->id;
				}
			}			
			elseif ($id != null)
			{
				$this->data = $this->ManufacturerFormCode->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'id' => $id
					)
				));
			}
			
			//return the id to the view
			$this->set('id', $id);	
		}
	}
?>