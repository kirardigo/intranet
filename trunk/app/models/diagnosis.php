<?php
	class Diagnosis extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'ICD9';
		
		var $validate = array(
			'code' => array(
				'formatted' => array(
					'rule' => '_validateCode',
					'message' => 'Not formatted correctly.'
				),
				'duplicate' => array(
					'rule' => 'isUnique',
					'message' => 'This code has already been used.'
				)
			),
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description must be specified.'
				)
			)
		);
		
		/**
		 * Validate that the code matches the proper pattern.
		 */
		function _validateCode($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			return preg_match('/^[a-z0-9]{3}\.[0-9]{0,2}$/i', $value);
		}
	}
?>