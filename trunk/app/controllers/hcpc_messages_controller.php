<?php
	class HcpcMessagesController extends AppController
	{
		var $pageTitle = 'HCPC Messages';
		
		/**
		 * Displays a pageable listing of all HCPC messages.
		 */
		function summary()
		{
			$postDataName = 'HcpcMessagesPost';
			$filterName = 'HcpcMessagesFilter';
			$conditions = array();
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['HcpcMessage.message']))
				{
					$conditions['HcpcMessage.message like'] = '%' . $conditions['HcpcMessage.message'] . '%';
					unset($conditions['HcpcMessage.message']);
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
			
			//set up the pagination
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'reference_number'
			);
			
			$this->set('records', $this->paginate('HcpcMessage'));
		}
		
		/**
		 * Allows the user to create or edit HCPC messages.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				if ($this->HcpcMessage->save($this->data) !== false)
				{
					$this->set('close', true);
				}
			}
			else if ($id != null)
			{
				$this->data = $this->HcpcMessage->find('first', array('conditions' => array('id' => $id)));
			}
			
			$this->set(compact('id'));
		}
	}
?>