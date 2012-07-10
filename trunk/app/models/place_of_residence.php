<?php
	class PlaceOfResidence extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'FU05BL_POR';
		
		/**
		 * Gets a list suitable for dropdowns.
		 * @param bool $showCode Determines whether to include initials in list.
		 * @param string $orderBy The field to order by.
		 * @return array The results.
		 */
		function getList($showCode = true, $orderBy = 'display_order')
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
					$results[$row[$this->alias]['cms_code']] = ($showCode ? "{$row[$this->alias]['cms_code']} - {$row[$this->alias]['cms_description']}" : "{$row[$this->alias]['cms_description']}");
				}
			}
			
			return $results;
		}
	}
?>