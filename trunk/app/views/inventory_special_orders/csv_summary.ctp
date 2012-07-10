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
header('Content-Disposition: attachment; filename="inventory_specials.csv";');

field('PO#');
field('PO Date');
field('MFG Inven#');
field('MFG Code');
field('MRS Inven#');
field('Description');
field('Serial#');
field('Qty');
field('UoM');
field('Cost');
field('DOP');
field('D');
field('Cond');
field('Locator');
field('Color');
field('Size');
field('Arms');
field('Rigging');
field('Wheels');
field('Accessories');
field('TCN');
field('Acct#');
field('Salesman');
field('Allocated Date');

newline();

foreach ($records as $row)
{
	field($row['InventorySpecialOrder']['original_purchase_order_number']);
	field(formatDate($row['InventorySpecialOrder']['original_purchase_order_date']));
	field($row['InventorySpecialOrder']['manufacturer_inventory_number']);
	field($row['InventorySpecialOrder']['manufacturer_code']);
	field($row['InventorySpecialOrder']['mrs_inventory_number']);
	field($row['InventorySpecialOrder']['description']);
	field($row['InventorySpecialOrder']['serial_number']);
	field($row['InventorySpecialOrder']['quantity']);
	field($row['InventorySpecialOrder']['unit_of_measure']);
	field($row['InventorySpecialOrder']['cost']);
	field(formatDate($row['InventorySpecialOrder']['date_of_purchase']));
	field($row['InventorySpecialOrder']['department_code']);
	field(ifset($conditions[$row['InventorySpecialOrder']['item_condition']], $row['InventorySpecialOrder']['item_condition']));
	field($row['InventorySpecialOrder']['locator']);
	field($row['InventorySpecialOrder']['color']);
	field($row['InventorySpecialOrder']['size']);
	field($row['InventorySpecialOrder']['arms']);
	field($row['InventorySpecialOrder']['rigging']);
	field($row['InventorySpecialOrder']['wheels']);
	field($row['InventorySpecialOrder']['accessories']);
	field($row['InventorySpecialOrder']['assigned_transaction_control_number']);
	field($row['InventorySpecialOrder']['account_number']);
	field($row['InventorySpecialOrder']['salesman_initials']);
	field(formatDate($row['InventorySpecialOrder']['assigned_date']));
	
	newline();
}

?>