<?php
	if (!isset($this->data))
	{
		echo '<div class="NoRecordNotice">No purchase orders within the last 6 months.</div>';
		exit;
	}
	
	$paginator->options(
		array(
			'url' => array(
				'controller' => 'modules/purchaseOrders',
				'action' => "forCustomer/{$accountNumber}/1"
			),
			'update' => 'PurchaseOrdersForCustomerContainer'
		)
	);
	
	if (!$isUpdate)
	{
		echo '<div id="PurchaseOrdersForCustomerContainer">';
	}
?>

<?= $form->input('PurchaseOrderDetail.account_number', array('type' => 'hidden', 'value' => $accountNumber)) ?>

<?= $this->element('page_links'); ?>
<table id="PurchaseOrdersForCustomerTable" class="Styled">
	<thead>
		<tr>
			<th style="white-space: nowrap;">TCN #</th>
			<th style="white-space: nowrap;">PO #</th>
			<th>Vendor Code</th>
			<th>Order Date</th>
			<th>Estimated Ship Date</th>
			<th>Received Date</th>
			<th>Ship to P/C</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		foreach ($this->data as $row)
		{
			if (count($row['PurchaseOrder']['transaction_control_numbers']) == 1)
			{
				$tcn = $row['PurchaseOrder']['transaction_control_numbers'][0];
			}
			else
			{
				$items = implode(',<br/>', $row['PurchaseOrder']['transaction_control_numbers']);
				$tcn = '<span>Multiple<div class="Tooltip">' . $items . '</div></span>';
			}
			
			echo $html->tableCells(
				array(
					$tcn,
					h(trim($row['PurchaseOrder']['purchase_order_number'])),
					h($row['PurchaseOrder']['vendor_code']),
					h($row['PurchaseOrder']['order_date']),
					h($row['PurchaseOrder']['shipping_acknowledgement_date']),
					h($row['PurchaseOrder']['received_acknowledgement_date']),
					h($row['PurchaseOrder']['ship_to_profit_center']),
					$html->link($html->image('iconDetail.png'), '#', array(
						'escape' => false,
						'title' => 'Show details',
						'class' => 'Detail'
					)) . $form->hidden('purchase_order_number', array('value' => $row['PurchaseOrder']['purchase_order_number']))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	// Clear the details when changing the page
	$('PurchaseOrdersForCustomerDetailInfo').update();
	Modules.PurchaseOrders.ForCustomer.addHandlers();
</script>

<?php if (!$isUpdate): ?>

</div>

<div id="PurchaseOrdersForCustomerDetailInfo" style="margin-top: 20px;"></div>

<?php endif; ?>
