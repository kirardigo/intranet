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
header('Content-Disposition: attachment; filename="summary_mrs.csv";');

field('Acct#');
field('PCtr');
field('D');
field('Setup');
field('Mo#');
field('POS');
field('Qty');
field('HCPC');
field('Inven#');
field('Description');
field('MRS#');
field('Total Net');
field('MRS Description');
field('MRS Invoice');
field('MRS Date');

newline();

foreach ($results as $row)
{
	$totalNet = $row['Rental']['carrier_1_net_amount'] + $row['Rental']['carrier_2_net_amount'] + $row['Rental']['carrier_3_net_amount'];
	
	field($row['Rental']['account_number']);
	field($row['Rental']['profit_center_number']);
	field($row['Rental']['department_code']);
	field($row['Rental']['setup_date']);
	field($row['Rental']['number_of_rental_months']);
	field($row['Rental']['place_of_service']);
	field($row['Rental']['quantity']);
	field($row['Rental']['healthcare_procedure_code']);
	field($row['Rental']['inventory_number']);
	field($row['Rental']['inventory_description']);
	field($row['Rental']['serial_number']);
	field(number_format($totalNet, 2));
	field($row['SerializedEquipment']['product_description']);
	field($row['SerializedEquipment']['mrs_invoice_number']);
	field($row['SerializedEquipment']['date_of_sale']);
	newline();
}

?>