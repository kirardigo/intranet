<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/oxygenDistribution/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/oxygenDistribution/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("OxygenDistributionAccountNumber").clear();
		$("OxygenDistributionInvoiceNumber").clear();
		$("OxygenDistributionDispensedDateStart").clear();
		$("OxygenDistributionDispensedDateEnd").clear();
		$("OxygenDistributionDispensedBy").clear();
		$("OxygenDistributionLotNumber").clear();
		
		$("OxygenDistributionIndexForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRow);
		mrs.bindDatePicker("OxygenDistributionDispensedDateStart");
		mrs.bindDatePicker("OxygenDistributionDispensedDateEnd");
		$("SearchButton").observe("click", function() {
			$("OxygenDistributionIndexForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("OxygenDistributionIndexForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/oxygenDistribution/index', 'id' => 'OxygenDistributionIndexForm'));
	
	echo $form->input('OxygenDistribution.account_number', array(
		'label' => 'Account#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('OxygenDistribution.invoice_number', array(
		'label' => 'Invoice#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('OxygenDistribution.dispensed_date_start', array(
		'label' => 'Date Start',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('OxygenDistribution.dispensed_date_end', array(
		'label' => 'Date End',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('OxygenDistribution.dispensed_by', array(
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('OxygenDistribution.lot_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';
?>

<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?= $paginator->sortableHeader('Acct#', 'OxygenDistribution.account_number') ?>
		<?= $paginator->sortableHeader('Invoice#', 'OxygenDistribution.invoice_number') ?>
		<?= $paginator->sortableHeader('Date', 'OxygenDistribution.dispensed_date') ?>
		<?= $paginator->sortableHeader('Dispensed By', 'OxygenDistribution.dispensed_by') ?>
		<?= $paginator->sortableHeader('Lot#', 'OxygenDistribution.lot_number') ?>
		<?= $paginator->sortableHeader('Quantity', 'OxygenDistribution.quantity') ?>
		<?= $paginator->sortableHeader('Tank Size', 'OxygenDistribution.tank_size') ?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['OxygenDistribution']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['OxygenDistribution']['account_number']),
					h($row['OxygenDistribution']['invoice_number']),
					formatDate($row['OxygenDistribution']['dispensed_date']),
					h($row['OxygenDistribution']['dispensed_by']),
					h($row['OxygenDistribution']['lot_number']),
					h($row['OxygenDistribution']['quantity']),
					h($row['OxygenDistribution']['tank_size'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>