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
header('Content-Disposition: attachment; filename="meu_courses.csv";');

field('MEU#');
field('Title');
field('Presenters');
field('Hours');
field('Certified');

newline();

foreach ($records as $row)
{
	field($row['StaffEducationCourse']['meu_number']);
	field($row['StaffEducationCourse']['title']);
	field($row['StaffEducationCourse']['presenters']);
	field($row['StaffEducationCourse']['credit_hours']);
	field(ifset($confirmationMethods[$row['StaffEducationCourse']['confirmation_method']]));
	
	newline();
}

?>