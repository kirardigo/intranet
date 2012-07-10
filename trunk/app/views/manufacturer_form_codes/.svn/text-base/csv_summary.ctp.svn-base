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
header('Content-Disposition: attachment; filename="mfg_header_codes.csv";');

field('Code');
field('Seq');
field('Description');

newline();

foreach ($records as $row)
{
	field($row['ManufacturerFormCode']['form_code']);
	field($row['ManufacturerFormCode']['sequence_number']);
	field($row['ManufacturerFormCode']['sequence_description']);
	
	newline();
}

?>