<?php
	class Invoice extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BT';
		
		var $belongsTo = array(
			'Customer' => array(
				'foreignKey' => array('field' => 'account_number', 'parent_field' => 'account_number')
			)
		);
		
		var $actsAs = array(
			'Chainable' => array(
				'ownerModel' => 'Customer',
				'ownerField' => 'invoice_pointer',
				'sortOrder' => 'date_of_service',
				'sortDirection' => 'desc',
				'unchainedIndexes' => array('account_number')
			),
			'Indexable',
			'Lockable',
			'Migratable' => array('key' => 'account_number', 'fields' => array('profit_center_number'))
		);
		
		/** Used to cache settings for nextInvoiceNumber so it doesn't have to hit the database on every call. */
		var $_settings = null;
		
		/**
		 * Gets the remaining balance on an invoice for a particular carrier with 
		 * pending transactions applied.
		 * @param string $invoiceNumber The invoice to look up.
		 * @param string $carrierNumber The carrier whose balance to look up.
		 * @return The balance, or false if a balance for the carrier on the specified invoice could not be found.
		 */
		function currentPendingBalance($invoiceNumber, $carrierNumber)
		{
			//grab the invoice
			$invoice = $this->find('first', array(
				'fields' => array('carrier_1_code', 'carrier_1_balance', 'carrier_2_code', 'carrier_2_balance', 'carrier_3_code', 'carrier_3_balance'),
				'conditions' => array(
					'Invoice.invoice_number' => $invoiceNumber,
					'or' => array(
						'Invoice.carrier_1_code' => $carrierNumber,
						'Invoice.carrier_2_code' => $carrierNumber,
						'Invoice.carrier_3_code' => $carrierNumber
					)
				),
				'contain' => array()
			));
			
			if ($invoice === false)
			{
				return false;
			}
			
			$balance = 0;
			
			//grab the specified carrier's balance
			foreach (array('1', '2', '3') as $field)
			{
				if ($invoice['Invoice']["carrier_{$field}_code"] == $carrierNumber)
				{
					$balance = $invoice['Invoice']["carrier_{$field}_balance"];
					break;
				}
			}
			
			//apply the pending balance
			$queue = ClassRegistry::init('TransactionQueue');
			return $balance + $queue->currentQueueTotal($invoiceNumber, $carrierNumber);
		}
		
		/**
		 * Handler method used as a callback when creating file notes to update the L1 information.
		 * @param array $data The postback data from the file notes module.
		 * @return bool True on success, false on failure.
		 */
		function handler_updateL1Information($data)
		{
			$id = $this->field('id', array('invoice_number' => $data['Invoice']['invoice_number']));
			
			return !!$this->save(array('Invoice' => array(
				'id' => $id,
				'line_1_status' => $data['Invoice']['line_1_status'],
				'line_1_initials' => $data['Invoice']['line_1_initials'],
				'line_1_date' => databaseDate($data['Invoice']['line_1_date']),
				'line_1_carrier_number' => $data['Invoice']['line_1_carrier_number']
			)));
		}
		
		/**
		 * Returns the next free available invoice number. The invoice number returned is effectively consumed
		 * at that point. 
		 * @return string The next available invoice number.
		 */
		function nextInvoiceNumber()
		{			
			//load and cache settings if we don't have them yet
			if ($this->_settings == null)
			{
				$this->_settings = ClassRegistry::init('Setting')->get(array('next_invoice_application_path'));
			}
			
			$result = exec($this->_settings['next_invoice_application_path'], $output, $returned);
			return $returned !== 0 ? false : trim($result);
		}
	}
?>