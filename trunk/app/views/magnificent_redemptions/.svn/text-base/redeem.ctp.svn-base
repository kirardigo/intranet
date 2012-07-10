<?php
	echo $html->css('tabs', false);
	echo $javascript->link(
		array(
			'tabs',
			'scriptaculous.js?load=effects,controls'
		),
		false
	);
?>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		// Fix the autocompleter to work with IE
		mrs.fixAutoCompleter("StaffSearch");
		
		// Wire up form buttons
		$('SaveButton').observe('click', function() {
			$('RedeemForm').submit();
		});
		$('CancelButton').observe('click', function() {
			location.href = '/magnificent_redemptions/redeem';
		});
		
		// Keep multiple tabs from both being filled out
		$('MagnificentRedemptionMagnificentRedemptionOptionId').observe('change', function() {
			$('MagnificentRedemptionValue').clear();
		});
		$('MagnificentRedemptionValue').observe('change', function() {
			$('MagnificentRedemptionMagnificentRedemptionOptionId').clear();
		});
	});
</script>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Redeem Magnificents</h1>
<br class="ClearBoth" />

<?= $form->create('', array('id' => 'RedeemForm', 'url' => "redeem")); ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Redeem</a></li>
	<li><a href="#">Donate</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Redeem Tab -->
	<?php
		echo $form->label('Redeeming User');
		echo '<div style="margin-bottom: 8px;">' . $currentUser . '</div>';
		echo $form->label('Available Magnificents');
		echo '<div style="margin-bottom: 8px;">' . $availableCredits . '</div>';
		
		if (count($availableOptions))
		{
			echo $form->input('MagnificentRedemption.magnificent_redemption_option_id', array('options' => $availableOptions, 'empty' => 'Choose', 'label' => 'Available Options'));
		}
		else
		{
			echo $html->tag('p', 'There are no options available.');
		}
	?>
	</div>
	<div class="TabPage" style="display: none;"><!-- Donate Tab -->
	<?php
		echo $form->label('Donating User');
		echo '<div style="margin-bottom: 8px;">' . $currentUser . '</div>';
		echo $form->label('Available Magnificents');
		echo '<div style="margin-bottom: 8px;">' . $availableCredits . '</div>';
		
		echo $form->label('Recipient User');
		echo $ajax->autoComplete('Staff.search', '/ajax/staff/autoComplete/0/0', array(
			'minChars' => 3,
			'style' => 'width: 300px;'
		));
		echo $form->input('MagnificentRedemption.value');
	?>
	</div>
</div>

<?php
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
 	$form->end();
?>
