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
header('Content-Disposition: attachment; filename="quotation.csv";');

field('S');
field('Acct #');
field('Invoice #');
field('TCN #');
field('Name');
field('Prog');
field('Prog Name');
field('Group Code');
field('Fac Type');
field('RRRH');
field('Staff');
field('Type');
field('MOB');
field('MFG');
field('WIP Description');
field('Quote');
field('Needs Quote');
field('Eval Date');
field('Quote CCS');
field('RTS Days');
field('Quote Date');
field('CCS Days');
field('Quote Print');
field('Amount');
field('Invoice Amount');

newline();

foreach ($results as $row)
{
	$invoiceAmount = ($row['Order']['invoice_number'] == '') ? '' : number_format($row['Invoice']['amount'], 2);
	
	field($row['Order']['status']);
	field($row['Order']['account_number']);
	field($row['Order']['invoice_number']);
	field($row['Order']['transaction_control_number']);
	field(substr($row['Order']['client_name'], 0, 20));
	field($row['Order']['program_referral_number']);
	field(ifset($row['Order']['program_referral_name']));
	field(ifset($row['AaaReferral']['group_code']));
	field(ifset($row['AaaReferral']['facility_type']));
	field($row['Order']['rehab_hospital']);
	field($row['Order']['staff_user_id']);
	field($row['Order']['order_type']);
	field($row['Order']['mobility_choice']);
	field($row['Order']['manufacturer_model']);
	field(substr($row['Order']['wip_description'], 0, 20));
	field($row['Order']['quote']);
	field($row['Order']['needs_quote_date']);
	field($row['Order']['evaluation_date']);
	field($row['Order']['quote_client_care_specialist_date']);
	field(ifset($row['Order']['rts_days']));
	field($row['Order']['quote_completed_date']);
	field(ifset($row['Order']['ccs_days']));
	field($row['Order']['quote_printed_date']);
	field(number_format($row['Order']['grand_total'], 2));
	field($invoiceAmount);
	newline();
}

?>