<?php
	class CountiesController extends AppController
	{
		/**
		 * Ajax action to find a county code by name. It expects
		 * $this->data['County']['name'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (trim($this->data['County']['name']) == '')
			{
				die();
			}
			
			$matches = $this->County->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'odhs_county_number', 'name'),
				'conditions' => array(
					'name like' => strtoupper($this->data['County']['name']) . '%',
					'is_active' => 1
				),
				'index' => 'C',
				'order' => array('name')
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'County.id',
				'id_prefix' => '',
				'value_fields' => array('County.name'),
				'informal_fields' => array('County.odhs_county_number'),
				'informal_format' => ' (<span class="CountyCode">%s</span>)'
			));
		}
		
		/**
		 * Get record information for a particular record.
		 * @param int $id The ID of the record.
		 */
		function json_information($id)
		{
			$record = $this->County->find('first', array(
				'contain' => array(),
				'fields' => array(
					'odhs_county_number'
				),
				'conditions' => array('id' => $id)
			));
			
			$this->set('json', array('number' => ifset($record['County']['odhs_county_number'])));
		}
	}
?>