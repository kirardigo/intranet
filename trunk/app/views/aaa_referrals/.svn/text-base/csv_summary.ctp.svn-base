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
header('Content-Disposition: attachment; filename="aaa.csv";');

field('AAA#');
field('Contact');
field('Title');
field('Facility');
field('Type');
field('Grouping Code');
field('Address');
field('City State');
field('Zip');
field('County');
field('Phone & Ext');
field('Cell');
field('Email');
field('Rehab');
field('R_Sales');
field('R_Mkt');
field('Hcare');
field('H_Sales');
field('H_Mkt');
field('Access');

newline();

foreach ($results as $row)
{
	field($row['AaaReferral']['aaa_number']);
	field($row['AaaReferral']['contact_name']);
	field($row['AaaReferral']['contact_title']);
	field($row['AaaReferral']['facility_name']);
	field($row['AaaReferral']['facility_type']);
	field($row['AaaReferral']['group_code']);
	field($row['AaaReferral']['address_1']);
	field($row['AaaReferral']['city_state']);
	field($row['AaaReferral']['zip_code']);
	field($row['AaaReferral']['county_name']);
	field($row['AaaReferral']['phone_number'] . (strlen($row['AaaReferral']['phone_extension']) > 0 ? ' x' : '') . $row['AaaReferral']['phone_extension']);
	field($row['AaaReferral']['cell_phone_number']);
	field($row['AaaReferral']['contact_email']);
	field($row['AaaReferral']['is_active_for_rehab'] ? 'Yes' : 'No');
	field($row['AaaReferral']['rehab_salesman']);
	field($row['AaaReferral']['rehab_market_code']);
	field($row['AaaReferral']['is_active_for_homecare'] ? 'Yes' : 'No');
	field($row['AaaReferral']['homecare_salesman']);
	field($row['AaaReferral']['homecare_market_code']);
	field($row['AaaReferral']['is_active_for_access'] ? 'Yes' : 'No');
	
	newline();
}

?>