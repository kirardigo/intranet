<?php
	class ProductRecall extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'PROD_RECALL';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
	}
?>