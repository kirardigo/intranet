<?php
	echo $html->css('tabs', false);
	echo $javascript->link('tabs', false);
?>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		$('CancelButton').observe('click', function() {
			location.href = '/magnificent_redemption_options';
		});
	});
</script>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right')); ?>
<h1 class="MagnificentHeader"><?= ($id == null) ? 'Create' : 'Edit' ?> Redemption Option</h1>
<br class="ClearBoth" />

<?= $form->create('', array('url' => "edit/{$id}")); ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
	<?php
		echo $form->input('MagnificentRedemptionOption.id');
		echo $form->input('MagnificentRedemptionOption.description', array('class' => 'Text300'));
		echo $form->input('MagnificentRedemptionOption.value');
		echo $form->input('MagnificentRedemptionOption.is_active', array('label' => array('class' => 'Checkbox')));
	?>
	</div>
</div>

<?php
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
 	$form->end();
?>