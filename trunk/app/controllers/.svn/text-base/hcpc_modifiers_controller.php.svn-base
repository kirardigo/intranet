<?php
	class HcpcModifiersController extends AppController
	{
		//set the page title
		var $pageTitle = "Hcpc Modifiers";	
		
		//get the models we are using
		var $uses = array(
			'HcpcModifier',
			'HcpcModifierAssociation',
			'Lookup'
		);
		
		/**
		 * Displays all the hcpc records. The default action.
		 */
		function summary()
		{
			$postDataName = 'HcpcModifiersPost';
			$filterName = 'HcpcModifiersFilter';
			$conditions = array();

			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['HcpcModifier.effective_date']))
				{
					$conditions['HcpcModifier.effective_date >='] = databaseDate($conditions['HcpcModifier.effective_date']);
					unset($conditions['HcpcModifier.effective_date']);
				}
				if (isset($conditions['HcpcModifier.termination_date']))
				{
					$conditions['HcpcModifier.termination_date <='] = databaseDate($conditions['HcpcModifier.termination_date']);
					unset($conditions['HcpcModifier.termination_date']);
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
				'order' => 'modifier'
			);
			
			$this->set('records', $this->paginate('HcpcModifier'));
		}
		
/*		function add()
		{
			//the form has data and is being submitted
			if (isset($this->data))
			{	
				//format the date values
				$this->data['HcpcModifier']['effective_date'] = databaseDate($this->data['HcpcModifier']['effective_date']);
				$this->data['HcpcModifier']['termination_date'] = databaseDate($this->data['HcpcModifier']['effective_date']);
			
				if ($this->HcpcModifier->save($this->data))
				{
					$this->redirect('/hcpcModifiers/summary');
				}
			}
		
			//get the 6 point classification lookups
			$SixPointClassification = $this->Lookup->get('6_point_classification');
			$this->set('SixPointClassification', $SixPointClassification);
		}*/
		
		/**
		* Adds or edits an hcpc modifier record
		**/
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				//format the date fields for the database
				$this->data['HcpcModifier']['effective_date'] = trim($this->data['HcpcModifier']['effective_date']) == '' ? null : databaseDate($this->data['HcpcModifier']['effective_date']);
				$this->data['HcpcModifier']['termination_date'] = trim($this->data['HcpcModifier']['termination_date']) == '' ? null : databaseDate($this->data['HcpcModifier']['termination_date']);
				
				if ($this->HcpcModifier->save($this->data) !== false)
				{
					$this->set('close', true);
				}
			}
			else if ($id != null)
			{
				$this->data = $this->HcpcModifier->find('first', array('conditions' => array('id' => $id)));
				
				//set the display format of the date fields
				$this->data['HcpcModifier']['effective_date'] = formatDate($this->data['HcpcModifier']['effective_date']);
				$this->data['HcpcModifier']['termination_date'] = formatDate($this->data['HcpcModifier']['termination_date']);
			}
			
			$levels = $this->Lookup->get('hcpc_modifier_levels');
			
			$this->set(compact('id', 'levels'));	
		}
			
		/**
		 * Associates the modifier and code.
		 */
		function json_associateModifier($code, $carrier, $modifier)
		{
			$saveData['HcpcModifierAssociation'] = array( 
				'hcpc_code' => $code,
				'carrier_number' => $carrier, 
				'hcpc_modifier' => $modifier
			);
			
			$success = $this->HcpcModifierAssociation->save($saveData);

			$this->set('json', array('success' => ($success !== false), 'id' => $this->HcpcModifierAssociation->id));
		}
		
		/**
		 * Removes the associated modifier.
		 */
		function json_removeAssociation($id)
		{
			$success = $this->HcpcModifierAssociation->delete($id);
			
			$this->set('json', array('success' => $success));
		}
	}
?>