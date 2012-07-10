<script type="text/javascript">
	function validateForm(event)
	{		
		valid = true;
		valid = $$R("HcpcCode");
		valid &= $$R("HcpcDescription"); 
		valid &= $$D("HcpcInitialDate");
		valid &= $$D("HcpcDiscontinuedDate");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
			event.stop();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("HcpcInitialDate");
		mrs.bindDatePicker("HcpcDiscontinuedDate");
	
		$("SaveButton").observe("click", validateForm);	
	});
</script>

<?php
	echo $form->create('', array('url' => '/hcpc/add', 'id' => 'HcpcNewForm'));
?>

<div class="GroupBox">
	<h2>HCPC</h2>
	<div class="Content">
	<?php
		echo $form->input('Hcpc.code', array(
			'class' => 'Text100'
		));
		echo $form->input('Hcpc.description', array(
			'class' => 'Text300'
		));
		echo $form->input('Hcpc.is_active', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('Hcpc.6_point_classification', array(
			'class' => 'Text100',
			'options' => $sixPointClassification
		));
		echo $form->input('Hcpc.pmd_class', array(
			'class' => 'Text300'
		));
		echo $form->input('Hcpc.is_serialized', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('style' => 'margin: 5px 0')
		));
		echo $form->input('Hcpc.cmn_code', array(
			'class' => 'Text300'
		));
		echo $form->input('Hcpc.initial_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
		echo $form->input('Hcpc.discontinued_date', array(
			'class' => 'Text100',
			'type' => 'text'
		));
	?>	
	</div>
</div>

<?php
	echo $form->submit('Save', array('id' => 'SaveButton'));
	echo $form->end();
?>