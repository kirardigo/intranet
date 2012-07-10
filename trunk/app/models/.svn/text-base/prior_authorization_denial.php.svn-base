<?php
	class PriorAuthorizationDenial extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'AUTH_PRIORS_DENIALS';
		
		var $validate = array(
			'code' => array(
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This code is already in use.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The code is required.'
				)
			),
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description is required.'
				)
			)
		);
	}
?>