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
header('Content-Disposition: attachment; filename="picklist.csv";');

field('Inventory#');
field('Ord Qty');
field('Description');
field('Updated');

newline();

foreach ($records as $row)
{
	field($row['Inventory']['inventory_number']);
	field($row['Inventory']['description']);
	field($row['Inventory']['customary_rate_or_retail_sales_rate']);
	field($row['Inventory']['medicare_allowable_sales_rate']);
	field($row['Inventory']['last_price_date']);
	
	newline();
}

?>