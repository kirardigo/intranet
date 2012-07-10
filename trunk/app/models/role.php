<?php
	class Role extends AppModel
	{
		var $validate = array(
			'name' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The name is required.'
				)
			)
		);
	}
?>