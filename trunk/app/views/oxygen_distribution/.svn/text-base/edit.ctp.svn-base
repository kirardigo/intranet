<script type="text/javascript">
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("OxygenDistributionDispensedDate");
	});
</script>
<div class="GroupBox" style="width: 500px;">
	<h2>Oxygen Distribution</h2>
	<div class="Content">
	<?php
		echo $form->create('', array('url' => "/oxygenDistribution/edit/{$id}"));
		
		echo $form->input('OxygenDistribution.account_number', array(
			'label' => 'Account#',
			'class' => 'Text75',
			'style' => 'margin-right: 20px;',
			'after' => $this->data['Customer']['name']
		));
		echo $form->input('OxygenDistribution.invoice_number', array(
			'label' => 'Invoice#',
			'class' => 'Text75'
		));
		echo $form->input('OxygenDistribution.dispensed_date', array(
			'type' => 'text',
			'class' => 'Text75'
		));
		echo $form->input('OxygenDistribution.dispensed_by', array(
			'class' => 'Text50'
		));
		echo $form->input('OxygenDistribution.tank_size', array(
			'options' => $tankSizes,
			'empty' => true
		));
		echo $form->input('OxygenDistribution.lot_number', array(
			'class' => 'Text100'
		));
		echo $form->input('OxygenDistribution.quantity', array(
			'class' => 'Text50'
		));
		
		echo $form->end();
	?>
	</div>
</div>