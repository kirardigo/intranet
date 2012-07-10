<script type="text/javascript">
	function deleteRow(element, id)
	{
		row = $(element).up().up();
		row.addClassName('Highlight');
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = '/physicians/delete/' + id;
		}
		
		row.removeClassName('Highlight');
	}
</script>

<br/>
<?= $html->link('Add a record', 'edit'); ?>
<br/><br/>

<?php
	echo $form->create('', array('url' => 'index'));
	echo $form->input('Search.physician_number', array('class' => 'Text200', 'div' => array('style' => 'float: left; margin-right: 10px;')));
	echo $form->input('Search.name', array('class' => 'Text200', 'div' => array('style' => 'float: left; margin-right: 10px;')));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => false));
	echo $form->end();
?>

<table class="Styled">
	<tr>
		<th><?= $paginator->sort('Phy#', 'Physician.physician_number') ?></th>
		<th><?= $paginator->sort('Name', 'Physician.name') ?></th>
		<th>Address</th>
		<th>City, State</th>
		<th>UPIN</th>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($pagedData as $row)
		{
			echo $html->tableCells(array(
					$html->link(ifnull($row['Physician']['physician_number'], 'Edit'), 'edit/' . $row['Physician']['id']),
					h($row['Physician']['name']),
					h($row['Physician']['address_1']),
					h($row['Physician']['city']),
					h($row['Physician']['unique_identification_number']),
					$html->link('Delete', '#', array('onclick' => "deleteRow(this, {$row['Physician']['id']}); return false;"))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>