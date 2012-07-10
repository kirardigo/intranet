
<script type="text/javascript">

	function validate(event)
	{
		if (!$$R("CustomerAccountNumber"))
		{
			event.stop();
		}
	}
	
	document.observe("dom:loaded", function() {
		$("CustomerAccountNumber").focus();
		$("ChangeProfitCenterForm").observe("submit", validate);
	});
</script>

<?php
	if (!$exists)
	{
		echo '<p class="Exception">That account does not exist.</p>';
	}
	else if ($alreadyInProfitCenter)
	{
		echo '<p class="Exception">The account is already in that profit center.</p>';
	}
	
	echo $form->create('', array('url' => "/customers/utilityChangeProfitCenter", 'id' => 'ChangeProfitCenterForm'));
	
	echo $form->input('Customer.account_number', array(
		'class' => 'Text75',
		'div' => array('class' => 'input text Horizontal')
	));
	
	echo $form->input('Customer.profit_center_number', array(
		'label' => 'Profit Center',
		'options' => $profitCenters,
		'class' => 'Text75',
		'div' => array('class' => 'input text Horizontal')
	));
	
	echo '<br />';
	echo $form->submit('Change');
	echo $form->end();
?>