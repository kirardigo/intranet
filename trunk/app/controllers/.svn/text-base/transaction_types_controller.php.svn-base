<?php
	class TransactionTypesController extends AppController
	{
		var $uses = array('TransactionType', 'Setting');
		
		/**
		 * Looks up the transaction type that should be used for charges.
		 * The JSON will contain a single value called "code".
		 */
		function json_chargeTransactionType()
		{
			$id = $this->Setting->get('charge_transaction_type_id');
			$code = $this->TransactionType->field('code', array('id' => $id));
			
			$this->set('json', array('code' => $code));
		}
		
		/**
		 * Looks up the transaction type that should be used for charges.
		 * The JSON will contain a value called "codes" which is an array of payment transaction type codes.
		 */
		function json_paymentTransactionTypes()
		{
			//grab payment transaction types
			$paymentTransactionTypes = Set::extract('/TransactionType/code', $this->TransactionType->find('all', array(
				'fields' => array('code'),
				'conditions' => array('is_payment' => 1),
				'contain' => array()
			)));
			
			$this->set('json', array('codes' => $paymentTransactionTypes));
		}
	}
?>