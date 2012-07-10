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
header('Content-Disposition: attachment; filename="vendors.csv";');

field('Code');
field('Name');
field('Vendor Acct#');
field('CSR Phone');
field('CSR Name');
field('Sls Name');
field('Sls Phone');
field('Sls Email');
field('Addr1');
field('Addr2');
field('City');
field('Zip');
field('Price List');

newline();

foreach ($records as $row)
{
	field($row['Vendor']['vendor_code']);
	field($row['Vendor']['name']);
	field($row['Vendor']['millers_account_number_with_vendor']);
	field($row['Vendor']['phone_number']);
	field($row['Vendor']['contact']);
	field($row['Vendor']['salesman']);
	field($row['Vendor']['salesman_cell_phone']);
	field($row['Vendor']['salesman_email']);
	field($row['Vendor']['address_1']);
	field($row['Vendor']['address_2']);
	field($row['Vendor']['city']);
	field($row['Vendor']['zip_code']);
	field(formatDate($row['Vendor']['price_list_date']));
	
	newline();
}

?>