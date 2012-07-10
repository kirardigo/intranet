<?php
	class MaintenanceLogAction extends AppModel
	{
		/**
		 * Get the records in a drop-down list format.
		 */
		function get()
		{
			$results = array();
			
			$records = $this->find('all', array(
				'contain' => array(),
				'order' => 'display_order'
			));
			
			foreach ($records as $row)
			{
				$results[$row[$this->alias]['description']] = $row[$this->alias]['description'];
			}
			
			return $results;
		}
	}
?>