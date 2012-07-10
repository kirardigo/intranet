<?php
	class Vendor extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05AE';
		
		var $actsAs = array('Defraggable');
		
		var $validate = array(
			'vendor_code' => array(
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This code has already been used.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The code must be specified.'
				)
			),
			'name' => array(
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This name has already been used.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The name must be specified.'
				)
			)
		);
	}
?>