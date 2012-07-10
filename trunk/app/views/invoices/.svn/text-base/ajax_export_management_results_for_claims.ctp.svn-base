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
	header('Content-Disposition: attachment; filename="btclaims.csv";');
	
	field('Acct#');
	field('Invoice#');
	field('TCN');
	field('L1 Date');
	field('DOS');
	field('Bal Due');
	field('L1');
	field('L1 Amt');
	field('CLFUP');
	field('Remarks');
	field('L1 Ini');
	field('Team');
	
	newline();
	
	foreach ($invoices as $invoice)
	{		
		field($invoice['Invoice']['account_number']);
		field($invoice['Invoice']['invoice_number']);
		field($invoice['Invoice']['transaction_control_number']);
		field(formatDate($invoice['Invoice']['line_1_date']));
		field(formatDate($invoice['Invoice']['date_of_service']));
		field($invoice['Invoice']['account_balance'] != null ? number_format($invoice['Invoice']['account_balance'], 2) : '');
		field($invoice['Invoice']['line_1_status']);
		field($invoice['Invoice']['line_1_amount']);
		field(formatDate($invoice['Invoice']['efn_followup_date']));
		field($invoice['Invoice']['reimbursement_memo']);
		field($invoice['Invoice']['line_1_initials']);
		field($invoice['Invoice']['team']);
		
		newline();
	}
?>