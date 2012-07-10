<?php
	class HcpcMessage extends AppModel
	{
		var $validate = array(
			'reference_number' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'message' => 'The reference number must be numeric.'
				),
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This reference number is already in use.'
				)
			),
			'message' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The message is required.'
				)
			)
		);
	}
?>