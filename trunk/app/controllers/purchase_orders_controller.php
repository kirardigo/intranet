<?php
	class PurchaseOrdersController extends AppController
	{
		var $uses = array(
			'PurchaseOrder',
			'PurchaseOrderDetail',
			'Lookup',
			'Vendor',
			'Inventory'
		);
		
		var $pageTitle = 'Purchase Orders';
		
		function ajax_autoCompleteTest()
		{
			pr($this->data);
			pr($this->data['PurchaseOrder']['default_accounting_code']);
			if (trim($this->data['PurchaseOrder']['default_accounting_code']) == '')
			{
				die();
			}
			
			$matches = $this->Customer->find('all', array(
				'fields' => array('id', 'account_number', 'name'),
				'conditions' => array('account_number like' => $accountCode . '%'),
				'order' => array('name'),
				'contain' => array()
			));

			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Customer.id', 
				'id_prefix' => 'customer_',
				'value_fields' => array('Customer.account_number'),
				'informal_fields' => array('Customer.name')
			));	
		}
		
		
		/**
		 * Retrieve the purchase order details for a particular customer.
		 * @param string $accountNumber The account number for the chosen customer.
		 */
		function module_forCustomer($accountNumber, $isUpdate = 0)
		{
			$timeFrame = databaseDate(date('Y-m-d', strtotime('-6 months')));
			
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->PurchaseOrderDetail->find('count', array(
					'contain' => array('PurchaseOrder'),
					'conditions' => array(
						'PurchaseOrderDetail.account_number' => $accountNumber,
						'PurchaseOrder.purchase_order_completion_date >' => $timeFrame
					)
				));
				
				return ($count > 0);
			}
			
			// Grab the purchase order details with a matching account number
			$accountDetails = $this->PurchaseOrderDetail->find('all', array(
				'contain' => array('PurchaseOrder'),
				'fields' => array(
					'purchase_order_number'
				),
				'conditions' => array(
					'PurchaseOrderDetail.account_number' => $accountNumber,
					'PurchaseOrder.purchase_order_completion_date >' => $timeFrame
				)
			));
			
			// Filter to a distinct list of purchase order numbers
			$numbers = array_unique(Set::extract('/PurchaseOrderDetail/purchase_order_number', $accountDetails));
			
			// Find all purchase order records within the last 6 months
			if (count($numbers) > 0)
			{
				$this->paginate = array(
					'contain' => array(),
					'fields' => array(
						'purchase_order_number',
						'vendor_code',
						'order_date',
						'shipping_acknowledgement_date',
						'received_acknowledgement_date',
						'ship_to_profit_center',
						'department_code'
					),
					'conditions' => array(
						'purchase_order_number' => is_array($numbers) ? $numbers : array(),
						'purchase_order_completion_date >' => $timeFrame
					),
					'order' => array('order_date desc'),
					'limit' => 20
				);
				
				$this->data = $this->paginate('PurchaseOrder');
				
				// Lookup the TCNs associated with this PO
				foreach ($this->data as $key => $row)
				{
					$transactionControlNumbers = $this->PurchaseOrderDetail->find('all', array(
						'contain' => array(),
						'fields' => array(
							'transaction_control_number',
							'transaction_control_number_file'
						),
						'conditions' => array(
							'account_number' => $accountNumber,
							'purchase_order_number' => $row['PurchaseOrder']['purchase_order_number']
						)
					));
					
					// Merge TCN & TCN Type and filter down to the unique values
					$this->data[$key]['PurchaseOrder']['transaction_control_numbers'] = array_unique(
						Set::format($transactionControlNumbers, '{0}:{1}', array(
							'{n}.PurchaseOrderDetail.transaction_control_number',
							'{n}.PurchaseOrderDetail.transaction_control_number_file'
						)
					));
				}
			}
			
			$this->set('accountNumber', $accountNumber);
			$this->set('isUpdate', $isUpdate);
		}
		
		/**
		 * Retrieves a pageable list of purchase orders.
		 */
		function summary()
		{
			$postDataName = 'PurchaseOrderPost';
			$filterName = 'PurchaseOrderFilter';
			$conditions = array();
			
			if (isset($this->data))
			{
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['PurchaseOrder.purchase_order_number']))
				{
					$conditions['PurchaseOrder.purchase_order_number'] = str_pad($conditions['PurchaseOrder.purchase_order_number'], 10, ' ', STR_PAD_LEFT);
				}
				
				if (isset($conditions['PurchaseOrder.order_date_start']))
				{
					$conditions['PurchaseOrder.order_date >='] = databaseDate($conditions['PurchaseOrder.order_date_start']);
					unset($conditions['PurchaseOrder.order_date_start']);
				}
				
				if (isset($conditions['PurchaseOrder.order_date_end']))
				{
					$conditions['PurchaseOrder.order_date <='] = databaseDate($conditions['PurchaseOrder.order_date_end']);
					unset($conditions['PurchaseOrder.order_date_end']);
				}
				
				if (isset($conditions['PurchaseOrder.vendor_code']))
				{
					$conditions['PurchaseOrder.vendor_code LIKE'] = $conditions['PurchaseOrder.vendor_code'] . '%';
					unset($conditions['PurchaseOrder.vendor_code']);
				}
				
				if (isset($conditions['PurchaseOrder']['is_open']))
				{
					$conditions['PurschaseOrder.received_acknowledgement_date'] = '';
					unset($conditions['PurchaseOrder']['is_open']);
				}
				
				$this->Session->write($postDataName, $this->data);
				$this->Session->write($filterName, $conditions);
			}
			else if ($this->Session->check($filterName))
			{
				//if we're not on a postback but we have a saved search, filter by it
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			
			// This trick allows the FU05 driver to use indexes when no conditions exist for improved performance.
			if (count($conditions) == 0)
			{
				$conditions['PurchaseOrder.purchase_order_number >='] = '';
			}
			
			//set up the pagination
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions
			);
		
			$purchaseOrders = $this->paginate('PurchaseOrder');
			$this->set(compact('purchaseOrders'));
		}
		
		/**
		 * For editing or adding a purchase order record
		 */
		function edit($id = null)
		{
			if(isset($this->data))
			{
				//gonna do some saving
				$this->PurchaseOrder->save($this->data);
				
			}
			else if ($id !== null)
			{
				//find the existing record
				$this->data = $this->PurchaseOrder->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'id' => $id		
					)
				));	
				
				//get the ship via field
				$shipVia = $this->Vendor->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'vendor_code' => $this->data['PurchaseOrder']['vendor_code']
					)
				));				
			}
			
			//get the po type lookup
			$poTypes = $this->Lookup->get('purchase_order_type');
			
			$this->set(compact('id', 'poTypes', 'shipVia'));
		}
		
		/**
		 * Retrieve the line item details for a purchase order.
		 * @param string $purchaseOrderNumber The purchase order number.
		 * @param string $accountNumber The account number to filter by, if specified.
		 */
		function ajax_purchaseOrderDetail($purchaseOrderNumber, $accountNumber = null)
		{
			$this->autoRenderAjax = false;
			
			$conditions = array(
				'purchase_order_number' => $purchaseOrderNumber
			);
			
			if ($accountNumber !== null)
			{
				$conditions['account_number'] = $accountNumber;
			}
			
			$this->data = $this->PurchaseOrderDetail->find('all', array(
				'contain' => array(),
				'fields' => array(
					'transaction_control_number',
					'transaction_control_number_file',
					'inventory_number',
					'inventory_description',
					'manufacturer_product_code',
					'quantity_ordered',
					'quantity_received',
					'quantity_back_ordered'
				),
				'conditions' => $conditions
			));
		}
	}
?>