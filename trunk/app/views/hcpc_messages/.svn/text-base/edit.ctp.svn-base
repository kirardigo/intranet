<script type="text/javascript">
	function closeWindow()
	{
		window.open("", "_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			//on a successful postback, close the entire window
			closeWindow();
		<?php endif; ?>
		
		//validate the form client-side on submit
		$("SaveButton").observe("click", function(event) {
			if (!$$R("HcpcMessageReferenceNumber") || !$$N("HcpcMessageReferenceNumber") || !$$R("HcpcMessageMessage"))
			{
				event.stop();
			}
		});
		
		//close the window on cancel
		$("CancelButton").observe("click", closeWindow);
	});
</script>
<?php
	echo $form->create('HcpcMessage', array('url' => "/hcpcMessages/edit/{$id}"));
	
	echo $form->hidden('id');
	echo $form->input('reference_number', array('class' => 'Text50'));	
	echo $form->input('message', array('type' => 'text', 'class' => 'Text350'));	
	echo '<br/>';
	
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	
	echo $form->end();
?>