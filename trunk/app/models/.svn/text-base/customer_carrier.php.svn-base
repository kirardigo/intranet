<?php
	class CustomerCarrier extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BR';
		
		var $belongsTo = array(
			'Carrier' => array(
				'foreignKey' => array('field' => 'carrier_number', 'parent_field' => 'carrier_number')
			)
		);
		
		var $actsAs = array(
			'Chainable' => array(
				'ownerModel' => 'Customer',
				'ownerField' => 'carrier_pointer',
				'saveViaFilepro' => true,
				'unchainedIndexes' => array('account_number')
			),
			'FormatDates',
			'Indexable',
			'Migratable' => array('key' => 'account_number')
		);
		
		var $validate = array(
			'carrier_name' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The carrier name is required.'
				)
			),
			'carrier_type' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The carrier type is required.'
				)
			),
			'gross_charge_percentage' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The benefit coverage percentage is required.'
				)
			),
			'source_of_payment_for_claim' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The payment source is required.'
				)
			),
			'insurance_type_code' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The insurance code is required.'
				)
			),
			'carrier_group_code' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The group code is required.'
				)
			),
			'claim_number' => array(
				'conditional' => array(
					'rule' => '_requiredForCertainGroupCodes',
					'message' => 'The claim number is required for this record.'
				)
			),
			'policy_holder_sex' => array(
				'conditional' => array(
					'rule' => '_requiredForCertainGroupCodes',
					'message' => 'The sex is required for this record.'
				)
			),
			'policy_holder_date_of_birth' => array(
				'conditional' => array(
					'rule' => '_requiredForCertainGroupCodes',
					'message' => 'The date of birth is required for this record.'
				)
			)
		);
		
		/**
		 * Verify that the appropriate fields are set when the group code is in a certain subset.
		 */
		function _requiredForCertainGroupCodes($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			$groupCodeSubset = array('MED',	'INS', 'NET', 'WEL');
			
			if (in_array($this->data[$this->alias]['carrier_group_code'], $groupCodeSubset))
			{
				if ($value == '')
				{
					return false;
				}
			}
			
			return true;
		}
		
		/**
		 * Custom sorting function for initial sort of carriers.
		 */
		function sortCarriers($a, $b)
		{
			$carrierTypeValues = array(
				'P' => 1,
				'S' => 2,
				'N' => 3,
				'' => 4
			);
			
			if ($a['is_active'] == $b['is_active'])
			{
				$aCarrierValue = $carrierTypeValues[strtoupper($a['carrier_type'])];
				$bCarrierValue = $carrierTypeValues[strtoupper($b['carrier_type'])];
				
				if ($aCarrierValue == $bCarrierValue)
				{
					return 0;
				}
				
				return ($aCarrierValue > $bCarrierValue) ? 1 : -1;
			}
			
			return ($a['is_active'] < $b['is_active']) ? 1 : -1;
		}
		
		/**
		 * Fetch the most recent transaction for a given account & carrier to find balance and date.
		 * @param string $accountNumber The customer account number.
		 * @param string $carrierNumber The customer carrier code.
		 * @return array Results of transaction search.
		 */
		function getCarrierBalance($accountNumber, $carrierNumber)
		{
			$customerModel = ClassRegistry::init('Customer');
			$transactionModel = ClassRegistry::init('Transaction');
			
			// Start by getting the beginning of the transaction chain from the customer record
			$nextRecordID = $customerModel->field('transaction_pointer', array('account_number' => $accountNumber));
			
			// Loop through transaction chain to find first record with matching carrier number
			while ($nextRecordID != 0)
			{
				$transaction = $transactionModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'id',
						'next_record_pointer',
						'account_number',
						'carrier_number',
						'carrier_balance_due',
						'transaction_date_of_service'
					),
					'conditions' => array('id' => $nextRecordID)
				));
				
				if ($transaction === false)
				{
					throw new Exception("Transaction chain for account number {$accountNumber} is broken.");
				}
				
				// If successful, return the whole record
				if ($transaction['Transaction']['carrier_number'] == $carrierNumber)
				{
					return $transaction;
				}
				
				$nextRecordID = $transaction['Transaction']['next_record_pointer'];
			}
			
			return false;
		}
		
		/**
		 * Check to see if carrier exists on account.
		 * @param string $accountNumber The account number to check.
		 * @param string $carrierNumber The carrier number to check.
		 * @return bool Indicates if carrier exists for account.
		 */
		function isCarrierOnAccount($accountNumber, $carrierNumber)
		{
			$customerModel = ClassRegistry::init('Customer');
			
			// Start by getting the beginning of the chain from the customer record
			$nextRecordID = $customerModel->field('carrier_pointer', array('account_number' => $accountNumber));
			
			while ($nextRecordID != 0)
			{
				$record = $this->find('first', array(
					'contain' => array(),
					'fields' => array(
						'next_record_pointer',
						'carrier_number'
					),
					'conditions' => array(
						'id' => $nextRecordID
					)
				));
				
				if ($record === false)
				{
					throw new Exception("CustomerCarrier chain for {$accountNumber} is broken.");
				}
				
				if ($carrierNumber == $record['CustomerCarrier']['carrier_number'])
				{
					return true;
				}
				else
				{
					$nextRecordID = $record['CustomerCarrier']['next_record_pointer'];
				}
			}
			
			return false;
		}
	}
?>