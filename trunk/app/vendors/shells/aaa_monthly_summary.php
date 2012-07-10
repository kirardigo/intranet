<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Summarize the AAA monthly quote & revenue totals.
	 */
	class AaaMonthlySummaryShell extends Shell 
	{
		var $uses = array(
			'AaaMonthlySummary',
			'AaaReferral',
			'Customer',
			'CustomerBilling',
			'Order',
			'Staff',
			'Transaction'
		);
		
		var $tasks = array('ReportParameters', 'Logging');
		
		var $parameters = array(
			array(
				'type'			=> 'string',
				'model' 		=> 'Virtual',
				'field'			=> 'starting_date',
				'flag'			=> 'date',
				'required'		=> true,
				'description' 	=> 'Program will look back this far for records to add to summary.'
			),
			array(
				'type' 			=> 'flag',
				'model' 		=> 'Virtual',
				'field'			=> 'is_verbose',
				'flag'			=> 'verbose',
				'description' 	=> 'Program will log additional messages to indicate progress.'
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$parameters = $this->ReportParameters->parse($this->parameters);
			$isVerbose = $parameters['Virtual']['is_verbose'];
			
			// Truncate the existing records
			$this->AaaMonthlySummary->query("truncate table {$this->AaaMonthlySummary->useTable}");
			
			$currentID = 0;
			$conditions = array(
				'id >' => $currentID,
				'quote_completed_date >=' => databaseDate($parameters['Virtual']['starting_date']),
				'program_referral_number !=' => '',
				'page_number' => 1
			);
			
			$record = $this->Order->find('first', array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'id'
			));
			
			while ($record !== false)
			{
				$conditions['id >'] = $record['Order']['id'];
				
				$orderSalesman = $record['Order']['staff_user_id'];
				$rehabSalesman = $this->AaaReferral->field('rehab_salesman', array('aaa_number' => $record['Order']['program_referral_number']));
				
				$billingRecord = $this->Customer->field('billing_pointer', array('account_number' => $record['Order']['account_number']));
				$programNumber = $this->CustomerBilling->field('school_or_program_number_from_aaa_file', array('id' => $billingRecord));
				
				if ($programNumber === false)
				{
					$programNumber = $record['Order']['program_referral_number'];
				}
				
				// Grab transactions for revenue breakdown
				$transactions = $this->Transaction->find('all', array(
					'contain' => array(),
					'fields' => array(
						'department_code',
						'period_posting_date',
						'salesman_number',
						'amount'
					),
					'conditions' => array(
						'referral_number_from_aaa_file' => $programNumber,
						'transaction_type' => array(1, 3),
						'period_posting_date' => date('Y-m-01', strtotime($record['Order']['quote_completed_date']))
					)
				));
				
				foreach ($transactions as $row)
				{
					$rowData = array(
						'aaa_number' => $record['Order']['program_referral_number'],
						'date_month' => databaseDate($row['Transaction']['period_posting_date']),
						'department_code' => $row['Transaction']['department_code'],
						'order_salesman' => $row['Transaction']['salesman_number']
					);
					
					$existingRecord = $this->AaaMonthlySummary->find('first', array(
						'contain' => array(),
						'conditions' => $rowData
					));
					
					if ($existingRecord === false)
					{
						$rowData['total_revenue_month'] = $row['Transaction']['amount'];
						$rowData['total_revenue_12months'] = $row['Transaction']['amount'];
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save(array('AaaMonthlySummary' => $rowData));
					}
					else
					{
						$existingRecord['AaaMonthlySummary']['total_revenue_month'] += $row['Transaction']['amount'];
						$existingRecord['AaaMonthlySummary']['total_revenue_12months'] += $row['Transaction']['amount'];
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save($existingRecord);
					}
					
					// Update the totals for the rolling 12 month period
					for ($i = 1; $i < 12; $i++)
					{
						$futureDate = databaseDate(date('Y-m-01', strtotime($row['Transaction']['period_posting_date'])) . " + {$i} months");
						
						$orderExisting = $this->AaaMonthlySummary->find('first', array(
							'contain' => array(),
							'conditions' => array(
								'aaa_number' => $record['Order']['program_referral_number'],
								'date_month' => $futureDate,
								'department_code' => $row['Transaction']['department_code'],
								'order_salesman' => $row['Transaction']['salesman_number']
							)
						));
						
						if ($orderExisting === false)
						{
							$orderSaveData['AaaMonthlySummary']['date_month'] = $futureDate;
							$orderSaveData['AaaMonthlySummary']['total_revenue_month'] = 0;
							$orderSaveData['AaaMonthlySummary']['total_quotes_12months'] = ifnull($row['Transaction']['amount'], 0);
							$this->AaaMonthlySummary->create();
							$this->AaaMonthlySummary->save($orderSaveData);
						}
						else
						{
							$orderExisting['AaaMonthlySummary']['total_revenue_12months'] += ifnull($row['Transaction']['amount'], 0);
							$this->AaaMonthlySummary->create();
							$this->AaaMonthlySummary->save($orderExisting);
						}
					}
				}
				
				// See if order salesman quote record exists
				$orderExisting = $this->AaaMonthlySummary->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'aaa_number' => $record['Order']['program_referral_number'],
						'date_month' => date('Y-m-01', strtotime($record['Order']['quote_completed_date'])),
						'department_code' => $record['Order']['dept_hrs'],
						'order_salesman' => $orderSalesman
					)
				));
				
				$orderSaveData['AaaMonthlySummary'] = array(
					'aaa_number' => $record['Order']['program_referral_number'],
					'date_month' => date('Y-m-01', strtotime($record['Order']['quote_completed_date'])),
					'department_code' => $record['Order']['dept_hrs'],
					'order_salesman' => $orderSalesman
				);
				
				// Update the total quotes for the current month for order salesman quotes
				if ($orderExisting === false)
				{
					$orderSaveData['AaaMonthlySummary']['total_quotes_month'] = ifnull($record['Order']['grand_total'], 0);
					$orderSaveData['AaaMonthlySummary']['total_quotes_12months'] = ifnull($record['Order']['grand_total'], 0);
					$this->AaaMonthlySummary->create();
					$this->AaaMonthlySummary->save($orderSaveData);
				}
				else
				{
					$orderExisting['AaaMonthlySummary']['total_quotes_month'] += ifnull($record['Order']['grand_total'], 0);
					$orderExisting['AaaMonthlySummary']['total_quotes_12months'] += ifnull($record['Order']['grand_total'], 0);
					$this->AaaMonthlySummary->create();
					$this->AaaMonthlySummary->save($orderExisting);
				}
				
				// See if rehab salesman quote record exists
				$rehabExisting = $this->AaaMonthlySummary->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'aaa_number' => $record['Order']['program_referral_number'],
						'date_month' => date('Y-m-01', strtotime($record['Order']['quote_completed_date'])),
						'department_code' => $record['Order']['dept_hrs'],
						'rehab_salesman' => $rehabSalesman
					)
				));
				
				$rehabSaveData['AaaMonthlySummary'] = array(
					'aaa_number' => $record['Order']['program_referral_number'],
					'date_month' => date('Y-m-01', strtotime($record['Order']['quote_completed_date'])),
					'department_code' => $record['Order']['dept_hrs'],
					'rehab_salesman' => $rehabSalesman
				);
				
				// Update the total quotes for the current month for rehab salesman quotes
				if ($rehabExisting === false)
				{
					$rehabSaveData['AaaMonthlySummary']['total_quotes_month'] = ifnull($record['Order']['grand_total'], 0);
					$rehabSaveData['AaaMonthlySummary']['total_quotes_12months'] = ifnull($record['Order']['grand_total'], 0);
					$this->AaaMonthlySummary->create();
					$this->AaaMonthlySummary->save($rehabSaveData);
				}
				else
				{
					$rehabExisting['AaaMonthlySummary']['total_quotes_month'] += ifnull($record['Order']['grand_total'], 0);
					$rehabExisting['AaaMonthlySummary']['total_quotes_12months'] += ifnull($record['Order']['grand_total'], 0);
					$this->AaaMonthlySummary->create();
					$this->AaaMonthlySummary->save($rehabExisting);
				}
				
				// Update the quote totals for the rolling 12 month period
				for ($i = 1; $i < 12; $i++)
				{
					$futureDate = databaseDate(date('Y-m-01', strtotime($record['Order']['quote_completed_date'])) . " + {$i} months");
					
					$orderExisting = $this->AaaMonthlySummary->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'aaa_number' => $record['Order']['program_referral_number'],
							'date_month' => $futureDate,
							'order_salesman' => $orderSalesman
						)
					));
					
					$rehabExisting = $this->AaaMonthlySummary->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'aaa_number' => $record['Order']['program_referral_number'],
							'date_month' => $futureDate,
							'rehab_salesman' => $rehabSalesman
						)
					));
					
					if ($orderExisting === false)
					{
						$orderSaveData['AaaMonthlySummary']['date_month'] = $futureDate;
						$orderSaveData['AaaMonthlySummary']['total_quotes_month'] = 0;
						$orderSaveData['AaaMonthlySummary']['total_quotes_12months'] = ifnull($record['Order']['grand_total'], 0);
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save($orderSaveData);
					}
					else
					{
						$orderExisting['AaaMonthlySummary']['total_quotes_12months'] += ifnull($record['Order']['grand_total'], 0);
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save($orderExisting);
					}
					
					if ($rehabExisting === false)
					{
						$rehabSaveData['AaaMonthlySummary']['date_month'] = $futureDate;
						$rehabSaveData['AaaMonthlySummary']['total_quotes_month'] = 0;
						$rehabSaveData['AaaMonthlySummary']['total_quotes_12months'] = ifnull($record['Order']['grand_total'], 0);
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save($rehabSaveData);
					}
					else
					{
						$rehabExisting['AaaMonthlySummary']['total_quotes_12months'] += ifnull($record['Order']['grand_total'], 0);
						$this->AaaMonthlySummary->create();
						$this->AaaMonthlySummary->save($rehabExisting);
					}
				}
				
				$record = $this->Order->find('first', array(
					'contain' => array(),
					'conditions' => $conditions,
					'order' => 'id'
				));
			}
			
			$this->Logging->writeElapsedTime();
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() 
		{
			$this->Logging->startTimer();
		}
	}
?>