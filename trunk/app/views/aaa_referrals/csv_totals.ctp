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
header('Content-Disposition: attachment; filename="aaaTotals.csv";');

field('AAA#');
field('Contact');
field('Facility');
field('Type');
field('Grouping Code');
field('R_Mkt');
field('R_Sales');
field('Month');
field('Quote Total');
field('Quote Trend');
field('Revenue Total');
field('Revenue Trend');

newline();

foreach ($results as $row)
{
	$salesman = empty($row['AaaMonthlySummary']['order_salesman']) ? $row['AaaMonthlySummary']['rehab_salesman'] : $row['AaaMonthlySummary']['order_salesman'];
	
	field($row['AaaMonthlySummary']['aaa_number']);
	field($row['AaaReferral']['contact_name']);
	field($row['AaaReferral']['facility_name']);
	field($row['AaaReferral']['facility_type']);
	field($row['AaaReferral']['group_code']);
	field($row['AaaReferral']['rehab_market_code']);
	field($salesman);
	field(formatDate($row['AaaMonthlySummary']['date_month']));
	field(round($row[0]['sum_quotes_month']));
	field(round($row[0]['sum_quotes_12months']));
	field(round($row[0]['sum_revenue_month']));
	field(round($row[0]['sum_revenue_12months']));
	
	newline();
}

?>