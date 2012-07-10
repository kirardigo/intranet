<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/staffPalCodes/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		location.href = "/staffPalCodes/edit/" + recordID;
		event.stop();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
	});
</script>

<?php
	echo $html->link('Add New Record', '/staffPalCodes/edit'); 
?>
<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Code', 'code');
			echo $paginator->sortableHeader('Description', 'description');
		?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					$form->hidden('StaffPalCode.id', array('value' => $row['StaffPalCode']['id'])) .
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false)),
					$row['StaffPalCode']['code'],
					$row['StaffPalCode']['description'],
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>