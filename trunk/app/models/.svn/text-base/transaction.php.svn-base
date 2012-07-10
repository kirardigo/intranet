<?php
	class Transaction extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BU';
		
		var $belongsTo = array(
			'Invoice' => array(
				'foreignKey' => array('field' => 'invoice_number', 'parent_field' => 'invoice_number')
			)
		);
		
		var $actsAs = array(
			'Chainable' => array(
				'ownerModel' => 'Customer',
				'ownerField' => 'transaction_pointer',
				'sortOrder' => 'transaction_date_of_service',
				'sortDirection' => 'desc',
				'unchainedIndexes' => array('account_number')
			),
			'FormatDates',
			'Indexable',
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
	}
?>