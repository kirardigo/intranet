<script type="text/javascript">
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/maintenanceLog/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("MaintenanceLogSerializedEquipmentNumber").clear();
		$("MaintenanceLogDateOfServiceStart").clear();
		$("MaintenanceLogDateOfServiceEnd").clear();
		$("MaintenanceLogComment").clear();
		$("MaintenanceLogMaintenanceType").clear();
		
		$("MaintenanceLogIndexForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRow);
		mrs.bindDatePicker("MaintenanceLogDateOfServiceStart");
		mrs.bindDatePicker("MaintenanceLogDateOfServiceEnd");
		$("SearchButton").observe("click", function() {
			$("MaintenanceLogIndexForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("MaintenanceLogIndexForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/maintenanceLog/index', 'id' => 'MaintenanceLogIndexForm'));
	
	echo $form->input('MaintenanceLog.serialized_equipment_number', array(
		'label' => 'MRS#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.profit_center_number', array(
		'label' => 'PCtr',
		'options' => $profitCenters,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.staff_initials', array(
		'label' => 'Ini',
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.date_of_service_start', array(
		'label' => 'DOS Start',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.date_of_service_end', array(
		'label' => 'DOS End',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.comment', array(
		'label' => 'Action',
		'options' => $maintenanceActions,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MaintenanceLog.maintenance_type', array(
		'label' => 'Type',
		'options' => $maintenanceTypes,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';
	
	echo $html->link('Add New Record', 'edit', array('target' => '_blank'));
?>

<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?= $paginator->sortableHeader('MRS#', 'MaintenanceLog.serialized_equipment_number') ?>
		<?= $paginator->sortableHeader('PCtr', 'MaintenanceLog.profit_center_number') ?>
		<?= $paginator->sortableHeader('Ini', 'MaintenanceLog.staff_initials') ?>
		<?= $paginator->sortableHeader('Account#', 'MaintenanceLog.account_number') ?>
		<?= $paginator->sortableHeader('DOS', 'MaintenanceLog.date_of_service') ?>
		<?= $paginator->sortableHeader('Action', 'MaintenanceLog.comment') ?>
		<?= $paginator->sortableHeader('Invoice#', 'MaintenanceLog.invoice_number') ?>
		<?= $paginator->sortableHeader('Cleaned', 'MaintenanceLog.is_cleaned') ?>
		<?= $paginator->sortableHeader('Type', 'MaintenanceLog.maintenance_type') ?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['MaintenanceLog']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['MaintenanceLog']['serialized_equipment_number']),
					ifset($profitCenters[$row['MaintenanceLog']['profit_center_number']]),
					h($row['MaintenanceLog']['staff_initials']),
					h($row['MaintenanceLog']['account_number']),
					formatDate($row['MaintenanceLog']['date_of_service']),
					h($row['MaintenanceLog']['comment']),
					h($row['MaintenanceLog']['invoice_number']),
					($row['MaintenanceLog']['is_cleaned'] ? 'Yes' : 'No'),
					ifset($maintenanceTypes[$row['MaintenanceLog']['maintenance_type']])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>