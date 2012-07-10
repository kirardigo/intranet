<?php
	class FacilityType extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'AAA_TYPES';
		
		/**
		 * Gets a list suitable for dropdowns.
		 * @param bool $showCode Determines whether to include code in list.
		 * @param string $orderBy The field to order by.
		 * @return array The results.
		 */
		function getList($showCode = true, $orderBy = 'code')
		{
			$results = false;
			
			$facilityTypes = $this->find('all', array(
				'contain' => array(),
				'order' => $orderBy
			));
			
			if ($facilityTypes !== false)
			{
				foreach ($facilityTypes as $row)
				{
					$results[$row[$this->alias]['code']] = ($showCode ? "{$row[$this->alias]['code']} - {$row[$this->alias]['description']}" : "{$row[$this->alias]['description']}");
				}
			}
			
			return $results;
		}
	}
?>