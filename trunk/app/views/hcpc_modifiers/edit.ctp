<script type="text/javascript">
	function closeWindow()
	{
		window.open("", "_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {	
		mrs.bindDatePicker("HcpcModifierEffectiveDate");
		mrs.bindDatePicker("HcpcModifierTerminationDate");
		
		<?php if (isset($close) && $close): ?>
			//on a successful postback, close the entire window
			closeWindow();
		<?php endif; ?>
		
		//validate the form client-side on submit
		$("SaveButton").observe("click", function(event) {
			if (!$$R("HcpcModifierModifier") || !$$D("HcpcModifierEffectiveDate") || !$$D("HcpcModifierTerminationDate") || !$$R("HcpcModifierDescription"))
			{
				event.stop();
			}
		});
		
		//close the window on cancel
		$("CancelButton").observe("click", closeWindow);
	});
</script>

<?php
	echo $form->create('HcpcModifier', array('url' => '/hcpcModifiers/edit/{$id}'));
?>

<div class="GroupBox">
	<h2>HCPC Modifier</h2>
	<div class="Content">
	<?php
		echo $form->hidden('id');
		echo $form->input('HcpcModifier.modifier', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcModifier.category', array(
			'class' => 'Text100'
		));
		echo $form->input('HcpcModifier.level', array(
			'options' => $levels,
			'empty' => true
		));
		echo $form->input('HcpcModifier.code', array(
			'class' => 'Text25'
		));
		echo $form->input('HcpcModifier.effective_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcModifier.termination_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('HcpcModifier.description', array(
			'class' => 'TextArea800'
		));
		echo $form->input('HcpcModifier.dmerc_note', array(
			'class' => 'TextArea800',
			'label' => 'DMERC Note'
		));
		echo $form->input('HcpcModifier.mrs_note', array(
			'class' => 'TextArea800',
			'label' => 'MRS Note'
		));
	?>	
	</div>
</div>

<?php
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>