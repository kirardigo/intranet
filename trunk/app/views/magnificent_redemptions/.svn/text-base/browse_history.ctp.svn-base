<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Magnificents History</h1>
<?= $html->link('Show my history', "history/{$currentUser}"); ?>
<br class="ClearBoth" />
<br/>

<table class="Styled">
<?php
	echo $html->tableHeaders(array('View', 'User', 'Earned', 'Redeemed', 'Available'));
	
	foreach ($this->data as $row)
	{
		echo $html->tableCells(
			array(
				$html->link('View', "history/{$row['username']}"),
				$row['user'],
				$row['earnedCredits'],
				$row['redeemedCredits'],
				$row['availableCredits'],
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>
