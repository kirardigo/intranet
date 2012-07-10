<?php
	class CollectionLetter extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'Collection_LTRS';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
	}
?>