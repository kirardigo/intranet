<?php
	class StaffPalCode extends AppModel
	{
		/**
		 * Get a list of codes suitable for use in a dropdown.
		 */
		function getList()
		{
			$results = array();
			
			$palCodes = $this->find('all', array(
				'contain' => array(),
				'order' => 'code'
			));
			
			foreach ($palCodes as $row)
			{
				$results[$row['StaffPalCode']['id']] = $row['StaffPalCode']['code'] . ' - ' . $row['StaffPalCode']['description'];
			}
			
			return $results;
		}
	}
?>