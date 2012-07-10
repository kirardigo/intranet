<?php
	class EligibilityRequestsController extends AppController
	{
		var $uses = array('EligibilityRequest', 'EligibilityResponse', 'CustomerCarrier', 'Setting');
		
		/**
		 * Module to perform eVOB functionality on a particular customer.
		 */
		function module_forCustomer($accountNumber)
		{
			//if customer inquiry is asking whether a customer should show the VOB tab, the answer is always yes
			if (isset($this->params['named']['checkForData']))
			{
				return true;
			}
			
			$carriers = $this->CustomerCarrier->find('all', array(
				'fields' => array('CustomerCarrier.carrier_number', 'CustomerCarrier.carrier_name', 'CustomerCarrier.last_zirmed_electronic_vob_date'),
				'conditions' => array(
					'CustomerCarrier.account_number' => $accountNumber,
					'CustomerCarrier.is_active' => true,
					'Carrier.group_code' => array('MED', 'WEL', 'INS')
				),
				'contain' => array('Carrier'),
				'order' => 'CustomerCarrier.carrier_number'
			));
			
			$this->set(array(
				'accountNumber' => $accountNumber,
				'carriers' => $carriers,
				'url' => $this->Setting->get('zirmed_eligibility_url')
			));
		}
		
		/**
		 * Submit an eVOB request for the specified account and carrier combination.
		 * @param string $accountNumber The account number to check.
		 * @param string $carrierNumber The carrier number to check.
		 */
		function json_submitRequest($accountNumber, $carrierNumber)
		{
			//submit the request
			$responseID = $this->EligibilityRequest->submitInquiry($accountNumber, $carrierNumber, '', '', User::current());
			
			if ($responseID === false)
			{
				$data = array('EligibilityResponse' => array('id' => false));
			}
			else
			{
				//if we got a response (accepted or rejected), pull the data so we can display summary info for the user
				$data = $this->EligibilityResponse->find('first', array(
					'fields' => array('status', 'rejected_reason', 'followup_action'),
					'conditions' => array('response_id' => $responseID),
					'contain' => array()
				));				
			}
			
			$this->set('json', $data);
		}
	}
?>