<?php
	class LookupsController extends AppController
	{
		var $pageTitle = 'Lookups';
		
		/**
		 * Get the list of all the current lookups
		 */
		function index()
		{
			$this->paginate = array(
				'contain' => array(),
				'fields' => array('id', 'name'),
				'order' => array('name')
			);
			
			$data = $this->paginate('Lookup');			
			$this->set('lookups', $data);
		}
		
		/**
		 * Edits a lookup.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{	
				//save the lookup value
				if($this->Lookup->save($this->data))
				{
					$this->redirect('index');					
				}
			}
			else if ($id != null)
			{
				//grab the lookup if we have one
				$this->data = $this->Lookup->find('first', array(
					'conditions' => array(
						'id' => $id	
					),
					'contain' => array()
				));
			}
		}
		
		/**
		 * Deletes a lookup for the given id
		 */
		function delete($id)
		{
			//delete lookup values
			$this->Lookup->LookupValue->deleteAll(array('lookup_id' => $id));
		
			//delete lookup
			$this->Lookup->delete($id);
			
			$this->redirect('index');
		}
		
		/**
		 * Lookup the description for a lookup code.
		 * Assumes that data[Lookup][name] & data[LookupValue][code] will be set.
		 */
		function ajax_name()
		{
			if (isset($this->data))
			{
				$lookupID = $this->Lookup->field('id', array('name' => $this->data['Lookup']['name']));
				$record = $this->Lookup->LookupValue->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'lookup_id' => $lookupID,
						'code' => $this->data['LookupValue']['code']
					)
				));
				
				$this->set('output', ifset($record['LookupValue']['description']));
			}
		}
	}
?>
