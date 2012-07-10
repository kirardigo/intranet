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
header('Content-Disposition: attachment; filename="maintenance_log.csv";');

field('MRS#');
field('PCtr');
field('Ini');
field('Account#');
field('DOS');
field('Action');
field('Invoice#');
field('Cleaned?');
field('Type');

newline();

foreach ($records as $row)
{
	field($row['MaintenanceLog']['serialized_equipment_number']);
	field(ifset($profitCenters[$row['MaintenanceLog']['profit_center_number']]));
	field($row['MaintenanceLog']['staff_initials']);
	field($row['MaintenanceLog']['account_number']);
	field(formatDate($row['MaintenanceLog']['date_of_service']));
	field($row['MaintenanceLog']['comment']);
	field($row['MaintenanceLog']['invoice_number']);
	field($row['MaintenanceLog']['is_cleaned'] ? 'Yes' : 'No');
	field(ifset($maintenanceTypes[$row['MaintenanceLog']['maintenance_type']]));
		
	newline();
}

?>