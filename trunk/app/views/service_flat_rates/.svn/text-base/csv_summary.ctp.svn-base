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
header('Content-Disposition: attachment; filename="service_flat_rates.csv";');

field('HCPC Code');
field('Description');
field('MRS Rate');
field('CMS Rate');

newline();

foreach ($records as $row)
{
	field($row['ServiceFlatRate']['hcpc_code']);
	field($row['ServiceFlatRate']['description']);
	field($row['ServiceFlatRate']['mrs_flat_rate']);
	field($row['ServiceFlatRate']['cms_flat_rate']);
	
	newline();
}

?>