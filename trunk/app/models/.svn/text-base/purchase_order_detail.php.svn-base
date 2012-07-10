<?php
	class PurchaseOrderDetail extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05CK';
		
		var $actsAs = array(
			'FormatDates',
			'Indexable',
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
		
		var $belongsTo = array(
			'PurchaseOrder' => array(
				'foreignKey' => array('field' => 'purchase_order_number', 'parent_field' => 'purchase_order_number')
			)
		);
	}
?>