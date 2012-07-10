<?php
	class InvoiceMemosController extends AppController
	{
		var $pageTitle = 'Invoice Memos';
		
		/**
		 * Summary report for filtered records.
		 */
		function summary()
		{
			$filterName = 'InvoiceMemoSummaryFilter';
			$postDataName = 'InvoiceMemoSummaryPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['InvoiceMemo.description']))
				{
					$filters['InvoiceMemo.description like'] = '%' . $filters['InvoiceMemo.description'] . '%';
					unset($filters['InvoiceMemo.description']);
				}
				
				if (isset($filters['InvoiceMemo.code']))
				{
					$filters['InvoiceMemo.code like'] = $filters['InvoiceMemo.code'] . '%';
					unset($filters['InvoiceMemo.code']);
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
			
			$findArray = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array('code')
			);
			
			if ($isExport)
			{
				$records = $this->InvoiceMemo->find('all', $findArray);
				
				$this->set(compact('records'));
				
				$this->autoLayout = false;
				$this->render('/invoice_memos/csv_summary');
				return;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('InvoiceMemo');
			
			$this->set(compact('records'));
		}
		
		/**
		 * Edit an existing record or create a new one.
		 * @param int $id The ID of the record to edit or null to create.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				if ($this->InvoiceMemo->save($this->data))
				{
					$this->set('close', true);
				}
			}
			else if ($id != null)
			{
				$this->data = $this->InvoiceMemo->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Delete a specified record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->InvoiceMemo->delete($id);
			
			$this->redirect($this->referer());
		}
	}
?>