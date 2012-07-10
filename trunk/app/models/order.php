<?php
	class Order extends AppModel
	{
		var $useTable = 'fp_orders';
		var $importFile = 'sql_order.txt';
		
		var $belongsTo = array();
		
		var $actsAs = array('FormatDates');
		
		/**
		 * Custom import function to load filePro export file.
		 */
		function fileProImport()
		{
			$keys = array(
				'Order' => array(
					'dept_hrs' => 0,
					'status' => 1,
					'type' => 2,
					'profit_center_number' => 3,
					'account_number' => 4,
					'invoice_number' => 5,
					'transaction_control_number_type' => 6,
					'transaction_control_number' => 7,
					'client_name' => 8,
					'customer_owned_equipment_number' => 9,
					'certificate_of_medical_necessity_number' => 10,
					'mrs_auth_number' => 11,
					'program_referral_number' => 12,
					'long_term_care_facility_number' => 13,
					'parent_number' => 14,
					'physician_number' => 15,
					'rehab_hospital' => 16,
					'staff_user_id' => 17,
					'notes' => 18,
					'order_type' => 19,
					'wip_description' => 20,
					'is_foam_in_place' => 21,
					'seating_code' => 22,
					'delivery_code' => 23,
					'worksite' => 24,
					'wip_program' => 25,
					'wip_amount' => 26,
					'work_in_process' => 27,
					'is_ok_to_schedule' => 28,
					'work_scheduled_date' => 29,
					'work_completed_date' => 30,
					'deletion_code' => 31,
					'deletion_notes' => 32,
					'equipment_ordered_date' => 33,
					'equipment_received_date' => 34,
					'page_number' => 35,
					'quote' => 36,
					'documentation' => 37,
					'funding' => 38,
					'equipment_on_order' => 39,
					'needs_quote_date' => 41,
					'evaluation_date' => 42,
					'quote_completed_date' => 43,
					'quote_printed_date' => 44,
					'quote_client_care_specialist_date' => 45,
					'funding_pending_date' => 47,
					'funding_approved_date' => 48,
					'grand_total' => 49,
					'mobility_choice' => 50,
					'manufacturer_model' => 51,
					'carrier_1_type' => 52,
					'carrier_1_code' => 53,
					'carrier_2_type' => 54,
					'carrier_2_code' => 55,
					'carrier_3_type' => 56,
					'carrier_3_code' => 57,
					'denied_date' => 58,
					'appealed_date' => 59,
					'invoiced_date' => 60,
					'claims_status' => 61,
					'verification_of_benefits_status' => 62,
					'verification_of_benefits_initials' => 63,
					'verification_of_benefits_date' => 64,
					'authorization_status' => 65,
					'authorization_initials' => 66,
					'authorization_date' => 67
				)
			);
			
			$nullable = array(
				'wip_amount',
				'work_scheduled_date',
				'work_completed_date',
				'equipment_ordered_date',
				'equipment_received_date',
				'needs_quote_date',
				'evaluation_date',
				'quote_completed_date',
				'quote_printed_date',
				'quote_client_care_specialist_date',
				'funding_pending_date',
				'funding_approved_date',
				'grand_total',
				'denied_date',
				'appealed_date',
				'invoiced_date',
				'verification_of_benefits_date',
				'authorization_date'
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
			
			$staffModel = ClassRegistry::init('Staff');
			
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
				$record['Order']['is_ok_to_schedule'] = ($record['Order']['is_ok_to_schedule'] == 'Y') ? 1 : 0;
				
				// Save the values
				$this->create();
				$this->save($record);
			}
			
			fclose($file);
		}
	}
?>