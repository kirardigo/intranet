<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/lookups/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
	});
</script>

<div style="margin-bottom: 5px;">
	<?= $html->link("New Lookup", '/lookups/edit/'); ?>
</div>

<table class="Styled">
	<tr>
		<th>Lookup Name</th>
		<th style="width: 50px;">Edit Lookup</th>
		<th style="width: 50px;">Edit Values</th>
		<th style="width: 50px;">Delete Lookup</th>
	</tr>
<?php
	foreach ($lookups as $lookup)
	{
		echo $html->tableCells(
			array(
				$form->hidden('id', array('value' => $lookup['Lookup']['id'])) . h($lookup['Lookup']['name']),
				array(
					$html->link($html->image("iconEdit.png"), "/lookups/edit/{$lookup['Lookup']['id']}", array('escape' => false)),
					array('class' => 'Center')
				),
				array(
					$html->link($html->image("iconListBullets.png"), "/lookupValues/index/{$lookup['Lookup']['id']}", array('escape' => false, 'target' => '_blank')),
					array('class' => 'Center')
				),
				array(
					$html->link($html->image("iconDelete.png"), '#', array('escape' => false, 'class' => 'deleteLink')),
					array('class' => 'Center')
				)
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>
<?= $paginator->numbers() ?> (Page <?= $paginator->counter() ?>)