<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/staffEducation/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/staffEducation/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("StaffEducationDateStart").clear();
		$("StaffEducationDateEnd").clear();
		$("StaffEducationUsername").clear();
		$("StaffEducationDepartmentCode").clear();
		$("StaffEducationProfitCenterNumber").clear();
		$("StaffEducationCourseMeuNumber").clear();
		
		$("StaffEducationSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("StaffEducationDateStart");
		mrs.bindDatePicker("StaffEducationDateEnd");
		
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		
		$("SearchButton").observe("click", function() {
			$("StaffEducationSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("StaffEducationSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
	
	document.observe("meu:updated", function(event) {
		$("StaffEducationSummaryForm").submit();
	});
</script>

<?php
	//start creating the form
	echo $form->create('', array('url' => '/staffEducation/summary', 'id' => 'StaffEducationSummaryForm'));
	
	//create the controls to search and export results
	echo $form->input('StaffEducation.date_start', array(
		'label' => 'Start Date',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffEducation.date_end', array(
		'label' => 'End Date',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffEducation.username', array(
		'label' => 'User',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffEducation.department_code', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffEducation.profit_center_number', array(
		'label' => 'PCtr',
		'options' => $profitCenters,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
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
	
	echo $html->link('Add New Record', '/staffEducation/edit', array('target' => '_blank'));	
?>

	<!--create the tabled list of results-->
	<div style="margin-bottom: 5px;"></div>
	<table class="Styled">
		<tr>
			<th>&nbsp;</th>
			<?php
				echo $paginator->sortableHeader('Date', 'date_completed');
				echo $paginator->sortableHeader('User', 'username');
				echo $paginator->sortableHeader('D', 'department_code');
				echo $paginator->sortableHeader('PCtr', 'profit_center_number');
				echo $paginator->sortableHeader('MEU#', 'StaffEducationCourse.meu_number');
				echo $paginator->sortableHeader('Title', 'StaffEducationCourse.title');
				echo $paginator->sortableHeader('Hours', 'StaffEducationCourse.credit_hours');
			?>
			<th>&nbsp;</th>
		</tr>
		<?php
			foreach($records as $row)
			{
				echo $html->tableCells(
					array(
						'<input type="hidden" value="' . $row['StaffEducation']['id'] . '" />' . 
						$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
						h($row['StaffEducation']['date_completed']),
						h($row['StaffEducation']['username']),
						h($row['StaffEducation']['department_code']),
						h($row['StaffEducation']['profit_center_number']),
						h($row['StaffEducationCourse']['meu_number']),
						h($row['StaffEducationCourse']['title']),
						h($row['StaffEducationCourse']['credit_hours']),
						
						$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
</table>
<?= $this->element('page_links'); ?>
		
		
		
		
		
		
		
		
		
		
		
		
		
		