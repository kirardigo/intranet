<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = $F(row.down("td").down("input"));
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/hcpcCompetitiveBids/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = $F(row.down("td").down("input"));
		
		window.open("/hcpcCompetitiveBids/edit/" + recordID, "_blank");
		event.stop();
	}
	
	document.observe("dom:loaded", function() {		
		$$(".DeleteLink").invoke("observe", "click", deleteRow);
		$$(".EditLink").invoke("observe", "click", editRow);
		
		$("ResetButton").observe("click", function() { 
			var form = $("HcpcSummaryForm");
			form.select("input[type=text]").invoke("clear"); 
			form.submit();
		});
	});
</script>

<?php
	//create the form
	echo $form->create('HcpcCompetitiveBid', array('url' => '/hcpcCompetitiveBids/summary', 'id' => 'HcpcSummaryForm'));
	
	//create the controls
	echo $form->input('bid_number', array('class' => 'Text50', 'label' => 'Bid #', 'div' => array('class' => 'Horizontal')));
	echo $form->input('assigned_carrier_number', array('class' => 'Text50', 'label' => 'Carrier #'));
	
	//create the buttons
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	echo '</div>';
	
	echo $html->link('Add New Record', '/hcpcCompetitiveBids/edit', array('target' => '_blank'));	
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Bid Number', 'bid_number');
			echo $paginator->sortableHeader('Carrier Number', 'assigned_carrier_number');
		?>
		<!-- <th>&nbsp;</th> -->
	</tr>
	<?php
		foreach($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['HcpcCompetitiveBid']['id'] . '" />' . 
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'EditLink', 'escape' => false)),
					h($row['HcpcCompetitiveBid']['bid_number']),
					h($row['HcpcCompetitiveBid']['assigned_carrier_number']),						
					//$html->link($html->image('iconDelete.png'), '#', array('class' => 'DeleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>
