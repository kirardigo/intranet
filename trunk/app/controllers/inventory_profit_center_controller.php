<?php
	class InventoryProfitCenterController extends AppController
	{
		//get the models we are using
		var $uses = array(
			'InventoryProfitCenter',
			'Inventory',
			'Lookup',
			'Rental',
			'Purchase',
			'InventoryYearEnd'
		);
		
		var $helpers = array('Ajax');
		
		/**
		 * Load the profit centers for a particular inventory item.
		 * @param string $inventoryNumber The inventory# to grab profit centers for.
		 */
		function module_profit_center_summary($inventoryNumber)
		{
			$records = $this->InventoryProfitCenter->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number' => $inventoryNumber
				),
				'order' => 'profit_center_number'
			));
			
			//get end of year values
			$eoyInfo = $this->InventoryYearEnd->find('first', array(
				'fields' => array(
					'current_year_020',
					'current_year_010',
					'current_year_050',
					'current_year_060'
				),
				'conditions' => array(
					'inventory_number' =>  $inventoryNumber
				),
				'index' => 'A',
				'contain' => array()
			));
			
			//for each record, we need to gather some more data
			foreach ($records as $i => $record)
			{
				//get the rental records
				$records[$i]['InventoryProfitCenter']['rental_count'] = $this->Rental->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'profit_center_number' => $record['InventoryProfitCenter']['profit_center_number'],
						'inventory_number' => $inventoryNumber,
						'returned_date <>' => null
					)
				));
				
				//get the sale records
				$records[$i]['InventoryProfitCenter']['sale_count'] = $this->Purchase->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'inventory_number' => $inventoryNumber, 
						'service_to_date >=' =>  'DATE_SUB(CURDATE(), INTERVAL 90 DAY)'
					)
				));	
				
				$profitCenterNumber = $record['InventoryProfitCenter']['profit_center_number'];
				
				if (isset($eoyInfo['InventoryYearEnd']["current_year_{$profitCenterNumber}"]))
				{
					$records[$i]['InventoryProfitCenter']['eoy'] = $eoyInfo['InventoryYearEnd']["current_year_{$profitCenterNumber}"];
				}
									
			}
					
			$this->set(compact('records'));			
		}
		
		/**
		 * Host action for the view module.
		 * @param string $code The HCPC code to view.
		 */
		function view($id)
		{
			$this->set('id', $id);
		}
		
		/*
		*  
		*/
		function ajax_profit_center_summary_view($id)
		{
			$this->autoRenderAjax = false;
		
			$records = $this->InventoryProfitCenter->find('all', array(
				'contain' => array(),
				'conditions' => array(
						
				)
			));
			
			$this->set(compact('records', $records));		
		}
		
		function ajax_profit_center_detail($id = null)
		{
			$this->autoRenderAjax = false;
				
			//get the Inventory profit center by id
			$this->data = $this->InventoryProfitCenter->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			//get the profit centers
			$profitCenters = $this->Lookup->getMedicalProfitCenters();
			
			//create an array to hold our new key, value pairs
			$editedProfitCenterRows = array();
			
			//need to changed the key value in the array for saving purposes
			foreach ($profitCenters as $row)
			{
				$editedProfitCenterRows[$row] = $row;
			}
			
			$locators = $this->Lookup->get('inventory_locator');
			
			$this->set(compact('profitCenters', 'locators', 'editedProfitCenterRows'));
		}
		
		function module_profit_center($inventoryNumber) 
		{
			//get the Inventory Profit Center data
			$this->data = $this->InventoryProfitCenter->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number' => $inventoryNumber	
				)
			));	
		}
		
		function json_profit_center_details($id)
		{
			$record = $this->InventoryProfitCenter->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			$this->set('json', array('record' => $record['InventoryProfitCenter']));
		}
		
		function json_deleteInventoryItemForProfitCenter($id)
		{
			//$success = true;
			$success = $this->InventoryProfitCenter->delete($id);
			
			$this->set('json', array('success' => $success));	
		}
		
		function json_edit($id = null)
		{
			if(isset($this->data))
			{
				//pr($this->data);
//				exit;
			
				//first test to make sure there is not an existing record
				//with the same inventory number and profit center number
				$existingRecord = $this->InventoryProfitCenter->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'inventory_number' => $this->data['InventoryProfitCenter']['inventory_number'],
						'profit_center_number' => $this->data['InventoryProfitCenter']['profit_center_number']
					)	
				));

				//if there is an existing record and the id value is null
				//the user is trying to insert a duplicate record
				if($existingRecord != 0 && $id == null)
				{
					$result = array('success' => 'duplicate');;
				
					$this->set('json', $result);				
				}
				else
				{
					if($id != null)
					{			
						$this->InventoryProfitCenter->id = $id;
					}
					
					$result = array('success' => true);
					
					$result['success'] = !!$this->InventoryProfitCenter->save($this->data);
					$this->set('json', $result);
				}					
			}
		}
	}	
?>