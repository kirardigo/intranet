<?php
	class GeneralLedgerController extends AppController
	{
		var $pageTitle = 'General Ledger';
		var $filterName = 'generalLedgersFilter';
		
		/**
		 * List the general ledger records
		 */
		function index()
		{
			$filterName = 'GeneralLedgerSummaryFilter';
			$postDataName = 'GeneralLedgerSummaryPost';
			$conditions = array();
			$isExport = 0;
			$records = array();
			$rentPurchase = array('R' => 'Rental', 'P' => 'Purchase');
			
			if (isset($this->data))
			{
				$this->Session->write($postDataName, $this->data);
				
				if (isset($this->data['Virtual']['is_export']))
				{
					$isExport = $this->data['Virtual']['is_export'];
					unset($this->data['Virtual']['is_export']);
				}
				
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['GeneralLedger.general_ledger_code']))
				{
					$filters['GeneralLedger.general_ledger_code like'] = $filters['GeneralLedger.general_ledger_code'] . '%';
					unset($filters['GeneralLedger.general_ledger_code']);
				}
				
				if (isset($filters['GeneralLedger.description']))
				{
					$filters['GeneralLedger.description like'] = $filters['GeneralLedger.description'] . '%';
					unset($filters['GeneralLedger.description']);
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
				'order' => array('GeneralLedger.general_ledger_code')
			);
			
			if ($isExport)
			{
				$records = $this->GeneralLedger->find('all', $findArray);
				
				$this->set(compact('records', 'rentPurchase'));
				
				$this->autoLayout = false;
				$this->render('/general_ledger/csv_summary');
				return;
			}
			
			// Don't show records when there are no conditions
			if (count($findArray['conditions']) == 0)
			{
				//$findArray['conditions']['GeneralLedger.id'] = 0;
			}
			
			$this->paginate = $findArray;
			
			$records = $this->paginate('GeneralLedger');
			
			$this->set(compact('records', 'rentPurchase'));
		}
		
		/**
		 * Edit a general ledger record.
		 * @param int $id The ID of the record to edit or null to create a new one.
		 */
		function edit($id = null)
		{
			$rentPurchase = array('R' => 'Rental', 'P' => 'Purchase');
			
			if (isset($this->data))
			{
				$this->GeneralLedger->set($this->data);
				
				if ($this->GeneralLedger->validates())
				{
					if (!$this->GeneralLedger->save($this->data))
					{
						$this->set('message', 'The record failed to save.');
					}
					
					$this->set('close', true);
				}
			}
			else
			{
				$this->data = $this->GeneralLedger->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id', 'rentPurchase'));
		}
		
		/**
		 * Delete a general ledger record.
		 * @param int $id The ID of the record to delete.
		 * @param int $page The page number of the index to return to.
		 */
		function delete($id, $page = 1)
		{
			$this->GeneralLedger->delete($id);
			$this->redirect("index/page:{$page}");
		}
		
		/**
		 * Tries to determine the GL code for a transaction against a particular invoice.
		 *
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		invoiceNumber The invoice number to try and determine the GL code for.
		 * 		carrierNumber The carrier number that is responsible for the transaction type.
		 * 		transactionType The transaction type code of the transaction to determine the GL code for.
		 * 
		 * The returned JSON will contain the following:
		 * 		code The GL code.
		 * 		description The GL code description. 
		 * 
		 * If the code couldn't be determined, both values will be null.
		 */
		function json_determineCode()
		{
		
			$code = $this->GeneralLedger->determineGLCodeForInvoice($this->params['form']['invoiceNumber'], $this->params['form']['carrierNumber'], $this->params['form']['transactionType']);
			$description = null;
			
			if ($code != null)
			{
				$description = trim($this->GeneralLedger->field('description', array('general_ledger_code' => $code)));
			}
			
			$this->set('json', compact('code', 'description'));
		}
		
		/**
		 * Verifies that a given GL code is valid. Expects $this->params['form'] to contain a 'code' variable.
		 * The JSON will contain an "exists" key that states whether or not the code is valid.
		 */
		function json_verify()
		{
			$id = $this->GeneralLedger->field('id', array('general_ledger_code' => $this->params['form']['code']));
			$this->set('json', array('exists' => $id !== false));
		}
		
		/**
		 * Looks up the description of a given GL code. Expects $this->params['form'] to contain a 'code' variable.
		 * The JSON will contain a "description" key that contains the description, or a blank string if the code isn't found.
		 */
		function json_description()
		{
			$description = $this->GeneralLedger->field('description', array('general_ledger_code' => $this->params['form']['code']));
			$this->set('json', array('description' => $description !== false ? trim($description) : ''));
		}

		/**
		 * AJAX action to get a GL description.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		general_ledger_code The code to find the description for.
		 */
		function ajax_description()
		{
			$match = $this->GeneralLedger->field('description', array('general_ledger_code' => $this->params['form']['general_ledger_code']));
			$this->set('output', $match !== false ? trim($match) : '');
		}
	}
?>