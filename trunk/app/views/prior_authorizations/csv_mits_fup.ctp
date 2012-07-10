<?php
Configure::write('debug', 0);

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
header('Content-Disposition: attachment; filename="prior_auths_mits.csv";');

field('Carr PA#');
field('Billing#');
field('Name');
field('DOB');
field('Acct#');
field('TCN#');
field('Desc');
field('Status');
field('Requested');
field('Request Amt');
field('MITS Request Response');
field('Approved');
field('Approve Amt');
field('Denied');
field('Appealed');
field('Activated');

newline();

foreach ($records as $row)
{
	field($row['PriorAuthorization']['carrier_authorization_number']);
	field($row['CustomerCarrier']['claim_number']);
	field($row['Customer']['name']);
	field(formatDate($row['CustomerBilling']['date_of_birth']));
	field($row['PriorAuthorization']['account_number']);
	field($row['PriorAuthorization']['transaction_control_number']);
	field($row['PriorAuthorization']['description']);
	field(ifset($statuses[$row['PriorAuthorization']['status']]));
	field(formatDate($row['PriorAuthorization']['date_requested']));
	field($row['PriorAuthorization']['amount_requested']);
	field(formatDate($row['PriorAuthorization']['mits_request_response_date']));
	field(formatDate($row['PriorAuthorization']['date_approved']));
	field($row['PriorAuthorization']['amount_approved']);
	field(formatDate($row['PriorAuthorization']['date_denied']));
	field(formatDate($row['PriorAuthorization']['appeals_date']));
	field(formatDate($row['PriorAuthorization']['date_activated']));
	
	newline();
}

?>