<script type="text/javascript">
	function cancelChanges()
	{
		if (confirm("Are you sure you want to abandon your changes?"))
		{
			location.href = "/transactions/utilityList";
		}
	}
	
	function saveChanges()
	{
		if (confirm("Do you wish to submit these modifications?"))
		{
			$("TransactionEditForm").submit();
		}
	}
	
	function updateGeneralLedgerDescription()
	{
		new Ajax.Request("/json/generalLedger/description", {
			parameters: { code: $F("TransactionGeneralLedgerCode") },
			onSuccess: function(transport) {
				$("TransactionGeneralLedgerDescription").value = transport.headerJSON.description;
			}
		});
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("TransactionTransactionDateOfService");
		
		$("CancelButton").observe("click", cancelChanges);
		$("SaveButton").observe("click", saveChanges);
		$("TransactionGeneralLedgerCode").observe("change", updateGeneralLedgerDescription);
	});
</script>

<?php
	echo $form->create('', array('url' => "/transactions/utilityEdit/{$id}", 'id' => 'TransactionEditForm'));
	
	echo $form->hidden('Transaction.id');
	echo $form->input('Transaction.account_number', array(
		'class' => 'ReadOnly'
	));
	echo '<br/>';
	echo $form->input('Transaction.invoice_number', array(
		'class' => 'Text75'
	));
	echo $form->input('Transaction.transaction_date_of_service', array(
		'type' => 'text',
		'label' => 'Date Of Service',
		'class' => 'Text75'
	));
	echo $form->input('Transaction.general_ledger_code', array(
		'class' => 'Text50'
	));
	echo $form->input('Transaction.general_ledger_description', array(
		'class' => 'Text400'
	));
	echo $form->input('Transaction.transaction_type', array(
		'class' => 'Text75'
	));
	echo $form->input('Transaction.department_code', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => true
	));
	echo $form->input('Transaction.serial_number', array(
		'class' => 'Text75'
	));
	echo $form->input('Transaction.amount', array(
		'class' => 'Text75'
	));
	echo $form->input('Transaction.carrier_number', array(
		'class' => 'Text75'
	));
	
	echo '<br/>';
	echo $form->button('Modify', array('id' => 'SaveButton', 'class' => 'Horizontal'));
	echo $form->button('Cancel', array('id' => 'CancelButton', 'style' => 'margin-left: 5px;'));
	echo $form->end();
?>