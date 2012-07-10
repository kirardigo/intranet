<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/generalLedger/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		window.open("/generalLedger/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("GeneralLedgerGeneralLedgerCode").clear();
		$("GeneralLedgerDescription").clear();
				
		$("GeneralLedgerSummaryForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".deleteLink").invoke("observe", "click", deleteRow);
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("GeneralLedgerSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("GeneralLedgerSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/generalLedger/index', 'id' => 'GeneralLedgerSummaryForm'));
	
	echo $form->input('GeneralLedger.general_ledger_code', array(
		'label' => 'Code',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('GeneralLedger.description', array(
		'class' => 'Text200',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('GeneralLedger.is_active', array(
		'options' => array(0 => 'No', 1 => 'Yes'),
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('GeneralLedger.rental_code_or_purchase_code', array(
		'label' => 'Rent/Purchase',
		'options' => array('R' => 'Rental', 'P' => 'Purchase'),
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('GeneralLedger.group_code', array(
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('GeneralLedger.accounting_code', array(
		'label' => 'Acct Code',
		'class' => 'Text100'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/generalLedger/edit', array('target' => '_blank')); 
?>

<div style="margin-bottom: 5px;"></div>
<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<?= $paginator->sortableHeader('G/L', 'GeneralLedger.general_ledger_code') ?>
		<?= $paginator->sortableHeader('Description', 'GeneralLedger.description') ?>
		<?= $paginator->sortableHeader('Active', 'GeneralLedger.is_active') ?>
		<?= $paginator->sortableHeader('Rent/Purchase', 'GeneralLedger.rental_code_or_purchase_code') ?>
		<?= $paginator->sortableHeader('Group Code', 'GeneralLedger.group_code') ?>
		<?= $paginator->sortableHeader('Acct Code', 'GeneralLedger.accounting_code') ?>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['GeneralLedger']['id'] . '" />' .
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false)),
					h($row['GeneralLedger']['general_ledger_code']),
					h($row['GeneralLedger']['description']),
					$row['GeneralLedger']['is_active'] ? 'Y' : 'N',
					h($row['GeneralLedger']['rental_code_or_purchase_code']),
					h($row['GeneralLedger']['group_code']),
					h($row['GeneralLedger']['accounting_code']),
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>