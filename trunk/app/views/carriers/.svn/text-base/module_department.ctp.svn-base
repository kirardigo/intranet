<div class="GroupBox">
	<h2>General Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Carrier.carrier_name_when_browsing_by_group', array(
				'label' => 'Name When Browsing',
				'class' => 'Text300'
			));
		?>
		<div class="FormColumn" style="margin-right: 30px;">
		<?php
			echo $form->input('Carrier.fee_schedule_used', array(
				'options' => $feeScheduleTypes,
				'empty' => true
			));
			echo $form->input('Carrier.guidelines_used', array(
				'options' => $guidelineTypes,
				'empty' => true
			));
			echo $form->input('Carrier.are_parts_increased', array(
				'label' => array('text' => 'Parts Increase?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 2px 0')
			));
		?>
		</div>
		<div class="FormColumn" style="margin-right: 30px;">
		<?php
			echo $form->input('Carrier.medicare_allowable_discount', array(
				'label' => 'Fee Schedule Discount',
				'class' => 'Text25'
			));
			echo $form->input('Carrier.is_out_of_network_ok', array(
				'label' => array('text' => 'Out Of Network Allowed?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 2px 0')
			));
			echo $form->input('Carrier.usual_customary_rate_increase', array(
				'label' => 'Increase %',
				'class' => 'Text25'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Carrier.mfg_suggested_retail_price_discount', array(
				'label' => 'MSRP Discount For Misc',
				'class' => 'Text25'
			));
			echo $form->input('Carrier.membership_discount', array(
				'label' => 'Not Covered Discount Required',
				'class' => 'Text25'
			));
		?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Homecare Info</h2>
	<div class="Content">
	<?php
		echo $form->input('Carrier.can_homecare_department_provide', array(
			'label' => array('text' => 'Can Homecare Provide?', 'class' => 'Checkbox'),
			'div' => array('style' => 'margin: 2px 0')
		));
		
		echo $form->input('Note.homecare.note', array(
			'label' => false,
			'value' => isset($noteRecord['homecare']['note']) ? $noteRecord['homecare']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['homecare']));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Rehab Info</h2>
	<div class="Content">
	<?php
		echo $form->input('Carrier.can_rehab_department_provide', array(
			'label' => array('text' => 'Can Rehab Provide?', 'class' => 'Checkbox'),
			'div' => array('style' => 'margin: 2px 0')
		));
		
		echo $form->input('Note.rehab.note', array(
			'label' => false,
			'value' => isset($noteRecord['rehab']['note']) ? $noteRecord['rehab']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['rehab']));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Service Info</h2>
	<div class="Content">
	<?php
		echo $form->input('Carrier.can_service_department_provide', array(
			'label' => array('text' => 'Can Service Provide?', 'class' => 'Checkbox'),
			'div' => array('style' => 'margin: 2px 0')
		));
		
		echo $form->input('Note.service.note', array(
			'label' => false,
			'value' => isset($noteRecord['service']['note']) ? $noteRecord['service']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['service']));
	?>
	</div>
</div>