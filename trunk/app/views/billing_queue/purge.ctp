<script type="text/javascript">
	function submitForm()
	{
		if (confirm("Are you sure you want to purge the queue with these options?"))
		{
			$("PurgeForm").submit();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("BillingQueuePurgeDate");
	});
</script>

<div class="GroupBox">
	<h2>Purge Billing Queue</h2>
	<div class="Content">
	<?php
		echo $form->create('', array('url' => 'purge', 'id' => 'PurgeForm'));
		
		echo $form->input('BillingQueue.form_code');
		echo $form->input('BillingQueue.purge_date');
		echo $form->input('BillingQueue.is_invoice_zero_balance_required', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'checked' => 'checked'));
		
		echo $form->button('Purge', array('onclick' => 'submitForm(); return false;'));
		echo $form->end();
	?>
	</div>
</div>
