<?php
	class Purchase extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BZ';
		
		var $belongsTo = array(
			'Inventory' => array(
				'foreignKey' => array('field' => 'inventory_number', 'parent_field' => 'inventory_number')
			)
		);
		
		var $actsAs = array(
			'Chainable' => array(
				'ownerModel' => 'Customer',
				'ownerField' => 'purchase_pointer',
				'unchainedIndexes' => array('account_number')
			),
			'FormatDates',
			'Indexable',
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
	}
?>