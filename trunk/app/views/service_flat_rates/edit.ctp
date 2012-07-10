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
			if (!$$R("ServiceFlatRateHcpcCode") || !$$R("ServiceFlatRateDescription") || !$$R("ServiceFlatRateMrsFlatRate") || !$$N("ServiceFlatRateMrsFlatRate", true) || !$$N("ServiceFlatRateCmsFlatRate", true))
			{
				event.stop();
			}
		});
		
		//close the window on cancel
		$("CancelButton").observe("click", closeWindow);
	});
</script>
<?php
	echo $form->create('ServiceFlatRate', array('url' => "/serviceFlatRates/edit/{$id}"));
	
	echo $form->hidden('id');
	echo $form->input('hcpc_code', array('class' => 'Text50', 'label' => 'HCPC Code'));	
	echo $form->input('description', array('type' => 'text', 'class' => 'Text350'));
	echo $form->input('mrs_flat_rate', array('type' => 'text', 'class' => 'Text50', 'label' => 'MRS Rate'));
	echo $form->input('cms_flat_rate', array('type' => 'text', 'class' => 'Text50', 'label' => 'CMS Rate'));
	
	echo '<br/>';
	
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	
	echo $form->end();
?>