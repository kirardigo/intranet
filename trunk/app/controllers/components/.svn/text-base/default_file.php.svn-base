<?php
	uses('File');
	
	/**
	 * This component is responsible for reading and writing to the FU05 Default File. This file
	 * is not like other FU05 files because it is not considered a "non-filePro" file. It's just 
	 * a regular text file with line-feed endings.
	 */
	class DefaultFileComponent extends Object
	{
		/** The full path to the default file. */
		var $file = '/u/apps/U05/FU05AN.DAT';
		
		/**
		 * Array of field definitions for each line (or lines) in the file. Each element consists of 
		 * the following:
		 * 		key: string The key for the field. Should be a valid variable name.
		 * 		value: array An array that tells the component how to process the field. This array
		 * 					 can have the following keys:
		 * 						length: int The length of the field
		 * 						row: int The line number in the file where the field is stored.
		 * 						label: string Label text to use when displayed to a user.
		 * 						parser: array Optional array used if the field needs special parsing. The
		 * 									  first element in the array should be the name of a function
		 * 									  that will handle the reading and writing. Whatever name is given,
		 * 									  two functions must exist in this class to handle it. One will
		 * 									  be the name of the function with a "_read" suffix and one with a 
		 * 									  "_write" suffix. The remaining elements in the array will be passed
		 * 									  as a final argument to each function when invoked.
		 * 									  The "_read" function has the following signature:
		 * 
		 * 									  /**
 		 * 										* @param $contents array The default file contents split into lines.
		 * 										* @param $config array The array that was the value in the $fields array
		 * 										* for the specific field.
		 * 										* @param $params array The extra elements in the original parser array.
		 * 										* @return string The value of the field.
		 * 										* /
		 * 									  function_name_read(&$contents, &$config, &$params);
		 *
		 * 									  while the write function has this signature:
		 * 
		 * 									  /**
 		 * 										* @param $name string The name of the field to write.
		 * 										* @param $config array The array that was the value in the $fields array
		 * 										* for the specific field.
		 * 										* @param $params array The extra elements in the original parser array.
		 * 										* @return mixed Either a string or an array of strings. If the return
		 * 										* value is an array, each element in the array will be added as a new
		 * 										* line in the file when written.
		 * 										* /
		 * 									  function_name_write(&$name, &$config, &$params);
		 * 
		 */
		var $fields = array(
			'printer_condense_on' => array('length' => 3, 'row' => 1, 'label' => 'Printer Condense On', 'parser' => array('_multi_row', 3)),
			'printer_condense_off' => array('length' => 3, 'row' => 4, 'label' => 'Printer Condense Off', 'parser' => array('_multi_row', 3)),
			'company_name' => array('length' => 30, 'row' => 7, 'label' => 'Company Name'),
			'tax_gl_code' => array('length' => 4, 'row' => 8, 'label' => 'Tax G/L Code'),
			'purchase_credit_gl_code' => array('length' => 4, 'row' => 9, 'label' => 'Purchase Credit G/L Code'),
			'rental_credit_gl_code' => array('length' => 4, 'row' => 10, 'label' => 'Rental Credit G/L Code'),
			'not_otherwise_classified' => array('length' => 9, 'row' => 11, 'label' => 'Not Otherwise Classified'),
			'clerk_number' => array('length' => 4, 'row' => 12, 'label' => 'Clerk Number (FL USE ONLY)'),
			'payment_gl' => array('length' => 4, 'row' => 13, 'label' => 'Payment G/L'),
			'oxygen_billing_code_1' => array('length' => 9, 'row' => 20, 'label' => 'Oxygen Billing Code 1'),
			'oxygen_billing_code_2' => array('length' => 9, 'row' => 21, 'label' => 'Oxygen Billing Code 2'),
			'oxygen_billing_code_3' => array('length' => 9, 'row' => 22, 'label' => 'Oxygen Billing Code 3'),
			'sender_code' => array('length' => 5, 'row' => 14, 'label' => 'Sender Code (FL USE ONLY)'),
			'name' => array('length' => 30, 'row' => 15, 'label' => 'Name'),
			'address' => array('length' => 20, 'row' => 16, 'label' => 'Address'),
			'city' => array('length' => 20, 'row' => 17, 'label' => 'City'),
			'state' => array('length' => 2, 'row' => 18, 'label' => 'State'),
			'zip' => array('length' => 9, 'row' => 19, 'label' => 'Zip'),
			'cert_on_emc' => array('length' => 20, 'row' => 23, 'label' => 'Cert. on EMC'),
			'place_of_service' => array('length' => 2, 'row' => 24, 'label' => 'Place of Service'),
			'co_insurance_form_code' => array('length' => 2, 'row' => 25, 'label' => 'Co-Ins Form Code'),
			'primary_form_code' => array('length' => 2, 'row' => 26, 'label' => 'Primary Form Code'),
			'last_invoice_generated' => array('length' => 7, 'row' => 27, 'label' => 'Last Inv Generated'),
			'pos_discount_gl' => array('length' => 4, 'row' => 28, 'label' => 'P.O.S. Discount G/L'),
			'is_running_compiled' => array('length' => 1, 'row' => 29, 'label' => 'Running Compiled?'),
			'use_bank_code' => array('length' => 1, 'row' => 30, 'label' => 'Use Bank Code?'),
			'maintenance_fee_form_code' => array('length' => 2, 'row' => 31, 'label' => 'Maint Fee Form Code'),
			'gl_maintenance_fee_code' => array('length' => 4, 'row' => 32, 'label' => 'G/L Maint Fee Code'),
			'medicare_carrier_1' => array('length' => 4, 'row' => 33, 'label' => 'Medicare Carrier #1'),
			'medicare_carrier_2' => array('length' => 4, 'row' => 34, 'label' => 'Medicare Carrier #2'),
			'medicare_carrier_3' => array('length' => 4, 'row' => 35, 'label' => 'Medicare Carrier #3'),
			'submission_number' => array('length' => 6, 'row' => 36, 'label' => 'Submission #'),
			'receiver_id' => array('length' => 16, 'row' => 37, 'label' => 'Rcvr ID'),
			'test_production_indicator' => array('length' => 4, 'row' => 38, 'label' => 'Test/Prod Indicator'),
			'claim_edit_indicator' => array('length' => 1, 'row' => 39, 'label' => 'Claim Edit Indicator'),
			'line_item_control_numbers' => array('length' => 10, 'row' => 40, 'label' => 'Line Item Ctl #s'),
			'current_post_period' => array('length' => 8, 'row' => 41, 'label' => 'Current Post Period', 'parser' => '_date')
		);
		
		/** Holds the contents of the default file after being loaded. */
		var $data = array();
		
		/** Keeps track of if the default file has already been loaded. */
		var $_loaded = false;
		
		/**
		 * Loads the default file into memory.
		 * @return bool True on success, false on failure.
		 */
		function load()
		{
			if (!$this->_loaded)
			{
				$f = new File($this->file);
				$contents = $f->read();

				if ($contents === false)
				{
					return false;
				}

				$this->_parse($contents);
				$this->_loaded = true;
			}
			
			return true;
		}
		
		/**
		 * Get the current posting period date.
		 * @return string The database-formatted (and comparison friendly) date string.
		 */
		function getCurrentPostingPeriod()
		{
			$this->load();
			
			return sprintf('%d-%d-%d', substr($this->data['current_post_period'], 4, 4), substr($this->data['current_post_period'], 0, 2), substr($this->data['current_post_period'], 2, 2));
		}
		
		/**
		 * Saves the default file back to disk.
		 * @return bool True if successful, false otherwise.
		 */
		function save()
		{
			if (!$this->_loaded)
			{
				return false;
			}
			
			//sort the keys by row number
			$rows = Set::extract($this->fields, '{s}.row');
			$keys = array_keys($this->fields);
			array_multisort($rows, $keys);
						
			$contents = array();
			
			foreach ($keys as $key)
			{
				$config = $this->fields[$key];
				$method = '_default';
				$params = array(&$key, &$config);
				
				//if the field has a parser, we need to invoke the _write method for it
				if (isset($config['parser']))
				{
					if (!is_array($config['parser']))
					{
						$config['parser'] = array($config['parser']);
					}
					
					$method = array_shift($config['parser']);
					$params = array(&$key, &$config, &$config['parser']);
				}
				
				//invoke the write function
				$result = call_user_func_array(array($this, $method . '_write'), $params);
				
				//force the result to an array
				if (!is_array($result))
				{
					$result = array($result);
				}
				
				//add each element of the result to the final output contents
				$contents = array_merge($contents, $result);
			}
			
			$f = new File($this->file/*. rand(1000, 2000)*/);
			$f->write(implode("\n", $contents) . "\n");
			$f->close();
			
			return true;
		}
		
		/**
		 * Parses the default file and loads the internal $data array.
		 * @param string $contents The raw contents of the default file.
		 */
		function _parse($contents)
		{
			//blow up the file into lines
			$lines = explode("\n", $contents);
			
			//load each field into the internal $data array.
			foreach ($this->fields as $name => $config)
			{
				$method = '_default';
				$params = array(&$lines, &$config);
				
				//if the field has a parser, we need to invoke the _read method for it
				if (isset($config['parser']))
				{
					if (!is_array($config['parser']))
					{
						$config['parser'] = array($config['parser']);
					}
					
					$method = array_shift($config['parser']);
					$params = array(&$lines, &$config, &$config['parser']);
				}
				
				//invoke the read function and save it to the internal array
				$this->data[$name] = call_user_func_array(array($this, $method . '_read'), $params); 
			}
		}
		
		/**
		 * Default field handler for reading a field.
		 * @param $contents array The default file contents split into lines.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @return string The value of the field.
		 */
		function _default_read(&$contents, &$config)
		{
			return str_pad($contents[$config['row'] - 1], $config['length']);
		}
		
		/**
		 * Default field handler for writing a field.
		 * @param $name string The name of the field to write.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @return string A value, padded to the length of the field, to write to disk.
		 */
		function _default_write(&$name, &$config)
		{
			return str_pad(substr($this->data[$name], 0, $config['length']), $config['length']);
		}
		
		/**
		 * Field handler for reading a field that comes from multiple consecutive, equal length lines. 
		 * The value returned is the concatenation of all lines read.
		 * @param $contents array The default file contents split into lines.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @param $params array An array containing the number of rows to read from.
		 * @return string The value of the field.
		 */
		function _multi_row_read(&$contents, &$config, &$params)
		{
			$result = '';
			$count = $params[0];
			
			for ($i = 0; $i < $count; $i++)
			{
				$result .= $this->_default_read($contents, array_merge($config, array('row' => $config['row'] + $i)));
			}
			
			return $result;
		}
		
		/**
		 * Field handler for writing a field to multiple lines.
		 * @param $name string The name of the field to write.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @param $params array An array containing the number of rows to write to.
		 * @return array An array of values to write to disk.
		 */
		function _multi_row_write(&$name, &$config, &$params)
		{
			$result = array();
			$count = $params[0];
			$length = $config['length'] * $count;
			$value = str_pad(substr($this->data[$name], 0, $length), $length);
			
			for ($i = 0; $i < $count; $i++)
			{
				$result[] = substr($value, 0, $config['length']);
				$value = substr($value, $config['length']);
			}
			
			return $result;
		}
		
		/**
		 * Field handler for reading a field containing a date.
		 * @param $contents array The default file contents split into lines.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @return string The value of the field.
		 */
		function _date_read(&$contents, &$config)
		{
			$value = $this->_default_read($contents, $config);
			
			//add a century to the date if it's not present (I don't agree
			//with the logic, but it's a straight port from MU05CG.TXT)
			if (strlen($value) != 8)
			{
				$year = (int)substr($value, -2);
				$century = $year < 5 ? '20' : '19';
				$value = substr($value, 0, 4) . $century . substr($value, -2);
			}
			
			return $value;
		}
		
		/**
		 * Field handler for writing a date field.
		 * @param $name string The name of the field to write.
		 * @param $config array The array that was the value in the $fields array
		 * for the specific field.
		 * @return string A value, padded to the length of the field, to write to disk.
		 */
		function _date_write(&$name, &$config)
		{
			return $this->_default_write($name, $config);
		}
	}
?>