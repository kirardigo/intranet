<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Pending Redemptions</h1>
<br class="ClearBoth" />

<table class="Styled">
<?php
	echo $html->tableHeaders(array('Review', 'Recipient', 'Value', 'Reward', 'Requested', 'Ordered'));
	
	foreach ($this->data as $row)
	{
		echo $html->tableCells(
			array(
				$html->link('Review', "review_pending/{$row['MagnificentRedemption']['id']}"),
				$row['MagnificentRedemption']['recipient_user'],
				$row['MagnificentRedemption']['value'],
				$row['MagnificentRedemption']['description'],
				$row['MagnificentRedemption']['requested_date'],
				$row['MagnificentRedemption']['ordered_date']
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>
