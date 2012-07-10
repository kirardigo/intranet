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
header('Content-Disposition: attachment; filename="diagnosis.csv";');

field('Code');
field('Description');
field('Complex?');
field('Modified By');
field('Modified');
field('Combo');
field('Num');

newline();

foreach ($records as $row)
{
	$complex = $row['Diagnosis']['is_complex_rehabilitation'] ? 'Y' : 'N';
	
	field($row['Diagnosis']['code']);
	field($row['Diagnosis']['description']);
	field($complex);
	field($row['Diagnosis']['modified_by']);
	field($row['Diagnosis']['modified']);
	field($row['Diagnosis']['combination']);
	field($row['Diagnosis']['number']);
	
	newline();
}

?>