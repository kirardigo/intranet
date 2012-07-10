<?php
	class FacilityTypesController extends AppController
	{
		/**
		 * Lookup the description for a code.
		 * Assumes that data[FacilityType][code] will be set.
		 */
		function ajax_name()
		{
			if (isset($this->data))
			{
				$record = $this->FacilityType->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'code' => $this->data['FacilityType']['code']
					)
				));
				
				$this->set('output', ifset($record['FacilityType']['description']));
			}
		}
	}
?>