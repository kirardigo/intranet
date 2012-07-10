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
	header('Content-Disposition: attachment; filename="invoices.csv";');
	
	field('PCtr');
	field('Acct#');
	field('D');
	field('TCN');
	field('Invoice#');
	field('R/P');
	field('DOS');
	field('BDT');
	field('L1');
	field('L1 INI');
	field('L1 Date');
	field('Days');
	field('L1 Carr');
	field('L1 Amt');
	field('Team');
	field('CLFUP');
	field('Carr1');
	field('Carr1_$');
	field('Carr2');
	field('Carr2_$');
	field('Carr3');
	field('Carr3_$');
	field('Gross Chg');
	field('Payments');
	field('Credits');
	field('Bal Due');
	field('Reimb Remarks');
	
	newline();
	
	foreach ($invoices as $invoice)
	{		
		field($invoice['Invoice']['profit_center_number']);
		field($invoice['Invoice']['account_number']);
		field($invoice['Invoice']['department_code']);
		field($invoice['Invoice']['transaction_control_number']);
		field($invoice['Invoice']['invoice_number']);
		field($invoice['Invoice']['rental_or_purchase']);
		field(formatDate($invoice['Invoice']['date_of_service']));
		field(formatDate($invoice['Invoice']['billing_date']));
		field($invoice['Invoice']['line_1_status']);
		field($invoice['Invoice']['line_1_initials']);
		field(formatDate($invoice['Invoice']['line_1_date']));
		field(weekdayDiff($invoice['Invoice']['line_1_date'], date('Y-m-d')));
		field($invoice['Invoice']['line_1_carrier_code']);
		field($invoice['Invoice']['line_1_amount']);
		field($invoice['Invoice']['team']);
		field(formatDate($invoice['Invoice']['efn_followup_date']));
		field($invoice['Invoice']['carrier_1_code']);
		field(number_format($invoice['Invoice']['carrier_1_balance'], 2));
		field($invoice['Invoice']['carrier_2_code']);
		field($invoice['Invoice']['carrier_2_balance'] != null ? number_format($invoice['Invoice']['carrier_2_balance'], 2) : '');
		field($invoice['Invoice']['carrier_3_code']);
		field($invoice['Invoice']['carrier_3_balance'] != null ? number_format($invoice['Invoice']['carrier_3_balance'], 2) : '');
		field($invoice['Invoice']['amount'] != null ? number_format($invoice['Invoice']['amount'], 2) : '');
		field($invoice['Invoice']['payments'] != null ? number_format($invoice['Invoice']['payments'], 2) : '');
		field($invoice['Invoice']['credits'] != null ? number_format($invoice['Invoice']['credits'], 2) : '');
		field($invoice['Invoice']['account_balance'] != null ? number_format($invoice['Invoice']['account_balance'], 2) : '');
		field($invoice['Invoice']['reimbursement_memo']);
		
		newline();
	}
?>