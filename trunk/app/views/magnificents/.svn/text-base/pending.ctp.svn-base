<script type="text/javascript">
	document.observe('dom:loaded', function() {
		$('ListShowAll').observe('click', function() {
			if ($('ListShowAll').checked)
			{
				location.href = '/magnificents/pending/1';
			}
			else
			{
				location.href = '/magnificents/pending';
			}
		});
	});
</script>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Pending Magnificents</h1>
<br class="ClearBoth" />

<?= $form->input('List.show_all', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'))); ?>

<table class="Styled">
<?php
	echo $html->tableHeaders(array(
		'Review',
		'Nominee',
		'Reason',
		'Date',
		'Intended Approver'
	));
	
	foreach ($this->data['Magnificents'] as $row)
	{
		echo $html->tableCells(
			array(
				$html->link('Review', "review_pending/{$row['Magnificent']['id']}"),
				$row['Magnificent']['recipient_user'],
				$row['Magnificent']['reason'],
				formatDate($row['Magnificent']['created']),
				$row['Magnificent']['approving_recipient_user']
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>
