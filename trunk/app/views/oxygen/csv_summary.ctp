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
header('Content-Disposition: attachment; filename="oxygen.csv";');

field('Acct#');
field('Name');
field('PCtr');
field('Type');
field('Pressure');
field('Status');
field('Lab#');
field('Referral#');
field('Setup Date');
field('Status Date');
field('Last Trx');
field('First Night');
field('1 Mo FUP');
field('3 Mo FUP');
field('Updated Ini');

newline();

foreach ($results as $row)
{
	field($row['Oxygen']['account_number']);
	field(ifset($row['Customer']['name']));
	field(ifset($row['Customer']['profit_center_number']));
	field(ifset($oxygenTypes[$row['Oxygen']['osa_type']]));
	field($row['Oxygen']['osa_pressure_setting']);
	field($row['Oxygen']['osa_status']);
	field($row['Oxygen']['osa_aaa_lab_code']);
	field($row['Oxygen']['osa_aaa_referral_code']);
	field($row['Oxygen']['osa_setup_date']);
	field($row['Oxygen']['osa_status_date']);
	field(ifset($row['Virtual']['last_trx_date']));
	field($row['Oxygen']['first_night_sleep_study_date']);
	field($row['Oxygen']['is_30_day_followup_returned'] ? 'Y' : '');
	field($row['Oxygen']['is_90_day_followup_returned'] ? 'Y' : '');
	field($row['Oxygen']['last_updated_ini']);
	newline();
}

?>