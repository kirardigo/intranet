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
header('Content-Disposition: attachment; filename="efn.csv";');

field('Acct#');
field('TCN#');
field('Name');
field('Memo');
field('Remarks');
field('Action');
field('D');
field('FUP Date');
field('Invoice#');
field('FUP Ini');
field('Priority');

newline();

foreach ($records as $row)
{
	field($row['FileNote']['account_number']);
	field($row['FileNote']['transaction_control_number']);
	field($row['FileNote']['name']);
	field($row['FileNote']['memo']);
	field($row['FileNote']['remarks']);
	field($row['FileNote']['action_code']);
	field($row['FileNote']['department_code']);
	field($row['FileNote']['followup_date']);
	field($row['FileNote']['invoice_number']);
	field($row['FileNote']['followup_initials']);
	field($row['FileNote']['priority_code']);
	
	newline();
}

?>