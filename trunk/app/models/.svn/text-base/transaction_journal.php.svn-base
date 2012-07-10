<?php
	class TransactionJournal extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BV';
		
		var $actsAs = array('Indexable');
		
		var $belongsTo = array(
			'GeneralLedger' => array(
				'foreignKey' => array('field' => 'general_ledger_code', 'parent_field' => 'general_ledger_code')
			)
		);
		
		/**
		 * Get the month-to-date net revenue.
		 * @param string $profitCenterNumber The number of the selected profit center.
		 * @param string $departmentCode The department code or null to use the default.
		 * @return float The month-to-date net revenue value.
		 */
		function getMonthToDateNetRevenue($profitCenterNumber, $departmentCode = null)
		{
			if ($departmentCode == null)
			{
				if ($profitCenterNumber == '021')
				{
					$departmentCode = 'A';
				}
				else if ($profitCenterNumber == '070')
				{
					$departmentCode = 'T';
				}
				else
				{
					$departmentCode = 'R';
				}
			}
			
			// Find all matching records in the transaction journal
			$results = $this->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'profit_center_number' => $profitCenterNumber,
					'department_code' => $departmentCode
				)
			));
			
			if ($results === false)
			{
				return false;
			}
			
			$subTotal = 0;
			
			// We only total the transaction amounts for certain inventory group codes
			foreach ($results as $key => $row)
			{
				$groupCode = $row['TransactionJournal']['inventory_group_code'];
				
				if ($groupCode >= '4100' && $groupCode <= '4199')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
				else if ($groupCode >= '4200' && $groupCode <= '4299')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
				else if ($groupCode >= '1200' && $groupCode <= '1299')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
				else if ($groupCode >= '4400' && $groupCode <= '4499')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
				else if ($groupCode == '6140')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
			}
			
			return $subTotal;
		}
		
		/**
		 * Get the month-to-date credits.
		 * @param string $profitCenterNumber The number of the selected profit center.
		 * @param string $departmentCode The department code or null to use the default.
		 * @return float The month-to-date credit value.
		 */
		function getMonthToDateCredits($profitCenterNumber, $departmentCode = null)
		{
			if ($departmentCode == null)
			{
				if ($profitCenterNumber == '021')
				{
					$departmentCode = 'A';
				}
				else if ($profitCenterNumber == '070')
				{
					$departmentCode = 'T';
				}
				else
				{
					$departmentCode = 'R';
				}
			}
			
			// Find all matching records in the transaction journal
			$results = $this->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'profit_center_number' => $profitCenterNumber,
					'department_code' => $departmentCode
				)
			));
			
			if ($results === false)
			{
				return false;
			}
			
			$subTotal = 0;
			
			$settingModel = ClassRegistry::init('Setting');
			$creditType = $settingModel->get('credit_transaction_type_id');
			
			// We only total the transaction amounts for certain inventory group codes
			foreach ($results as $key => $row)
			{
				$groupCode = $row['TransactionJournal']['inventory_group_code'];
				
				if ($groupCode >= '4100' && $groupCode <= '4199' && $row['TransactionJournal']['transaction_type'] == $creditType)
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
				else if ($groupCode == '6140')
				{
					$subTotal += $row['TransactionJournal']['amount'];
				}
			}
			
			return $subTotal;
		}
	}
?>