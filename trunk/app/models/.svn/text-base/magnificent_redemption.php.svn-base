<?php
	class MagnificentRedemption extends AppModel
	{
		var $belongsTo = array('MagnificentRedemptionOption');
		
		var $actsAs = array('FormatDates');
		
		var $validate = array(
			'magnificent_redemption_option_id' => array(
				'required' => array(
					'rule' => array('chooseOptionOrDonation'),
					'message' => 'You must choose an option or specify a donation.'
				)
			),
			'value' => array(
				'available' => array(
					'rule' => array('limitToAvailable'),
					'message' => 'You do not have this many Magnificents available.'
				)
			)
		);
		
		/**
		 * Validation to ensure form is filled out correctly.
		 */
		function chooseOptionOrDonation($check)
		{
			if ($this->data[$this->alias]['magnificent_redemption_option_id'] == '' &&
				$this->data[$this->alias]['value'] == '')
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * Validation to ensure that the value does not exceed the available amount.
		 */
		function limitToAvailable($check)
		{
			if ($this->data[$this->alias]['value'] > $this->availableMagnificents($this->data[$this->alias]['recipient_user']))
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * Find the number of used redemptions for a user.
		 * @param string $user The user to query for.
		 */
		function usedRedemptions($user)
		{
			$data = $this->find('first', array(
				'contain' => array(),
				'fields' => array('SUM(value) as used'),
				'conditions' => array('recipient_user' => $user)
			));
			
			return is_numeric($data[0]['used']) ? $data[0]['used'] : 0;
		}
		
		/**
		 * Get the available magnificent amount.
		 * @param string $user The user to query for.
		 */
		function availableMagnificents($user)
		{
			$magnificentModel = ClassRegistry::init('Magnificent');
			return $magnificentModel->totalMagnificents($user) - $this->usedRedemptions($user);
		}
	}
?>