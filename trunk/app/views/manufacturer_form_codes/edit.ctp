<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("manufacturerFormCode:updated");
			closeWindow();
		<?php endif; ?>
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
	});
</script>

<?= $form->create('', array('url' => "/manufacturerFormCodes/edit/{$id}", 'id' => 'MfgFormCodeEditForm')); ?>

<div class="GroupBox" style="width: 400px;">
	<h2>MFG Header Codes</h2>
	<div class="Content">
	<?php
		echo $form->input('ManufacturerFormCode.form_code', array(
			'class' => 'Text100'
		));
		echo $form->input('ManufacturerFormCode.sequence_number', array(
			'label' => 'Seq#',
			'class' => 'Text50'
		));
		echo $form->input('ManufacturerFormCode.sequence_description', array(
			'label' => 'Description',
			'class' => 'Text300'
		));
	?>
	</div>
</div>

<?php
	echo $form->hidden('ManufacturerFormCode.id');
	echo $form->submit('Save', array('id' => 'SaveButton', 'style' => 'margin: 0;', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>