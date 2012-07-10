<?php
	class FileNoteActionCode extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'ORD_MEMO_AC';
		
		/**
		 * Get an array of action codes grouped by department code.
		 */
		function getCodeList($departmentCode = null)
		{
			$sort = $departmentCode == null ? 'description' : 'id';
			
			$actionCodes = array();
			$findArray = array(
				'contain' => array(),
				'conditions' => array(),
				'order' => $sort
			);
			
			if ($departmentCode != null)
			{
				$findArray['conditions'] = array(
					'department' => $departmentCode
				);
				$findArray['index'] = 'C';
			}
			
			$actionCodeRecords = $this->find('all', $findArray);
			
			foreach ($actionCodeRecords as $row)
			{
				if (trim($row[$this->alias]['code']) != '')
				{
					$actionCodes[$row[$this->alias]['code']] = $row[$this->alias]['description'];
				}
			}
			
			return $actionCodes;
		}
	}
?>