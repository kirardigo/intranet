<?php
	class InventorySpecialOrdersController extends AppController
	{
		var $uses = array(
			'InventorySpecialOrder',
			'Lookup',
			'Department'
		);
		var $pageTitle = 'Inventory Specials';
		
		/**
		 * Lookup special orders for a particular inventory item.
		 * @param string $inventoryNumber The inventory number to find special orders for.
		 */
		function module_inventory($inventoryNumber)
		{
			$records = $this->InventorySpecialOrder->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'mrs_inventory_number' => $inventoryNumber
				),
				'order' => 'original_purchase_order_date desc'
			));
			
			$conditions = $this->Lookup->get('item_conditions');
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('records', 'conditions', 'departments'));
		}
		
		/**
		 * Container screen.
		 */
		function management()
		{
			
		}
		
		/**
		 * Summary of inventory specials.
		 */
		function module_summary()
		{
			$filterName = "InventorySpecialFilter";
			$postDataName = "InventorySpecialPost";
			$isExport = 0;
			
			$conditions = $this->Lookup->get('item_conditions');
			$departments = $this->Department->getCodeList();
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			$this->set(compact('conditions', 'departments', 'isPostback'));
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				$filters['InventorySpecialOrder.assigned_date'] = null;
				
				if (isset($filters['InventorySpecialOrder.original_purchase_order_number']))
				{
					$filters['InventorySpecialOrder.original_purchase_order_number LIKE'] = $filters['InventorySpecialOrder.original_purchase_order_number'] . '%';
					unset($filters['InventorySpecialOrder.original_purchase_order_number']);
				}
				if (isset($filters['InventorySpecialOrder.manufacturer_inventory_number']))
				{
					$filters['InventorySpecialOrder.manufacturer_inventory_number LIKE'] = $filters['InventorySpecialOrder.manufacturer_inventory_number'] . '%';
					unset($filters['InventorySpecialOrder.manufacturer_inventory_number']);
				}
				if (isset($filters['InventorySpecialOrder.mrs_inventory_number']))
				{
					$filters['InventorySpecialOrder.mrs_inventory_number LIKE'] = $filters['InventorySpecialOrder.mrs_inventory_number'] . '%';
					unset($filters['InventorySpecialOrder.mrs_inventory_number']);
				}
				if (isset($filters['InventorySpecialOrder.po_date_start']))
				{
					$filters['InventorySpecialOrder.original_purchase_order_date >='] = databaseDate($filters['InventorySpecialOrder.po_date_start']);
					unset($filters['InventorySpecialOrder.po_date_start']);
				}
				if (isset($filters['InventorySpecialOrder.po_date_end']))
				{
					$filters['InventorySpecialOrder.original_purchase_order_date <='] = databaseDate($filters['InventorySpecialOrder.po_date_end']);
					unset($filters['InventorySpecialOrder.po_date_end']);
				}
				if (isset($filters['InventorySpecialOrder.assigned_date_start']) && $filters['InventorySpecialOrder.assigned_date_start'] != '')
				{
					$filters['InventorySpecialOrder.assigned_date >='] = databaseDate($filters['InventorySpecialOrder.assigned_date_start']);
					unset($filters['InventorySpecialOrder.assigned_date_start']);
					unset($filters['InventorySpecialOrder.assigned_date']); // remove default filter in this case
				}
				if (isset($filters['InventorySpecialOrder.assigned_date_end']) && $filters['InventorySpecialOrder.assigned_date_end'] != '')
				{
					$filters['InventorySpecialOrder.assigned_date <='] = databaseDate($filters['InventorySpecialOrder.assigned_date_end']);
					unset($filters['InventorySpecialOrder.assigned_date_end']);
					unset($filters['InventorySpecialOrder.assigned_date']); // remove default filter in this case
				}
			}
			else if (isset($this->params['named']['reset']))
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
			}
			else if ($this->Session->check($filterName))
			{
				$filters = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			
			if (!isset($filters))
			{
				$filters['InventorySpecialOrder.assigned_date'] = null;
			}
			
			if ($isExport)
			{
				$records = $this->InventorySpecialOrder->find('all', array(
					'contain' => array(),
					'conditions' => $filters
				));
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/inventory_special_orders/csv_summary');
				return;
			}
			
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $filters
			);
			
			$records = $this->paginate('InventorySpecialOrder');
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('records', 'departments'));
		}
		
		/**
		 * Edit an individual record.
		 * @param int $id The ID of the record to edit.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->data['InventorySpecialOrder']['original_purchase_order_date'] = databaseDate($this->data['InventorySpecialOrder']['original_purchase_order_date']);
				$this->data['InventorySpecialOrder']['date_of_purchase'] = databaseDate($this->data['InventorySpecialOrder']['date_of_purchase']);
				$this->data['InventorySpecialOrder']['assigned_date'] = databaseDate($this->data['InventorySpecialOrder']['assigned_date']);
				
				if ($this->InventorySpecialOrder->save($this->data))
				{
					$this->set('close', true);
				}
			}
			else
			{
				$this->data = $this->InventorySpecialOrder->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					formatDatesInArray($this->data['InventorySpecialOrder'], array('original_purchase_order_date', 'assigned_date', 'date_of_purchase'));
				}
			}
			
			$conditions = $this->Lookup->get('item_conditions');
			$locatorOptions = $this->Lookup->get('inventory_locator');
			$departments = $this->Department->getCodeList();
			
			$this->set(compact('id', 'conditions', 'departments', 'locatorOptions'));
		}
		
		/**
		 * Delete a record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->InventorySpecialOrder->delete($id);
			
			$this->redirect($this->referer());
		}
		
		/**
		 * Delete a record and get result via JSON.
		 * @param int $id The ID of the record to delete.
		 */
		function json_delete($id)
		{
			$success = $this->InventorySpecialOrder->delete($id);
			$this->set('json', array('success' => $success));
		}
	}
?>