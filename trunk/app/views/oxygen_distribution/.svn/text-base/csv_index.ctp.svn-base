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
header('Content-Disposition: attachment; filename="oxygen_distribution.csv";');

field('Account#');
field('Invoice#');
field('Date');
field('Dispensed By');
field('Lot Number');
field('Quantity');
field('Tank Size');

newline();

foreach ($records as $row)
{
	field($row['OxygenDistribution']['account_number']);
	field($row['OxygenDistribution']['invoice_number']);
	field(formatDate($row['OxygenDistribution']['dispensed_date']));
	field($row['OxygenDistribution']['dispensed_by']);
	field($row['OxygenDistribution']['lot_number']);
	field($row['OxygenDistribution']['quantity']);
	field($row['OxygenDistribution']['tank_size']);
		
	newline();
}

?>