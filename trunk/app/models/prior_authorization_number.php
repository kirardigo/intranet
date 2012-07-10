<?php
	class PriorAuthorizationNumber extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'INFO_AUTH_NUM';
		
		var $actsAs = array(
			'Incrementable' => array(
				'fields' => array(
					'authorization_number' => array('returnIncremented' => true)
				)
			)
		);
	}
?>