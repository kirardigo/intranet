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
header('Content-Disposition: attachment; filename="aaa_calls.csv";');

field('AAA#');
field('Precall Goal');
field('Call Date');
field('Thank you');
field('Call Type');
field('Staff');
field('Name');
field('PCtr');
field('HCare Sls');
field('HCare Mkt');
field('Next Call');
field('Completed');

newline();

foreach ($records as $row)
{
	field($row['AaaCall']['aaa_number']);
	field(ifset($row['AaaCall']['precall_goal']));
	field(ifset($row['AaaCall']['call_date']));
	field(($row['AaaCall']['follow_up_thank_you'] == 1 ? ' Yes' : ' No'));
	field($row['AaaCall']['call_type']);
	field($row['AaaCall']['sales_staff_initials']);
	field(ifset($row['AaaCall']['facility_name']));
	field(ifset($row['AaaCall']['profit_center_number']));
	field(ifset($row['AaaCall']['homecare_salesman']));
	field(ifset($row['AaaCall']['homecare_market_code']));
	field($row['AaaCall']['next_call_date']);
	field($row['AaaCall']['followup_complete_date']);
	
	newline();
}

?>