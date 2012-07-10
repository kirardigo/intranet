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
header('Content-Disposition: attachment; filename="summary.csv";');

field('Acct#');
field('PCtr');
field('Name');
field('Setup');
field('Phone');
field('Address 1');
field('Address 2');
field('City');
field('Zip');
field('County');
field('Bill Name');
field('Bill Address');
field('Bill City');
field('Bill Zip');
field('Email');
field('DOB');
field('Sex');
field('LTCF#');
field('Ref#');
field('Prog#');

newline();

foreach ($results as $row)
{
	field($row['Customer']['account_number']);
	field($row['Customer']['profit_center_number']);
	field(formatName($row['Customer']['name']));
	field($row['Customer']['setup_date']);
	field($row['Customer']['phone_number']);
	field($row['Customer']['address_1']);
	field($row['Customer']['address_2']);
	field($row['Customer']['city']);
	field($row['Customer']['zip_code']);
	field($row['Customer']['county']);
	field(formatName($row['CustomerBilling']['billing_name']));
	field($row['CustomerBilling']['address_1']);
	field($row['CustomerBilling']['city']);
	field($row['CustomerBilling']['zip_code']);
	field($row['Customer']['email']);
	field($row['CustomerBilling']['date_of_birth']);
	field($row['CustomerBilling']['sex']);
	field($row['CustomerBilling']['long_term_care_facility_number']);
	field($row['CustomerBilling']['referral_number_from_aaa_file']);
	field($row['CustomerBilling']['school_or_program_number_from_aaa_file']);
	newline();
}

?>