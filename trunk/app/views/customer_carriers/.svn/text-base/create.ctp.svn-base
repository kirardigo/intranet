<script type="text/javascript">
	function confirmCancel(event)
	{
		if (confirm("Are you sure you wish to abandon your changes?"))
		{
			location.href = "/customers/inquiry/accountNumber:" + $F("CustomerCarrierAccountNumber") + "/tab:1";
		}
	}
	
	document.observe("dom:loaded", function() {
		<?php if (!$success): ?>
		alert("<?= $message ?>");
		location.href = "/customers/inquiry/accountNumber:" + $F("CustomerCarrierAccountNumber") + "/tab:1";
		<?php endif; ?>
		
		mrs.bindDatePicker("CustomerCarrierPolicyHolderDateOfBirth");
		
		$("CancelButton").observe("click", confirmCancel);
	});
</script>

<?php
	echo $form->create('', array('id' => 'CustomerCarrierCreateForm', 'url' => "/customerCarriers/create/{$this->data['CustomerCarrier']['account_number']}/{$carrierID}"));
?>
<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<h2>Customer Carrier Detail</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerCarrier.account_number', array(
				'label' => 'Account #',
				'class' => 'ReadOnly Text100',
				'readonly' => 'readonly'
			));
			echo $form->input('CustomerCarrier.carrier_number', array(
				'label' => 'Carrier #',
				'class' => 'ReadOnly Text100',
				'readonly' => 'readonly'
			));
			echo $form->input('CustomerCarrier.carrier_name', array('class' => 'Text300'));
			echo $form->input('CustomerCarrier.claim_number', array('label' => 'Claim #'));
			echo $form->input('CustomerCarrier.carrier_type', array(
				'options' => array(
					'P' => 'Primary',
					'S' => 'Secondary',
					'N' => 'None'
				),
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerCarrier.is_active', array(
				'label' => array('class' => 'Checkbox'),
				'style' => 'margin-left: 20px;',
				'div' => array('style' => 'margin-top: 13px;')
			));
			echo '<div class="ClearBoth"></div>';
			echo $form->input('CustomerCarrier.gross_charge_percentage', array(
				'label' => 'Benefit Coverage %',
				'class' => 'Text25'
			));
		?>
	</div>
</div>
<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<h2>Policy Holder Info</h2>
	<div class="Content">
	<?php
		echo $form->input('CustomerCarrier.policy_holder_sex', array(
			'label' => 'Sex',
			'div' => array('class' => 'Horizontal'),
			'options' => $sexes,
			'empty' => true
		));
		echo $form->input('CustomerCarrier.policy_holder_date_of_birth', array(
			'type' => 'text',
			'label' => 'DOB',
			'class' => 'Text100'
		));
	?>
	</div>
</div>

<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<h2>Misc</h2>
	<div class="Content">
	<?php
		echo $form->hidden('CustomerCarrier.is_tax_exempt');
		echo $form->input('CustomerCarrier.source_of_payment_for_claim', array(
			'label' => 'Source Pmt',
			'options' => $paymentSources,
			'empty' => ''
		));
		echo $form->input('CustomerCarrier.insurance_type_code', array(
			'label' => 'Ins Code',
			'options' => $insuranceTypes,
			'empty' => '',
			'style' => 'width: 200px;'
		));
		echo $form->input('CustomerCarrier.carrier_group_code', array('label' => 'Grp Code', 'class' => 'Text50'));
	?>
	</div>
</div>
<div class="ClearBoth"></div>

<div class="ClearBoth"></div>
<?php
	echo $form->submit('Create', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>