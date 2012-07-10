<?php
	class HcpcCarriersController extends AppController
	{
		//get the models we are using
		var $uses = array(
			'Hcpc', 
			'Lookup',
			'HcpcModifier',
			'HcpcModifierAssociations',
			'HcpcIcd9Crosswalk',
			'HcpcCarrier'
		);
		
		/**
		 * Adds a new Hcpc Carrier record.
		 */
		function add($code)
		{
			//the form has data and is being submitted
			if (isset($this->data))
			{
				$this->data['HcpcCarrier']['initial_date'] = trim($this->data['HcpcCarrier']['initial_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['initial_date']);
				$this->data['HcpcCarrier']['discontinued_date'] = trim($this->data['HcpcCarrier']['discontinued_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['discontinued_date']);	
				$this->data['HcpcCarrier']['updated_date'] = trim($this->data['HcpcCarrier']['updated_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['updated_date']);	
				$this->data['HcpcCarrier']['allowable_sale'] = trim($this->data['HcpcCarrier']['allowable_sale']) == '' ? null : $this->data['HcpcCarrier']['allowable_sale'];
				$this->data['HcpcCarrier']['allowable_rent'] = trim($this->data['HcpcCarrier']['allowable_rent']) == '' ? null : $this->data['HcpcCarrier']['allowable_rent'];	
				$this->data['HcpcCarrier']['hcpc_message_reference_number'] = trim($this->data['HcpcCarrier']['hcpc_message_reference_number']) == '' ? null : $this->data['HcpcCarrier']['hcpc_message_reference_number'];	
				
				if ($this->HcpcCarrier->save($this->data))
				{
					$this->redirect('/hcpc/index');
				}
			}
			
			//get the lookups
			$initialReplacement = $this->Lookup->get('initial_replacement');
			array_unshift($initialReplacement, array('' => '-Select one-'));
			
			$rpCodes = $this->Lookup->get('rp_codes');
			array_unshift($rpCodes, array('' => '-Select one-'));
			
			$this->set(compact('code', 'initialReplacement', 'rpCodes'));
		}
		
		function json_edit($id)
		{
			//form submission
			if (isset($this->data) && isset($this->data['HcpcCarrier']))
			{
				$result = array('success' => true);
				
				//format the dates to database format
				$this->data['HcpcCarrier']['initial_date'] = trim($this->data['HcpcCarrier']['initial_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['initial_date']);
				$this->data['HcpcCarrier']['discontinued_date'] = trim($this->data['HcpcCarrier']['discontinued_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['discontinued_date']);	
				$this->data['HcpcCarrier']['updated_date'] = trim($this->data['HcpcCarrier']['updated_date']) == '' ? null : databaseDate($this->data['HcpcCarrier']['updated_date']);	
				$this->data['HcpcCarrier']['allowable_sale'] = trim($this->data['HcpcCarrier']['allowable_sale']) == '' ? null : $this->data['HcpcCarrier']['allowable_sale'];
				$this->data['HcpcCarrier']['allowable_rent'] = trim($this->data['HcpcCarrier']['allowable_rent']) == '' ? null : $this->data['HcpcCarrier']['allowable_rent'];	
				$this->data['HcpcCarrier']['hcpc_message_reference_number'] = trim($this->data['HcpcCarrier']['hcpc_message_reference_number']) == '' ? null : $this->data['HcpcCarrier']['hcpc_message_reference_number'];	
			
				$result['success'] = !!$this->HcpcCarrier->save($this->data);
				$this->set('json', $result);
			}	
		}
		
		function ajax_carriersSummary($hcpcCode)
		{
			$this->autoRenderAjax = false;
			
			$carriers = $this->HcpcCarriers->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'hcpc_code' => $hcpcCode
				)
			));
			
			$this->set('carriers', $carriers);
		}
	}
?>