<?php
	class ApplicationFolder extends AppModel
	{
		var $actsAs = array('Tree');
		var $displayField = 'folder_name';
		var $order = array('ApplicationFolder.display_order', 'ApplicationFolder.folder_name');
		
		var $hasMany = array(
			'Application' => array('dependent' => true)
		);
	}
?>