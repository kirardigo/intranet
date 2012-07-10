<script type="text/javascript">
	function deleteRow(element, id)
	{
		row = $(element).up().up();
		row.addClassName('Highlight');
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = '/customers/delete/' + id;
		}
		
		row.removeClassName('Highlight');
	}
</script>

<br/>
<?= $html->link('Add a record', 'edit'); ?>
<br/><br/>

<?php
	echo $form->create('', array('url' => 'index'));
	echo $form->input('Search.account_number', array('class' => 'Text200', 'div' => array('style' => 'float: left; margin-right: 10px;')));
	echo $form->input('Search.name', array('class' => 'Text200', 'div' => array('style' => 'float: left; margin-right: 10px;')));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => false));
	echo $form->end();
?>

<table class="Styled">
	<tr>
		<th><?= $paginator->sort('Account Number', 'Customer.account_number'); ?></th>
		<th>Profit Center</th>
		<th><?= $paginator->sort('Name', 'Customer.name'); ?></th>
		<th>Address</th>
		<th>City, State</th>
		<th>Setup Date</th>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($pagedData as $row)
		{
			echo $html->tableCells(array(
					$html->link(ifnull($row['Customer']['account_number'], 'Edit'), 'edit/' . $row['Customer']['id']),
					h($row['Customer']['profit_center_number']),
					h($row['Customer']['name']),
					h($row['Customer']['address_1']),
					h($row['Customer']['city']),
					h(formatDate($row['Customer']['setup_date'])),
					$html->link('Delete', '#', array('onclick' => "deleteRow(this, {$row['Customer']['id']}); return false;"))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>