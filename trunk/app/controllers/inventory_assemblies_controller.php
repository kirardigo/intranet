<?php
	class InventoryAssembliesController extends AppController
	{
		var $uses = array('Inventory', 'InventoryAssembly', 'Lookup');
		
		/**
		 * Module to allow a user to create assemblies for a given product.
		 */
		function module_products($masterProduct)
		{
			//grab any existing assembly records for the product
			$records = $this->InventoryAssembly->find('all', array(
				'conditions' => array(
					'inventory_number_master' => $masterProduct
				),
				'contain' => array(),
				'order' => 'inventory_number_item'
			));
			
			//since inventory core is FU05, we can't join it so we have to pull the fields we need from it one by one
			foreach ($records as $i => $row)
			{
				$core = $this->Inventory->find('first', array(
					'fields' => array('description', 'cost_of_goods_sold_mrs'),
					'conditions' => array('inventory_number' => $row['InventoryAssembly']['inventory_number_item']),
					'contain' => array()
				));
				
				$records[$i]['InventoryAssembly']['description'] = $core['Inventory']['description'];
				$records[$i]['InventoryAssembly']['cost_of_goods_sold_mrs'] = $core['Inventory']['cost_of_goods_sold_mrs'];
			}
			
			//grab the assembly type lookup so we can resolve the codes
			$assemblyTypes = $this->Lookup->get('assembly_types', true);
			
			$this->set(compact('masterProduct', 'records', 'assemblyTypes'));
		}
		
		/**
		 * Ajax action to render an interface to allow a user to edit an assembly record. This is part of 
		 * the "module_products" inventory assembly module.
		 * @param int $id The ID of the assembly record to edit.
		 */
		function ajax_edit($id)
		{
			$this->helpers[] = 'Ajax';
			$this->autoRenderAjax = false;
			
			$this->data = $this->InventoryAssembly->find('first', array('conditions' => array('id' => $id), 'contain' => array()));
			$this->set('assemblyTypes', $this->Lookup->get('assembly_types', true));
		}
		
		/**
		 * Adds a new assembly record.
		 */
		function json_add()
		{
			$success = false;
			$message = '';

			//prevent small circular references
			if ($this->data['InventoryAssembly']['inventory_number_master'] == $this->data['InventoryAssembly']['inventory_number_item'])
			{
				$message = 'Cannot add item to itself.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			//we can't add an item that doesn't exist
			if ($this->Inventory->field('id', array('inventory_number' => $this->data['InventoryAssembly']['inventory_number_item'])) === false)
			{
				$message = 'Inventory item does not exist.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			//we can't add an item to an assembly that's already there
			$existingID = $this->InventoryAssembly->field('id', array(
				'inventory_number_master' => $this->data['InventoryAssembly']['inventory_number_master'],
				'inventory_number_item' => $this->data['InventoryAssembly']['inventory_number_item']
			));
			
			if ($existingID !== false)
			{
				$message = 'Item already exists in assembly.';
				$this->set('json', array('success' => $success, 'message' => $message));
				return;
			}
			
			//save the assembly record
			if ($this->InventoryAssembly->save($this->data))
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
		 * Updates an existing assembly record.
		 */
		function json_edit()
		{
			$this->set('json', array('success' => !!$this->InventoryAssembly->save($this->data)));
		}
		
		/**
		 * Delete an assembly record.
		 * @param int $id The ID of the record to remove.
		 */
		function json_delete($id)
		{		
			$this->set('json', array('success' => $this->InventoryAssembly->delete($id)));	
		}
	}
?>