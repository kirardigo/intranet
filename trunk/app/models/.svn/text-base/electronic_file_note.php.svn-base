<?php
	class ElectronicFileNote extends AppModel
	{
		var $useTable = 'fp_file_notes';
		var $importFile = 'sql_efn.txt';
		
		/**
		 * Custom import function to load filePro export file.
		 */
		function fileProImport()
		{
			$keys = array(
				'ElectronicFileNote' => array(
					'account_number' => 0,
					'transaction_control_number' => 1,
					'name' => 2,
					'memo' => 3,
					'remarks' => 4,
					'has_remarks' => 5,
					'transaction_control_number_type' => 6,
					'action_code' => 7,
					'department_code' => 8,
					'followup_date' => 9,
					'invoice_number' => 10,
					'followup_initials' => 11,
					'priority_code' => 12,
					'is_client_responsibility' => 14,
					'should_be_deleted' => 16
				)
			);
			
			$nullable = array(
				'account_number',
				'transaction_control_number',
				'transaction_control_number_type',
				'action_code',
				'department_code',
				'followup_date',
				'invoice_number',
				'followup_initials',
				'priority_code'
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
			
			$customerModel = ClassRegistry::init('Customer');
			
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
				
				// Special data massaging
				if ($record['ElectronicFileNote']['account_number'] != '')
				{
					$profitCenterNumber = $customerModel->field('profit_center_number', array('account_number' => $record['ElectronicFileNote']['account_number']));
					
					if ($profitCenterNumber)
					{
						$record['ElectronicFileNote']['profit_center_number'] = $profitCenterNumber;
					}
				}
				
				// Save the values
				$this->create();
				$this->save($record);
			}
			
			fclose($file);
		}
		
		/**
		 * Get the oldest followup date for a given TCN #.
		 * @parameter int $transactionControlNumber The TCN number.
		 * @return date The database date for the oldest followup date.
		 */
		function getOldestFollowupDateByTCN($transactionControlNumber)
		{
			return $this->field('MIN(followup_date)', array('transaction_control_number' => $transactionControlNumber));
		}
		
		/**
		 * Get the oldest followup date for a given customer invoice.
		 * @param string $accountNumber The customer's account number.
		 * @param string $invoiceNumber The selected invoice number.
		 * @param string $transactionControlNumber The transaction control number.
		 * @param string $actionCode The action code of the EFN.
		 * @return date The database date for the oldest followup date.
		 */
		function getOldestFollowupDateByInvoice($accountNumber, $invoiceNumber, $transactionControlNumber, $actionCode = 'CLFUP')
		{
			$followup = $this->field('MIN(followup_date)', array(
				'account_number' => $accountNumber,
				'invoice_number' => $invoiceNumber,
				'action_code' => $actionCode
			));
			
			if ($followup !== false)
			{
				return $followup;
			}
			
			$followup = $this->field('MIN(followup_date)', array(
				'account_number' => $accountNumber,
				'transaction_control_number' => $transactionControlNumber,
				'action_code' => $actionCode
			));
			
			if ($followup !== false)
			{
				return $followup;
			}
			
			$followup = $this->field('MIN(followup_date)', array(
				'account_number' => $accountNumber,
				'action_code' => $actionCode
			));
			
			return $followup;
		}
	}
?>