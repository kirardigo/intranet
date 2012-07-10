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
header('Content-Disposition: attachment; filename="distributor_header.csv";');

field('Order#');
field('Acct#');
field('Invoice#');
field('Sls');
field('R');
field('M');
field('Date');
field('DOS');
field('Days');
field('R/A');
field('Bill');
field('Ship');
field('Code');
field('Name');
field('Zip');
field('PO#');
field('%');
field('Total');

newline();

foreach ($results as $row)
{
	field($row['DistributorOrder']['order_number']);
	field($row['DistributorOrder']['account_number']);
	field($row['DistributorOrder']['invoice_number']);
	field($row['DistributorOrder']['ship_salesman']);
	field($row['DistributorOrder']['ship_region']);
	field($row['DistributorOrder']['ship_market']);
	field(formatDate($row['DistributorOrder']['order_date']));
	field(formatDate($row['DistributorOrder']['date_of_service']));
	field(ifset($row['DistributorOrder']['days']));	
	field($row['DistributorOrder']['has_return_authorization_number'] ? 'Y' : 'N');
	field($row['DistributorOrder']['bill_to_aaa_number']);
	field($row['DistributorOrder']['ship_to_aaa_number']);
	field($row['DistributorOrder']['ship_to_code']);
	field($row['DistributorOrder']['ship_to_name']);
	field($row['DistributorOrder']['ship_to_zip_code']);
	field($row['DistributorOrder']['purchase_order_number']);
	field($row['DistributorOrder']['discount']);
	field($row['DistributorOrder']['invoice_total']);
	newline();
}

?>