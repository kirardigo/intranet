<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/priorAuthorizationDenials/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/priorAuthorizationDenials/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("PriorAuthorizationDenialCode").clear();
		$("PriorAuthorizationDenialDescription").clear();
				
		$("AuthPriorDenialSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("AuthPriorDenialSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("AuthPriorDenialSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/priorAuthorizationDenials/summary', 'id' => 'AuthPriorDenialSummaryForm'));
	
	echo $form->input('PriorAuthorizationDenial.code', array(
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('PriorAuthorizationDenial.description', array(
		'class' => 'Text200'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/priorAuthorizationDenials/edit', array('target' => '_blank')); 
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
					'<input type="hidden" value="' . $row['PriorAuthorizationDenial']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					$row['PriorAuthorizationDenial']['code'],
					$row['PriorAuthorizationDenial']['description'],
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>