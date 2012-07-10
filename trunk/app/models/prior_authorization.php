<?php
	class PriorAuthorization extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'AUTH_PRIORS';
		
		var $actsAs = array(
			'FormatDates',
			'Migratable' => array('key' => 'account_number')
		);
		
		var $validate = array(
			'account_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The Account# is required.'
				)
			),
			'transaction_control_number_file' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The TCN File is required.'
				)
			),
			'transaction_control_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The TCN# is required.'
				)
			),
			'department_code' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The department is required.'
				)
			),
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description is required.'
				)
			),
			'date_requested' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The requested date is required.'
				)
			)
		);
	}
?>