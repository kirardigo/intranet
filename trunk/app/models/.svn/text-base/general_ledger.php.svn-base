<?php
	class GeneralLedger extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05AA';
		
		var $actsAs = array('Indexable', 'Defraggable');
		
		var $validate = array(
			'general_ledger_code' => array(
				'formatted' => array(
					'rule' => '_validateCode',
					'message' => 'Not formatted correctly.'
				),
				'duplicate' => array(
					'rule' => 'isUnique',
					'message' => 'This code has already been used.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The code must be specified.'
				)
			),
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description must be specified.'
				)
			),
			'group_code' => array(
				'formatted' => array(
					'rule' => '_validateCode',
					'message' => 'Not formatted correctly.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The code must be specified.'
				)
			),
			'accounting_code' => array(
				'formatted' => array(
					'rule' => '_validateCode',
					'message' => 'Not formatted correctly.'
				)
			)
		);
		
		/**
		 * Validate that the code matches the proper pattern.
		 */
		function _validateCode($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			// We are only testing for formatting if the field has data
			if (strlen($value) == 0)
			{
				return true;
			}
			
			return preg_match('/^[0-9]{3}[0-9A-Z]$/', $value);
		}
		
		/**
		 * Tries to automatically determine the GL code for a transaction against a given invoice. 
		 * @param string $invoiceNumber The invoice number of the invoice to determine the code for.
		 * @param string $carrierNumber The carrier that is responsible for the transaction type.
		 * @param string $transactionType The transaction type code of the transaction to determine the GL code for.
		 * @return string The code, if any, or null if one could not be determined.
		 */
		function determineGLCodeForInvoice($invoiceNumber, $carrierNumber, $transactionType)
		{
			//These rules all come straight from Peggy. They're quite hard-coded, but we're under
			//FU05 limitations so at least it's limited to this function. No other method should
			//re-use these rules if it can be helped.			
						
			if ($transactionType == '2')
			{
				$carrier = ClassRegistry::init('Carrier');
				$groupCode = $carrier->field('group_code', array('carrier_number' => $carrierNumber));
				
				switch ($groupCode)
				{
					case 'INS':
					case 'NET':
						return '27';
					case 'PAT':
						return '30';
					case 'MED':
						return '24';
					case 'WEL':
						return '31';
					default:
						return null;
				}
			}
			else if ($transactionType == '3')
			{
				$invoice = ClassRegistry::init('Invoice');
				$code = $invoice->field('rental_or_purchase', array('invoice_number' => $invoiceNumber));
				
				switch ($code)
				{
					case 'P': 
						return '3304';
					case 'R':
						return '3512';
					default:
						return null;
				}
			}
			else
			{
				return null;
			}
		}
		
		/**
		 * Determine the invoice type based on the GL code.
		 * @param string $generalLedgerCode The general ledger code.
		 * @return mixed The invoice type string or false if unable to determine.
		 */
		function determineInvoiceType($generalLedgerCode)
		{
			if ($generalLedgerCode = '3304' || substr($generalLedgerCode, -1, 1) == 'S')
			{
				return 'purchase';
			}
			else if ($generalLedgerCode = '3512' || substr($generalLedgerCode, -1, 1) == 'R')
			{
				return 'rental';
			}
			
			return false;
		}
	}
?>