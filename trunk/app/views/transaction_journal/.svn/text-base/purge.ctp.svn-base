<script type="text/javascript">
	function submitForm()
	{
		if (confirm("Are you sure you want to purge the queue with these options?"))
		{
			$("PurgeForm").submit();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("TransactionJournalPurgeDate");
	});
</script>

<div class="GroupBox">
	<h2>Purge Transaction Journal</h2>
	<div class="Content">
	<?php
		echo $form->create('', array('url' => 'purge', 'id' => 'PurgeForm'));
		
		echo $form->input('TransactionJournal.profit_center_number', array(
			'options' => $profitCenters,
			'empty' => array('ALL' => 'ALL')
		));
		echo $form->input('TransactionJournal.purge_date');
		
		echo $form->button('Purge', array('onclick' => 'submitForm(); return false;'));
		echo $form->end();
	?>
	</div>
</div>
