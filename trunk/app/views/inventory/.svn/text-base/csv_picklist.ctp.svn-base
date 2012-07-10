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
field('EOMmfg/Cost/EOMret');
field('COGS');
field('020');
field('010');
field('060');
field('050');
field('070');
newline();

foreach ($records as $row)
{
	/*Each record will actually consist of three rows*/
	
	//first row
	field($row['Inventory']['inventory_number']);
	field('__________');
	field($row['Inventory']['description']);	
	field($row['Inventory']['manufacturer_unit_of_measure']);
	field($row['Inventory']['cost_of_goods_sold_mrs']);
	field(ifset($row['Inventory']['stock_level']['020'], 0));
	field(ifset($row['Inventory']['stock_level']['010'], 0));
	field(ifset($row['Inventory']['stock_level']['060'], 0));
	field(ifset($row['Inventory']['stock_level']['050'], 0));
	field(ifset($row['Inventory']['stock_level']['070'], 0));
	newline();
	
	//second row
	field('');
	field($row['Inventory']['item_count']);
	field('');
	field($row['Inventory']['inventory_is_cost_of_goods']);
	field('');
	field(ifset($row['Inventory']['turns']['020'], 0));
	field(ifset($row['Inventory']['turns']['010'], 0));
	field(ifset($row['Inventory']['turns']['060'], 0));
	field(ifset($row['Inventory']['turns']['050'], 0));
	field(ifset($row['Inventory']['turns']['070'], 0));
	newline();
	
	//third row
	field('');
	field('');
	field('');
	field($row['Inventory']['retail_unit_of_measure']);
	field('');
	field('');
	field('');
	field('');
	field('');
	field('');
	newline();
}

?>