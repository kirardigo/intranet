<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("aaaCall:updated", {});
			closeWindow();
		<?php endif; ?>
		
		mrs.bindDatePicker("AaaCallCallDate");
		mrs.bindDatePicker("AaaCallNextCallDate");
		mrs.bindDatePicker("AaaCallFollowupCompleteDate");
		
		$("SaveButton").observe("click", function() {
			$("AaaCallEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("AaaCallAaaNumber").observe("change", function() {
			new Ajax.Request("/json/AaaReferrals/information/" + $F("AaaCallAaaNumber"), {
				onSuccess: function(transport) {
					$("AaaReferralName").innerHTML = transport.headerJSON.facility_name;
				}
			});
		});
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'AaaCallEditForm', 'target' => '_parent')); ?>

<div class="GroupBox">
	<h2>AAA Call</h2>
	<div class="Content">
	<?php
		echo $form->input('AaaCall.aaa_number', array(
			'class' => 'Text75',
			'style' => 'margin-right: 20px;',
			'after' => '<span id="AaaReferralName">' . ifset($this->data['AaaReferral']['facility_name']) . '</span>'
		));
		echo $form->input('AaaCall.precall_goal', array(
			'class' => 'Text500'
		));
		echo $form->input('AaaCall.call_date', array(
			'class' => 'Text75',
			'type' => 'text'
		));
		echo '<div style="clear: both;"></div>';
		echo $form->input('AaaCall.follow_up_thank_you', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('AaaCall.call_type', array(
			'class' => 'Text75'
		));
		echo $form->input('AaaCall.sales_staff_initials', array(
			'class' => 'Text75'
		));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Notes</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.call.note', array(
			'label' => 'Call',
			'value' => isset($noteRecord['call']['note']) ? $noteRecord['call']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['call']));
		
		echo $form->input('Note.manager.note', array(
			'label' => 'Manager',
			'value' => isset($noteRecord['manager']['note']) ? $noteRecord['manager']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['manager']));
		
		echo $form->input('AaaCall.next_call_date', array(
			'type' => 'text',
			'class' => 'Text75'
		));
		
		echo $form->input('Note.next_call.note', array(
			'label' => 'Next Call',
			'value' => isset($noteRecord['next_call']['note']) ? $noteRecord['next_call']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['next_call']));
		
		echo $form->input('AaaCall.followup_complete_date', array(
			'type' => 'text',
			'class' => 'Text75'
		));
	?>
	</div>
</div>

<?php
	echo $form->hidden('AaaCall.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>