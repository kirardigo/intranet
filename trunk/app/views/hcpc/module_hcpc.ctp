<?php
	echo $form->create('', array('id' => 'HcpcForm', 'url' => "/hcpc/fakeSave"));
	echo $form->hidden('Hcpc.id');
?>

<div class="GroupBox">
	<h2>HCPC</h2>
	<div class="Content">
		<div class="FormColumn">
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
			echo $form->input('Hcpc.is_serialized', array(
				'label' => array('class' => 'Checkbox'),
				'div' => array('style' => 'margin: 5px 0')
			));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('Hcpc.6_point_classification', array(
					'class' => 'Text100',
					'label' => 'Class',
					'options' => $sixPointClassification
				));
				echo $form->input('Hcpc.pmd_class', array(
					'label' => 'PMD Class',
					'options' => $pmdClasses
				));
				
				echo $form->input('Hcpc.cmn_code', array(
					'label' => 'CMN Code',
					'class' => 'Text300'
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
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
		<br style="clear: both;" /><br />
	</div>
</div>

<?php
	echo $ajax->submit('Save', array(
		'id' => 'HcpcFormSave',
		'class' => 'StyledButton',
		'div' => array('class' => 'Horizontal'),
		'url' => "/json/hcpc/edit/{$this->data['Hcpc']['id']}",
		'condition' => 'Modules.Hcpc.Hcpc.onBeforePost(event)',
		'complete' => 'Modules.Hcpc.Hcpc.onPostCompleted(request)'
	));

	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>

<script type="text/javascript">
	Modules.Hcpc.Hcpc.init();
</script>