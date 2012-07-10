<?php
	class CustomerCarriersController extends AppController
	{
		var $uses = array(
			'Carrier',
			'CustomerCarrier',
			'Customer',
			'Lookup'
		);
		
		/**
		 * Checks to see whether or not a particular customer carrier is active.
		 * Expects $this->params['form']['accountNumber'] and $this->params['form']['carrierNumber'] to be set.
		 * The returned JSON has one variable - isActive, which is either true of false.
		 */
		function json_checkStatus()
		{
			$carriers = Set::extract('/carrier_number', $this->Customer->activeCarriers($this->params['form']['accountNumber']));
			$this->set('json', array('isActive' => in_array($this->params['form']['carrierNumber'], $carriers)));
		}
		
		/**
		 * Check to ensure carrier is on then account and get carrier name.
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		account_number The account number to lookup.
		 * 		carrier_number The carrier number to find the name for.
		 */
		function json_checkCarrierReturnName()
		{
			$onAccount = $this->CustomerCarrier->isCarrierOnAccount($this->params['form']['account_number'], $this->params['form']['carrier_number']);
			$name = $this->CustomerCarrier->field('carrier_name', array('carrier_number' => $this->params['form']['carrier_number']));
			
			$this->set('json', array('notExist' => !$onAccount, 'name' => $name));
		}
		
		/**
		 * AJAX action to get a carrier name.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		carrier_number The carrier number to find the name for.
		 */
		function ajax_name()
		{
			$match = $this->CustomerCarrier->field('carrier_name', array('carrier_number' => $this->params['form']['carrier_number']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * Module to display carriers for a particular customer.
		 * @param string $accountNumber The account number of the customer.
		 */
		function module_forCustomer($accountNumber)
		{
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$pointer = $this->Customer->field('carrier_pointer', array('account_number' => $accountNumber));
				
				return ($pointer != 0);
			}
			
			$results = $this->Customer->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'Customer.account_number' => $accountNumber
				),
				'fields' => array(
					'id',
					'account_number',
					'name'
				),
				'chains' => array(
					'CustomerCarrier' => array(
						'contain' => array(),
						'fields' => array(
							'id',
							'carrier_number',
							'carrier_type',
							'is_active',
							'claim_number',
							'insuree_name',
							'policy_holder_date_of_birth',
							'signature_authorization_on_file',
							'policy_termination_date',
							'sequence_number',
							'source_of_payment_for_claim',
							'insurance_type_code',
							'carrier_name'
						)
					)
				)
			));
			
			if ($results !== false)
			{
				usort($results['CustomerCarrier'], array($this->CustomerCarrier, 'sortCarriers'));
			}
			
			$this->set(compact('results'));
		}
		
		/**
		 * Get the carrier detail form for a particular customer carrier.
		 * @param int $customerCarrierID The ID of the customer carrier record.
		 */
		function ajax_carrierDetail($customerCarrierID)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->CustomerCarrier->find('first', array(
				'contain' => array('Carrier'),
				'conditions' => array(
					'id' => $customerCarrierID
				)
			));
			
			$sexes = $this->Lookup->get('sex');
			$relationships = $this->Lookup->get('relationship', true);
			$signatureAuthorizations = $this->Lookup->get('signature_authorization', true);
			$paymentSources = $this->Lookup->get('payment_sources', true, true);
			$insuranceTypes = $this->Lookup->get('insurance_types', true, true);
			$employmentStatuses = $this->Lookup->get('employment_status', true);
			
			$this->set(compact('employmentStatuses', 'insuranceTypes', 'paymentSources',
				'signatureAuthorizations', 'relationships', 'sexes'));
			$this->helpers[] = 'ajax';
		}
		
		/**
		 * Save the customer core information from module_core.
		 * @param int $customerCarrierID The ID of the CustomerCarrier record to update.
		 */
		function json_saveDetail($customerCarrierID)
		{
			$success = true;
			$message = '';
			
			if (isset($this->data))
			{
				// Make sure that we don't have more than 3 active carriers or more than 1 primary or secondary
				$accountNumber = $this->CustomerCarrier->field('account_number', array('id' => $customerCarrierID));
				$carrierPointer = $this->Customer->field('carrier_pointer', array('account_number' => $accountNumber));
				$activeCount = 0;
				$primaryCount = 0;
				$secondaryCount = 0;
				$activePercentage = 0;
				
				while ($carrierPointer != 0)
				{
					$record = $this->CustomerCarrier->find('first', array(
						'contain' => array(),
						'fields' => array(
							'id',
							'next_record_pointer',
							'gross_charge_percentage',
							'carrier_type',
							'is_active'
						),
						'conditions' => array('id' => $carrierPointer)
					));
					
					// Don't count the current record towards the overall count
					if ($record['CustomerCarrier']['id'] != $customerCarrierID)
					{
						if ($record['CustomerCarrier']['is_active'])
						{
							$activeCount++;
						}
						if (strtoupper($record['CustomerCarrier']['carrier_type']) == 'P')
						{
							$primaryCount++;
						}
						if (strtoupper($record['CustomerCarrier']['carrier_type']) == 'S')
						{
							$secondaryCount++;
						}
						
						$activePercentage += ($record['CustomerCarrier']['gross_charge_percentage'] == "") ? 0 : $record['CustomerCarrier']['gross_charge_percentage'];
					}
					
					$carrierPointer = $record['CustomerCarrier']['next_record_pointer'];
				}
				
				if ($this->data['CustomerCarrier']['is_active'] && $activeCount >= 3)
				{
					$success = false;
					$message = 'Cannot create more than 3 active carriers.';
				}
				else if ($this->data['CustomerCarrier']['carrier_type'] == 'P' && $primaryCount >= 1)
				{
					$success = false;
					$message = 'Cannot create more than 1 primary carrier.';
				}
				else if ($this->data['CustomerCarrier']['carrier_type'] == 'S' && $secondaryCount >= 1)
				{
					$success = false;
					$message = 'Cannot create more than 1 secondary carrier.';
				}
				
				// Make sure coverage of active carriers does not exceed 100%
				if ($success && $this->data['CustomerCarrier']['is_active'] &&
					$activePercentage + $this->data['CustomerCarrier']['gross_charge_percentage'] > 100)
				{
					$success = false;
					$message = 'Total benefit coverage exceeds 100%.';
				}
				
				if ($success)
				{
					// Clean up the data before saving
					$this->data['CustomerCarrier']['net_charge_percentage'] = $this->data['CustomerCarrier']['gross_charge_percentage'];
					$this->data['CustomerCarrier']['policy_holder_date_of_birth'] = databaseDate($this->data['CustomerCarrier']['policy_holder_date_of_birth']);
					$this->data['CustomerCarrier']['policy_effective_date'] = databaseDate($this->data['CustomerCarrier']['policy_effective_date']);
					$this->data['CustomerCarrier']['policy_termination_date'] = databaseDate($this->data['CustomerCarrier']['policy_termination_date']);
					$this->data['CustomerCarrier']['last_zirmed_electronic_vob_date'] = databaseDate($this->data['CustomerCarrier']['last_zirmed_electronic_vob_date']);
					
					if (!$this->CustomerCarrier->save($this->data))
					{
						$success = false;
						$message = "Could not save CustomerCarrier record. " . print_r($this->CustomerCarrier->validationErrors, true);
					}
					if ($success && !$this->Carrier->save($this->data))
					{
						$success = false;
						$message = "Could not save master Carrier record.";
					}
				}
			}
			
			$this->set('json', array('success' => $success, 'message' => $message));
		}
		
		/**
		 * Remove a customer carrier from an account.
		 * @param int $id The ID of the customer carrier record.
		 */
		function json_carrierDelete($id)
		{
			$success = true;
			$message = '';
			
			$accountNumber = $this->CustomerCarrier->field('account_number', array('id' => $id));
			$carrierNumber = $this->CustomerCarrier->field('carrier_number', array('id' => $id));
			$transactionPointer = $this->Customer->field('transaction_pointer', array('account_number' => $accountNumber));
			
			$transactionModel = ClassRegistry::init('Transaction');
			
			// Loop through chain until we find a transaction that matches the carrier number
			while ($transactionPointer != 0)
			{
				$record = $transactionModel->find('first', array(
					'contain' => array(),
					'fields' => array(
						'next_record_pointer',
						'carrier_number'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				if ($record === false)
				{
					$success = false;
					$message = 'Transaction chain is broken. Must fix before deleting carrier.';
					break;
				}
				
				if ($record['Transaction']['carrier_number'] == $carrierNumber)
				{
					$success = false;
					$message = "Transactions exist for customer carrier. Cannot delete record.";
					break;
				}
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			if ($success)
			{
				$this->CustomerCarrier->deleteViaFilepro($id);
			}
			
			$this->set('json', array('success' => $success, 'message' => $message));
		}
		
		/**
		 * Create a new carrier for a customer.
		 */
		function create($accountNumber, $carrierID)
		{
			$this->pageTitle = 'New Customer Carrier';
			
			$success = true;
			$message = '';
			
			// Lookup customer ID based on account number
			$customerID = $this->Customer->field('id', array('account_number' => $accountNumber));
			$carrierPointer = $this->Customer->field('carrier_pointer', array('id' => $customerID));
			
			if (isset($this->data))
			{
				$this->CustomerCarrier->set($this->data);
				
				if ($this->CustomerCarrier->validates())
				{
					$this->data['CustomerCarrier']['policy_holder_date_of_birth'] = databaseDate($this->data['CustomerCarrier']['policy_holder_date_of_birth']);
					
					$this->CustomerCarrier->addToChain($customerID, $this->data);
					
					$this->redirect("/customers/inquiry/accountNumber:{$accountNumber}/tab:1");
				}
			}
			else
			{
				// Lookup Carrier record
				$record = $this->Carrier->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $carrierID)
				));
				
				$hasActive = array(
					'P' => false,
					'S' => false,
					'N' => false
				);
				
				// Make sure carrier does not already exist in chain
				while ($carrierPointer != 0)
				{
					$current = $this->CustomerCarrier->find('first', array(
						'contain' => array(),
						'fields' => array(
							'next_record_pointer',
							'carrier_number',
							'carrier_type',
							'is_active'
						),
						'conditions' => array('id' => $carrierPointer)
					));
					
					if ($current['CustomerCarrier']['carrier_number'] == $record['Carrier']['carrier_number'])
					{
						$success = false;
						$message = 'Carrier already associated with customer. Cannot insert.';
						break;
					}
					
					if ($current['CustomerCarrier']['is_active'])
					{
						$hasActive[$current['CustomerCarrier']['carrier_type']] = true;
					}
					
					$carrierPointer = $current['CustomerCarrier']['next_record_pointer'];
				}
				
				// Set carrier type & active status appropriately
				foreach ($hasActive as $type => $exists)
				{
					if (!$exists)
					{
						$carrierType = $type;
						$isActive = 1;
						break;
					}
				}
				
				if (!isset($carrierType))
				{
					$carrierType = 'N';
					$isActive = 0;
				}
				
				// Assemble record to insert to CustomerCarrier chain
				$this->data['CustomerCarrier'] = array(
					'account_number' => $accountNumber,
					'carrier_number' => $record['Carrier']['carrier_number'],
					'carrier_name' => $record['Carrier']['name'],
					'carrier_type' => $carrierType,
					'is_active' => $isActive,
					'is_tax_exempt' => 0,
					'carrier_group_code' => $record['Carrier']['group_code']
				);
				
				// Set defaults for payment source
				if ($record['Carrier']['group_code'] == 'MED')
				{
					$this->data['CustomerCarrier']['source_of_payment_for_claim'] = 'C';
				}
				else if ($record['Carrier']['group_code'] == 'WEL')
				{
					$this->data['CustomerCarrier']['source_of_payment_for_claim'] = 'D';
				}
				else if ($record['Carrier']['group_code'] == 'BWC')
				{
					$this->data['CustomerCarrier']['source_of_payment_for_claim'] = 'B';
				}
				else if ($record['Carrier']['group_code'] == 'INS' || $record['Carrier']['group_code'] == 'NET')
				{
					$this->data['CustomerCarrier']['source_of_payment_for_claim'] = 'F';
				}
			}
			
			$sexes = $this->Lookup->get('sex');
			$relationships = $this->Lookup->get('relationship', true);
			$signatureAuthorizations = $this->Lookup->get('signature_authorization', true);
			$paymentSources = $this->Lookup->get('payment_sources', true, true);
			$insuranceTypes = $this->Lookup->get('insurance_types', true, true);
			$employmentStatuses = $this->Lookup->get('employment_status', true);
			
			$this->set(compact('employmentStatuses', 'insuranceTypes', 'paymentSources',
				'signatureAuthorizations', 'relationships', 'sexes', 'carrierID', 'success', 'message'));
		}
		
		/**
		 * Show a summary of the customer carrier information for an account.
		 * @param string $accountNumber The customer's account number.
		 */
		function module_customerSummary($accountNumber)
		{
			$results = $this->Customer->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'Customer.account_number' => $accountNumber
				),
				'fields' => array(
					'id',
					'account_number',
					'name'
				),
				'chains' => array(
					'CustomerCarrier' => array(
						'contain' => array(),
						'fields' => array(
							'id',
							'carrier_number',
							'carrier_name',
							'carrier_type',
							'is_active',
							'claim_number'
						)
					)
				)
			));
			
			// Lookup the customer carrier balances
			if ($results !== false)
			{
				usort($results['CustomerCarrier'], array($this->CustomerCarrier, 'sortCarriers'));
				
				foreach ($results['CustomerCarrier'] as $key => $row)
				{
					$transaction = $this->CustomerCarrier->getCarrierBalance($accountNumber, $row['carrier_number']);
					
					if ($transaction !== false)
					{
						$results['CustomerCarrier'][$key]['Transaction'] = $transaction['Transaction'];
					}
				}
			}
			
			// Lookup the account balance
			$accountInfo = $this->Customer->getAccountBalance($accountNumber);
			$this->data['Customer']['account_balance'] = $accountInfo['Transaction']['account_balance'];
			
			$this->set(compact('results'));
		}
		
		/**
		 * Show a customer carrier balance information for an account.
		 * @param string $accountNumber The customer's account number.
		 */
		function module_clientHeader($accountNumber)
		{
			$results = $this->Customer->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'Customer.account_number' => $accountNumber
				),
				'fields' => array(
					'id',
					'account_number',
					'name'
				),
				'chains' => array(
					'CustomerCarrier' => array(
						'contain' => array(),
						'fields' => array(
							'id',
							'carrier_number',
							'carrier_name',
							'carrier_type',
							'is_active',
							'claim_number'
						)
					)
				)
			));
			
			// Lookup the customer carrier balances
			if ($results !== false)
			{
				usort($results['CustomerCarrier'], array($this->CustomerCarrier, 'sortCarriers'));
				
				foreach ($results['CustomerCarrier'] as $key => $row)
				{
					$transaction = $this->CustomerCarrier->getCarrierBalance($accountNumber, $row['carrier_number']);
					
					if ($transaction !== false)
					{
						if ($transaction['Transaction']['carrier_balance_due'] == 0)
						{
							//unset($results['CustomerCarrier'][$key]);
							//continue;
						}
						
						$results['CustomerCarrier'][$key]['Transaction'] = $transaction['Transaction'];
					}
				}
			}
			
			$this->set(compact('results'));
		}
	}
?>