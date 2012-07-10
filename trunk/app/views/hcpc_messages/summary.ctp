<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = $F(row.down("td").down("input"));
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/hcpcMessages/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = $F(row.down("td").down("input"));
		
		window.open("/hcpcMessages/edit/" + recordID, "_blank");
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
	echo $form->create('HcpcMessage', array('url' => '/hcpcMessages/summary', 'id' => 'HcpcSummaryForm'));
	
	//create the controls
	echo $form->input('reference_number', array('class' => 'Text50', 'label' => 'Ref #', 'div' => array('class' => 'Horizontal')));
	echo $form->input('message', array('class' => 'Text350', 'type' => 'text'));
	
	//create the buttons
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	echo '</div>';
	
	echo $html->link('Add New Record', '/hcpcMessages/edit', array('target' => '_blank'));	
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Reference Number', 'reference_number');
			echo $paginator->sortableHeader('Message', 'message');
		?>
		<!-- <th>&nbsp;</th> -->
	</tr>
	<?php
		foreach($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['HcpcMessage']['id'] . '" />' . 
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'EditLink', 'escape' => false)),
					h($row['HcpcMessage']['reference_number']),
					h($row['HcpcMessage']['message']),						
					//$html->link($html->image('iconDelete.png'), '#', array('class' => 'DeleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>
