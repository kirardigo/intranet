<?php
	class EligibilityRequest extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'ZirmedRequest';
		
		/**
		 * Submits an eVOB request to Zirmed via the Miller's zirmed website.
		 * @param string $accountNumber The account number of the customer the request is for.
		 * @param string $carrierNumber The carrier number to check eligibility for.
		 * @param string $tcnNumber The transaction control number.
		 * @param string $tcnFile The transaction control file.
		 * @param string $requestedBy The username of the MRS user performing the request.
		 * @param string $serviceDate The date of service for the request. Omit for the current date.
		 * @return mixed The Zirmed response ID if successful, false otherwise.
		 */
		function submitInquiry($accountNumber, $carrierNumber, $tcnNumber, $tcnFile, $requestedBy, $serviceDate = null)
		{
			//grab the customer record
			$customer = ClassRegistry::init('Customer')->find('first', array(
				'fields' => array('name', 'profit_center_number'),
				'conditions' => array('account_number' => $accountNumber),
				'contain' => array()
			));
			
			if ($customer === false)
			{
				return false;
			}
			
			//grab the profit center
			$profitCenter = ClassRegistry::init('ProfitCenter')->find('first', array(
				'fields' => array('profit_center_number', 'tax_identification_number', 'npi_number'),
				'conditions' => array('profit_center_number' => $customer['Customer']['profit_center_number']),
				'contain' => array()
			));
			
			if ($profitCenter === false)
			{
				return false;
			}
			 
			//grab the carrier
			$carrier = ClassRegistry::init('Carrier')->find('first', array(
				'fields' => array(
					'name', 
					'zirmed_evob_payor_identification_number', 
					'provider_number_for_profit_center_010', 
					'provider_number_for_profit_center_020', 
					'provider_number_for_profit_center_021',
					'provider_number_for_profit_center_050',
					'provider_number_for_profit_center_060'
				),
				'conditions' => array('carrier_number' => $carrierNumber),
				'contain' => array()
			));
			
			if ($carrier === false)
			{
				return false;
			}
			
			//grab the customer carrier
			$customerCarrier = ClassRegistry::init('CustomerCarrier')->find('first', array(
				'fields' => array('insuree_relationship', 'claim_number'),
				'conditions' => array('account_number' => $accountNumber, 'carrier_number' => $carrierNumber),
				'contain' => array()
			));
			
			if ($customerCarrier === false)
			{
				return false;
			}
			
			//grab the customer billing
			$customerBilling = ClassRegistry::init('CustomerBilling')->find('first', array(
				'fields' => array('date_of_birth'),
				'conditions' => array('account_number' => $accountNumber),
				'contain' => array()
			));
			
			if ($customerBilling === false)
			{
				return false;
			}
			
			//rip apart the name so we can submit it as first and last
			$name = array_map('trim', explode(',', $customer['Customer']['name']));
			
			//save some typing later
			$relationship = $customerCarrier['CustomerCarrier']['insuree_relationship'];
			
			//grab zirmed settings we need to submit the request
			$settings = ClassRegistry::init('Setting')->get(array('zirmed_provider_name', 'zirmed_request_url'));
			
			//craft the request record
			$data = array('EligibilityRequest' => array(
				'provider_name' => $settings['zirmed_provider_name'],
				'payer_name' => $carrier['Carrier']['name'],
				'subscriber_member_id' => $customerCarrier['CustomerCarrier']['claim_number'],
				'patient_first_name' => $name[1],
				'patient_last_name' => $name[0],
				'patient_date_of_birth' => $customerBilling['CustomerBilling']['date_of_birth'],
				'patient_relationship_to_insured' => $relationship == 'S' ? 'S' : ($relationship == 'P' ? '01' : ($relationship == 'C' ? '19' : '34')),
				'other_provider_name' => '',
				'date_of_service' => $serviceDate !== null ? databaseDate($serviceDate) : date('Y-m-d'),
				'service_type' => '',
				'provider_inquiry_reference_number' => $accountNumber . '~' . $carrierNumber,
				'payer_id' => $carrier['Carrier']['zirmed_evob_payor_identification_number'],
				'account_break_out_value' => '',
				'simple_file_format_version' => '1',
				'provider_entity_id' => $requestedBy,
				'provider_first_name' => '',
				'provider_middle_name' => '',
				'provider_tax_id' => $profitCenter['ProfitCenter']['tax_identification_number'],
				'provider_payer_assigned_number' => $profitCenter['ProfitCenter']['npi_number'],
				'provider_other_identifier' => $carrier['Carrier']["provider_number_for_profit_center_{$profitCenter['ProfitCenter']['profit_center_number']}"],
				'processing_status' => '',
				'response_id' => '',
				'tcn_number' => $tcnNumber,
				'tcn_file' => $tcnFile
			));
			
			$this->create();
			
			//save the request
			if ($this->save($data) === false)
			{
				return false;
			}
			
			//fire off the zirmed http request that will use the request record to contact zirmed
			if (@file_get_contents($settings['zirmed_request_url'] . $this->id, 'r') === false)
			{
				return false;
			}
			
			//go read the record we saved since the zirmed site will have updated the record with the response ID if successful
			$response = $this->find('first', array(
				'conditions' => array('id' => $this->id),
				'contain' => array()
			));
			
			//make sure the request was processed ok
			if ($response['EligibilityRequest']['processing_status'] != 'Y')
			{
				return false;
			}
			
			//return the response ID from the request
			return $response['EligibilityRequest']['response_id'];
		}
	}
?>