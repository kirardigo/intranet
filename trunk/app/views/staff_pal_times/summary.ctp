<script type="text/javascript">	
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/staffPalTimes/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		location.href = "/staffPalTimes/edit/" + recordID;
		event.stop();
	}
	
	function resetFilters()
	{
		$("StaffProfitCenterNumber").clear();
		$("StaffDepartment").clear();
		$("StaffUserId").clear();
		$("StaffFullName").clear();
		$("StaffPalTimeStartDate").clear();
		$("StaffPalTimeEndDate").clear();
		
		descField = $("StaffPalTimeStaffPalCodeId");
		for (i = 0; i < descField.length; i++)
		{
			descField.options[i].selected = false;
		}
		
		$("StaffPalTimeForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("StaffPalTimeStartDate");
		mrs.bindDatePicker("StaffPalTimeEndDate");
		
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("StaffPalTimeForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("StaffPalTimeForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
		
		mrs.makeScrollable("ResultsTable", { aoColumns: [{bSortable: false}, null, null, null, null, null, null, null, {bSortable: false}] });
	});
</script>

<?php
	echo $form->create('', array('url' => '/staffPalTimes/summary', 'id' => 'StaffPalTimeForm'));
	
	
	echo $form->input('StaffPalTime.staff_pal_code_id', array(
		'label' => 'Description**',
		'options' => $palCodes,
		'multiple' => 'multiple',
		'style' => 'height: 60px',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Staff.profit_center_number', array(
		'label' => 'PCtr',
		'options' => $profitCenters,
		'empty' => '',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Staff.department', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => '',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffPalTime.staff_user_id', array(
		'label' => 'Login*',
		'class' => 'Text100',
		'maxlength' => false,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Staff.full_name', array(
		'label' => 'Full Name',
		'class' => 'Text200'
	));
	echo '<br/>';
	echo $form->input('StaffPalTime.start_date', array(
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('StaffPalTime.end_date', array(
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo '<div style="margin: 10px 0 0 5px">*Separate multiple values with commas<br/>**Ctrl-click for multiple</div>';
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->button('Search', array('id' => 'SearchButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	echo '</div>';
	
	echo $html->link('Add New Record', '/staffPalTimes/edit'); 
?>
<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th>PCtr</th>
			<th>D</th>
			<th>Login</th>
			<th>Full Name</th>
			<th>Description</th>
			<th>Time</th>
			<th>Date</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['StaffPalTime']['id'] . '" />' .
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false)),
					$row['Staff']['profit_center_number'],
					$row['Staff']['department'],
					$row['Staff']['user_id'],
					$row['Staff']['full_name'],
					$row['StaffPalCode']['code'] . ' - ' . $row['StaffPalCode']['description'],
					$row['StaffPalTime']['time'],
					$row['StaffPalTime']['pal_date'],
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>