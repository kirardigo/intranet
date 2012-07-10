<?php
	echo $html->css('tabs', false);
?>

<script type="text/javascript">
	function copyOfficeInfo()
	{
		$("PhysicianLocationName").value = $F("PhysicianName");
		$("PhysicianLocationAddress1").value = $F("PhysicianAddress1");
		$("PhysicianLocationAddress2").value = $F("PhysicianAddress2");
		$("PhysicianLocationCity").value = $F("PhysicianCity");
		$("PhysicianLocationZipCode").value = $F("PhysicianZipCode");
		$("PhysicianLocationPhoneNumber").value = $F("PhysicianPhoneNumber");
		$("PhysicianLocationFaxNumber").value = $F("PhysicianFaxNumber");
	}
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("physician:updated", {
				id: $F("PhysicianId")
			});
			closeWindow();
		<?php endif; ?>
		
		mrs.bindDatePicker("PhysicianLicenseNumberUpdateDate");
		
		mrs.bindPhoneFormatting(
			"PhysicianPhoneNumber",
			"PhysicianFaxNumber",
			"PhysicianLocationPhoneNumber",
			"PhysicianLocationFaxNumber"
		);
		
		$("SaveButton").observe("click", function() {
			$("PhysicianEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'PhysicianEditForm')); ?>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
	
		<?php if ($id !== null): ?>
		<div class="GroupBox" style="width: 877px">
			<h2>Physician Number</h2>
			<div class="Content">
			<?php
				echo ifset($this->data['Physician']['physician_number']);
				echo $form->hidden('Physician.physician_number', array('value' => $this->data['Physician']['physician_number']));
			?>
			</div>
		</div>
		<?php endif; ?>
		
		<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
			<h2>Practice Business Office</h2>
			<div class="Content">
			<?php
				echo $form->input('Physician.name', array('class' => 'Text250'));
				echo $form->input('Physician.address_1', array('class' => 'Text250'));
				echo $form->input('Physician.address_2', array('class' => 'Text250'));
				echo $form->input('Physician.city', array('class' => 'Text250', 'label' => 'City, State'));
				echo $form->input('Physician.zip_code');
				echo $form->input('Physician.phone_number');
				echo $form->input('Physician.fax_number', array(
					'div' => array('class' => 'Horizontal', 'style' => 'margin-right: 80px;')
				));
				echo $form->input('Physician.is_fax_cmn_allowed', array(
					'label' => array('text' => 'Fax CMN?', 'class' => 'Checkbox'),
					'div' => array('style' => 'margin-top: 15px;')
				));
				echo '<div class="ClearBoth"></div>';
				echo $form->input('Physician.number_of_days_for_followup_1', array('label' => 'Days CMN FUP 1'));
				echo $form->input('Physician.number_of_days_for_followup_2', array('label' => 'Days CMN FUP 2'));
				echo $form->input('Physician.specialty', array('label' => 'Type/Specialty'));
				echo $form->input('Physician.contact_name', array('class' => 'Text250'));
				echo $form->input('Physician.email', array('class' => 'Text250'));
			?>
			</div>
		</div>
		
		<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
			<div style="float: right; margin: 3px 5px;">
				<a href="#" onclick="copyOfficeInfo(); return false;">Copy Office Info</a>
			</div>
			<h2>Location Client Seen</h2>
			<div class="Content">
			<?php
				echo $form->input('Physician.location_name', array('class' => 'Text250', 'label' => 'Name'));
				echo $form->input('Physician.location_address_1', array('class' => 'Text250', 'label' => 'Address 1'));
				echo $form->input('Physician.location_address_2', array('class' => 'Text250', 'label' => 'Address 2'));
				echo $form->input('Physician.location_city', array('class' => 'Text250', 'label' => 'City'));
				echo $form->input('Physician.location_zip_code', array('label' => 'Zip Code'));
				echo $form->input('Physician.location_phone_number', array('label' => 'Phone Number'));
				echo $form->input('Physician.location_fax_number', array('label' => 'Fax Number'));
			?>
			</div>
		</div>
		
		<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
			<h2>Identification Numbers</h2>
			<div class="Content">
			<?php
				echo $form->input('Physician.unique_identification_number', array('label' => 'UPIN#'));
				echo $form->input('Physician.medicaid_provider_number', array('label' => 'ODJFS#'));
				echo $form->input('Physician.national_provider_identification_number', array('label' => 'NPI'));
				echo $form->input('Physician.license_number', array('class' => 'Text250'));
				echo $form->input('Physician.license_number_update_date', array('type' => 'text'));
			?>
			</div>
		</div>
		
		<div class="ClearBoth"></div>
		
		<div class="GroupBox" style="width: 877px">
			<h2>Notes</h2>
			<div class="Content">
			<?php
				echo $form->input('Note.note', array(
					'label' => false,
					'value' => isset($noteRecord['comments']['note']) ? $noteRecord['comments']['note'] : '',
					'class' => 'StandardTextArea'
				));
				echo $this->element('note_info', array('noteRecord' => &$noteRecord['comments']));
			?>
			</div>
		</div>
	</div>
</div>
<br/>
<?php
	echo $form->hidden('Physician.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>