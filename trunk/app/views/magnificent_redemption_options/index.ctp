<script type="text/javascript">
	function deleteRow(element, id)
	{
		row = $(element).up().up();
		row.addClassName('Highlight');
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = '/magnificent_redemption_options/delete/' + id;
		}
		
		row.removeClassName('Highlight');
	}
</script>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right')); ?>
<h1 class="MagnificentHeader">Redemption Options</h1>
<?= $html->link('Create New Option', 'edit'); ?>
<br/><br class="ClearBoth" />

<table class="Styled">
<?php
	echo $html->tableHeaders(array('Edit', 'Value', 'Description', 'Active?', 'Delete'));
	
	foreach ($this->data as $row)
	{
		echo $html->tableCells(
			array(
				$html->link('Edit', "edit/{$row['MagnificentRedemptionOption']['id']}"),
				$row['MagnificentRedemptionOption']['value'],
				$row['MagnificentRedemptionOption']['description'],
				($row['MagnificentRedemptionOption']['is_active']) ? 'Yes' : 'No',
				$html->link('Delete', '#', array('onclick' => "deleteRow(this, {$row['MagnificentRedemptionOption']['id']}); return false;"))
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>
