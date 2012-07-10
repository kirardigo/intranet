<?php
	class RoleApplicationFolder extends AppModel
	{
		var $belongsTo = array(
			'Role',
			'ApplicationFolder'
		);
	}
?>