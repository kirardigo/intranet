<?php
	function field($value, $addDelimiter = true)
	{
		$value = str_replace('"', '""', $value);
		echo "\"{$value}\"" . ($addDelimiter ? ',' : '');
	}
	
	function newline()
	{
		echo "\r\n";
	}
	
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="transactions.csv";');
	
	field('PCtr');
	field('Acct#');
	field('Invoice#');
	field('TCN');
	field('DOS');
	field('Carr');
	field('Type');
	field('G/L Description');
	field('Inven#');
	field('Inventory Description');
	field('HCPC');
	field('R/P');
	field('Amount');
	field('Chg');
	field('Pmt');
	field('Crd');
	field('COGS BU');
	field('COGS');
	field('COGS Detail');
	
	newline();
	
	$previousAccount = '';
	$previousInvoice = '';
				
	foreach ($transactions as $transaction)
	{
		$isSubtracted = $transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['is_amount_subtracted'];
		$amount = $isSubtracted ? number_format(h($transaction['Transaction']['amount']) * -1, 2) : number_format(h($transaction['Transaction']['amount']), 2);
		
		$cogs = '';
		$total = '';
		$buTotal = '';
		$detail = '';
		
		if ($transaction['Transaction']['account_number'] != $previousAccount || $transaction['Transaction']['invoice_number'] != $previousInvoice)
		{
			$cogs = unserialize($transaction['Transaction']['cost_of_goods_sold']);
				
			if (is_array($cogs))
			{
				$total = isset($cogs['total']) ? round($cogs['total'], 2) : '';
				$buTotal = isset($cogs['buTotal']) ? round($cogs['buTotal'], 2) : '';
				unset($cogs['total']);
				unset($cogs['buTotal']);
				
				foreach ($cogs as $cog)
				{
					$detail .= '(' . $cog['CostOfGoodsSold']['manufacturer_code'] . ': ' . $cog['CostOfGoodsSold']['manufacturer_invoice_amount'] . ') ';
				}
			} 
		}
		
		field($transaction['Transaction']['profit_center_number']);
		field($transaction['Transaction']['account_number']);
		field($transaction['Transaction']['invoice_number']);
		field($transaction['Transaction']['transaction_control_number']);
		field(formatDate($transaction['Transaction']['transaction_date_of_service']));
		field($transaction['Transaction']['carrier_number']);
		field($transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['description']);
		field($transaction['Transaction']['general_ledger_description']);
		field($transaction['Transaction']['inventory_number']);
		field($transaction['Transaction']['inventory_description']);
		field($transaction['Transaction']['healthcare_procedure_code']);
		field($transaction['Transaction']['rental_or_purchase']);
		field($amount);
		field($transaction['Transaction']['transaction_type'] == $chargeType ? $amount : '');
		field($transaction['Transaction']['transaction_type'] == $paymentType ? $amount : '');
		field($transaction['Transaction']['transaction_type'] == $creditType ? $amount : '');
		field($buTotal);
		field($total);
		field($detail);
		
		$previousAccount = $transaction['Transaction']['account_number'];
		$previousInvoice = $transaction['Transaction']['invoice_number'];
		
		newline();
	}
?>