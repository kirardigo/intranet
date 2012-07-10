<?php
	class LookupValuesController extends AppController
	{
		var $uses = array('Lookup', 'LookupValue');
		
		/**
		 * Gets the list of lookup values for the given lookup id
		 */
		function index($id = null)
		{
			$lookupValues = $this->LookupValue->find('all', array(
				'conditions' => array(
					'lookup_id' => $id
				),
				'contain' => array()
			));
			
			$this->set(compact('id', 'lookupValues'));
		}
		
		/**
		 * Edit lookup value record.
		 */
		function edit($lookupID, $id = null)
		{
			if (!empty($this->data))
			{	
				//if the id is null this is a new record so get the count so we can set the display_order
				if ($id == null)
				{	
					//get the record count
					$recordCount = $this->LookupValue->find('count', array(
						'conditions' => array('lookup_id' => $lookupID)
					));
					
					//set the display order
					$this->data['LookupValue']['display_order'] = $recordCount + 1;			
					
					//also set lookup id
					$this->data['LookupValue']['lookup_id'] = $lookupID;	
				}
				
				//save the lookup value
				if ($this->LookupValue->save($this->data))
				{
  					$this->redirect("index/{$lookupID}");		
				}
			}
			else if ($id != null)
			{
				//grab the lookup values if we have one
				$this->data = $this->LookupValue->find('first', array(
					'conditions' => array(
						'id' => $id	
					),
					'contain' => array()
				));		
			}
			
			$this->set(compact('lookupID', 'id'));
		}
		
		/**
		 * Move the item clicked up
		 */
		function moveUp($id = null)
		{
			//for the passed in item, get the current sort value
			$value = $this->LookupValue->find('first', array(
				'fields' => array('lookup_id', 'display_order'),
				'conditions' => array(
					'id' => $id
				),
				'contain' => array()
			));
			
			if ($value === false)
			{
				die("Record does not exist.");
			}
			
			//move the clicked item up by subtracting 1 from the sort order				
			$this->LookupValue->save(array('LookupValue' => array(
				'id' => $id,
				'display_order' => $value['LookupValue']['display_order'] - 1
			)));
			
			//move the record that was above the original record down one place by adding one to the display order
			$this->LookupValue->updateAll(
				array('LookupValue.display_order' => $value['LookupValue']['display_order']),
				array(
					'id <>' => $id,
					'lookup_id' => $value['LookupValue']['lookup_id'],
					'display_order' => $value['LookupValue']['display_order'] - 1
				)
			);
			
			$this->redirect("/lookupValues/index/{$value['LookupValue']['lookup_id']}");
		}
		
		/**
		 * Move the item clicked down
		 */
		function moveDown($id = null)
		{
			//for the passed in item, get the current sort value
			$value = $this->LookupValue->find('first', array(
				'fields' => array('lookup_id', 'display_order'),
				'conditions' => array(
					'id' => $id
				),
				'contain' => array()
			));
			
			if ($value === false)
			{
				die("Record does not exist.");
			}
			
			$this->LookupValue->save(array('LookupValue' => array(
				'id' => $id,
				'display_order' => $value['LookupValue']['display_order'] + 1
			)));
			
			$this->LookupValue->updateAll(
				array('LookupValue.display_order' => $value['LookupValue']['display_order']),
				array(
					'id <>' => $id,
					'lookup_id' => $value['LookupValue']['lookup_id'],
					'display_order' => $value['LookupValue']['display_order'] + 1
				)
			);
			
			$this->redirect("/lookupValues/index/{$value['LookupValue']['lookup_id']}");
		}
		
		/**
		 * Delete the lookup value
		 */
		function delete($id)
		{
			$record = $this->LookupValue->find('first', array(
				'contain' => array(),
				'fields' => array('lookup_id', 'display_order'),
				'conditions' => array('id' => $id)
			));
			
			if ($record === false)
			{
				die("Record does not exist.");
			}
			
			if ($this->LookupValue->delete($id))
			{
				//reduce the display order of any value that came after the one we deleted
				$this->LookupValue->updateAll(
					array('display_order' => 'display_order - 1'),
					array(
						'lookup_id' => $record['LookupValue']['lookup_id'],
						'display_order >' => $record['LookupValue']['display_order']
					)
				);
			}
			
			$this->redirect("/lookupValues/index/{$record['LookupValue']['lookup_id']}");
		}
	}
?>