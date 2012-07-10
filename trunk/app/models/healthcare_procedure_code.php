<?php
	class HealthcareProcedureCode extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'HCPC';
		
		/**
		 * Override the base class implementation to use filePro indexing where possible.
		 * @param string $fieldName The name of the field to return.
		 * @param array $conditions The conditions to find the first record for.
		 */
		function field($fieldName, $conditions)
		{
			$findArray = array(
				'contain' => array(),
				'fields' => array($fieldName),
				'conditions' => $conditions
			);
			
			// Use filePro index if possible
			if (isset($conditions['code']))
			{
				$findArray['index'] = 'A';
			}
			
			if ($data = $this->find('first', $findArray))
			{
				if (strpos($fieldName, '.') === false)
				{
					if (isset($data[$this->alias][$fieldName]))
					{
						return $data[$this->alias][$fieldName];
					}
				}
				else
				{
					$fieldName = explode('.', $fieldName);
					if (isset($data[$fieldName[0]][$fieldName[1]]))
					{
						return $data[$fieldName[0]][$fieldName[1]];
					}
				}
				if (isset($data[0]) && count($data[0]) > 0)
				{
					$fieldName = key($data[0]);
					return $data[0][$fieldName];
				}
			}
			
			return false;
		}
	}
?>