<script type="text/javascript">
	function submitForm()
	{
		if (confirm("Are you sure you want to purge the queue?"))
		{
			$("PurgeForm").submit();
		}
	}
</script>


<div class="GroupBox">
	<h2>Purge Transaction Queue</h2>
	<div class="Content">
	<?php
		echo $form->create('', array('url' => 'purge', 'id' => 'PurgeForm'));
		echo $form->hidden('TransactionQueue.hidden'); // Needs to be in form so that $this->data is set
		echo $form->button('Purge', array('onclick' => 'submitForm(); return false;'));
		echo $form->end();
	?>
	</div>
</div>
