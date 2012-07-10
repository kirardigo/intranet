<?php
	class Lookup extends AppModel
	{
		var $hasMany = array('LookupValue' => array('order' => 'id'));
		
		/**
		 * Return the list of "medical" profit centers.
		 */
		function getMedicalProfitCenters()
		{
			return array('010', '020', '050', '060');
		}
		
		/**
		 * Get the values for a particular lookup name.
		 * @param string $lookupName The name of the lookup.
		 * @param bool $showCodes Determines whether or not to show the codes in the list.
		 * @param bool $sortByCodes Determines whether to sort by code instead of name.
		 * @param bool $codesOnly Determines whether or not to only show the codes in the list. Overrides
		 * the $showCodes parameter.
		 */
		function get($lookupName, $showCodes = false, $sortByCodes = false, $codesOnly = false)
		{
			$result = false;
			
			$valueSort = ($sortByCodes) ? 'code' : array('display_order', 'description');
			
			$data = $this->find('first', array(
				'contain' => array(
					'LookupValue' => array(
						'order' => $valueSort
					)
				),
				'conditions' => array(
					'name' => $lookupName
				)
			));
			
			if (isset($data['LookupValue']))
			{
				foreach ($data['LookupValue'] as $key => $row)
				{
					$result[$row['code']] = $codesOnly ? $row['code'] : ($showCodes ? "{$row['code']} - {$row['description']}" : $row['description']);
				}
			}
			
			return $result;
		}
		
		/**
		 * Looks up the description of a particular code for a given lookup.
		 * @param string $lookupName The name of the lookup to use.
		 * @param string $code The code to get the description for.
		 * @return string The description associated with the code, or false if it couldn't be found.
		 */
		function description($lookupName, $code)
		{
			$data = $this->find('first', array(
				'conditions' => array('Lookup.name' => $lookupName),
				'contain' => array('LookupValue' => array('fields' => array('description'), 'conditions' => array('code' => $code)))
			));
			
			return ifset($data['LookupValue'][0]['description'], false);
		}
	}
?>