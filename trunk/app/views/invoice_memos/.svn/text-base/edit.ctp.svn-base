<script type="text/javascript">
	function submitForm()
	{
		$("InvoiceMemoForm").submit();
	}
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("invoiceMemo:updated");
			closeWindow();
		<?php endif; ?>
		
		$("SaveButton").observe("click", submitForm);
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
	});
</script>

<?php
	echo $form->create('', array('url' => "edit/{$id}", 'id' => 'InvoiceMemoForm'));
	
	echo $form->input('InvoiceMemo.code', array(
		'class' => 'Text50'
	));
	echo $form->input('InvoiceMemo.description', array(
		'class' => 'Text500'
	));
	echo $form->input('InvoiceMemo.memo', array(
		'style' => 'width: 575px; height: 80px;'
	));
	
	echo '<div style="margin-top: 5px;"></div>';
	echo $form->button('Save', array('id' => 'SaveButton', 'style' => 'margin: 0;'));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>