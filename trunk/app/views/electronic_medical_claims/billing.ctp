<script type="text/javascript">
	function submitForm()
	{
		if ($$R("BillingFormCode") && $$R("BillingProfitCenter") && $$R("BillingBillingDate") && $$D("BillingBillingDate") && $$R("BillingCarrierCode1"))
		{
			$("BillingForm").submit();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("BillingBillingDate");
	});
</script>


<div class="GroupBox">
	<h2>EMC Billing</h2>
	<div class="Content">
	<?php
		echo $form->create('Billing', array('url' => '/emc/billing', 'id' => 'BillingForm'));
		
		echo $form->input('form_code', array('label' => 'EMC Form Code', 'maxlength' => 2, 'class' => 'Text35'));
		echo $form->input('profit_center', array('maxlength' => 3, 'class' => 'Text35'));
		echo $form->input('billing_date', array('label' => 'Date to Update Billing Date', 'class' => 'Text65'));
		
		echo $form->input('carrier_code_1', array('label' => 'MED Carrier Code', 'maxlength' => 4, 'class' => 'Text35'));
		echo $form->input('carrier_code_2', array('label' => 'MED Carrier Code', 'maxlength' => 4, 'class' => 'Text35'));
		echo $form->input('carrier_code_3', array('label' => 'MED Carrier Code', 'maxlength' => 4, 'class' => 'Text35'));
		echo $form->input('carrier_code_4', array('label' => 'MED Carrier Code', 'maxlength' => 4, 'class' => 'Text35'));
		echo $form->input('carrier_code_5', array('label' => 'MED Carrier Code', 'maxlength' => 4, 'class' => 'Text35'));

		echo $form->button('Begin', array('onclick' => 'submitForm(); return false;'));
		echo $form->end();
	?>
	</div>
</div>
