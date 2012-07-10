<?php
	Configure::write('debug', 1);
	class MikeController extends AppController
	{
		var $autoRender = false;
		var $uses = array(
			'Customer',
			'County',
			'Staff',
			'Inventory',
			'PurchaseOrderDetail',
			'PurchaseOrder'
		);
		

		
		function getRecords($poNumber)
		{
		
			//$poNumber = '    ' . $poNumber;	
			pr($poNumber);
					
			//find the existing record
			$records = $this->PurchaseOrderDetail->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'purchase_order_number' => $poNumber
					)
			));
			
			pr($records);
			
			pr('/' . $records['PurchaseOrderDetail']['purchase_order_number'] . '/');
			
		}
	}
?>
