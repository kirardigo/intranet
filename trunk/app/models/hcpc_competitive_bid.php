<?php
	class HcpcCompetitiveBid extends AppModel
	{
		var $validate = array(
			'bid_number' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'message' => 'The bid number must be numeric.'
				),
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This bid number is already in use.'
				)
			),
			'assigned_carrier_number' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The carrier number is required.'
				)
			)
		);
	}
?>