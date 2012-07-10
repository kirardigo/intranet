<?php

	if ($isBatchInvoicingRunning)
	{
		echo '<h2 class="Warning">Warning - Batch Invoicing process is in use. Contact the Systems Administrator before starting another batch.</h2>';
	}

	echo $form->create('Invoice', array('id' => 'BatchInvoicingModuleForm', 'url' => '/modules/invoices/batch'));
	
	echo $form->input('invoicing_type', array('id' => 'BatchInvoicingModuleInvoicingType', 'label' => 'Invoices for', 'options' => $invoicingTypes));
	
	echo $form->input('begin_date', array('id' => 'BatchInvoicingModuleBeginDate'));
	echo $form->input('end_date', array('id' => 'BatchInvoicingModuleEndDate'));
	
	echo $form->input('account_number', array('id' => 'BatchInvoicingModuleAccountNumber'));
	echo $form->input('profit_center_number', array('id' => 'BatchInvoicingModuleProfitCenterNumber'));
	
	echo $form->input('printer', array('options' => $printers));
	
	echo '<br />';
	echo $form->input('should_suppress_printing', array('type' => 'checkbox', 'label' => array('text' => 'Do Not Print Invoices', 'class' => 'Checkbox')));
	
	echo '<br /><br />';
	echo $form->submit('Print Invoices');
	echo $form->end();
?>

<script type="text/javascript">
	Modules.Invoices.Batch.initialize("<?= $periodPostingDate ?>");
</script>