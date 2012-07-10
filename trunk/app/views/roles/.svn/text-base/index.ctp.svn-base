<script type="text/javascript">
	function editRecord(event)
	{
		event.stop();

		var recordID = event.element().up("td").down("input").value;		
		window.open("/roles/edit/" + recordID, "_blank");
	}
	
	function deleteRecord(event)
	{
		event.stop();
		
		if (confirm("Are you sure you want to delete this role?"))
		{
			var recordID = event.element().up("tr").down("input").value;		
			document.location = "/roles/delete/" + recordID;
		}
	}

	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRecord);
		$$(".deleteLink").invoke("observe", "click", deleteRecord);
	});
</script>

<?= $html->link('Create a new role', '/roles/edit', array('target' => '_blank')) ?>
<br /><br />

<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Name', 'name');
		?>
		<th class="Center">&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $record)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" id="recordID" value="' . $record['Role']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($record['Role']['name']),
					array(
						$record['Role']['is_in_use'] ? '[Role in use - cannot delete]' : $html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false)),
						array('class' => 'Center')
					)
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>