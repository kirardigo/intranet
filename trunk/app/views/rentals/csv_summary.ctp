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
field('CB Zip');
field('CB');
field('D');
field('A');
field('Setup');
field('Returned');
field('Mo#');
field('POS');
field('Qty');
field('HCPC');
field('G/L');
field('Inven#');
field('Description');
field('MRS#');
field('Class');
field('Total Net');
field('Carr 1');
field('Net 1');
field('Gross 1');
field('Carr 2');
field('Net 2');
field('Gross 2');
field('Carr 3');
field('Net 3');
field('Gross 3');
field('ICD9');
field('ICD9');
field('ICD9');
field('ICD9');
field('Capped');
field('DM');

newline();

foreach ($results as $row)
{
	$totalNet = $row['Rental']['carrier_1_net_amount'] + $row['Rental']['carrier_2_net_amount'] + $row['Rental']['carrier_3_net_amount'];
	
	field($row['Rental']['account_number']);
	field($row['Rental']['profit_center_number']);
	field($row['Rental']['competitive_bid_zip'] ? 'Y' : '');
	field($row['Rental']['competitive_bid_hcpc'] ? 'Y' : '');
	field($row['Rental']['department_code']);
	field($row['Rental']['assignment_status_code']);
	field($row['Rental']['setup_date']);
	field($row['Rental']['returned_date']);
	field($row['Rental']['number_of_rental_months']);
	field($row['Rental']['place_of_service']);
	field($row['Rental']['quantity']);
	field($row['Rental']['healthcare_procedure_code']);
	field($row['Rental']['general_ledger_code']);
	field($row['Rental']['inventory_number']);
	field($row['Rental']['inventory_description']);
	field($row['Rental']['serial_number']);
	field($row['Rental']['6_point_classification']);
	field(number_format($totalNet, 2));
	field($row['Rental']['carrier_1_code']);
	field(number_format($row['Rental']['carrier_1_net_amount'], 2));
	field(number_format($row['Rental']['carrier_1_gross_amount'], 2));
	field($row['Rental']['carrier_2_code']);
	field(($row['Rental']['carrier_2_code']) ? number_format($row['Rental']['carrier_2_net_amount'], 2) : '');
	field(($row['Rental']['carrier_2_code']) ? number_format($row['Rental']['carrier_2_gross_amount'], 2) : '');
	field($row['Rental']['carrier_3_code']);
	field(($row['Rental']['carrier_3_code']) ? number_format($row['Rental']['carrier_3_net_amount'], 2) : '');
	field(($row['Rental']['carrier_3_code']) ? number_format($row['Rental']['carrier_3_gross_amount'], 2) : '');
	field(ifset($row['Rental']['icd9_1']));
	field(ifset($row['Rental']['icd9_2']));
	field(ifset($row['Rental']['icd9_3']));
	field(ifset($row['Rental']['icd9_4']));
	field($row['Rental']['capped_status']);
	field($row['Oxygen']['respiratory_code']);
	newline();
}

?>