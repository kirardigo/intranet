<?php
	class Statement extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'STATEMENT';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
	}
?>