<script type="text/javascript">
	function editRecord(event)
	{
		event.stop();

		var recordID = event.element().up("td").down("input").value;		
		window.open("/permissions/edit/" + recordID, "_blank");
	}
	
	function deleteRecord(event)
	{
		event.stop();
		
		if (confirm("Are you sure you want to delete this permission?"))
		{
			var recordID = event.element().up("tr").down("input").value;		
			document.location = "/permissions/delete/" + recordID;
		}
	}

	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRecord);
		$$(".deleteLink").invoke("observe", "click", deleteRecord);
	});
</script>

<?= $html->link('Create a new permission', '/permissions/edit', array('target' => '_blank')) ?>
<br /><br />

<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Domain', 'PermissionDomain.name');
			echo $paginator->sortableHeader('Permission', 'permission');
		?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $record)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" id="recordID" value="' . $record['Permission']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($record['PermissionDomain']['name']),
					h($record['Permission']['permission']),
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>