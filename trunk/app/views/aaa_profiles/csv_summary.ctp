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
header('Content-Disposition: attachment; filename="aaa_profiles.csv";');

field('AAA#');
field('Name');
field('D');
field('PCtr');
field('HCare Sls');
field('HCare Mkt');

newline();

foreach ($records as $row)
{
	field($row['AaaProfile']['aaa_number']);
	field(ifset($row['AaaReferral']['facility_name']));
	field($row['AaaProfile']['department_code']);
	field(ifset($row['AaaReferral']['profit_center_number']));
	field(ifset($row['AaaReferral']['homecare_salesman']));
	field(ifset($row['AaaReferral']['homecare_market_code']));
	
	newline();
}

?>