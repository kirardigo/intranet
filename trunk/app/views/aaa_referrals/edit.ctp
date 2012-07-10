<?php
	echo $html->css('tabs', false);
	echo $javascript->link('scriptaculous.js?load=effects,controls', false);
?>

<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	function aaaCallback()
	{
		return "data[AaaReferral][search]=" + $F("AaaReferralMailtoAaaNumber");
	}
	
	function aaaAfterUpdate(element, listItem)
	{
		new Ajax.Request("/json/aaaReferrals/information/" + listItem.id, {
			onSuccess: function(transport) {
				$("AaaReferralMethodOfCommunication").value = transport.headerJSON.method_of_communication;
				$("AaaReferralMailAddress1").value = transport.headerJSON.mail_address_1;
				$("AaaReferralMailAddress2").value = transport.headerJSON.mail_address_2;
				$("AaaReferralMailCityStateZip").value = transport.headerJSON.mail_city_state_zip;
			}
		});
	}
	
	document.observe('dom:loaded', function() {
		<?php
			if (isset($close) && $close)
			{
				if (isset($new) && $new)
				{
					echo 'alert("New AAA Number: ' . $this->data['AaaReferral']['aaa_number'] . '\nYou have successfully saved.");';
				}
				else
				{
					echo 'alert("You have successfully saved.");';
				}
				
				echo 'window.opener.document.fire("aaaReferral:updated", { id: $F("AaaReferralId") }); closeWindow();';
			}
		?>
		
		mrs.fixAutoCompleter("AaaProfitCenterCountyName");
		
		mrs.bindPhoneFormatting(
			"AaaReferralPhoneNumber",
			"AaaReferralFaxNumber",
			"AaaReferralCellPhoneNumber"
		);
		
		$("SaveButton").observe("click", function() {
			$("AaaReferralEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("CopyContactInfoLink").observe("click", function(event) {
			event.stop();
			$("AaaReferralMethodOfCommunication").clear();
			$("AaaReferralMailAddress1").clear();
			$("AaaReferralMailAddress2").value = $F("AaaReferralAddress1");
			$("AaaReferralMailCityStateZip").value = $F("AaaReferralCityState") + " " + $F("AaaReferralZipCode");
			
			if ($("AaaReferralAaaNumber") != undefined)
			{
				$("AaaReferralMailtoAaaNumber").value = $F("AaaReferralAaaNumber");
			}
		});
		
		new Ajax.Autocompleter("AaaReferralMailtoAaaNumber", "AaaReferralMailtoAaaNumber_autoComplete", "/ajax/aaaReferrals/autoCompleteByFacility", {
			minChars: 3,
			callback: aaaCallback,
			afterUpdateElement: aaaAfterUpdate
		});
		mrs.fixAutoCompleter("AaaReferralMailtoAaaNumber");
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'AaaReferralEditForm')); ?>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
		
		<?php if ($id !== null): ?>
		<div class="GroupBox" style="width: 877px">
			<h2>AAA Number</h2>
			<div class="Content">
			<?php
				echo ifset($this->data['AaaReferral']['aaa_number']);
				echo $form->hidden('AaaReferral.aaa_number', array('value' => $this->data['AaaReferral']['aaa_number']));
			?>
			</div>
		</div>
		<?php endif; ?>
		
		<div class="FormColumn">
			<div class="GroupBox" style="width: 425px;">
				<h2>Referral Information</h2>
				<div class="Content">
				<?php
					echo $form->input('AaaReferral.contact_name', array('class' => 'Text250'));
					echo $form->input('AaaReferral.contact_title');
					echo $form->input('AaaReferral.staff_credentials', array(
						'options' => $credentials,
						'empty' => true
					));
					echo $form->input('AaaReferral.facility_name', array('class' => 'Text250'));
					echo $form->input('AaaReferral.facility_type', array(
						'options' => $facilityTypes,
						'empty' => true
					));
					echo $form->input('AaaReferral.group_code', array('class' => 'Text250'));
				?>
				</div>
			</div>
			
			<div class="GroupBox" style="width: 425px;">
				<span style="float: right; margin: 3px 3px 0 0;"><a href="#" id="CopyContactInfoLink">Copy Contact Info</a></span>
				<h2>Mailing Information</h2>
				<div class="Content">
				<?php
					echo $form->input('AaaReferral.mailto_aaa_number', array(
						'label' => 'Mail To AAA#',
						'class' => 'Text50'
					));
					echo '<div id="AaaReferralMailtoAaaNumber_autoComplete" style="display: none;" class="auto_complete"></div>';
					echo $form->input('AaaReferral.method_of_communication', array(
						'label' => 'Correspondence Preference',
						'options' => $communicationMethods,
						'empty' => true
					));
					echo $form->input('AaaReferral.mail_address_1', array('class' => 'Text250'));
					echo $form->input('AaaReferral.mail_address_2', array('class' => 'Text250'));
					echo $form->input('AaaReferral.mail_city_state_zip', array(
						'label' => 'Mail City, State Zip',
						'class' => 'Text250'
					));
				?>
				</div>
			</div>
			
			<div class="GroupBox" style="width: 425px;">
				<h2>Notes</h2>
				<div class="Content">
				<?php
					echo $form->input('Note.general.note', array(
						'label' => 'General Notes',
						'class' => 'TextArea400'
					));
					echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['general']));
				?>
				</div>
			</div>
			
			<?= $html->link('Add AAA Call Record', '/aaaCalls/edit', array('target' => '_blank')); ?>
		</div>
		
		<div class="FormColumn">
			<div class="GroupBox" style="width: 420px;">
				<h2>Contact Information</h2>
				<div class="Content">
				<?php
					echo $form->input('AaaReferral.address_1', array('class' => 'Text250'));
					echo $form->input('AaaReferral.city_state', array(
						'label' => 'City, State',
						'class' => 'Text250'
					));
					echo $form->input('AaaReferral.zip_code');
					
					echo '<div><label for="CountyName">County</label>';
					echo $ajax->autoComplete('AaaProfitCenter.county_name', '/ajax/aaaProfitCenters/autoComplete', array(
						'style' => 'width: 180px;',
						'minChars' => 3,
						'value' => ifset($this->data['AaaReferral']['county_name'])
					));
					if (isset($this->validationErrors['AaaReferral']['county_name']))
					{
						echo $html->div('error-message', $this->validationErrors['AaaReferral']['county_name']);
					}
					echo '</div>';
					
					echo $form->input('AaaReferral.phone_number', array(
						'div' => array('class' => 'Horizontal')
					));
					echo $form->input('AaaReferral.phone_extension', array(
						'class' => 'Text50',
						'label' => 'Extension'
					));
					echo $form->input('AaaReferral.fax_number');
					echo $form->input('AaaReferral.cell_phone_number');
					echo $form->input('AaaReferral.contact_email', array('class' => 'Text250'));
				?>
				</div>
			</div>
			
			<div class="GroupBox" style="width: 420px;">
				<h2>Other Information</h2>
				<div class="Content">
				<?php
					echo $form->input('AaaReferral.staff_initials_email_1', array(
						'label' => 'Email Ini 1',
						'class' => 'Text50',
						'div' => array('class' => 'Horizontal')
					));
					echo $form->input('AaaReferral.staff_initials_email_2', array(
						'label' => 'Email Ini 2',
						'class' => 'Text50',
						'div' => array('class' => 'Horizontal')
					));
					echo $form->input('AaaReferral.staff_initials_email_3', array(
						'label' => 'Email Ini 3',
						'class' => 'Text50'
					));
					echo '<div style="margin-top: 5px"></div>';
					
					echo $form->input('AaaReferral.is_active_for_rehab', array(
						'label' => array('class' => 'Checkbox')
					));
					
					echo $form->input('AaaReferral.rehab_salesman', array(
						'options' => $rehabSalesman,
						'empty' => true
					));
					echo $form->input('AaaReferral.rehab_market_code', array(
						'options' => $rehabMarketingCodes,
						'empty' => true
					));
					echo '<div style="margin-top: 5px"></div>';
					
					echo $form->input('AaaReferral.is_active_for_homecare', array(
						'label' => array('class' => 'Checkbox')
					));
					echo $form->input('AaaReferral.homecare_salesman', array('class' => 'Text50'));
					echo $form->input('AaaReferral.homecare_market_code', array(
						'options' => $marketCodes,
						'empty' => true
					));
					echo '<div style="margin-top: 5px"></div>';
					
					echo $form->input('AaaReferral.is_active_for_access', array(
						'label' => array('class' => 'Checkbox')
					));
					echo '<div style="margin-top: 5px"></div>';
					
					echo $form->input('AaaReferral.is_aaa_inactive', array(
						'label' => array('class' => 'Checkbox', 'text' => 'Is Deactivated')
					));
				?>
				</div>
			</div>
		</div>
		
		<div class="ClearBoth"></div>
	</div>
</div>
<br/>
<?php
	echo $form->hidden('AaaReferral.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>