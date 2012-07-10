<?php
	class AaaProfitCentersController extends AppController
	{
		/**
		 * Ajax action to find a county code by name. It expects
		 * $this->data['County']['name'] to be set.
		 */
		function ajax_autoComplete()
		{
			if (trim($this->data['AaaProfitCenter']['county_name']) == '')
			{
				die();
			}
			
			$matches = $this->AaaProfitCenter->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'county_code', 'county_name'),
				'conditions' => array(
					'or' => array(
						'county_code' => $this->data['AaaProfitCenter']['county_name'],
						'county_name like' => $this->data['AaaProfitCenter']['county_name'] . '%'
					)
				),
				'order' => array('county_name')
			));
			
			$this->set('output', array(
				'data' => $matches,
				'id_field' => 'AaaProfitCenter.id',
				'id_prefix' => 'aaaProfitCenter_',
				'value_fields' => array('AaaProfitCenter.county_name'),
				'informal_fields' => array('AaaProfitCenter.county_code'),
				'informal_format' => ' (<span class="CountyCode">%s</span>)'
			));
		}
	}
?>