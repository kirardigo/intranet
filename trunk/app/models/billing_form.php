<?php
	class BillingForm extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'FORM1500';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
	}
?>