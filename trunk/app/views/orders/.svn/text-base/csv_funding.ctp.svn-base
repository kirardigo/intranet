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
header('Content-Disposition: attachment; filename="funding.csv";');

field('PCtr');
field('Acct#');
field('TCN#');
field('RRRH');
field('Staff');
field('Name');
field('Auth');
field('Description');
field('WIP');
field('Type');
field('Total');
field('EFN FUP');
field('C1');
field('Code');
field('Group');
field('C2');
field('Code');
field('C3');
field('Code');
field('Quoted');
field('Fund Req');
field('Fund Aprv');
field('Days');
field('Denied');
field('Appealed');
field('Invoiced');
field('Claims');
field('VOB');
field('Ini');
field('Date');
field('Auth');
field('Ini');
field('Date');

newline();

foreach ($results as $row)
{
	field($row['Order']['profit_center_number']);
	field($row['Order']['account_number']);
	field($row['Order']['transaction_control_number']);
	field($row['Order']['rehab_hospital']);
	field($row['Order']['staff_user_id']);
	field(substr($row['Order']['client_name'], 0, 20));
	field($row['Order']['mrs_auth_number']);
	field($row['Order']['wip_description']);
	field($row['Order']['work_in_process']);
	field($row['Order']['order_type']);
	field($row['Order']['grand_total']);
	field(formatDate($row['Order']['oldest_efn_followup_date']));
	field($row['Order']['carrier_1_type']);
	field($row['Order']['carrier_1_code']);
	field($row['Order']['carrier_1_group_code']);
	field($row['Order']['carrier_2_type']);
	field($row['Order']['carrier_2_code']);
	field($row['Order']['carrier_3_type']);
	field($row['Order']['carrier_3_code']);
	field($row['Order']['quote_completed_date']);
	field($row['Order']['funding_pending_date']);
	field($row['Order']['funding_approved_date']);
	field(ifset($row['Order']['funding_days']));
	field($row['Order']['denied_date']);
	field($row['Order']['appealed_date']);
	field($row['Order']['invoiced_date']);
	field($row['Order']['claims_status']);
	field($row['Order']['verification_of_benefits_status']);
	field($row['Order']['verification_of_benefits_initials']);
	field($row['Order']['verification_of_benefits_date']);
	field($row['Order']['authorization_status']);
	field($row['Order']['authorization_initials']);
	field($row['Order']['authorization_date']);
	newline();
}

?>