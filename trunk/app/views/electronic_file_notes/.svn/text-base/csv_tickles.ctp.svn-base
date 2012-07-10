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
header('Content-Disposition: attachment; filename="efn.csv";');

field('PCtr');
field('Acct #');
field('H/R');
field('TCN');
field('Invoice #');
field('Client');
field('Memo');
field('Remarks');
field('Action');
field('Dept');
field('FUP INI');
field('Days');
newline();

foreach ($results as $row)
{
	field($row['ElectronicFileNote']['profit_center_number']);
	field($row['ElectronicFileNote']['account_number']);
	field($row['ElectronicFileNote']['transaction_control_number_type']);
	field($row['ElectronicFileNote']['transaction_control_number']);
	field($row['ElectronicFileNote']['invoice_number']);
	field($row['ElectronicFileNote']['name']);
	field($row['ElectronicFileNote']['memo']);
	field($row['ElectronicFileNote']['remarks']);
	field($row['ElectronicFileNote']['action_code']);
	field($row['ElectronicFileNote']['department_code']);
	field($row['ElectronicFileNote']['followup_initials']);
	field($row['ElectronicFileNote']['days']);
	newline();
}

?>