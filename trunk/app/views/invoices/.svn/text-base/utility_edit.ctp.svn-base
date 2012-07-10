<script type="text/javascript">
	function cancelChanges()
	{
		if (confirm("Are you sure you want to abandon your changes?"))
		{
			location.href = "/invoices/utilityList";
		}
	}
	
	function saveChanges()
	{
		if (confirm("Do you wish to submit these modifications?"))
		{
			$("InvoiceEditForm").submit();
		}
	}

	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("InvoiceBillingDate");
		mrs.bindDatePicker("InvoiceDateOfService");
		
		$("CancelButton").observe("click", cancelChanges);
		$("SaveButton").observe("click", saveChanges);
	});
</script>

<?php
	echo $form->create('', array('url' => "/invoices/utilityEdit/{$id}", 'id' => 'InvoiceEditForm'));
	
	echo $form->hidden('Invoice.id');
	echo $form->input('Invoice.account_number', array(
		'class' => 'ReadOnly'
	));
	echo '<br/>';
	echo $form->input('Invoice.invoice_number', array(
		'class' => 'Text75'
	));
	echo $form->input('Invoice.billing_date', array(
		'type' => 'text',
		'class' => 'Text75'
	));
	echo $form->input('Invoice.date_of_service', array(
		'type' => 'text',
		'class' => 'Text75'
	));
	echo $form->input('Invoice.amount', array(
		'class' => 'Text75'
	));
	echo $form->input('Invoice.department_code', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => true
	));
	echo $form->input('Invoice.carrier_1_code', array(
		'label' => 'Carrier 1'
	));
	echo $form->input('Invoice.carrier_2_code', array(
		'label' => 'Carrier 2'
	));
	echo $form->input('Invoice.carrier_3_code', array(
		'label' => 'Carrier 3'
	));
	
	echo '<br/>';
	echo $form->button('Modify', array('id' => 'SaveButton', 'class' => 'Horizontal'));
	echo $form->button('Cancel', array('id' => 'CancelButton', 'style' => 'margin-left: 5px;'));
	echo $form->end();
?>