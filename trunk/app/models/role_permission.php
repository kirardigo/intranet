<?php
	class RolePermission extends AppModel
	{
		var $belongsTo = array('Role', 'Permission');
	}
?>