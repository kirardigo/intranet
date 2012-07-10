<?php
	class Application extends AppModel
	{
		var $order = array('Application.display_order', 'Application.name');
		
		var $belongsTo = array(
			'ApplicationFolder'
		);
	}
?>