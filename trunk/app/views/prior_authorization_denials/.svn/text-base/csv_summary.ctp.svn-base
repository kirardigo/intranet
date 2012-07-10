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
header('Content-Disposition: attachment; filename="prior_auth_denials.csv";');

field('Code');
field('Description');

newline();

foreach ($records as $row)
{
	field($row['PriorAuthorizationDenial']['code']);
	field($row['PriorAuthorizationDenial']['description']);
	
	newline();
}

?>