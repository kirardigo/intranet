<?php
	class DistributorOrder extends AppModel
	{
		var $useTable = 'fp_distributor_orders';
		var $importFile = 'sql_dist_hdr.txt';
		
		/**
		 * Custom import function to load filePro export file.
		 */
		function fileProImport()
		{
			$keys = array(
				'DistributorOrder' => array(
					'bill_to_aaa_number' => 0,
					'bill_to_code' => 1,
					'bill_to_name' => 2,
					'bill_to_zip_code' => 3,
					'ship_to_aaa_number' => 4,
					'ship_to_code' => 5,
					'ship_to_name' => 6,
					'ship_to_zip_code' => 7,
					'ordered_by' => 8,
					'purchase_order_number' => 9,
					'order_date' => 10,
					'print_date' => 11,
					'discount' => 12,
					'order_number' => 13,
					'has_return_authorization_number' => 14,
					'account_number' => 15,
					'shipping_tracking_number' => 16,
					'fax_number' => 17,
					'invoice_number' => 18,
					'invoice_total' => 19,
					'ship_salesman' => 20,
					'ship_region' => 21,
					'ship_market' => 22
				)
			);
			
			$nullable = array(
				'bill_to_aaa_number',
				'ship_to_aaa_number',
				'order_date',
				'print_date',
				'discount',
				'invoice_total'
			);
			
			// Setup file paths
			$settingsModel = ClassRegistry::init('Setting');
			$transferPath = $settingsModel->get('transfer_file_path');
			$filename = "{$transferPath}/{$this->importFile}";
			
			if (!($file = fopen($filename,"r")))
			{
				return;
			}
			
			$currentLine = 0; // Specify the current line
			
			// Truncate existing data
			$this->query("truncate table {$this->useTable}");
			
			// Read the data from the file
			while (!feof($file))
			{
				$line = rtrim(fgets($file, 2048));
				$currentLine++;
				
				// Skip header & blank lines
				if ($currentLine == 1 || strlen($line) == 0)
				{
					continue;
				}
				
				$datarow = preg_split("/\|/", $line);
				
				// Build arrays with data structure to be saved
				foreach ($keys as $modelName => $fields )
				{
					foreach ($fields as $fieldName => $column)
					{
						$value = trim(ifset($datarow[$column]));
						$record[$modelName][$fieldName] = ($value === '' && in_array($fieldName, $nullable)) ? null : $value;
					}
				}
				
				// Special processing
				$record['DistributorOrder']['order_date'] = ($record['DistributorOrder']['order_date'] == null) ? null : databaseDate(formatU05Date($record['DistributorOrder']['order_date']));
				$record['DistributorOrder']['print_date'] = ($record['DistributorOrder']['print_date'] == null) ? null : databaseDate(formatU05Date($record['DistributorOrder']['print_date']));
				
				if ($record['DistributorOrder']['has_return_authorization_number'] == 'Y')
				{
					$record['DistributorOrder']['has_return_authorization_number'] = true;
				}
				else
				{
					$record['DistributorOrder']['has_return_authorization_number'] = false;
				}
				
				// Save the values
				$this->create();
				$this->save($record);
			}
			
			fclose($file);
		}
	}
?>