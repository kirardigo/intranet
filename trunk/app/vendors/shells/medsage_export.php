<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Export information for medSage.
	 */
	class MedsageExportShell extends Shell 
	{
		var $uses = array(
			'Carrier',
			'CompetitiveBidZipCode',
			'Customer',
			'Inventory',
			'Lookup',
			'Oxygen',
			'Physician',
			'Process',
			'Setting',
			'Transaction',
			'Vendor'
		);
		
		var $data = '';
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to mark as the creator for generated records.'
			),
			array(
				'type'			=> 'date',
				'model' 		=> 'Transaction',
				'field'			=> 'transaction_date_of_service',
				'flag'			=> 'date',
				'required' 		=> true,
				'description' 	=> 'Program will look back this far for records to export.'
			),
			array(
				'type'			=> 'date',
				'model' 		=> 'Transaction',
				'field'			=> 'transaction_date_of_service_end',
				'flag'			=> 'date_end',
				'default' 		=> 'today',
				'description' 	=> 'Program will grab records to export up to this date.'
			),
			array(
				'type'			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'is_being_uploaded',
				'flag'			=> 'upload',
				'description'	=> 'Program will automatically upload the file to medSage.'
			),
			array(
				'type' 			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'is_verbose',
				'flag'			=> 'verbose',
				'description'	=> 'Program will output extra information when this flag is set.'
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			$this->Logging->write('Export started');
			
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			$isVerbose = $parameters['Virtual']['is_verbose'];
			
			//initialize the process
			$processID = $this->Process->createProcess('medSage Export', false);
			$this->Process->updateProcess($processID, 0, 'Finding transactions since ' . formatDate($parameters['Transaction']['transaction_date_of_service']));
			
			$chargeTransactionType = $this->Setting->get('charge_transaction_type_id');
			
			//find records within the specified date range with an appropriate GL code
			$results = $this->Transaction->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'transaction_date_of_service >=' => $parameters['Transaction']['transaction_date_of_service'],
					'transaction_date_of_service <=' => $parameters['Transaction']['transaction_date_of_service_end'],
					'transaction_type' => $chargeTransactionType,
					'general_ledger_code' => array('455R', '455S', '456R', '456S')
				)
			));
			
			$countResults = count($results);
			$this->Process->updateProcess($processID, 10, "Found {$countResults} records");
			
			if ($isVerbose)
			{
				$this->Logging->write("Found {$countResults} records");
			}
			
			$currentResult = 0;
			
			//loop through results to associate data from other models
			foreach ($results as $key => $row)
			{
				$currentResult++;
				$currentPercent = round(($currentResult / $countResults) * 89 + 10);
				$this->Process->updateProcess($processID, $currentPercent, "Associating data for record {$currentResult} of {$countResults}");
				
				//link record to Customer
				$customer = $this->Customer->find('first', array(
					'contain' => array(), 
					'conditions' => array(
						'account_number' => $row['Transaction']['account_number']
					),
					'chains' => array(
						'CustomerCarrier' => array(
							'contain' => array(),
							'fields' => array(
								'carrier_number',
								'carrier_type',
								'is_active'
							),
							'conditions' => array(
								'carrier_type' => 'P',
								'is_active' => 1
							),
							'required' => false
						)
					)
				));
				
				if ($customer === false)
				{
					if ($isVerbose)
					{
						$this->Logging->write("Removed: Customer not found ({$row['Transaction']['account_number']})");
					}
					
					unset($results[$key]);
					continue;
				}
				else
				{
					$results[$key]['Customer'] = $customer['Customer'];
					
					//filter out any record that is not for the customer's active primary carrier
					if (!isset($customer['CustomerCarrier'][0]) || (isset($customer['CustomerCarrier'][0])
						&& $customer['CustomerCarrier'][0]['carrier_number'] != $row['Transaction']['carrier_number']))
					{
						if ($isVerbose)
						{
							$this->Logging->write("Removed: Not active primary carrier ({$row['Transaction']['account_number']} {$row['Transaction']['carrier_number']})");
						}
						
						unset($results[$key]);
						continue;
					}
					
					//link record to CustomerBilling
					//deceased field not always set to zero so we must test for not equal to 1
					$billingRecord = $this->Customer->CustomerBilling->find('first', array(
						'contain' => array(),
						'fields' => array('date_of_birth', 'physician_number'),
						'conditions' => array(
							'id' => $customer['Customer']['billing_pointer'],
							'is_deceased !=' => 1
						)
					));
					
					if ($billingRecord === false)
					{
						if ($isVerbose)
						{
							$this->Logging->write("Removed: Billing not found or deceased ({$row['Transaction']['account_number']} {$customer['Customer']['billing_pointer']})");
						}
						
						unset($results[$key]);
						continue;
					}
					else
					{
						$results[$key]['CustomerBilling'] = $billingRecord['CustomerBilling'];
					}
					
					//link record to Physician
					if (isset($billingRecord['CustomerBilling']['physician_number']))
					{
						$physicianRecord = $this->Physician->find('first', array(
							'contain' => array(),
							'fields' => array('name', 'fax_number'),
							'conditions' => array('physician_number' => $billingRecord['CustomerBilling']['physician_number'])
						));
						$results[$key]['Physician'] = $physicianRecord['Physician'];
					}
				}
				
				//find sleep records with non-discharged status
				$sleepCount = $this->Oxygen->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'account_number' => $row['Transaction']['account_number'],
						'osa_status !=' => array('', 'D')
					)
				));
				
				if ($sleepCount == 0)
				{
					if ($isVerbose)
					{
						$this->Logging->write("Removed: No sleep record or discharged ({$row['Transaction']['account_number']})");
					}
					
					unset($results[$key]);
					continue;
				}
				
				//find if user is competitive bid
				$inCompetitiveBidZip = $this->CompetitiveBidZipCode->find('count', array(
					'conditions' => array(
						'competitive_bid_zip_code' => $row['Transaction']['client_zip_code']
					)
				));
				
				//we can look at the transaction carrier because we are only including
				//transactions for the customer's primary carrier anyway
				if ($inCompetitiveBidZip && $row['Transaction']['carrier_number'] == 'MC20')
				{
					if ($isVerbose)
					{
						$this->Logging->write("Removed: Competitive bid zip ({$row['Transaction']['account_number']})");
					}
					
					unset($results[$key]);
					continue;
				}
				
				//link record to Inventory & Vendor
				$inventoryRecord = $this->Inventory->find('first', array(
					'contain' => array(),
					'fields' => array('manufacturer_product_code', 'vendor_code'),
					'conditions' => array('inventory_number' => $row['Transaction']['inventory_number'])
				));
				
				if ($inventoryRecord !== false)
				{
					$results[$key]['Inventory'] = $inventoryRecord['Inventory'];
					$results[$key]['Vendor']['name'] = $this->Vendor->field('name', array('vendor_code' => $inventoryRecord['Inventory']['vendor_code']));
				}
				
				//link record to Carrier
				$carrier = $this->Carrier->find('first', array(
					'contain' => array(),
					'fields' => array('name'),
					'conditions' => array(
						'carrier_number' => $row['Transaction']['carrier_number']
					)
				));
				
				if ($carrier !== false)
				{
					$results[$key]['Carrier'] = $carrier['Carrier'];
				}
			}
			
			$this->Process->updateProcess($processID, 99, "Generating attachment");
			
			// Writes the output CSV to $this->data.
			$this->renderOutput($results);
			
			$this->Process->addFile($processID, 'Export', 'medsage.csv', 'text/csv', $this->data);
			$this->Process->updateProcess($processID, 100, "Export complete");
			$this->Logging->write('Export completed');
			
			if ($parameters['Virtual']['is_being_uploaded'])
			{
				$outputFile = new File('/tmp/medsage.csv');
				
				if ($outputFile->exists())
				{
					$outputFile->delete();
				}
				
				$outputFile->create();
				$outputFile->write($this->data, 'w', true);
				$outputFile->close();
				
				$ftpOutput = array();
				exec("sftp -b /home/emrs/medsage_sftp_script millers@office.medsagetechnologies.com", $ftpOutput);
				
				foreach ($ftpOutput as $ftpLine)
				{
					$this->Logging->write($ftpLine);
				}
				
				$this->Process->updateProcess($processID, 100, "Upload complete");
				$this->Logging->write('Upload completed');
			}
			
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
		}
		
		/**
		 * Escape a field for export.
		 */
		function field($value, $addDelimiter = true)
		{
			$value = str_replace('"', '""', $value);
			$this->data .= "\"{$value}\"" . ($addDelimiter ? ',' : '');
		}
		
		/**
		 * End current line in export.
		 */
		function newline()
		{
			$this->data .= "\r\n";
		}
		
		/**
		 * Render the output file.
		 */
		function renderOutput($results)
		{
			$profitCenters = $this->Lookup->get('profit_centers');
			
			$this->field('patient_key');
			$this->field('patient_integration_date');
			$this->field('supply_part_key');
			$this->field('reorder_amount');
			$this->field('patient_name');
			$this->field('patient_phone_number');
			$this->field('supply_hcpc');
			$this->field('supply_product_class');
			$this->field('plan_key');
			$this->field('order_type');
			$this->field('date_of_setup');
			$this->field('confirm_code');
			$this->field('customer_order_key');
			$this->field('supply_category_key');
			$this->field('supply_description');
			$this->field('supplier_name');
			$this->field('supplier_part_number');
			$this->field('patient_street_address');
			$this->field('patient_street_address_2');
			$this->field('patient_city');
			$this->field('patient_state');
			$this->field('patient_zip_code');
			$this->field('referrer_key');
			$this->field('referrer_name');
			$this->field('plan_name');
			$this->field('patient_branch_code');
			$this->field('patient_email_address');
			$this->field('patient_is_email_enabled');
			$this->field('patient_alt_phone_number');
			$this->field('patient_alt_name');
			$this->field('customer_defined');
			$this->field('patient_day_to_call');
			$this->field('patient_time_to_call');
			$this->field('patient_followup_phone_number');
			$this->field('patient_followup_time_to_call');
			$this->field('patient_language');
			$this->newline();
			
			foreach ($results as $row)
			{
				if (isset($row['Customer']))
				{
					$profitCenter = $row['Customer']['profit_center_number'] . '-' . ifset($profitCenters[$row['Customer']['profit_center_number']]);
					
					$email = strpos($row['Customer']['email'], '@') === false ? '' : $row['Customer']['email'];
					
					if (strpos($row['Customer']['city'], ',') != -1)
					{
						$pieces = explode(',', $row['Customer']['city']);
						$city = $pieces[0];
						$state = trim(ifset($pieces[1]));
					}
					else
					{
						$city = $row['Customer']['city'];
					}
				}
				
				if (isset($row['CustomerBilling']['date_of_birth']) || isset($row['Physician']['fax_number']))
				{
					$customerDefined = ifset($row['CustomerBilling']['date_of_birth']) . '~' . ifset($row['Physician']['fax_number']);
				}
				
				if ($row['Transaction']['rental_or_purchase'] == 'R')
				{
					$row['Transaction']['inventory_description'] = '(Rental) ' . $row['Transaction']['inventory_description'];
				}
				
				$this->field($row['Transaction']['account_number']);
				$this->field($row['Transaction']['transaction_date_of_service']);
				$this->field($row['Transaction']['inventory_number']);
				$this->field($row['Transaction']['quantity']);
				$this->field(ifset($row['Customer']['name']));
				$this->field(ifset($row['Customer']['phone_number']));
				$this->field($row['Transaction']['healthcare_procedure_code']);
				$this->field('');
				$this->field($row['Transaction']['carrier_number']);
				$this->field('D');
				$this->field('');
				$this->field('true');
				$this->field($row['Transaction']['transaction_control_number']);
				$this->field('');
				$this->field($row['Transaction']['inventory_description']);
				$this->field(ifset($row['Vendor']['name']));
				$this->field(ifset($row['Inventory']['manufacturer_product_code']));
				$this->field(ifset($row['Customer']['address_1']));
				$this->field(ifset($row['Customer']['address_2']));
				$this->field(ifset($city));
				$this->field(ifset($state));
				$this->field(ifset($row['Customer']['zip_code']));
				$this->field(ifset($row['CustomerBilling']['physician_number']));
				$this->field(ifset($row['Physician']['name']));
				$this->field(ifset($row['Carrier']['name']));
				$this->field($profitCenter);
				$this->field($email);
				$this->field('false');
				$this->field('');
				$this->field(ifset($row['Customer']['emergency_contact_name']));
				$this->field($customerDefined);
				$this->field('');
				$this->field('');
				$this->field('');
				$this->field('');
				$this->field('english');
				$this->newline();
			}
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>