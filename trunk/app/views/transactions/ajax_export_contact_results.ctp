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
	header('Content-Disposition: attachment; filename="trans_contact.csv";');
	
	field('PCtr');
	field('Acct#');
	field('Setup');
	field('Invoice#');
	field('TCN');
	field('DOS');
	field('Inven#');
	field('HCPC');
	field('Inven Desc');
	field('Serial#');
	field('Carr1');
	field('Carr2');
	field('Carr3');
	field('Slsman');
	field('Client');
	field('Addr1');
	field('Addr2');
	field('City/State');
	field('Zip');
	field('Phone');
	field('Deceased?');
	
	newline();
	
	foreach ($output as $account => $invoices)
	{
		foreach ($invoices as $invoice => $transaction)
		{		
			field($transaction['profit_center_number']);
			field($transaction['account_number']);
			field(($transaction['setup_date'] != null ? date('m/Y', strtotime($transaction['setup_date'])) : ''));
			field($transaction['invoice_number']);
			field($transaction['transaction_control_number']);
			field(formatDate($transaction['transaction_date_of_service']));
			field($transaction['inventory_number']);
			field($transaction['healthcare_procedure_code']);
			field($transaction['inventory_description']);
			field($transaction['serial_number']);
			field($transaction['carrier_1_number']);
			field($transaction['carrier_2_number']);
			field($transaction['carrier_3_number']);
			field($transaction['salesman_number']);
			field(formatName($transaction['client_name']));
			field($transaction['address_1']);
			field($transaction['address_2']);
			field($transaction['city_state']);
			field($transaction['zip_code']);
			field($transaction['phone_number']);
			field($transaction['is_deceased'] ? 'Y' : 'N');
			
			newline();
		}
	}
?>