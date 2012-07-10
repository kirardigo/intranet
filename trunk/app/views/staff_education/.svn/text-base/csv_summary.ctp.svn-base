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

field('Date');
field('User');
field('D');
field('PCtr');
field('MEU#');
field('Title');
field('Hours');

newline();

foreach ($records as $row)
{
	field(formatDate($row['StaffEducation']['date_completed']));
	field($row['StaffEducation']['username']);
	field($row['StaffEducation']['department_code']);
	field($row['StaffEducation']['profit_center_number']);
	field($row['StaffEducationCourse']['meu_number']);
	field($row['StaffEducationCourse']['title']);
	field($row['StaffEducationCourse']['credit_hours']);
		
	newline();
}

?>