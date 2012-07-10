<?php
	class ClientCommunicationLogController extends AppController
	{
		var $uses = array(
			'ClientCommunicationLog',
			'ClientCommunicationLogType',
			'ClientCommunicationLogStatus'
		);
		
		/**
		 * Display the client communication log records for a given customer.
		 * @param string $accountNumber The account number of the customer.
		 */
		function module_forCustomer($accountNumber)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->ClientCommunicationLog->find('count', array(
					'contain' => array(),
					'conditions' => array('account_number' => $accountNumber)
				));
				
				return ($count > 0);
			}
			
			$isUpdate = !empty($this->params['named']);
			
			$this->paginate = array(
				'contain' => array('ClientCommunicationLogType', 'ClientCommunicationLogStatus'),
				'conditions' => array('account_number' => $accountNumber)
			);
			
			$this->data = $this->paginate('ClientCommunicationLog');
			
			$this->set(compact('accountNumber', 'isUpdate'));
		}
		
		/**
		 * Display the CCL details form.
		 * @param int $id The ID of the CCL record to edit.
		 */
		function ajax_editLog($id = null)
		{
			$this->autoRenderAjax = false;
			
			if ($id != null)
			{
				$this->data = $this->ClientCommunicationLog->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$cclTypes = $this->ClientCommunicationLogType->find('list');
			$cclStatuses = $this->ClientCommunicationLogStatus->find('list');
			
			$this->set(compact('id', 'cclTypes', 'cclStatuses'));
		}
		
		/**
		 * Post the CCL record
		 */
		function json_postLog()
		{
			$success = 'test';
			$this->set('json', compact('success'));
		}
	}
?>