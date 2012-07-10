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
header('Content-Disposition: attachment; filename="physician.csv";');

field('Phy#');
field('Name');
field('Main Office');
field('City');
field('Zip');
field('Phone');
field('Fax');
field('Type/Spec');
field('Email');
field('Client Location');
field('UPIN#');
field('ODJFS#');
field('NPI#');
field('License#');
field('License Date');
field('Notes');

newline();

foreach ($results as $row)
{
	$officeAddress = h($row['Physician']['address_1']);
	if ($row['Physician']['address_2'] != '')
	{
		$officeAddress .= ' ' . h($row['Physician']['address_2']);
	}
	
	$clientAddress = h($row['Physician']['location_address_1']);
	if ($row['Physician']['location_address_2'] != '')
	{
		$clientAddress .= ' ' . h($row['Physician']['location_address_2']);
	}
	$clientAddress .= ' ' . h($row['Physician']['location_city']) . ' ' . h($row['Physician']['location_zip_code']);
	
	field($row['Physician']['physician_number']);
	field($row['Physician']['name']);
	field($officeAddress);
	field($row['Physician']['city']);
	field($row['Physician']['zip_code']);
	field($row['Physician']['phone_number']);
	field($row['Physician']['fax_number']);
	field($row['Physician']['specialty']);
	field($row['Physician']['email']);
	field($clientAddress);
	field($row['Physician']['unique_identification_number']);
	field($row['Physician']['medicaid_provider_number']);
	field($row['Physician']['national_provider_identification_number']);
	field($row['Physician']['license_number']);
	field(formatDate($row['Physician']['license_number_update_date']));
	field('Notes App');
	
	newline();
}

?>