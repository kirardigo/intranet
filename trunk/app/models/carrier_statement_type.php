<?php
	class CarrierStatementType extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'FU05BF_STMT_TYPE';
		
		/**
		 * Get list of options suitable for use in select list.
		 */
		function getList($showType = false, $sortByType = false)
		{
			$results = array();
			
			$order = $sortByType ? 'type' : 'description';
			
			$records = $this->find('all', array(
				'contain' => array(),
				'fields' => array('type', 'description'),
				'order' => $order
			));
			
			foreach ($records as $key => $row)
			{
				$results[$row[$this->alias]['type']] = ($showType) ? "{$row[$this->alias]['type']}: {$row[$this->alias]['description']}" : $row[$this->alias]['description'];
			}
			
			return $results;
		}
	}
?>