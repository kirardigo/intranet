<?= $form->create('', array('id' => 'CustomerModuleCoreForm', 'url' => "/customers/fakeSave")); ?>

<div style="margin: 10px 0;">
<?php
	echo $ajax->submit('Save', array(
		'id' => 'CustomerCoreSaveTop',
		'class' => 'StyledButton',
		'url' => "/json/customers/saveCore/{$accountNumber}",
		'condition' => 'Modules.Customers.Core.onBeforePost(event)',
		'complete' => 'Modules.Customers.Core.onPostCompleted(request)'
	));
?>
</div>

<div class="GroupBox FormColumn" style="min-width: 310px; height: 425px;">
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
			echo $form->input('Customer.phone_number', array('label' => 'Phone', 'class' => 'Text75', 'div' => array('class' => 'Horizontal')));
			echo $form->input('Customer.cell_phone', array('class' => 'Text75'));
			echo '<div class="ClearBoth"></div>';
			echo $form->input('Customer.work_phone', array('class' => 'Text75'));
			echo $form->input('Customer.email', array('class' => 'Text250'));
			echo $form->input('Customer.is_using_email', array(
				'type' => 'checkbox',
				'label' => array('class' => 'Checkbox',	'text' => 'Is Using Email?'),
				'div' => array('style' => 'margin: 5px 0px;')
			));
			echo $form->input('Customer.place_of_residence', array(
				'options' => $placesOfResidence,
				'empty' => true
			));
			echo $form->input('Customer.county', array('class' => 'Text200', 'div' => array('class' => 'Horizontal')));
			echo '<div style="display: none;" id="CustomerCounty_autoComplete" class="auto_complete"></div>';
			echo $form->input('Customer.county_number', array('label' => 'County #', 'class' => 'Text75 ReadOnly', 'readonly' => 'readonly'));
			echo '<div class="ClearBoth"></div>';
			echo $form->input('CustomerBilling.salesman_number', array(
				'label' => 'Salesman',
				'class' => 'Text50',
				'style' => 'margin-right: 10px;',
				'after' => ifset($this->data['CustomerBilling']['salesman_name'], '')
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 315px; height: 425px;">
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
			echo $form->input('CustomerBilling.phone_number', array('label' => 'Billing Phone', 'class' => 'Text75'));
			echo $form->input('CustomerBilling.insuree_name', array(
				'class' => 'Text150',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.insuree_relationship', array(
				'label' => 'Relationship',
				'options' => $relationships,
				'empty' => ''
			));
			echo $form->input('CustomerBilling.home_health_agency_number', array(
				'label' => 'Home Health Agency',
				'class' => 'Text100'
			));
			echo $form->input('CustomerBilling.home_health_agency_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
			echo $form->input('Customer.account_status_code', array(
				'label' => 'Account Status',
				//'options' => $customerStatuses,
				//'empty' => true,
				'value' => ifset($customerStatuses[$this->data['Customer']['account_status_code']]),
				'class' => 'ReadOnly Text200',
				'readonly' => 'readonly'
			));
			echo $form->input('Customer.profit_center_number', array(
				'label' => 'Profit Center',
				'value' => $profitCenters[$this->data['Customer']['profit_center_number']],
				'class' => 'ReadOnly',
				'readonly' => 'readonly'
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="min-width: 200px; height: 425px;">
	<h2>General Info</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerBilling.date_of_birth', array('type' => 'text', 'class' => 'Text75'));
			echo $form->input('CustomerBilling.sex', array('options' => $sexes, 'empty' => ''));
			echo $form->input('CustomerBilling.social_security_number', array('class' => 'Text75'));
			echo $form->input('CustomerBilling.is_deceased', array(
				'label' => array('class' => 'Checkbox',	'text' => 'Is Deceased?'),
				'div' => array('style' => 'margin: 5px 0px')
			));
			echo $form->input('CustomerBilling.date_of_injury', array('type' => 'text', 'class' => 'Text75'));
			echo $form->input('CustomerBilling.profile_number', array(
				'options' => $profileNumbers,
				'empty' => ''
			));
			echo $form->input('CustomerBilling.weight', array(
				'label' => 'Weight (lbs)',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.height', array('label' => 'Height (in)', 'class' => 'Text75'));
			echo $form->input('CustomerBilling.new_client_packet_code', array(
				'label' => 'New Client Packet',
				'class' => 'Text75'
			));
			echo $form->input('Customer.setup_date', array('type' => 'text', 'class' => 'Text75'));
		?>
	</div>
</div>

<div class="ClearBoth">

<div class="GroupBox" style="height: 100px;">		
	<h2>Physicians</h2>
	<div class="Content">
		<?php
			for ($i = 1; $i <= 2; $i++)
			{
				$billingFieldName = ($i == 1) ? 'physician_number' : 'physician_number_2';
				
				echo $form->hidden("Physician.{$i}.id");
				echo $form->input("CustomerBilling.{$billingFieldName}", array(
					'id' => "Physician{$i}PhysicianNumber",
					'label' => 'Number',
					'class' => 'Text50 ReadOnly',
					'readonly' => 'readonly',
					'div' => array('class' => 'Text75 Horizontal')
				));
				echo $form->input("Physician.{$i}.name", array(
					'class' => 'Text350 ReadOnly',
					'readonly' => 'readonly',
					'autocomplete' => 'off',
					'div' => array('class' => 'Text400 Horizontal'),
					'after' => $html->image('indicator.gif', array('id' => "Physician{$i}Indicator", 'style' => 'display: none;'))
				));
				echo '<div style="display: none;" id="Physician' . $i . '_autoComplete" class="auto_complete AutoComplete550"></div>';
				echo $form->input("Physician.{$i}.phone_number", array(
					'class' => 'Text75 ReadOnly',
					'readonly' => 'readonly',
					'div' => array('class' => 'Horizontal')
				));
				
				echo '<div style="float: left; margin-top: 10px; margin-left: 20px;" class="IconContainer">';
				
				echo $html->link($html->image('iconSearch.png', array('title' => 'Change Physician')), "#", array(
					'escape' => false,
					'id' => "PhysicianCore{$i}Search"
				));
				
				$style = ($this->data['CustomerBilling'][$billingFieldName] === '') ? 'display: none;' : '';
				
				echo $html->link($html->image('iconDelete.png', array('title' => 'Remove Physician')), "#", array(
					'escape' => false,
					'id' => "PhysicianCore{$i}Delete",
					'style' => $style
				));
				echo $html->link($html->image('iconEdit.png', array('title' => 'Edit Physician Details')), "#", array(
					'escape' => false,
					'id' => "PhysicianCore{$i}Edit",
					'style' => $style
				));
				
				echo '</div>';
				echo '<div class="ClearBoth"></div><div style="height: 5px;"></div>';
			}
		?>
	</div>
</div>

<div class="GroupBox" style="height: 135px;">		
	<h2>AAA Referrals</h2>
	<div class="Content">
		<?php
			for ($i = 1; $i <= 3; $i++)
			{
				$customerFieldName = $aaaFields[$i]['field'];
				
				echo $form->hidden("AaaReferral.{$i}.id");
				
				echo $form->input("CustomerBilling.{$customerFieldName}", array(
					'id' => "AaaReferral{$i}AaaNumber",
					'label' => $aaaFields[$i]['label'],
					'class' => 'Text50 ReadOnly',
					'readonly' => 'readonly',
					'div' => array('class' => 'Text75 Horizontal')
				));
				echo $form->input("AaaReferral.{$i}.facility_name", array(
					'value' => isset($this->data['AaaReferral'][$i]) ? $this->data['AaaReferral'][$i]['facility_name'] . ': ' . $this->data['AaaReferral'][$i]['contact_name'] : '',
					'label' => 'Facility Name: Contact Name',
					'class' => 'Text350 ReadOnly',
					'readonly' => 'readonly',
					'autocomplete' => 'off',
					'div' => array('class' => 'Text400 Horizontal'),
					'after' => $html->image('indicator.gif', array('id' => "AaaReferral{$i}Indicator", 'style' => 'display: none;'))
				));
				echo '<div style="display: none;" id="AaaReferral' . $i . '_autoComplete" class="auto_complete AutoComplete550"></div>';
				echo $form->input("AaaReferral.{$i}.phone_number", array(
					'class' => 'Text75 ReadOnly',
					'readonly' => 'readonly',
					'div' => array('class' => 'Horizontal')
				));
				
				echo '<div style="float: left; margin-top: 10px; margin-left: 20px;" class="IconContainer">';
				
				echo $html->link($html->image('iconSearch.png', array('title' => 'Change AAA Referral')), "#", array(
					'escape' => false,
					'id' => "AaaReferralCore{$i}Search"
				));
				
				$style = ($this->data['CustomerBilling'][$customerFieldName] == '') ? 'display: none;' : '';
				
				echo $html->link($html->image('iconDelete.png', array('title' => 'Remove AAA Referral')), "#", array(
					'escape' => false,
					'id' => "AaaReferralCore{$i}Delete",
					'style' => $style
				));
				echo $html->link($html->image('iconEdit.png', array('title' => 'Edit AAA Referral')), "#", array(
					'escape' => false,
					'id' => "AaaReferralCore{$i}Edit",
					'style' => $style
				));
				
				echo '</div>';
				echo '<div class="ClearBoth"></div><div style="height: 5px;"></div>';
			}
		?>
	</div>
</div>

<div class="GroupBox">
	<h2>Diagnoses</h2>
	<div class="Content">
		<div class="FormColumn" style="width: 425px;">
			<?php
				for ($i = 1; $i <= 3; $i++)
				{
					echo $form->hidden("Diagnosis.{$i}.id");
					echo $form->hidden("CustomerBilling.diagnosis_code_{$i}");
					echo $form->input("Diagnosis.{$i}.code", array(
						'label' => false,
						'class' => 'Text35 ReadOnly',
						'readonly' => 'readonly',
						'before' => "{$i}. ",
						'div' => array('class' => 'Horizontal')
					));
					echo $form->input("Diagnosis.{$i}.description", array(
						'label' => false,
						'class' => 'Text250 ReadOnly',
						'readonly' => 'readonly',
						'autocomplete' => 'off',
						'div' => array('class' => 'Horizontal')
					));
					echo '<div style="display: none;" id="Diagnosis' . $i . '_autoComplete" class="auto_complete"></div>';
					echo '<div style="float: left;" class="IconContainer">';
						echo $html->link($html->image('iconSearch.png', array('title' => 'Change Diagnosis')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Search"
						));
						
						$style = ($this->data['CustomerBilling']["diagnosis_code_{$i}"] === '') ? 'display: none;' : '';
						
						echo $html->link($html->image('iconDelete.png', array('title' => 'Remove Diagnosis')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Delete",
							'style' => $style
						));
						echo $html->link($html->image('iconEdit.png', array('title' => 'Edit Diagnosis Details')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Edit",
							'style' => $style
						));
					echo '</div>';
					echo '<div class="ClearBoth"></div>';
				}
			?>
		</div>

		<div class="FormColumn" style="width: 425px;">
			<?php
				for ($i = 4; $i <= 6; $i++)
				{
					echo $form->hidden("Diagnosis.{$i}.id");
					echo $form->hidden("CustomerBilling.diagnosis_code_{$i}");
					echo $form->input("Diagnosis.{$i}.code", array(
						'label' => false,
						'class' => 'Text35 ReadOnly',
						'readonly' => 'readonly',
						'before' => "{$i}. ",
						'div' => array('class' => 'Horizontal')
					));
					echo $form->input("Diagnosis.{$i}.description", array(
						'label' => false,
						'class' => 'Text250 ReadOnly',
						'readonly' => 'readonly',
						'autocomplete' => 'off',
						'div' => array('class' => 'Horizontal')
					));
					echo '<div style="display: none;" id="Diagnosis' . $i . '_autoComplete" class="auto_complete"></div>';
					echo '<div style="float: left;" class="IconContainer">';
						echo $html->link($html->image('iconSearch.png', array('title' => 'Change Diagnosis')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Search"
						));
						
						$style = ($this->data['CustomerBilling']["diagnosis_code_{$i}"] === '') ? 'display: none;' : '';
						
						echo $html->link($html->image('iconDelete.png', array('title' => 'Remove Diagnosis')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Delete",
							'style' => $style
						));
						echo $html->link($html->image('iconEdit.png', array('title' => 'Edit Diagnosis Details')), "#", array(
							'escape' => false,
							'id' => "DiagnosisCore{$i}Edit",
							'style' => $style
						));
					echo '</div>';
					echo '<div class="ClearBoth"></div>';
				}
			?>
		</div>
		
		<br class="ClearBoth" />
	</div>
	<div class="ClearBoth"></div>
</div>

<div class="ClearBoth"></div>

<div class="GroupBox">
	<h2>Carriers</h2>
	<div class="Content">
		<div id="CustomerCoreCarriers"></div>
	</div>
</div>

<div class="GroupBox" style="height: 65px">
	<h2>Measurements</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerBilling.stats_seat_width', array(
				'label' => 'Seat Width',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_hip_width', array(
				'label' => 'Hip Width',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_hip_shoulder', array(
				'label' => 'Hip/Shoulder',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_hip_knee_right', array(
				'label' => 'Hip/Knee (R)',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_hip_knee_left', array(
				'label' => 'Hip/Knee (L)',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_knee_foot', array(
				'label' => 'Knee/Foot',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_updated', array(
				'type' => 'text',
				'label' => 'Updated Date',
				'class' => 'Text75 ReadOnly',
				'readonly' => 'readonly',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.stats_ini', array(
				'label' => 'Updated By',
				'class' => 'Text150 ReadOnly',
				'readonly' => 'readonly',
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="min-width: 310px; height: 270px;">
	<h2>Emergency Contact</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerBilling.emergency_contact_name', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.emergency_contact_address_1', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.emergency_contact_address_2', array('class' => 'Text250'));
			echo $form->input('CustomerBilling.emergency_contact_city', array(
				'label' => 'City, State',
				'class' => 'Text200',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.emergency_contact_zip_code', array('label' => 'Zip', 'class' => 'Text75'));
			echo $form->input('CustomerBilling.emergency_contact_phone_number', array(
				'label' => 'Phone',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerBilling.emergency_contact_relationship', array(
				'label' => 'Relationship',
				'options' => $relationships,
				'empty' => ''
			));
			echo $form->input('CustomerBilling.is_legal_representative', array('label' => array('class' => 'Checkbox', 'text' => 'Is Legal Representative?')));
			echo $form->input('CustomerBilling.advance_directive_code', array(
				'label' => 'Advance Directive',
				'options' => $advanceDirectives,
				'empty' => ''
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="min-width: 200px; height: 270px;">
	<h2>Archive Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Customer.archive_status', array(
				'options' => array('F' => 'File', 'S' => 'Scanned', 'A' => 'Archived'),
				'empty' => true
			));
			echo $form->input('Customer.archive_date', array('type' => 'text', 'class' => 'Text75'));
			echo $form->input('Customer.storage_scan_box', array('class' => 'Text100'));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 315px;">
	<h2>HIPAA Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Customer.hipaa_information_provided_date', array(
				'type' => 'text',
				'label' => 'Date Provided',
				'class' => 'Text75'
			));
			echo $form->input('Customer.hipaa_flag', array(
				'label' => 'HIPAA Flag',
				'options' => array('PNN' => 'Privacy Notice - No Restrictions', 'PNR' => 'Privacy Notice - Restrictions'),
				'empty' => true
			));
			echo $form->input('Customer.hipaa_note', array(
				'label' => 'HIPAA Note',
				'class' => 'Text300'
			));
		?>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 315px;">
	<h2>Account Verification</h2>
	<div class="Content">
		<?php
			echo $form->input('Customer.address_verification_date', array(
				'type' => 'text',
				'label' => 'Verification Date',
				'class' => 'Text75'
			));
			echo $form->input('Customer.address_verification_user', array(
				'label' => 'Verification User',
				'class' => 'Text100'
			));
		?>
	</div>
</div>

<br class="ClearBoth" />

<?php
	echo $ajax->submit('Save', array(
		'id' => 'CustomerCoreSaveBottom',
		'class' => 'StyledButton',
		'url' => "/json/customers/saveCore/{$accountNumber}",
		'condition' => 'Modules.Customers.Core.onBeforePost(event)',
		'complete' => 'Modules.Customers.Core.onPostCompleted(request)'
	));
	echo $form->end();
?>

<script type="text/javascript">
	Modules.Customers.Core.addHandlers();
	Modules.Customers.Core.updateCarrierInfo('<?= $accountNumber ?>');
</script>