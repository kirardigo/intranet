<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("diagnosis:updated", {
				id: $F("DiagnosisId")
			});
			closeWindow();
		<?php endif; ?>
		
		$("SaveButton").observe("click", function() {
			$("DiagnosisEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("DiagnosisCode").focus();
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'DiagnosisEditForm')); ?>

<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
	<h2>Diagnosis</h2>
	<div class="Content">
	<?php
		echo $form->input('Diagnosis.code', array(
			'class' => 'Text75',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('Diagnosis.modified_by', array(
			'class' => 'Text75 ReadOnly',
			'readonly' => 'readonly',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('Diagnosis.modified', array(
			'type' => 'text',
			'class' => 'Text75 ReadOnly',
			'readonly' => 'readonly'
		));
		echo '<div class="ClearBoth"></div>';
		echo $form->input('Diagnosis.description', array('class' => 'Text350'));
		echo $form->input('Diagnosis.is_complex_rehabilitation', array(
			'label' => array('text' => 'Complex Rehab?', 'class' => 'Checkbox'),
			'div' => array('style' => 'margin-top: 15px;')
		));
	?>
	</div>
</div>
<br class="ClearBoth" />

<?php
	echo $form->hidden('Diagnosis.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>