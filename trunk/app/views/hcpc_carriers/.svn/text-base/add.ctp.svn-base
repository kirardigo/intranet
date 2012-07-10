<script type="text/javascript">
	function validateForm(event)
	{		
		valid = true;		
		valid &= $$R("HcpcCarrierCarrierNumber");
		valid &= $$N("HcpcCarrierAllowableSale", true);
		valid &= $$N("HcpcCarrierAllowableRent", true);
		valid &= $$N("HcpcCarrierPreviousAllowableSale", true);
		valid &= $$N("HcpcCarrierPreviousAllowableRent", true);
		valid &= $$D("HcpcCarrierInitialDate");
		valid &= $$D("HcpcCarrierDiscontinuedDate");
		valid &= $$D("HcpcCarrierUpdatedDate");
		valid &= $$N("HcpcCarrierHcpcMessageReferenceNumber");

		if (!valid)
		{
			alert("Highlighted fields are required.");
			event.stop();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("HcpcCarrierInitialDate");
		mrs.bindDatePicker("HcpcCarrierDiscontinuedDate");
		mrs.bindDatePicker("HcpcCarrierUpdatedDate");
	
		$("SaveButton").observe("click", validateForm);	
	});
</script>

<?php
	echo $form->create('', array('url' => '/hcpcCarriers/add/' . $code, 'id' => 'HcpcCarrierNewForm'));
?>
<div class="GroupBox">
	<h2>HCPC</h2>
	<div class="Content">
	<?php
		echo $form->input('HcpcCarrier.hcpc_code', array(
			'class' => 'Text100',
			'value' => $code,
			'readonly' => 'readonly'
		));
		echo $form->input('HcpcCarrier.carrier_number', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcCarrier.allowable_sale', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcCarrier.allowable_rent', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcCarrier.allowable_units', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcCarrier.rp_code', array(
			'class' => 'Text100',
			'options' => $rpCodes
		));
		echo $form->input('HcpcCarrier.is_authorization_required', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('HcpcCarrier.is_medicare_covered', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('HcpcCarrier.initial_replacement', array(
			'class' => 'Text150',
			'options' => $initialReplacement
		));
		echo $form->input('HcpcCarrier.previous_allowable_sale', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.previous_allowable_rent', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.initial_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.discontinued_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.updated_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.use_hcpc_crosswalk', array(
			'label' => array('class' => 'Checkbox', 'text' => 'Use ICD9 Crosswalk'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('HcpcCarrier.hcpc_message_reference_number', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcCarrier.notes', array(
			'class' => 'Text300'
		));
	?>	
	</div>
</div>

<?php
	echo $form->submit('Save', array('id' => 'SaveButton'));
	echo $form->end();
?>