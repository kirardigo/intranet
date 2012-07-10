<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/lookupValues/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
	});
</script>

<?= $html->link("New Lookup Value", "/lookupValues/edit/{$id}", array('style' => 'float: left; padding-bottom: 5px;')); ?>
<table class="Styled">
	<tr>
		<th>Code</th>
		<th>Description</th>
		<th style="width: 40px;">Edit</th>
		<th style="width: 40px;">Up</th>
		<th style="width: 40px;">Down</th>
		<th style="width: 40px;">Delete</th>
	</tr>
	<?php
		foreach ($lookupValues as $i => $lookupValue)
		{	
			$valueID = $lookupValue['LookupValue']['id'];
			
			echo $html->tableCells(array(
				array(
					array(
						$form->hidden('id', array('value' => $valueID)) . h($lookupValue['LookupValue']['code']),
						array('width' => '50px;')
					),
					h($lookupValue['LookupValue']['description']),
					array(
						$html->link($html->image("iconEdit.png"), "/lookupValues/edit/{$id}/{$valueID}", array('escape' => false)), 
						array('class' => 'Center')
					),
					array(
						$html->link($html->image("iconArrowUp.png"), "/lookupValues/moveUp/{$valueID}", array('escape' => false)), 
						array('style' => 'visibility: ' . ($i == 0 ? ' hidden' : 'visible') . ';', 'class' => 'Center')
					),
					array(
						$html->link($html->image("iconArrowDown.png"), "/lookupValues/moveDown/{$valueID}", array('escape' => false)),
						array('style' => 'visibility: ' . ($i == count($lookupValues) - 1 ? ' hidden' : 'visible') . ';', 'class' => 'Center')
					),
					array(
						$html->link($html->image("iconDelete.png"), '#', array('class' => 'deleteLink', 'escape' => false)),
						array('class' => 'Center')
					)
				)),
				array(),
				array('class' => 'Alt')
			);
	
		}
	?>
</table>
