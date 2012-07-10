<?php
	class PurchasesController extends AppController
	{
		var $uses = array(
			'Purchase',
			'Customer',
			'Lookup'
		);
		
		/**
		 * Lookup purchases for a customer.
		 * @param string $accountNumber The customer's account number.
		 * @param string $invoiceNumber Optional. Pass an invoice number to filter the purchases
		 * down to those that pertain to the specified invoice.
		 */
		function module_forCustomer($accountNumber, $invoiceNumber = null)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$pointer = $this->Customer->field('purchase_pointer', array('account_number' => $accountNumber));
				
				return ($pointer != 0);
			}
			
			if (isset($this->params['named']['sort']))
			{
				$chainSort = $this->params['named']['sort'] . ' ' . strtoupper($this->params['named']['direction']);
			}
			else
			{
				$chainSort = 'date_of_service desc';
			}
			
			$this->data = $this->Customer->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'account_number' => $accountNumber
				),
				'chains' => array(
					'Purchase' => array(
						'contain' => array(),
						'fields' => array(
							'healthcare_procedure_code',
							'inventory_number',
							'inventory_description',
							'serial_number',
							'carrier_1_code',
							'carrier_1_net_amount',
							'carrier_1_gross_amount',
							'carrier_2_code',
							'carrier_2_net_amount',
							'carrier_2_gross_amount',
							'carrier_3_code',
							'carrier_3_net_amount',
							'carrier_3_gross_amount',
							'date_of_service'
						),
						'conditions' => $invoiceNumber != null ? array('Purchase.invoice_number' => $invoiceNumber) : array(),
						'order' => $chainSort,
						'required' => false
					)
				)
			));
			
			$isUpdate = !empty($this->params['named']);
			
			// Set chain pagination parameters
			$this->helpers[] = 'paginator';
			$page = ifset($this->params['named']['page'], 1);
			$limit = 20;
			$totalCount = count($this->data['Purchase']);
			
			// Filter the data to the current page
			$start = $page * $limit - $limit;
			$end = $page * $limit - 1;
			$purchases = array();
			
			for ($i = $start; $i <= $end; $i++)
			{
				if (isset($this->data['Purchase'][$i]))
				{
					$purchases[$i] = $this->data['Purchase'][$i];
				}
			}
			
			$this->data['Purchase'] = $purchases;
			
			// Set paginator variables
			$this->params['paging']['Purchase'] = array(
				'page'		=> $page,
				'current'	=> count($this->data['Purchase']),
				'count'		=> $totalCount,
				'prevPage'	=> ($page > 1),
				'nextPage'	=> ($totalCount > ($page * $limit)),
				'pageCount'	=> ceil($totalCount / $limit),
				'defaults'	=> array(),
				'options'	=> array()
			);
			
			// Set parameters for sortable headers
			if (isset($this->params['named']['sort']))
			{
				$this->params['paging']['Purchase']['options'] = array(
					'sort' => $this->params['named']['sort'],
					'direction' => $this->params['named']['direction']
				);
			}
			
			$this->set(compact('accountNumber', 'invoiceNumber', 'isUpdate'));
		}
		
		/**
		 * Retrieve the line item details for a purchase.
		 * @param string $id The purchase ID.
		 */
		function ajax_purchaseDetail($id)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->Purchase->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			// Lookup diagnoses
			$diagnosisModel = ClassRegistry::init('Diagnosis');
			for ($i = 1; $i <= 4; $i++)
			{
				$fieldName = 'equipment_diagnosis_code_' . $i;
				
				$data = $diagnosisModel->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'number' => $this->data['Purchase'][$fieldName]
					),
					'index' => 'C'
				));
				
				$this->data['Diagnosis'][$i]['code'] = ($data !== false) ? $data['Diagnosis']['code'] : false;
				$this->data['Diagnosis'][$i]['description'] = ($data !== false) ? $data['Diagnosis']['description'] : false;
			}
			
			// If HCPC is specified, look up the description
			if (ifset($this->data['Purchase']['healthcare_procedure_code']) !== '')
			{
				$hcpcModel = ClassRegistry::init('HealthcareProcedureCode');
				$this->data['HealthcareProcedureCode']['description'] = $hcpcModel->field('description', array(
					'code' => $this->data['Purchase']['healthcare_procedure_code']
				));
			}
			
			// If physician is specified, look up the record
			if (ifset($this->data['Purchase']['physician_equipment_code'])  !== '')
			{
				$physicianModel = ClassRegistry::init('Physician');
				$physician = $physicianModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'name'
					),
					'conditions' => array(
						'physician_number' => $this->data['Purchase']['physician_equipment_code']
					)
				));
				
				$this->data['Physician'] = $physician['Physician'];
			}
		}
		
		function totalPurchasedForInventoryItem($inventoryNumber)
		{
			$purchaseCount = $this->Purchase->find('count', array(
				'contain' => array(),
				'conditions' => array(
					'inventory_number' => $inventoryNumber, 
					'service_to_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)'
				)
			));
		}
	}
?>