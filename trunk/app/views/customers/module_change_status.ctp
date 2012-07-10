<div id="ModuleChangeStatusContainer"></div>

<?php
	$before = '<div class="GroupBox"><h2>Change Customer Status</h2><div class="Content">';
	$before .= $form->hidden('Customer.account_number');
	$before .= $form->input('Customer.account_status_code', array('options' => $statuses, 'label' => 'Status'));
	$before .= '</div></div>';
	
	//remove line feeds so it will render correctly in the javascript
	$before = str_replace(array('"', "\n"), array('\"', ''), $before);
?>

<script type="text/javascript">
	document.observe("fileNote:postCompleted", Modules.Customers.ChangeStatus.onStatusChanged);

	new Ajax.Updater("ModuleChangeStatusContainer", "/modules/fileNotes/create/<?= $this->data['Customer']['account_number'] ?>", {
		parameters: {			
			before: "<?= $before ?>",
			handler: "Customer.handler_changeStatus"
		},
		evalScripts: true
	});
</script>