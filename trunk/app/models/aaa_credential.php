<?php
	class AaaCredential extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'AAA_STAFF';
		
		/**
		 * Gets a list suitable for dropdowns.
		 * @param bool $showCode Determines whether to include code in list.
		 * @param string $orderBy The field to order by.
		 * @return array The results.
		 */
		function getList($showCode = true, $orderBy = 'credentials')
		{
			$results = array();
			
			$credentials = $this->find('all', array(
				'contain' => array(),
				'order' => $orderBy
			));
			
			if ($credentials !== false)
			{
				foreach ($credentials as $row)
				{
					$results[$row[$this->alias]['credentials']] = ($showCode ? "{$row[$this->alias]['credentials']} - {$row[$this->alias]['description']}" : "{$row[$this->alias]['description']}");
				}
			}
			
			return $results;
		}
	}
?>