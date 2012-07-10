<?php
	class DistributorOrderLine extends AppModel
	{
		var $useTable = 'fp_distributor_order_lines';
		var $importFile = 'sql_dist_detail.txt';
		
		var $belongsTo = array('DistributorOrder');
		
		/**
		 * Custom import function to load filePro export file.
		 */
		function fileProImport()
		{
			$keys = array(
				'DistributorOrderLine' => array(
					'line_number' => 0,
					'order_number' => 1,
					'quantity' => 2,
					'inventory_number' => 3,
					'description' => 4,
					'cost' => 5,
					'upholstery_code' => 6,
					'general_ledger_code' => 7,
					'net' => 8
				)
			);
			
			$nullable = array(
				'quantity',
				'cost',
				'net'
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
				$distributorOrderModel = ClassRegistry::init('DistributorOrder');
				$orderID = $distributorOrderModel->field('id', array('order_number' => $record['DistributorOrderLine']['order_number']));
				$record['DistributorOrderLine']['distributor_order_id'] = $orderID === false ? null : $orderID;
				
				// Save the values
				$this->create();
				$this->save($record);
			}
			
			fclose($file);
		}
	}
?>