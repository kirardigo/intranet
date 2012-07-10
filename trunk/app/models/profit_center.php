<?php
	class ProfitCenter extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05AX';
		
		var $actsAs = array(
			'Indexable',
			'Defraggable',
			'Lockable'
		);
		
		/**
		 * Retrieves and consumes (so use sparingly!) the next available account number for a profit center.
		 * @param string $profitCenterNumber The profit center to get the next available account number for.
		 * @return string The next account number to use, or false if one couldn't be assigned (i.e. the profit center didn't 
		 * exist or we've hit the max available for that profit center).
		 */
		function nextFreeAccountNumber($profitCenterNumber)
		{
			//grab the id of the profit center
			$id = $this->field('id', array('profit_center_number' => $profitCenterNumber));
			$account = false;
			
			//if we can't find it there's nothing to do
			if ($id === false)
			{
				return false;
			}
			
			//acquire a record lock
			if ($this->lock($id))
			{
				try
				{
					//pull the last used and highest available account numbers for the profit center
					$data = $this->find('first', array(
						'fields' => array('highest_available_account_number', 'last_account_number_used'), 
						'conditions' => array('id' => $id),
						'contain' => array()
					));
					
					//figure out the next number to use
					$next = (int)substr($data['ProfitCenter']['last_account_number_used'], 1) + 1;
					$highest = (int)substr($data['ProfitCenter']['highest_available_account_number'], 1);
					
					//we can only assign a new account if the next number is within the bounds of the highest available number
					if ($next <= $highest)
					{
						//put the prefix letter back on the account number and left pad the number with zeros
						$account = substr($data['ProfitCenter']['last_account_number_used'], 0, 1) . str_pad($next, 5, '0', STR_PAD_LEFT);
					
						//update the profit center to show that we consumed the next account number
						$this->save(array('ProfitCenter' => array(
							'id' => $id,
							'last_account_number_used' => $account
						)));
					}
					
					//release the record lock
					$this->unlock($id);
				}
				catch (Exception $ex)
				{
					$this->unlock($id);
					return false;
				}
			}
			
			//return the next free number if we have one
			return $account;
		}
	}
?>