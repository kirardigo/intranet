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
	header('Content-Disposition: attachment; filename="transactions.csv";');
	
	field('PCtr');
	field('D');
	field('Acct#');
	field('Setup');
	field('BZ');
	field('Invoice#');
	field('TCN');
	field('PPD');
	field('DOS');
	field('R/P');
	field('T');
	field('Qty');
	field('Inven#');
	field('HCPC');
	field('BH');
	field('GL Code');
	field('Inven Desc');
	field('Serial#');
	field('Carr1');
	field('Carr1_$');
	field('Carr2');
	field('Carr2_$');
	field('Carr3');
	field('Carr3_$');
	field('Total');
	field('Slsman');
	field('Referral');
	field('LTCF');
	field('Type');
	field('Pys_Code');
	
	newline();
	
	foreach ($transactions as $transaction)
	{		
		field($transaction['Transaction']['profit_center_number']);
		field($transaction['Transaction']['department_code']);
		field($transaction['Transaction']['account_number']);
		field(($transaction['Transaction']['setup_date'] != null ? date('m/Y', strtotime($transaction['Transaction']['setup_date'])) : ''));
		field($transaction['Transaction']['competitive_bid_zip_code_flag']);
		field($transaction['Transaction']['invoice_number']);
		field($transaction['Transaction']['transaction_control_number']);
		field(formatDate($transaction['Transaction']['period_posting_date']));
		field(formatDate($transaction['Transaction']['transaction_date_of_service']));
		field($transaction['Transaction']['rental_or_purchase']);
		field($transactionTypes[$transaction['Transaction']['transaction_type']]);
		field(number_format($transaction['Transaction']['quantity'], 0));
		field($transaction['Transaction']['inventory_number']);
		field($transaction['Transaction']['healthcare_procedure_code']);
		field($transaction['Transaction']['competitive_bid_hcpc_flag']);
		field($transaction['Transaction']['general_ledger_code']);
		field($transaction['Transaction']['inventory_description']);
		field($transaction['Transaction']['serial_number']);
		field($transaction['Transaction']['carrier_1_number']);
		field(number_format($transaction['Transaction']['carrier_1_amount'], 2));
		field($transaction['Transaction']['carrier_2_number']);
		field($transaction['Transaction']['carrier_2_amount'] != null ? number_format($transaction['Transaction']['carrier_2_amount'], 2) : '');
		field($transaction['Transaction']['carrier_3_number']);
		field($transaction['Transaction']['carrier_3_amount'] != null ? number_format($transaction['Transaction']['carrier_3_amount'], 2) : '');
		field(number_format($transaction['Transaction']['total_amount'], 2));
		field($transaction['Transaction']['salesman_number']);
		field($transaction['Transaction']['referral_number_from_aaa_file']);
		field($transaction['Transaction']['long_term_care_facility_number']);
		field($transaction['Transaction']['long_term_care_facility_type']);
		field($transaction['Transaction']['physician_number']);
		
		newline();
	}
?>