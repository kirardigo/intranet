<?= $form->create('', array('id' => 'CustomerCarrierForm', 'url' => '/customerCarriers/fakeSave')); ?>
<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="CustomerCarrierSaveTop" value="Save" />
</div>

<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<h2>Customer Carrier Detail</h2>
	<div class="Content">
		<div class="FormColumn">
		<?php
			echo $form->hidden('CustomerCarrier.id');
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
			echo $form->input('CustomerCarrier.signature_authorization_on_file', array(
				'label' => 'Signature Auth',
				'options' => $signatureAuthorizations,
				'empty' => '',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerCarrier.gross_charge_percentage', array(
				'label' => 'Benefit Coverage %',
				'class' => 'Text25'
			));
			echo $form->input('CustomerCarrier.policy_group_number', array('label' => 'Group #'));
			echo $form->input('CustomerCarrier.policy_group_name', array('label' => 'Group Name', 'class' => 'Text300'));
			echo $form->input('CustomerCarrier.policy_effective_date', array(
				'type' => 'text',
				'label' => 'Effective Date',
				'class' => 'Text100',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerCarrier.policy_termination_date', array(
				'type' => 'text',
				'label' => 'Termination Date',
				'class' => 'Text100'
			));
			echo $form->input('CustomerCarrier.last_zirmed_electronic_vob_date', array(
				'type' => 'text',
				'label' => 'Last eVOB Date',
				'class' => 'Text100'
			));
		?>
		</div>
	</div>
	<br class="ClearBoth" /><br/>
</div>
<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<span style="float: right; margin: 3px 3px 0 0;"><a href="#" id="CopyPolicyHolderLink">Copy Client Address</a></span>
	<h2>Policy Holder Info</h2>
	<div class="Content">
	<?php
		echo $form->input('CustomerCarrier.insuree_relationship', array(
			'label' => 'Client Relationship to Policy Holder',
			'options' => $relationships,
			'empty' => true
		));
		echo $form->input('CustomerCarrier.insuree_name', array('label' => 'Name', 'class' => 'Text300'));
		echo $form->input('CustomerCarrier.policy_holder_address_1', array('label' => 'Address 1', 'class' => 'Text300'));
		echo $form->input('CustomerCarrier.policy_holder_address_2', array('label' => 'Address 2', 'class' => 'Text300'));
		echo $form->input('CustomerCarrier.policy_holder_city', array('label' => 'City, State', 'class' => 'Text250'));
		echo $form->input('CustomerCarrier.policy_holder_zip_code', array('label' => 'Zip', 'class' => 'Text100'));
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
		echo $form->input('CustomerCarrier.policy_holder_employment_status', array(
			'options' => $employmentStatuses,
			'empty' => true
		));
		echo $form->input('CustomerCarrier.policy_holder_identification_number', array(
			'label' => 'Identification#',
			'class' => 'Text200'
		));
	?>
	</div>
</div>
<div class="ClearBoth"></div>

<div class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0;">
	<h2>Misc</h2>
	<div class="Content">
	<?php
		echo $form->hidden('Carrier.id');
		echo $form->input('Carrier.phone_number', array('label' => 'Carrier Phone#', 'class' => 'Text100'));
		echo $form->input('Carrier.fax_number', array('label' => 'Carrier Fax#', 'class' => 'Text100'));
		echo $form->input('CustomerCarrier.sequence_number', array('label' => 'Sequence#', 'class' => 'Text50'));
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
<div id="WorkersCompSection" class="GroupBox" style="float: left; width: 400px; margin: 0 10px 10px 0; display: none;">
	<h2>Worker's Compensation</h2>
	<div class="Content">
	<?php
		echo $form->input('CustomerCarrier.insurance_location_identification_number', array(
			'label' => 'Insurance Location ID#',
			'class' => 'Text100'
		));
		echo $form->input('CustomerCarrier.employer_name', array('class' => 'Text300'));
		echo $form->input('CustomerCarrier.employer_address_1', array('class' => 'Text300'));
		echo $form->input('CustomerCarrier.employer_address_2', array('class' => 'Text300'));
		echo $form->input('CustomerCarrier.employer_city', array(
			'label' => 'Employer City, State',
			'class' => 'Text250'
		));
		echo $form->input('CustomerCarrier.employer_zip_code', array('class' => 'Text100'));
		echo $form->input('CustomerCarrier.employer_identification_number', array('class' => 'Text200'));
	?>
	</div>
</div>
<div class="ClearBoth"></div>

<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="CustomerCarrierSaveBottom" value="Save" />
</div>
<?= $form->end() ?>

