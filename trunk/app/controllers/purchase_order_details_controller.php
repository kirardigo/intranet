<?php
	class PurchaseOrderDetailsController extends AppController
	{
		var $uses = array(
			'PurchaseOrder',
			'PurchaseOrderDetail'
		);
	
		var $helpers = array('Ajax');
	
		/**
		 * Gets a list of inventory items on a purchase order
		 */
		function module_inventory_purchase_order_items($poNumber)
		{
			$records = $this->PurchaseOrderDetail->find('all', array(
				'contain' => array(),
				'conditions' => array('purchase_order_number' => $poNumber)
			));
			
			$this->set('records', $records);		
		}
		
		/**
		 * Load the item detail module
		 */
		function module_item_detail($poId = null, $id = null)
		{	
			if ($id != null)
			{
				$this->data = $this->PurchaseOrderDetail->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'id' => $id
					)	
				));
			}
			
			$poValues = $this->PurchaseOrder->find('first', array(
					'fields' => array(),
					'contain' => array(),
					'conditions' => array('id' => $poId)
			));
			
			$this->set(compact('id', 'poValues', 'poId'));
		}
		
		/**
		 * Edit or add a new purchase order item
		 */
		function edit($poId, $recordId = null)
		{
			if (isset($this->data))
			{
				//save the record
				if($this->PurchaseOrderDetail->save($this->data))
				{
					$this->redirect("/purchase_order_details/edit/{$poId}/{$this->PurchaseOrderDetail->id}");
				}
			}
			elseif ($recordId != null)
			{
				//find the record
				$this->data = $this->PurchaseOrderDetail->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'id' => $recordId
					)
				));
				
				//get the MFG UOM from Inventory
				$uom = $this->Inventory->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'manufacturer_unit_of_measure' => $this->data['PurchaseOrderDetail']['manufacturer_unit_of_measure']
					)
				));
			}
			
			$this->set(compact('poId', 'uom');			
		}	
	}
?>
