<script type="text/javascript">
	document.observe("dom:loaded", function() {
		$("StaffPalCodeCode").focus();
	});
</script>

<?php
	echo $html->link('Return to summary', '/staffPalCodes/summary');
	echo '<div style="margin-bottom: 5px;"></div>';
	
	echo $form->create('', array('url' => "/staffPalCodes/edit/{$id}"));
	
	echo $form->input('StaffPalCode.code', array('class' => 'Text50'));
	echo $form->input('StaffPalCode.description', array('class' => 'Text300'));
	
	echo '<br/>';
	echo $form->end('Save');
?>