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
header('Content-Disposition: attachment; filename="general_ledger.csv";');

field('G/L');
field('Description');
field('Is Active');
field('Rent/Purchase');
field('Group Code');
field('Acct Code');

newline();

foreach ($records as $row)
{
	$active = $row['GeneralLedger']['is_active'] ? 'Y' : 'N';
	
	field($row['GeneralLedger']['general_ledger_code']);
	field($row['GeneralLedger']['description']);
	field($active);
	field($row['GeneralLedger']['rental_code_or_purchase_code']);
	field($row['GeneralLedger']['group_code']);
	field($row['GeneralLedger']['accounting_code']);
	
	newline();
}

?>