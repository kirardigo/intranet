<div id="ModuleEditL1InformationContainer"></div>

<?php
	$before = '<div class="GroupBox"><h2>Change L1 Information</h2><div class="Content">';
	$before .= $form->hidden('Invoice.invoice_number');
	$before .= $form->input('Invoice.line_1_status', array('label' => 'L1 Status'));
	$before .= $form->input('Invoice.line_1_initials', array('label' => 'L1 Initials'));
	$before .= $form->input('Invoice.line_1_date', array('type' => 'text', 'label' => 'L1 Date'));
	$before .= $form->input('Invoice.line_1_carrier_number', array('label' => 'L1 Carrier'));
	$before .= '</div></div>';
	
	//remove line feeds so it will render correctly in the javascript
	$before = str_replace(array('"', "\n"), array('\"', ''), $before);
?>

<script type="text/javascript">
	document.observe("fileNote:beforePost", Modules.Invoices.EditL1Information.onBeforePost);
	document.observe("fileNote:postCompleted", Modules.Invoices.EditL1Information.onInformationUpdated);

	new Ajax.Updater("ModuleEditL1InformationContainer", "/modules/fileNotes/create/<?= $this->data['Invoice']['account_number'] ?>", {
		parameters: {
			invoice: "<?= $this->data['Invoice']['invoice_number'] ?>",
			before: "<?= $before ?>",
			handler: "Invoice.handler_updateL1Information"
		},
		evalScripts: true
	});
</script>