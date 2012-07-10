<?php
	class Customer extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BL';
		
		var $belongsTo = array(
			'CustomerBilling' => array(
				'foreignKey' => 'billing_pointer'
			)
		);
		
		var $actsAs = array(
			'FormatDates',
			'ChainOwner' => array(
				'CustomerCarrier' => 'carrier_pointer',
				'Invoice' => 'invoice_pointer',
				'Transaction' => 'transaction_pointer',
				'Purchase' => 'purchase_pointer',
				'Rental' => 'rental_equipment_pointer'
			),
			'Indexable',
			'Defraggable',
			'CustomerAccountAuditable'
		);
		
		var $validate = array(
			'name' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The name must be specified.'
				)
			),
			'address_1' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The address must be specified.'
				)
			),
			'city' => array(
				'formatted' => array(
					'rule' => '_validateCityFormat',
					'message' => 'The city, state format is wrong.'
				),
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The city, state must be specified.'
				)
			),
			'zip_code' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The zip code must be specified.'
				)
			),
			'phone_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The phone number must be specified.'
				)
			),
			'profit_center_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The profit center must be specified.'
				)
			)
		);
		
		/**
		 * Ensure that city is correctly formatted.
		 */
		function _validateCityFormat($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			return (preg_match('/^[A-Z0-9 ]+, [A-Z]{2}$/i', $value) != 0);
		}
		
		/**
		 * Handler method used as a callback when creating file notes to update the customer status.
		 * @param array $data The postback data from the file notes module.
		 * @return bool True on success, false on failure.
		 */
		function handler_changeStatus($data)
		{
			return $this->changeStatus(
				$data['Customer']['account_number'], 
				$data['Customer']['account_status_code']
			);
		}
		
		/**
		 * Method to change a customer's status.
		 * @param string $accountNumber The customer's account number.
		 * @param string $status The new status.
		 * @return bool True if successful, false otherwise.
		 */
		function changeStatus($accountNumber, $status)
		{
			$id = $this->field('id', array('account_number' => $accountNumber));
			
			return !!$this->save(array('Customer' => array(
				'id' => $id,
				'account_status_code' => $status
			)));
		}
		
		/**
		 * Fetch the most recent transaction for a given account & carrier to find balance and date.
		 * @param string $accountNumber The customer account number.
		 * @return array Results of transaction search.
		 */
		function getAccountBalance($accountNumber)
		{
			$transactionModel = ClassRegistry::init('Transaction');
			
			// Start by getting the beginning of the transaction chain from the customer record
			$nextRecordID = $this->field('transaction_pointer', array('account_number' => $accountNumber));
			
			// Loop through transaction chain to find first record with matching carrier number
			if ($nextRecordID != 0)
			{
				$transaction = $transactionModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'account_number',
						'account_balance'
					),
					'conditions' => array('id' => $nextRecordID)
				));
				
				if ($transaction === false)
				{
					throw new Exception("Transaction chain for account number {$accountNumber} is broken.");
				}
				
				$transaction['Transaction']['account_balance'] = number_format($transaction['Transaction']['account_balance'], 2);
				
				return $transaction;
			}
			
			return false;
		}
		
		/**
		 * Gets a list of the active carriers. 
		 * @param string $accountNumber The account number to get the carriers for.
		 * @param array $fields An optional list of fields to get from each customer carrier
		 * record. If you don't specify an array, only the carrier number will be returned.
		 * @param bool States whether or not to include (contain) the Carrier record as well. False by default.
		 * @return array An array of active carriers.
		 */
		function activeCarriers($accountNumber, $fields = null, $includeCarrierRecord = false)
		{
			$data = $this->find('first', array(
				'fields' => array(),
				'conditions' => array(
					'account_number' => $accountNumber
				),
				'chains' => array(
					'CustomerCarrier' => array(
						'fields' => $fields != null ? $fields : array('CustomerCarrier.carrier_number'),
						'conditions' => array('CustomerCarrier.is_active' => true),
						'contain' => $includeCarrierRecord ? array('Carrier') : array()
					)
				),
				'contain' => array()
			));
			
			return $data !== false ? $data['CustomerCarrier'] : false;
		}
		
		/**
		 * Get the primary carrier code for an account.
		 */
		function getPrimaryCarrierCode($accountNumber)
		{
			$data = $this->find('first', array(
				'fields' => array('account_number'),
				'conditions' => array(
					'account_number' => $accountNumber
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
							'is_active' => true,
							'carrier_type' => 'P'
						)
					)
				),
				'contain' => array()
			));
			
			return ifset($data['CustomerCarrier'][0]['carrier_number']);
		}
	}
?>