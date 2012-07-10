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
header('Content-Disposition: attachment; filename="coe.csv";');

field('Acct#');
field('Status');
field('Name');
field('PCtr');
field('Profile');
field('Prog');
field('Prog Contact');
field('Prog Facility');
field('LTCF');
field('Acct Sls');
field('BU Sls');
field('Prog Rehab Sls');
field('Prog HCare Sls');
field('COE#');
field('Active');
field('DOP');
field('Service');
field('Sleep');
field('MFG');
field('Desc');
field('Model#');
field('Serial#');
field('HCPC');
field('Carr#');
field('Invoice#');
field('TCN#');
field('Tilt MFG');
field('Tilt Model#');
field('Tilt Serial#');
field('Client');
field('Address1');
field('Address2');
field('City');
field('Zip');
field('Phone#');
field('Deceased');

newline();

foreach ($records as $row)
{
	field($row['CustomerOwnedEquipment']['account_number']);
	field($row['CustomerOwnedEquipment']['account_status_code']);
	field($row['CustomerOwnedEquipment']['customer_name']);
	field($row['CustomerOwnedEquipment']['profit_center_number']);
	field($row['CustomerOwnedEquipment']['stats_profile']);
	field($row['CustomerOwnedEquipment']['aaa_program_number']);
	field($row['CustomerOwnedEquipment']['program_contact_name']);
	field($row['CustomerOwnedEquipment']['program_facility_name']);
	field($row['CustomerOwnedEquipment']['aaa_ltcf_number']);
	field($row['CustomerOwnedEquipment']['account_salesman']);
	field($row['CustomerOwnedEquipment']['transaction_salesman']);
	field($row['CustomerOwnedEquipment']['program_rehab_salesman']);
	field($row['CustomerOwnedEquipment']['program_homecare_salesman']);
	field($row['CustomerOwnedEquipment']['customer_owned_equipment_id_number']);
	field(($row['CustomerOwnedEquipment']['is_active'] ? 'Yes' : 'No'));
	field(formatDate($row['CustomerOwnedEquipment']['date_of_purchase']));
	field(formatDate($row['CustomerOwnedEquipment']['last_service_date']));
	field(formatDate($row['CustomerOwnedEquipment']['last_sleep_date']));
	field($row['CustomerOwnedEquipment']['manufacturer_frame_code']);
	field($row['CustomerOwnedEquipment']['description']);
	field($row['CustomerOwnedEquipment']['model_number']);
	field($row['CustomerOwnedEquipment']['serial_number']);
	field($row['CustomerOwnedEquipment']['purchase_healthcare_procedure_code']);
	field($row['CustomerOwnedEquipment']['initial_carrier_number']);
	field($row['CustomerOwnedEquipment']['invoice_number']);
	field($row['CustomerOwnedEquipment']['transaction_control_number']);
	field($row['CustomerOwnedEquipment']['tilt_manufacturer_code']);
	field($row['CustomerOwnedEquipment']['tilt_model_number']);
	field($row['CustomerOwnedEquipment']['tilt_serial_number']);
	field(formatName($row['CustomerOwnedEquipment']['client_name']));
	field($row['CustomerOwnedEquipment']['address_1']);
	field($row['CustomerOwnedEquipment']['address_2']);
	field($row['CustomerOwnedEquipment']['city_state']);
	field($row['CustomerOwnedEquipment']['zip_code']);
	field($row['CustomerOwnedEquipment']['phone_number']);
	field($row['CustomerOwnedEquipment']['is_deceased'] ? 'Y' : 'N');
	
	newline();
}

?>