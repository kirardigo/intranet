<?= $javascript->link(array('scriptaculous.js?load=effects,controls'), false); ?>

<script type="text/javascript">
	document.observe("dom:loaded", function() {
		$("StaffSearch").focus();
		mrs.bindDatePicker("StaffPalTimePalDate");
		
		new Ajax.Autocompleter("StaffSearch", "StaffFullNameAutocomplete", "/ajax/staff/autoCompleteName", {
			minChars: 2,
			afterUpdateElement: function(element, listItem) {
				$("StaffPalTimeStaffId").value = listItem.id;
			}
		});
	});
</script>

<?php
	echo $html->link('Return to summary', '/staffPalTimes/summary');
	echo '<div style="margin-bottom: 5px;"></div>';
	
	echo $form->create('', array('url' => "/staffPalTimes/edit/{$id}"));
	
	echo $form->hidden('StaffPalTime.staff_id');
	echo $form->input('Staff.search', array('label' => 'Name', 'class' => 'Text200', 'value' => ifset($this->data['Staff']['full_name'])));
	echo '<div id="StaffFullNameAutocomplete" class="auto_complete" style="display: none;"></div>';
	echo $form->input('StaffPalTime.staff_pal_code_id', array(
		'label' => 'Description',
		'options' => $staffPalCodes,
		'empty' => true
	));
	echo $form->input('StaffPalTime.time', array('class' => 'Text75'));
	echo $form->input('StaffPalTime.pal_date', array(
		'type' => 'text',
		'label' => 'Date',
		'class' => 'Text75'
	));
	
	echo '<br/>';
	echo $form->end('Save');
?>