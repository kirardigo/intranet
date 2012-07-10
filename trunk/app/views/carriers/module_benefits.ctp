<div class="FormColumn">
	<div class="GroupBox">
		<h2>Address</h2>
		<div class="Content">
		<?php
			echo $form->input('Carrier.name', array(
				'class' => 'Text300'
			));
			echo $form->input('Carrier.address_1', array(
				'class' => 'Text300'
			));
			echo $form->input('Carrier.address_2', array(
				'class' => 'Text300'
			));
			echo $form->input('Carrier.city', array(
				'label' => 'City/State',
				'class' => 'Text250',
				'div' => array('class' => 'FormColumn')
			));
			echo $form->input('Carrier.zip_code', array(
				'class' => 'Text100'
			));
		?>
		</div>
	</div>
	<div class="GroupBox">
		<h2>Website</h2>
		<div class="Content">
		<?php
			echo $form->input('Carrier.web_address', array(
				'label' => 'Website',
				'class' => 'Text400'
			));
			echo $form->input('Carrier.web_login', array(
				'label' => 'Login',
				'class' => 'Text200'
			));
			echo $form->input('Carrier.web_password', array(
				'label' => 'Password',
				'class' => 'Text200'
			));
		?>
		</div>
	</div>
</div>
<div class="FormColumn" style="width: 300px;">
	<div class="GroupBox">
		<h2>CSR Numbers</h2>
		<div class="Content">
		<?php
			echo $form->input('Carrier.phone_number', array(
				'label' => 'CSR/IVR Phone',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.toll_free_phone_number', array(
				'label' => 'CSR 800 Phone',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.fax_number', array(
				'label' => 'CSR Fax',
				'class' => 'Text100'
			));
		?>
		</div>
	</div>
	<div class="GroupBox">
		<h2>VOB</h2>
		<div class="Content">
		<?php	
			echo $form->input('Carrier.zirmed_evob_payor_identification_number', array(
				'label' => 'Zirmed VOB Payor ID',
				'class' => 'Text50'
			));
			echo $form->input('Carrier.vob_phone_number', array(
				'label' => 'VOB Phone',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.vob_fax_number', array(
				'label' => 'VOB Fax',
				'class' => 'Text100'
			));
		?>
		</div>
	</div>
	<div class="GroupBox">
		<h2>Misc</h2>
		<div class="Content">
		<?php
			echo $form->input('Carrier.is_carrier_inactive', array(
				'label' => array('text' => 'Inactive?', 'class' => 'Checkbox'),
				'class' => 'ReadOnly',
				'disabled' => 'disabled',
				'div' => array('class' => 'FormColumn', 'style' => 'margin-top: 12px;')
			));
			echo $form->input('Carrier.carrier_number_replacement', array(
				'label' => 'Placement Carrier',
				'readonly' => 'readonly',
				'class' => 'Text50 ReadOnly'
			));
		?>
			<div class="ClearBoth"></div>
		</div>
	</div>
</div>

<div class="ClearBoth"></div><br/>

<div class="GroupBox">
	<h2>VOB Notes</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.vob.note', array(
			'label' => false,
			'value' => isset($noteRecord['vob']['note']) ? $noteRecord['vob']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['vob']));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Authorization Info</h2>
	<div class="Content">
		<div class="FormColumn" style="margin-right: 30px;">
		<?php
			echo $form->input('Carrier.is_auth_required_for_dme', array(
				'label' => 'DME Auth Required',
				'options' => $dmeOptions,
				'empty' => true
			));
			echo $form->input('Carrier.is_auth_required_for_misc_hcpcs', array(
				'label' => array('text' => 'E1399/K0108 Auth Required?', 'class' => 'Checkbox'),
				'type' => 'checkbox',
				'div' => array('style' => 'margin: 2px 0')
			));
			echo $form->input('Carrier.is_cmn_required_for_auth', array(
				'label' => array('text' => 'CMN Required For Auth?', 'class' => 'Checkbox'),
				'type' => 'checkbox',
				'div' => array('style' => 'margin: 2px 0')
			));
			echo $form->input('Carrier.auth_fax_number', array(
				'label' => 'Authorization Fax',
				'class' => 'Text75'
			));
			echo $form->input('Carrier.auth_phone_number', array(
				'label' => 'Authorization Phone',
				'class' => 'Text75'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Carrier.auth_required_comment', array(
				'class' => 'Text300',
				'label' => 'Comment'
			));
			echo $form->input('Carrier.use_abn_when_not_medically_necessary', array(
				'label' => array('text' => 'ABN Required If Not Medically Necessary?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 2px 0')
			));
			echo $form->input('Carrier.use_abn_when_not_covered', array(
				'label' => array('text' => 'ABN Required If Not Covered?', 'class' => 'Checkbox'),
				'type' => 'checkbox',
				'div' => array('style' => 'margin: 2px 0')
			));
			echo $form->input('Carrier.is_advance_beneficiary_notice_ok', array(
				'label' => array('text' => 'ABN Allowed For Upgrade?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 2px 0')
			));
		?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Authorization Notes</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.auth.note', array(
			'label' => false,
			'value' => isset($noteRecord['auth']['note']) ? $noteRecord['auth']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['auth']));
	?>
	</div>
</div>

<script type="text/javascript">
	Modules.Carriers.Benefits.init();
</script>