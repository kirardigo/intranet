<?php
	class InventoryController extends AppController
	{
		//get the models we are using
		var $uses = array(
			'Hcpc',
			'Inventory',
			'InventoryAssembly',
			'InventoryBundle',
			'InventoryProfitCenter',
			'InventorySpecialOrder',
			'Lookup',
			'Note',
			'Purchase',
			'PurchaseOrder',
			'PurchaseOrderDetail',
			'ServiceFlatRate'
		);
		
		var $helpers = array('Ajax', 'Permission');
		
		var $pageTitle = 'Inventory';
		
		/**
		 * Host action for the view module.
		 * @param string $id The id to view.
		 */
		function view($id)
		{
			$this->set('id', $id);
		}
		
		/**
		 * Autocompleter for inventory numbers.
		 */
		function ajax_autoComplete()
		{
			if (!isset($this->data['Inventory']['search']) || $this->data['Inventory']['search'] == '')
			{
				die();
			}
			
			$matches = $this->Inventory->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'inventory_number', 'description'),
				'conditions' => array(
					'inventory_number like' => $this->data['Inventory']['search'] . '%'
				)
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Inventory.id', 
				'value_fields' => array('Inventory.inventory_number'),
				'informal_fields' => array('Inventory.description')
			));
		}
		
		/**
		 * AJAX action to get inventory description.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		inventory_number The number to find the description for.
		 */
		function ajax_description()
		{
			$match = $this->Inventory->field('description', array('inventory_number' => $this->params['form']['inventory_number']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * Container screen.
		 */
		function management()
		{
			
		}
		
		/**
		 * Gets a list all the inventory records.
		 */
		function module_summary()
		{
			$postDataName = 'InventoryPost';
			$filterName = 'InventoryFilter';
			$isExport = 0;
			$isPicklistExport = 0;
			
			$conditions = array(
				'Inventory.is_discontinued' => false
			);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			if (!empty($this->data))
			{			
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				if (isset($this->data['Virtual']['is_picklist_export']))
				{
					$isPicklistExport = $this->data['Virtual']['is_picklist_export'];
					unset($this->data['Virtual']['is_picklist_export']);
				}
				
				if (isset($conditions['Inventory.inventory_number']))
				{
					$conditions['Inventory.inventory_number like'] = $conditions['Inventory.inventory_number'] . '%';
					unset($conditions['Inventory.inventory_number']);
				}
				
				if (isset($conditions['Inventory.description']))
				{
					$conditions['Inventory.description like'] = '%' . $conditions['Inventory.description'] . '%';
					unset($conditions['Inventory.description']);
				}
				
				if (isset($conditions['Inventory.medicare_healthcare_procedure_code']))
				{
					$conditions['Inventory.medicare_healthcare_procedure_code like'] = '%' . $conditions['Inventory.medicare_healthcare_procedure_code'] . '%';
					unset($conditions['Inventory.medicare_healthcare_procedure_code']);
				}
				
				if (isset($conditions['Inventory.profit_center_number']))
				{
					$profitCenterItems = $this->InventoryProfitCenter->find('all', array(
						'contain' => array(),
						'fields' => array('inventory_number'),
						'conditions' => array(
							'profit_center_number' => $conditions['Inventory.profit_center_number']
						)
					));
					
					$numbers = array();
					
					foreach ($profitCenterItems as $item)
					{
						$numbers[] = $item['InventoryProfitCenter']['inventory_number'];
					}
					
					$conditions['Inventory.inventory_number'] = $numbers;
					unset($conditions['Inventory.profit_center_number']);
				}
				
				// This filter only needs to be set when not displaying discontinued, otherwise show all
				if ($conditions['Inventory.show_discontinued'] == 0)
				{
					$conditions['Inventory.is_discontinued'] = false;
				}
				else
				{
					unset($conditions['Inventory.is_discontinued']);
				}
				unset($conditions['Inventory.show_discontinued']);
				
				$this->Session->write($postDataName, $this->data);
				$this->Session->write($filterName, $conditions);
			}
			else if ($this->Session->check($filterName))
			{
				//if we're not on a postback but we have a saved search, filter by it
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			else
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
			}
			
			$findArray = array(
				'contain' => array(),
				'conditions' => $conditions
			);
			
			if (isset($index))
			{
				$findArray['index'] = $index;
			}
			
			if ($isExport)
			{
				$records = $this->Inventory->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/inventory/csv_summary');
				return;
			}
			
			if ($isPicklistExport)
			{
				//get our records
				$records = $this->Inventory->find('all', $findArray);
				
				$orderQuantity = array();
				
				//get all purchase orders within the last 6 months
				$orders = $this->PurchaseOrder->find('all', array(
					'contain' => array(),
					'fields' => array('purchase_order_number'),
					'conditions' => array('order_date >' => databaseDate('-6 months'))
				));
				
				//get line items from purchase orders to add up the quantity ordered
				foreach ($orders as $order)
				{
					$details = $this->PurchaseOrderDetail->find('all', array(
						'contain' => array(),
						'fields' => array('inventory_number', 'quantity_ordered'),
						'conditions' => array(
							'purchase_order_number' => $order['PurchaseOrder']['purchase_order_number']
						)
					));
					
					foreach ($details as $detail)
					{
						if (isset($orderQuantity[$detail['PurchaseOrderDetail']['inventory_number']]))
						{
							$orderQuantity[$detail['PurchaseOrderDetail']['inventory_number']] += $detail['PurchaseOrderDetail']['quantity_ordered'];
						}
						else
						{
							$orderQuantity[$detail['PurchaseOrderDetail']['inventory_number']] = $detail['PurchaseOrderDetail']['quantity_ordered'];
						}
					}
				}
				
				foreach ($records as $key => $record)
				{
					$records[$key]['Inventory']['item_count'] = ifset($orderQuantity[$record['Inventory']['inventory_number']], 0);
					
					//get the profit center records
					$profitCenterRecords = $this->InventoryProfitCenter->find('all', array(
						'contain' => array(),
						'fields' => array('stock_level', 'profit_center_number'),
						'conditions' => array(
							'inventory_number' => $record['Inventory']['inventory_number']
						)
					));
					
					//for each record we need to get the profit center "turns"
					foreach ($profitCenterRecords as $pcRecord)
					{
						$profitCenter = $pcRecord['InventoryProfitCenter']['profit_center_number'];
						
						//get the quantity of item purchased by profit center
						$itemPurchases	= $this->Purchase->find('all', array(
							'contain' => array(),
							'fields' => array('quantity'),
							'conditions' => array(
								'inventory_number' => $record['Inventory']['inventory_number'],
								'profit_center_number' => $profitCenter,
								'date_of_service >' => databaseDate('-30 days')
							)
						));
						
						$quantityPurchased = 0;
						
						foreach ($itemPurchases as $itemPurchase)
						{
							$quantityPurchased += $itemPurchase['Purchase']['quantity'];
						}
						
						$records[$key]['Inventory']['turns'][$profitCenter] = $quantityPurchased;
						$records[$key]['Inventory']['stock_level'][$profitCenter] = $pcRecord['InventoryProfitCenter']['stock_level'];
					}
				}
				
				//set up the view records
				$this->set(compact('records'));
				
				//render details
				$this->autoLayout = false;
				$this->render('/inventory/csv_picklist');
				return;
			}
						
			//set up the pagination
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'inventory_number'
			);
			
			$records = $this->paginate('Inventory');
			
			$this->set(compact('records', 'isPostback'));
		}
		
		/**
		 * Edit an inventory record and related info.
		 * @param int $id The ID of the inventory record.
		 */
		function edit($id = null)
		{
			if ($id != null)
			{
				$this->data = $this->Inventory->find('first', array(
					'contain' => array(),
					'fields' => array(
						'inventory_number',
						'description'
					),
					'conditions' => array( 
						'id' => $id
					)
				));
			}
			
			$this->set(compact('id'));
		}
		
		function json_delete($id)
		{
			//make sure they can delete
			$this->demandPermission('Inventory.delete');
			
			//need to get the inventory number 
			$inventoryNumber = $this->Inventory->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id),
				'fields' => array('inventory_number')
			));
			
			//pr($inventoryNumber);
			//exit;
		
			//delete the inventory record
			$this->Inventory->delete($id);
			
			//delete all profit center records
			$this->InventoryProfitCenter->deleteAll(array('inventory_number' => $inventoryNumber['Inventory']['inventory_number']));
			
			//delete the bundles
			$this->InventoryBundle->deleteAll(array(
				'or' => array(
					'inventory_number_master' => $inventoryNumber['Inventory']['inventory_number'],
					'inventory_number_item' => $inventoryNumber['Inventory']['inventory_number']
				)
			));
			
			//delete the assemblies
			$this->InventoryAssembly->deleteAll(array(
				'or' => array(
					'inventory_number_master' => $inventoryNumber['Inventory']['inventory_number'],
					'inventory_number_item' => $inventoryNumber['Inventory']['inventory_number']
				)
			));
			
			//delete the specials
			$this->InventorySpecialOrder->deleteAll(array('manufacturer_inventory_number' => $inventoryNumber['Inventory']['inventory_number']));
			
			$this->set('json', array('success' => true));
		}
		
		/**
		 * Loads up the core module.
		 * @param int $id The ID of the inventory record.
		 */
		function module_inventory_core($id = null)
		{
			$noteRecord = array();
		
			if ($id != null)
			{
				//get the inventory record
				$this->data = $this->Inventory->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					formatDatesInArray($this->data['Inventory'], array(
						'replacement_or_discontinuation_date',
						'vendor_cost_date',
						'cost_of_goods_sold_update_date',
						'last_price_date'
					));
				}	
			}
			
			//get flat rate codes based on the hcpc value
			$flatRateCodes = $this->ServiceFlatRate->find('all', array(
				'fields' => array('ServiceFlatRate.id', 'ServiceFlatRate.hcpc_code', 'ServiceFlatRate.description', 'ServiceFlatRate.mrs_flat_rate'),
				'contain' => array(),
				'conditions' => array(
					'hcpc_code like ' => '%' . $this->data['Inventory']['medicare_healthcare_procedure_code'] .'%'
				)
			));
			
			//loop through codes
			$codes = array();
			
			foreach($flatRateCodes as $row)
			{
				$codes[$row['ServiceFlatRate']['id']] = $row['ServiceFlatRate']['hcpc_code'] . ' - ' . $row['ServiceFlatRate']['description'] . ' - ' . $row['ServiceFlatRate']['mrs_flat_rate'];
			} 

			//get picklist lookups
			$picklistAutomatic = $this->Lookup->get('picklist_automatic');
			
			$this->set(compact('id', 'picklistAutomatic', 'codes', 'hcpcDescription'));
		}
		
		/**
		 * Loads up the core module.
		 * @param int $id The ID of the inventory record.
		 */
		function module_inventory_view_core($id)
		{
			$this->data = $this->Inventory->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
		}	
		
		/**
		 * Save data for inventory core.
		 */
		function json_edit()
		{
			if (isset($this->data))
			{
				pr($this->data);
				$result = array('success' => true);
				
				$this->data['Inventory']['replacement_or_discontinuation_date'] = databaseDate($this->data['Inventory']['replacement_or_discontinuation_date']);
				$this->data['Inventory']['vendor_cost_date'] = databaseDate($this->data['Inventory']['vendor_cost_date']);
				$this->data['Inventory']['cost_of_goods_sold_update_date'] = databaseDate($this->data['Inventory']['cost_of_goods_sold_update_date']);
				$this->data['Inventory']['last_price_date'] = databaseDate($this->data['Inventory']['last_price_date']);
				
				$result['success'] = !!$this->Inventory->save($this->data);
				$result['id'] = $this->Inventory->id;
				
				$this->set('json', $result);
			}	
		}
		
		/**
		 * Open a popup for the copy functionality.
		 * @param int @id The ID of the record to copy.
		 */
		function module_copy($id)
		{
			$this->data = $this->Inventory->find('first', array(
				'contain' => array(),
				'fields' => array('id', 'inventory_number', 'description'),
				'conditions' => array('id' => $id)
			));
		}
		
		/**
		 * Copy an inventory item to a new item.
		 * @param int @id The ID of the record to copy.
		 */
		function json_copy($id)
		{
			//get the item by the id
			$currentItem = $this->Inventory->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			//new copied items
			$newInventoryNumber = $this->data['Inventory']['new_inventory_number'];
			$newInventoryDescription = $this->data['Inventory']['new_description'];
			
			//crete a new item
			$this->data = $this->Inventory->create();
			
			//copy the item
			$this->data = $currentItem;
			
			//set the copied values
			$this->data['Inventory']['id'] = '';
			$this->data['Inventory']['inventory_number'] = $newInventoryNumber;
			$this->data['Inventory']['description'] = $newInventoryDescription;
			
			//save the new item
			$this->Inventory->save($this->data);
			
			//find the profit center records
			$profitCenterRecords = $this->InventoryProfitCenter->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number' => $currentItem['Inventory']['inventory_number']
				)
			));
			
			//copy and create the profit center records			
			foreach ($profitCenterRecords as $i => $record)
			{
				$newProfitCenterRecord = $this->InventoryProfitCenter->create();
				
				$newProfitCenterRecord = $record;
				
				$newProfitCenterRecord['InventoryProfitCenter']['id'] = '';
				$newProfitCenterRecord['InventoryProfitCenter']['inventory_number'] = $newInventoryNumber;
				
				$this->InventoryProfitCenter->save($newProfitCenterRecord);
			}
			
			//let the view see the ID of what was inserted
			$this->set('json', array('insertedID' => $this->Inventory->id));
		}
		
		/**
		 * Used to check if an entered inventory number is valid
		 */
		function json_checkIfValidInventoryNumber($inventoryNumber)
		{
			$inventoryItemCount = $this->Inventory->find('count', array(
				'contain' => array(),
				'conditions' => array('inventory_number' => $inventoryNumber)
			));	
			
			$this->set('json', array('count' => $inventoryItemCount !== false ? $inventoryItemCount : ''));
			
			//$this->set(compact('inventoryItemCount'));
		}
	}
?>