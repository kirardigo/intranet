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
header('Content-Disposition: attachment; filename="prior_auths.csv";');

field('Carr PA#');
field('Acct#');
field('Name');
field('Carr#');
field('Claim#');
field('D');
field('TCN#');
field('Invoice#');
field('DOS');
field('Desc');
field('Type');
field('Status');
field('Renewal');
field('Appealed');
field('Requested');
field('Days');
field('Request Amt');
field('Approved');
field('Approve Amt');
field('PA#');
field('Activated');
field('Expiration');
field('Start Date');
field('End Date');
field('CMN');
field('Months');
field('Insulin Dpdt');
field('Test / Day');
field('Denied');
field('Appealed');
field('Appeal Amt');
field('Appeal Note');

newline();

foreach ($records as $row)
{
	$daysDiff = '';
			
	if ($row['PriorAuthorization']['date_requested'] != '')
	{
		if ($row['PriorAuthorization']['date_approved'] != '')
		{
			$daysDiff = weekdayDiff($row['PriorAuthorization']['date_requested'], $row['PriorAuthorization']['date_approved']);
		}
		else if ($row['PriorAuthorization']['date_denied'] != '' && $row['PriorAuthorization']['date_appealed'] == '')
		{
			$daysDiff = weekdayDiff($row['PriorAuthorization']['date_requested'], $row['PriorAuthorization']['date_denied']);
		}
		else if ($row['PriorAuthorization']['date_denied'] != '' && $row['PriorAuthorization']['date_appealed'] > $row['PriorAuthorization']['date_denied'])
		{
			$daysDiff = weekdayDiff($row['PriorAuthorization']['date_requested'], databaseDate('today'));
		}
		else if ($row['PriorAuthorization']['date_denied'] != '' && $row['PriorAuthorization']['date_appealed'] < $row['PriorAuthorization']['date_denied'])
		{
			$daysDiff = weekdayDiff($row['PriorAuthorization']['date_requested'], databaseDate('today'));
		}
		else if ($row['PriorAuthorization']['date_approved'] == '' && $row['PriorAuthorization']['date_denied'] == '')
		{
			$daysDiff = weekdayDiff($row['PriorAuthorization']['date_requested'], databaseDate('today'));
		}
	}
	
	field($row['PriorAuthorization']['carrier_authorization_number']);
	field($row['PriorAuthorization']['account_number']);
	field($row['Customer']['name']);
	field($row['PriorAuthorization']['carrier_number']);
	field($row['CustomerCarrier']['claim_number']);
	field($row['PriorAuthorization']['department_code']);
	field($row['PriorAuthorization']['transaction_control_number']);
	field($row['PriorAuthorization']['invoice_number']);
	field(formatDate($row['PriorAuthorization']['date_of_service']));
	field($row['PriorAuthorization']['description']);
	field(ifset($types[$row['PriorAuthorization']['type']]));
	field(ifset($statuses[$row['PriorAuthorization']['status']]));
	field(($row['PriorAuthorization']['is_renewal'] ? 'Y' : 'N'));
	field(($row['PriorAuthorization']['is_appealed'] ? 'Y' : 'N'));
	field(formatDate($row['PriorAuthorization']['date_requested']));
	field($daysDiff);
	field($row['PriorAuthorization']['amount_requested']);
	field(formatDate($row['PriorAuthorization']['date_approved']));
	field($row['PriorAuthorization']['amount_approved']);
	field($row['PriorAuthorization']['carrier_authorization_number']);
	field(formatDate($row['PriorAuthorization']['date_activated']));
	field(formatDate($row['PriorAuthorization']['date_expiration']));
	field(formatDate($row['PriorAuthorization']['authorization_start_date']));
	field(formatDate($row['PriorAuthorization']['authorization_end_date']));
	field($row['PriorAuthorization']['authorization_cmn']);
	field($row['PriorAuthorization']['number_of_months']);
	field(($row['PriorAuthorization']['is_insulin_dependent'] ? 'Y' : 'N'));
	field($row['PriorAuthorization']['tests_per_day']);
	field(formatDate($row['PriorAuthorization']['date_denied']));
	field(formatDate($row['PriorAuthorization']['appeals_date']));
	field($row['PriorAuthorization']['appeals_amount']);
	field($row['PriorAuthorization']['appeals_note']);
	
	newline();
}

?>