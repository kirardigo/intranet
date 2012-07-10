<?php
	class CustomerBilling extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BQ';
		
		var $actsAs = array(
			'FormatDates', 
			'Indexable',
			'Migratable' => array('key' => 'account_number'),
			'CustomerAccountAuditable'
		);
		
		var $validate = array(
			'billing_name' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The billing name must be specified.'
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
	}
?>