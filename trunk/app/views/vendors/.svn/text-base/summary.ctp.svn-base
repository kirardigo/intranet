<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/vendors/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/vendors/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		
		$("VendorSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("VendorSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("VendorSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/vendors/summary', 'id' => 'VendorSummaryForm'));
	
	echo $form->input('Vendor.vendor_code', array(
		'label' => 'Code',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Vendor.name', array('class' => 'Text200'));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/vendors/edit', array('target' => '_blank')); 
?>

<div style="margin-bottom: 5px;"></div>
<table class="Styled" style="width: 1800px">
	<tr>
		<th>&nbsp;</th>
		<?= $paginator->sortableHeader('Code', 'Vendor.vendor_code') ?>
		<?= $paginator->sortableHeader('Name', 'Vendor.name') ?>
		<?= $paginator->sortableHeader('Vendor Acct#', 'Vendor.millers_account_number_with_vendor') ?>
		<?= $paginator->sortableHeader('CSR Phone', 'Vendor.phone_number') ?>
		<?= $paginator->sortableHeader('CSR Name', 'Vendor.contact') ?>
		<?= $paginator->sortableHeader('Sls Name', 'Vendor.salesman') ?>
		<?= $paginator->sortableHeader('Sls Phone', 'Vendor.salesman_cell_phone') ?>
		<?= $paginator->sortableHeader('Sls Email', 'Vendor.salesman_email') ?>
		<?= $paginator->sortableHeader('Addr1', 'Vendor.address_1') ?>
		<?= $paginator->sortableHeader('Addr2', 'Vendor.address_2') ?>
		<?= $paginator->sortableHeader('City', 'Vendor.city') ?>
		<?= $paginator->sortableHeader('Zip', 'Vendor.zip_code') ?>
		<?= $paginator->sortableHeader('Price List', 'Vendor.price_list_date') ?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['Vendor']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['Vendor']['vendor_code']),
					h($row['Vendor']['name']),
					h($row['Vendor']['millers_account_number_with_vendor']),
					h($row['Vendor']['phone_number']),
					h($row['Vendor']['contact']),
					h($row['Vendor']['salesman']),
					h($row['Vendor']['salesman_cell_phone']),
					h($row['Vendor']['salesman_email']),
					h($row['Vendor']['address_1']),
					h($row['Vendor']['address_2']),
					h($row['Vendor']['city']),
					h($row['Vendor']['zip_code']),
					formatDate($row['Vendor']['price_list_date']),
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>