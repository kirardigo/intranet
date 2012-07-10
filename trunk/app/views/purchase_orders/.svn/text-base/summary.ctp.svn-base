<script type="text/javascript">
	function editRow(event)
	{
		recordID = this.up("td").down("input").value;
		
		window.open("/purchase_orders/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters(event)
	{
		event.stop();
		$("PurchaseOrderPurchaseOrderNumber").clear();
		$("PurchaseOrderOrderDateStart").clear();
		$("PurchaseOrderOrderDateEnd").clear();
		$("PurchaseOrderVendorCode").clear();
		
		$("PurchaseOrderSummaryForm").submit();
	}
	
	document.observe('dom:loaded', function() {	
		mrs.bindDatePicker("PurchaseOrderOrderDateStart");
		mrs.bindDatePicker("PurchaseOrderOrderDateEnd");
		
		$$(".editLink").invoke("observe", "click", editRow);
		$("ResetButton").observe("click", resetFilters);
	});
</script>
<?php
	echo $form->create('', array('id' => 'PurchaseOrderSummaryForm', 'url' => '/purchase_orders/summary'));
	
	echo $form->input('PurchaseOrder.purchase_order_number', array(
		'label' => 'Order#',
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('PurchaseOrder.order_date_start', array(
		'label' => 'Order Date Start',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal'),
		'type' => 'text'
	));
	echo $form->input('PurchaseOrder.order_date_end', array(
		'label' => 'Order Date End',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal'),
		'type' => 'text'
	));
	echo $form->input('PurchaseOrder.vendor_code', array(
		'label' => 'Vendor Code',
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Inventory.show_discontinued', array(
		'label' => 'Show Discontinued?',
		'options' => array(0 => 'No', 1 => 'Yes')
	));
	echo $form->input('PurchaseOrder.is_open', array(
		'label' => 'All Open',
		'type' => 'checkbox'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0;">';
	echo $form->submit('Filter', array('id' => 'SearchButton', 'div' => false, 'class' => 'StyledButton', 'style' => 'margin-right: 5px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();		
	echo '</div>';
	
	echo '<div style="margin: 5px 0">' . $html->link('Add New Record', '/purchaseOrders/edit', array('target' => '_blank')) . '</div>';
?>

<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th style="width: 20px;">&nbsp;</th>';
			echo $paginator->sortableHeader('Order#', 'purchse_order_number');
			echo $paginator->sortableHeader('Date', 'order_date');
			echo $paginator->sortableHeader('Vendor Code', 'vendor_code');
		?>
	</tr>
<?php
	foreach ($purchaseOrders as $purchaseOrder)
	{
		echo $html->tableCells(
			array(
				'<input type="hidden" value="' . $purchaseOrder['PurchaseOrder']['id'] . '" />' .
				$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
				h($purchaseOrder['PurchaseOrder']['purchase_order_number']),
				formatDate($purchaseOrder['PurchaseOrder']['order_date']),
				h($purchaseOrder['PurchaseOrder']['vendor_code'])
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>