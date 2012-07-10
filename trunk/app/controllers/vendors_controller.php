<?php
	class VendorsController extends AppController
	{
		var $pageTitle = 'Vendor';
		
		var $uses = array('Vendor', 'Note');
		
		/**
		 * List the vendor records
		 */
		function summary()
		{
			$filterName = 'VendorSummaryFilter';
			$postDataName = 'VendorSummaryPost';
			$conditions = array();
			$records = array();
			$isExport = 0;
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['Vendor.vendor_code']))
				{
					$filters['Vendor.vendor_code like'] = $filters['Vendor.vendor_code'] . '%';
					unset($filters['Vendor.vendor_code']);
				}
				
				if (isset($filters['Vendor.name']))
				{
					$filters['Vendor.name like'] = $filters['Vendor.name'] . '%';
					unset($filters['Vendor.name']);
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$this->Session->write($filterName, $conditions);
			}
			else if ($this->Session->check($filterName))
			{
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			else
			{
				$this->Session->delete($filterName);
				$this->Session->delete($postDataName);
			}
			
			// exclude records that have been marked to not be shown
			$conditions['Vendor.picklist_sort <>'] = 'X';
			
			$findArray = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array()
			);
			
			if ($isExport)
			{
				$records = $this->Vendor->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/vendors/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 1)
			{
				$findArray['conditions']['Vendor.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('Vendor');
			
			$this->set(compact('records'));
		}
		
		/**
		 * Edit a vendor record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->Vendor->set($this->data);
				
				if ($this->Vendor->validates())
				{
					$this->data['Vendor']['price_list_date'] = databaseDate($this->data['Vendor']['price_list_date']);
					
					if ($this->Vendor->save($this->data))
					{
						$targetUri = $this->Vendor->generateTargetUri($this->Vendor->id);
						$this->Note->saveNote($targetUri, 'general', $this->data['Note']['general']['note']);
						$this->Note->saveNote($targetUri, 'ordering', $this->data['Note']['ordering']['note']);
						$this->Note->saveNote($targetUri, 'shipping', $this->data['Note']['shipping']['note']);
						$this->Note->saveNote($targetUri, 'discount', $this->data['Note']['discount']['note']);
						$this->Note->saveNote($targetUri, 'salesman', $this->data['Note']['salesman']['note']);
						$this->Note->saveNote($targetUri, 'purchasing', $this->data['Note']['purchasing']['note']);
						
						$this->set('close', true);
					}
				}
			}
			else
			{
				$this->data = $this->Vendor->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($id != null)
				{
					$this->data['Vendor']['price_list_date'] = formatDate($this->data['Vendor']['price_list_date']);
					$this->data['Note'] = $this->Note->getNotes($this->Vendor->generateTargetUri($id));
				}
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Gets info for the purchase order view
		 */
		function json_poDetails($vendorNumber)
		{
			$record = $this->Vendor->find('first', array(
				'contain' => array(),
				'fields' => array(
					'name',
					'phone_number'
				),
				'conditions' => array('vendor_code' => $vendorNumber)
			));
			
			$success = true;
			if (!$record)
			{
				$success = false;
			}
			
			$this->set('json', array('success' => ($success !== false), 'vendor' => $record['Vendor']));
		}
		
		/**
		 * Delete a vendor record.
		 * @param int $id The ID of the record to delete.
		 * @param int $page The page number of the index to return to.
		 */
		function delete($id, $page = 1)
		{
			$this->Vendor->delete($id);
			$this->redirect("index/page:{$page}");
		}
	}
?>