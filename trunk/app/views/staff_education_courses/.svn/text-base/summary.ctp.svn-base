<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/staffEducationCourses/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/staffEducationCourses/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("StaffEducationCourseMeuNumber").clear();
		
		$("StaffEducationCoursesSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		
		$("SearchButton").observe("click", function() {
			$("StaffEducationCoursesSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("StaffEducationCoursesSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/staffEducationCourses/summary', 'id' => 'StaffEducationCoursesSummaryForm'));
	
	echo $form->input('StaffEducationCourse.meu_number', array(
		'label' => 'MEU#',
		'class' => 'Text75'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/staffEducationCourses/edit', array('target' => '_blank')); 
?>
<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('MEU#', 'meu_number');
			echo $paginator->sortableHeader('Title', 'title');
			echo $paginator->sortableHeader('Presenters', 'presenters');
			echo $paginator->sortableHeader('Hours', 'credit_hours');
			echo $paginator->sortableHeader('Certified', 'confirmation_method');
		?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['StaffEducationCourse']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['StaffEducationCourse']['meu_number']),
					h($row['StaffEducationCourse']['title']),
					h($row['StaffEducationCourse']['presenters']),
					h($row['StaffEducationCourse']['credit_hours']),
					h(ifset($confirmationMethods[$row['StaffEducationCourse']['confirmation_method']])),
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>