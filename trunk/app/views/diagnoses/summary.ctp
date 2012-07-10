<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/diagnoses/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/diagnoses/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("DiagnosisCode").clear();
		$("DiagnosisDescription").clear();
		$("DiagnosisIsComplexRehabilitation").clear();
				
		$("DiagnosisSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("DiagnosisSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("DiagnosisSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/diagnoses/summary', 'id' => 'DiagnosisSummaryForm'));
	
	echo $form->input('Diagnosis.code', array(
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Diagnosis.description', array(
		'class' => 'Text200',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Diagnosis.is_complex_rehabilitation', array(
		'options' => array(0 => 'No', 1 => 'Yes'),
		'empty' => 'Any'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/diagnoses/edit', array('target' => '_blank')); 
?>
<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Code', 'code');
			echo $paginator->sortableHeader('Description', 'description');
			echo $paginator->sortableHeader('Complex?', 'is_complex_rehabilitation');
			echo $paginator->sortableHeader('Modified By', 'modified_by');
			echo $paginator->sortableHeader('Modified', 'modified');
			echo $paginator->sortableHeader('Combo', 'combination');
			echo $paginator->sortableHeader('Num', 'number');
		?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['Diagnosis']['id'] . '" />' .
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false)),
					$row['Diagnosis']['code'],
					$row['Diagnosis']['description'],
					$row['Diagnosis']['is_complex_rehabilitation'] ? 'Y' : 'N',
					$row['Diagnosis']['modified_by'],
					$row['Diagnosis']['modified'],
					$row['Diagnosis']['combination'],
					$row['Diagnosis']['number'],
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>