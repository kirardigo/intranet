<script type="text/javascript">
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("InvoiceAgingDate");
	});
</script>

<?php
	if (isset($noAgingDate))
	{
		echo '<p class="Warning">You must specify an aging date.</p>';
	}
	
	echo '<fieldset>';
	echo '<legend>Filters</legend>';
	
	echo $form->create('Customer', array('url' => '/reports/accountsReceivable/aging'));
	
	echo $form->input('Invoice.aging_date');

	echo $form->input('Option.sort_by_account_number', array(
		'type' => 'radio', 
		'legend' => false, 
		'options' => array('Name', 'Account Number'), 
		'div' => array('class' => 'Radio'),
		'before' => $form->label('Sort By '),
		'after' => 
			'<br />' . 
			$form->input('Customer.start_range', array('label' => false, 'div' => false)) .
			' to ' .
			$form->input('Customer.end_range', array('label' => false, 'div' => false))
		));
	
	echo '<br />';
	echo '<div class="FormColumn">';
	
	echo $form->input('Customer.profit_center_number', array('label' => 'Profit Center'));
	echo $form->input('CustomerCarrier.carrier_number');
	echo $form->input('Carrier.statement_type');
	echo $form->input('Carrier.group_code', array('label' => 'Carrier Grouping Code'));
	
	echo '</div>';
	echo '<div class="FormColumn">';
	
	echo $form->input('Transaction.account_balance', array('label' => 'Account Balances Greater Than')) . ' ';
	echo $form->input('Invoice.minimum_days_old') . ' ';
	echo $form->input('Carrier.network') . ' ';

	echo '</div>';
	echo '<br style="clear: left" /><br />';
	
	echo '<div class="FormColumn">';
	
	echo $form->input('Option.break_down_by_invoice', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	echo $form->input('Option.print_customer_memo', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	
	echo '</div>';
	echo '<div class="FormColumn">';
	
	echo $form->input('Option.age_from_service_date', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	echo $form->input('Option.print_summary_page_only', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	echo $form->input('Option.print_with_carrier_number', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	
	echo '</div>';
	echo '<div class="FormColumn">';
	
	echo $form->input('Option.print_with_billing_date_blank', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	echo $form->input('Option.print_with_credit_invoices_displayed', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));

	echo '</div>';
	echo '<br style="clear: left" /><br />';
	
	echo $form->end('Generate');	
	echo '</fieldset>';
?>