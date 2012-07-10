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
header('Content-Disposition: attachment; filename="inventory_bundles.csv";');

field('Master#');
field('Description');
field('Seq');
field('Item#');
field('Description');

newline();

foreach ($records as $row)
{
	field($row['InventoryBundle']['inventory_number_master']);
	field($descriptions[$row['InventoryBundle']['inventory_number_master']]);
	field($row['InventoryBundle']['invoicing_sequence']);
	field($row['InventoryBundle']['inventory_number_item']);
	field($descriptions[$row['InventoryBundle']['inventory_number_item']]);
	
	newline();
}

?>