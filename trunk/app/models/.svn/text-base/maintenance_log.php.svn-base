<?php
	class MaintenanceLog extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'MAINTLOG';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
		
		var $validate = array(
			'serialized_equipment_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field is required'
				)
			),
			'date_of_service' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field is required'
				)
			),
			'comment' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field is required'
				)
			)
		);
	}
?>