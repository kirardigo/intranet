<?php
	class ExtraNarrative extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05DW';
		
		var $actsAs = array(
			'Indexable', 
			'Defraggable',
			'Migratable' => array('key' => 'account_number')
		);
	}
?>