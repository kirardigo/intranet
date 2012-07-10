<script type="text/javascript">
	function formConfirm(event)
	{
		if (!confirm("Are you sure you wish to balance this account?"))
		{
			event.stop();
		}
		
		return true;
	}
	
	document.observe("dom:loaded", function() {
		$("TransactionAccountNumber").focus();
		$("BalanceForm").observe("submit", formConfirm);
	});
</script>

<?php
	echo $form->create('', array('url' => '/transactions/utilitySortBalance', 'id' => 'BalanceForm'));
	echo $form->input('Transaction.account_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->submit('Rebalance', array('style' => 'margin-top: 7px'));
	echo $form->end();
?>
