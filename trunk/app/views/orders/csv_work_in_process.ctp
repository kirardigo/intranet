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
header('Content-Disposition: attachment; filename="wip.csv";');

field('Acct #');
field('TCN');
field('Client');
field('Prog');
field('LTCF');
field('Type');
field('Description');
field('FIP');
field('WIP');
field('Approved');
field('Days');
field('Auth Exp');
field('Ordered');
field('Received');
field('OK');
field('WIP Amt');
field('Scheduled');
field('Completed');
field('Invoice');
field('Invoice Amt');
field('Variance');
field('Sls_conpl');
newline();

foreach ($results as $row)
{
	$invoiceAmount = ($row['Order']['invoice_number'] != '') ? number_format($row['Invoice']['amount'], 2) : '';
	$variance = ($row['Order']['invoice_number'] != '') ? number_format($row['Invoice']['amount'] - $row['Order']['wip_amount'], 2) : '';
	
	field($row['Order']['account_number']);
	field($row['Order']['transaction_control_number']);
	field(substr($row['Order']['client_name'], 0, 20));
	field($row['Order']['program_referral_number']);
	field($row['Order']['long_term_care_facility_number']);
	field($row['Order']['order_type']);
	field(substr($row['Order']['wip_description'], 0, 20));
	field($row['Order']['is_foam_in_place'] ? 'Y' : 'N');
	field($row['Order']['work_in_process']);
	field($row['Order']['funding_approved_date']);
	field(ifset($row['Order']['days_old']));
	field($row['PriorAuthorization']['date_expiration']);
	field($row['Order']['equipment_ordered_date']);
	field($row['Order']['equipment_received_date']);
	field($row['Order']['is_ok_to_schedule'] ? 'Y' : 'N');
	field(number_format($row['Order']['wip_amount'], 2));
	field($row['Order']['work_scheduled_date']);
	field($row['Order']['work_completed_date']);
	field($row['Order']['invoice_number']);
	field($invoiceAmount);
	field($variance);
	field($row['Order']['staff_user_id']);
	newline();
}

?>