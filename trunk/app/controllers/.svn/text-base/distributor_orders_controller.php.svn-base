<?php
	class DistributorOrdersController extends AppController
	{
		var $uses = array('DistributorOrder', 'Invoice', 'Lookup');
		
		/**
		 * Container for reporting modules.
		 */
		function reporting()
		{
			$this->pageTitle = 'Distributor Management';
		}
		
		/**
		 * Generate summary report of distributor orders.
		 */
		function module_summary($isUpdate = 0)
		{
			$filterName = 'DistributorOrdersModuleSummaryFilter';
			$postDataName = 'DistributorOrdersModuleSummaryPost';
			
			$isExport = 0;
			
			// Only perform certain actions if performing a search
			if ($isUpdate)
			{
				if (isset($this->data['DistributorOrder']['is_export']))
				{
					$isExport = $this->data['DistributorOrder']['is_export'];
					unset($this->data['DistributorOrder']['is_export']);
				}
				
				$conditions = array();
				
				if (isset($this->data))
				{
					$this->Session->write($postDataName, $this->data);
					
					$filters = Set::filter($this->postConditions($this->data));
					
					switch ($filters['DistributorOrder.order_status'])
					{
						case 'pending':
							$filters['DistributorOrder.invoice_number'] = '';
							$filters['DistributorOrder.purchase_order_number !='] = 'QUOTE';
							break;
						case 'complete':
							$filters['DistributorOrder.invoice_number !='] = '';
							break;
						case 'quote':
							$filters['DistributorOrder.purchase_order_number'] = 'QUOTE';
							break;
					}
					
					unset($filters['DistributorOrder.order_status']);
					
					if (isset($filters['DistributorOrder.ship_to_zip_code']))
					{
						$filters['DistributorOrder.ship_to_zip_code like'] = $filters['DistributorOrder.ship_to_zip_code'] . '%';
						unset($filters['DistributorOrder.ship_to_zip_code']);
					}
					
					if (isset($filters['DistributorOrder.order_date_start']))
					{
						$filters['DistributorOrder.order_date >='] = databaseDate($filters['DistributorOrder.order_date_start']);
						unset($filters['DistributorOrder.order_date_start']);
					}
					
					if (isset($filters['DistributorOrder.order_date_end']))
					{
						$filters['DistributorOrder.order_date <='] = databaseDate($filters['DistributorOrder.order_date_end']);
						unset($filters['DistributorOrder.order_date_end']);
					}
					
					if (isset($filters['DistributorOrder.print_date_start']))
					{
						$filters['DistributorOrder.print_date >='] = databaseDate($filters['DistributorOrder.print_date_start']);
						unset($filters['DistributorOrder.print_date_start']);
					}
					
					if (isset($filters['DistributorOrder.print_date_end']))
					{
						$filters['DistributorOrder.print_date <='] = databaseDate($filters['DistributorOrder.print_date_end']);
						unset($filters['DistributorOrder.print_date_end']);
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
				
				$results = $this->DistributorOrder->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				if ($results !== false)
				{
					foreach ($results as $key => $row)
					{
						$dateOfService = $this->Invoice->field('date_of_service', array(
							'account_number' => $row['DistributorOrder']['account_number'],
							'invoice_number' => $row['DistributorOrder']['invoice_number']
						));
						
						$results[$key]['DistributorOrder']['date_of_service'] = $dateOfService;
						
						if ($dateOfService != '')
						{
							$results[$key]['DistributorOrder']['days'] = weekdayDiff($row['DistributorOrder']['order_date'], $dateOfService);
						}
					}
				}
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/distributor_orders/csv_summary');
				}
			}
			
			$orderStatuses = $this->Lookup->get('order_statuses');
			
			$this->helpers[] = 'ajax';
			$this->set(compact('isUpdate', 'orderStatuses'));
		}
	}
?>