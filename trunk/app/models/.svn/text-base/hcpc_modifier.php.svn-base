<?php
	class HcpcModifier extends AppModel
	{
		var $validate = array(
			'code' => array(
				'requiredOnPrimary' => array(
					'rule' => array('codeRequiredOnPrimary'),
					'message' => 'The code must be set when the level is primary.'
				),
				'onlyAllowedOnPrimar' => array(
					'rule' => array('codeOnlyAllowedOnPrimary'),
					'message' => 'The code may only be set when level is primary.'
				),
				'distinct' => array(
					'rule' => array('codeUniqueWhenSet'),
					'message' => 'This code is already in use for another record.'
				)
			)
		);
		
		/**
		 * Ensure code is set for primary level.
		 */
		function codeRequiredOnPrimary()
		{
			if ($this->data[$this->alias]['level'] == 'P' && $this->data[$this->alias]['code'] == '')
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * Ensure code is not set for levels other than primary.
		 */
		function codeOnlyAllowedOnPrimary()
		{
			if ($this->data[$this->alias]['level'] != 'P' && $this->data[$this->alias]['code'] != '')
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * Ensure code is unique when set.
		 */
		function codeUniqueWhenSet()
		{
			if ($this->data[$this->alias]['code'] != '')
			{
				$count = $this->find('count', array(
					'contain' => array(),
					'conditions' => array(
						'code' => $this->data[$this->alias]['code'],
						'id !=' => $this->data[$this->alias]['id']
					)
				));
				
				if ($count > 0)
				{
					return false;
				}
			}
			
			return true;
		}
	}
?>