<?php
	class InventoryBundlesController extends AppController
	{
		var $uses = array('InventoryBundle', 'Inventory');
		
		/**
		 * Get the products within a bundle.
		 */
		function module_products($masterProduct)
		{
			$records = $this->InventoryBundle->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number_master' => $masterProduct
				),
				'order' => 'invoicing_sequence'
			));
			
			foreach ($records as $key => $row)
			{
				$records[$key]['InventoryBundle']['description'] = $this->Inventory->field('description', array(
					'inventory_number' => $row['InventoryBundle']['inventory_number_item']
				));
			}
			
			$this->set(compact('masterProduct', 'records'));
		}
		
		/**
		 * Summary of inventory specials.
		 */
		function module_summary()
		{
			$filterName = "InventoryBundleFilter";
			$postDataName = "InventoryBundlePost";
			$isExport = 0;
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['InventoryBundle.inventory_number_master']))
				{
					$filters['InventoryBundle.inventory_number_master LIKE'] = $filters['InventoryBundle.inventory_number_master'] . '%';
					unset($filters['InventoryBundle.inventory_number_master']);
				}
				
				if (isset($filters['InventoryBundle.inventory_number_item']))
				{
					$filters['InventoryBundle.inventory_number_item LIKE'] = $filters['InventoryBundle.inventory_number_item'] . '%';
					unset($filters['InventoryBundle.inventory_number_item']);
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
			else
			{
				$filters = array();
			}
			
			$order = array('inventory_number_master', 'invoicing_sequence');
			
			if ($isExport)
			{
				$records = $this->InventoryBundle->find('all', array(
					'contain' => array(),
					'conditions' => $filters,
					'order' => $order
				));
				
				$descriptions = array();
				
				foreach ($records as $key => $row)
				{
					if (!isset($descriptions[$row['InventoryBundle']['inventory_number_item']]))
					{
						$descriptions[$row['InventoryBundle']['inventory_number_item']] = $this->Inventory->field('description', array(
							'inventory_number' => $row['InventoryBundle']['inventory_number_item']
						));
					}
					
					if (!isset($descriptions[$row['InventoryBundle']['inventory_number_master']]))
					{
						$descriptions[$row['InventoryBundle']['inventory_number_master']] = $this->Inventory->field('description', array(
							'inventory_number' => $row['InventoryBundle']['inventory_number_master']
						));
					}
				}
				
				$this->set(compact('records', 'descriptions'));
				
				$this->autoLayout = false;
				$this->render('/inventory_bundles/csv_summary');
				return;
			}
			
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $filters,
				'order' => $order
			);
			
			$records = $this->paginate('InventoryBundle');
			
			$descriptions = array();
			
			foreach ($records as $key => $row)
			{
				if (!isset($descriptions[$row['InventoryBundle']['inventory_number_item']]))
				{
					$descriptions[$row['InventoryBundle']['inventory_number_item']] = $this->Inventory->field('description', array(
						'inventory_number' => $row['InventoryBundle']['inventory_number_item']
					));
				}
				
				if (!isset($descriptions[$row['InventoryBundle']['inventory_number_master']]))
				{
					$descriptions[$row['InventoryBundle']['inventory_number_master']] = $this->Inventory->field('description', array(
						'inventory_number' => $row['InventoryBundle']['inventory_number_master']
					));
				}
			}
			
			$this->set(compact('records', 'descriptions', 'isPostback'));
		}
		
		/**
		 * Container for view.
		 */
		function management()
		{
			
		}
		
		/**
		 * Edit an inventory bundle.
		 * @param int $id The ID of the record or null for new.
		 */
		function edit($id = null)
		{
			if ($id != null)
			{
				$this->data = $this->InventoryBundle->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Add a new record.
		 */
		function json_add()
		{
			$success = false;
			$message = '';
			
			// Prevent small circular references
			if ($this->data['InventoryBundle']['inventory_number_master'] == $this->data['InventoryBundle']['inventory_number_item'])
			{
				$message = 'Cannot add item to itself.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			// Cannot add to item that doesn't exist
			$newID = $this->Inventory->field('id', array('inventory_number' => $this->data['InventoryBundle']['inventory_number_master']));
			
			if ($newID === false)
			{
				$message = 'Inventory master does not exist.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			// Cannot add item that doesn't exist
			$newID = $this->Inventory->field('id', array('inventory_number' => $this->data['InventoryBundle']['inventory_number_item']));
			
			if ($newID === false)
			{
				$message = 'Inventory item does not exist.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			// These steps only apply for truly new records.
			if (!isset($this->data['InventoryBundle']['id']) || $this->data['InventoryBundle']['id'] == '')
			{
				// Cannot add existing
				$existingID = $this->InventoryBundle->field('id', array(
					'inventory_number_master' => $this->data['InventoryBundle']['inventory_number_master'],
					'inventory_number_item' => $this->data['InventoryBundle']['inventory_number_item']
				));
				
				if ($existingID !== false)
				{
					$message = 'Item already exists in bundle.';
					$this->set('json', array('success' => $success, 'message' => $message));
					return;
				}
				
				// Lookup existing sequence numbers
				$maxSequence = $this->InventoryBundle->field('MAX(invoicing_sequence)', array(
					'inventory_number_master' => $this->data['InventoryBundle']['inventory_number_master']
				));
				
				if ($maxSequence === false)
				{
					$this->data['InventoryBundle']['invoicing_sequence'] = 1;
				}
				else
				{
					$this->data['InventoryBundle']['invoicing_sequence'] = $maxSequence + 1;
				}
			}
			
			if ($this->InventoryBundle->save($this->data))
			{
				$success = true;
			}
			else
			{
				$message = 'Could not save record.';
			}
			
			$this->set('json', array('success' => $success, 'message' => $message));
		}
		
		/**
		 * Delete a bundle record.
		 * @param int $id The ID of the record to remove.
		 */
		function json_delete($id)
		{
			$oldRecord = $this->InventoryBundle->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($oldRecord !== false && $this->InventoryBundle->delete($id))
			{
				// If record was deleted, move up all items that followed it
				$this->InventoryBundle->updateAll(
					array(
						'invoicing_sequence' => 'invoicing_sequence - 1'
					),
					array(
						'inventory_number_master' => $oldRecord['InventoryBundle']['inventory_number_master'],
						'invoicing_sequence >' => $oldRecord['InventoryBundle']['invoicing_sequence']
					)
				);
				
				$this->set('json', array('success' => true));
			}
			else
			{
				$this->set('json', array('success' => false));
			}
		}
		
		/**
		 * Move a product up in the bundle sequence.
		 * @param int $id The ID of the record to move.
		 */
		function json_moveUp($id)
		{
			$success = false;
			
			$oldRecord = $this->InventoryBundle->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			// Move the previous record down
			$result = $this->InventoryBundle->updateAll(
				array(
					'invoicing_sequence' => 'invoicing_sequence + 1'
				),
				array(
					'inventory_number_master' => $oldRecord['InventoryBundle']['inventory_number_master'],
					'invoicing_sequence' => $oldRecord['InventoryBundle']['invoicing_sequence'] - 1
				)
			);
			
			// Move the record up
			if ($result !== false)
			{
				$saveData['InventoryBundle'] = array(
					'id' => $id,
					'invoicing_sequence' => $oldRecord['InventoryBundle']['invoicing_sequence'] - 1
				);
				
				$this->InventoryBundle->create();
				if ($this->InventoryBundle->save($saveData))
				{
					$success = true;
				}
			}
			
			$this->set('json', array('success' => $success));
		}
		
		/**
		 * Move a product down in the bundle sequence.
		 * @param int $id The ID of the record to move.
		 */
		function json_moveDown($id)
		{
			$success = false;
			
			$oldRecord = $this->InventoryBundle->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			// Move the following record up
			$result = $this->InventoryBundle->updateAll(
				array(
					'invoicing_sequence' => 'invoicing_sequence - 1'
				),
				array(
					'inventory_number_master' => $oldRecord['InventoryBundle']['inventory_number_master'],
					'invoicing_sequence' => $oldRecord['InventoryBundle']['invoicing_sequence'] + 1
				)
			);
			
			// Move the record down
			if ($result !== false)
			{
				$saveData['InventoryBundle'] = array(
					'id' => $id,
					'invoicing_sequence' => $oldRecord['InventoryBundle']['invoicing_sequence'] + 1
				);
				
				$this->InventoryBundle->create();
				if ($this->InventoryBundle->save($saveData))
				{
					$success = true;
				}
			}
			
			$this->set('json', array('success' => $success));
		}
	}
?>