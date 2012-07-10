<?php
	class AaaSalesman extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'AAA_SLSMN';
		
		/**
		 * Gets a list suitable for dropdowns.
		 * @param bool $showCode Determines whether to include initials in list.
		 * @param string $orderBy The field to order by.
		 * @return array The results.
		 */
		function getList($showCode = true, $orderBy = 'staff_full_name')
		{
			$results = array();
			
			$records = $this->find('all', array(
				'contain' => array(),
				'order' => $orderBy
			));
			
			if ($records !== false)
			{
				foreach ($records as $row)
				{
					$results[$row[$this->alias]['staff_initials']] = ($showCode ? "{$row[$this->alias]['staff_initials']} - {$row[$this->alias]['staff_full_name']}" : "{$row[$this->alias]['staff_full_name']}");
				}
			}
			
			return $results;
		}
	}
?>