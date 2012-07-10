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
header('Content-Disposition: attachment; filename="pal_time.csv";');

field('PCtr');
field('D');
field('Login');
field('Staff');
field('Description');
field('Time');
field('Date');

newline();

foreach ($records as $row)
{
	field($row['Staff']['profit_center_number']);
	field($row['Staff']['department']);
	field($row['Staff']['user_id']);
	field($row['Staff']['full_name']);
	field($row['StaffPalCode']['code'] . ' - ' . $row['StaffPalCode']['description']);
	field($row['StaffPalTime']['time']);
	field($row['StaffPalTime']['pal_date']);
	
	newline();
}

?>