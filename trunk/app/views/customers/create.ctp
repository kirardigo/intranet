<script type="text/javascript">
	function copyClientInfo(event)
	{
		event.stop();
		$("CustomerBillingBillingName").value = $F("CustomerName");
		$("CustomerBillingAddress1").value = $F("CustomerAddress1");
		$("CustomerBillingAddress2").value = $F("CustomerAddress2");
		$("CustomerBillingCity").value = $F("CustomerCity");
		$("CustomerBillingZipCode").value = $F("CustomerZipCode");
		$("CustomerBillingPhoneNumber").value = $F("CustomerPhoneNumber");
	}
	
	function submitValidation(event)
	{
		if (!$F("CustomerName").include(", "))
		{
			if (!confirm("Name should be Last Name, First Name. Press Cancel to correct."))
			{
				event.stop();
			}
		}
	}
	
	function confirmCancel(event)
	{
		if (confirm("Are you sure you wish to abandon your changes?"))
		{
			location.href = '/customers/inquiry';
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindPhoneFormatting(
			"CustomerPhoneNumber",
			"CustomerBillingPhoneNumber"
		);
		
		$("CopyClientInfoLink").observe("click", copyClientInfo);
		$("CustomerCreateForm").observe("submit", submitValidation);
		$("CancelButton").observe("click", confirmCancel);
	});
</script>

<?php
	echo $form->create('', array('id' => 'CustomerCreateForm', 'url' => '/customers/create'));
?>
<div class="GroupBox FormColumn" style="min-width: 310px; height: 300px;">
	<h2>Client Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Customer.name', array('class' => 'Text250'));
			echo $form->input('Customer.address_1', array('class' => 'Text250'));
			echo $form->input('Customer.address_2', array('class' => 'Text250'));
			echo $form->input('Customer.city', array(
				'label' => 'City, State',
				'class' => 'Text200',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Customer.zip_code', array('label' => 'Zip', 'class' => 'Text75'));
			echo '<div class="ClearBoth"></div>';
			echo $form->input('Customer.phone_number', array('label' => 'Phone', 'class' => 'Text100'));
			echo $form->input('Customer.profit_center_number', array(
				'options' => $profitCenters,
				'empty' => true
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="min-width: 310px; height: 300px;">
	<span style="float: right; margin: 3px 3px 0 0;"><a href="#" id="CopyClientInfoLink">Copy Client Info</a></span>
	<h2>Billing Info</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerBilling.billing_name', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.address_1', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.address_2', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.city', array(
				'label' => 'City, State',
				'class' => 'Text200',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.zip_code', array('label' => 'Zip', 'class' => 'Text75'));
			echo '<div class="ClearBoth"></div>';
			echo $form->input('CustomerBilling.phone_number', array('label' => 'Billing Phone', 'class' => 'Text100'));
		?>
	</div>
</div>

<div class="ClearBoth"></div>
<?php
	echo $form->submit('Create', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>