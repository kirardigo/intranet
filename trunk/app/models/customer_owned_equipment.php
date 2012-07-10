<?php
	class CustomerOwnedEquipment extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'INFO_EQUIP';
		
		var $actsAs = array(
			'FormatDates',
			'Migratable' => array('key' => 'account_number')
		);
		
		var $validate = array(
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description field is required.'
				)
			),
			'model_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The model number field is required.'
				)
			)
		);
	}
?>