<script type="text/javascript">
	function editRecord(event)
	{
		event.stop();

		var recordID = event.element().up("td").down("input").value;		
		window.open("/secureRoutes/edit/" + recordID, "_blank");
	}
	
	function deleteRecord(event)
	{
		event.stop();
		
		if (confirm("Are you sure you want to delete this secure route?"))
		{
			var recordID = event.element().up("tr").down("input").value;		
			document.location = "/secureRoutes/delete/" + recordID;
		}
	}

	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRecord);
		$$(".deleteLink").invoke("observe", "click", deleteRecord);
	});
</script>

<?= $html->link('Create a new route', '/secureRoutes/edit', array('target' => '_blank')) ?>
<br /><br />

<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Domain', 'PermissionDomain.name');
			echo $paginator->sortableHeader('Prefix', 'prefix');
			echo $paginator->sortableHeader('Controller', 'controller');
			echo $paginator->sortableHeader('Action', 'action');
		?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $record)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" id="recordID" value="' . $record['SecureRoute']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($record['PermissionDomain']['name']),
					h($record['SecureRoute']['prefix']),
					h($record['SecureRoute']['controller']),
					h($record['SecureRoute']['action']),
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>