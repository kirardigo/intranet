<?php
	echo $html->css('tabs', false);
	echo $javascript->link('tabs', false);
?>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		mrs.bindDatePicker('MagnificentRedemptionOrderedDate');
		mrs.bindDatePicker('MagnificentRedemptionDispensedDate');
		
		$('CancelButton').observe('click', function() {
			location.href = '/magnificent_redemptions/pending';
		});
	});
</script>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Review Outstanding Order</h1>
<br class="ClearBoth" />

<?= $form->create('', array('url' => "review_pending/{$id}")); ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
	<?php
		echo $form->label('Recipient');
		echo '<div style="margin-bottom: 8px;">' . $this->data['MagnificentRedemption']['recipient_user'] . '</div>';
		echo $form->label('Value');
		echo '<div style="margin-bottom: 8px;">' . $this->data['MagnificentRedemption']['value'] . '</div>';
		echo $form->label('Requested Reward');
		echo '<div style="margin-bottom: 8px;">' . $this->data['MagnificentRedemption']['description'] . '</div>';
		echo $form->label('Requested Date');
		echo '<div style="margin-bottom: 8px;">' . $this->data['MagnificentRedemption']['requested_date'] . '</div>';
		echo $form->input('MagnificentRedemption.ordered_date', array('type' => 'text'));
		echo $form->input('MagnificentRedemption.dispensed_date', array('type' => 'text'));
		echo $form->input('MagnificentRedemption.notes', array('class' => 'StandardTextArea'));
		echo $form->input('MagnificentRedemption.id');
	?>
	</div>
</div>

<?php
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
 	$form->end();
?>