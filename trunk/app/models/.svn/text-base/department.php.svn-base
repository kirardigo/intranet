<?php
	class Department extends AppModel
	{
		/**
		 * Get a list based on abbreviation rather than id.
		 * @return array
		 */
		function getCodeList()
		{
			$final = array();
			
			$results = $this->find('all', array(
				'contain' => array(),
				'order' => array(
					'name'
				)
			));
			
			if ($results !== false)
			{
				foreach ($results as $row)
				{
					$final[$row['Department']['abbreviation']] = $row['Department']['name'];
				}
			}
			
			return $final;
		}
	}
?>