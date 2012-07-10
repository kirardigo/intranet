<?php
	class EraClaimServiceLineAdjustment extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05FB_Adjustments';
		
		var $actsAs = array(
			'Indexable',
			'Migratable' => array('key' => 'mrs_account_number')
		);
	}
?>