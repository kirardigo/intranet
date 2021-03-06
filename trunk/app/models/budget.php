<?php
	class Budget extends AppModel
	{
		var $useTable = 'fp_budget';
		var $importFile = 'sql_budget.txt';
		
		var $actsAs = array('FormatDates');
		
		/**
		 * Custom import function to load filePro export file.
		 */
		function fileProImport()
		{
			$keys = array(
				'Budget' => array(
					'date' => 0,
					'profit_center_number' => 1,
					'department' => 2,
					'amount' => 3
				)
			);
			
			$nullable = array();
			
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
				
				$datarow = preg_split("/\t/", $line);
				
				// Build arrays with data structure to be saved
				foreach ($keys as $modelName => $fields )
				{
					foreach ($fields as $fieldName => $column)
					{
						$value = trim(ifset($datarow[$column]));
						$record[$modelName][$fieldName] = ($value === '' && in_array($fieldName, $nullable)) ? null : $value;
					}
				}
				
				// Special data massaging
				$record['Budget']['amount'] = str_replace('$', '', str_replace(',', '', str_replace('"', '', $record['Budget']['amount'])));
				
				// Save the values
				$this->create();
				$this->save($record);
			}
			
			fclose($file);
		}
	}
?>