<?php
	class TransactionType extends AppModel
	{
		/**
		 * Get the adjusted amount for calculations based on transaction type.
		 * @param float $amount The amount to adjust.
		 * @param int $transactionTypeRecord The applicable transaction type record.
		 * @return float The adjusted amount.
		 */
		function getAdjustedAmount($amount, $transactionTypeRecord)
		{
			if ($transactionTypeRecord == null)
			{
				return $amount;
			}
			
			$isAmountSubtracted = $transactionTypeRecord['TransactionType']['is_amount_subtracted'];
			return ($isAmountSubtracted) ? $amount * -1 : $amount;
		}
	}
?>