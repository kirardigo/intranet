<table id="PurchaseOrdersForCustomerDetailTable" class="Styled">
	<thead>
		<tr>
			<th>#</th>
			<th>TCN #</th>
			<th>Inventory #</th>
			<th>Description</th>
			<th>Mfg Product Code</th>
			<th>Ordered</th>
			<th>Received</th>
			<th>Backordered</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		$i = 1;
		foreach (ifset($this->data, array()) as $row)
		{
			echo $html->tableCells(
				array(
					h($i++),
					h($row['PurchaseOrderDetail']['transaction_control_number'] . ':' . $row['PurchaseOrderDetail']['transaction_control_number_file']),
					h($row['PurchaseOrderDetail']['inventory_number']),
					h($row['PurchaseOrderDetail']['inventory_description']),
					h($row['PurchaseOrderDetail']['manufacturer_product_code']),
					h($row['PurchaseOrderDetail']['quantity_ordered']),
					h($row['PurchaseOrderDetail']['quantity_received']),
					h($row['PurchaseOrderDetail']['quantity_back_ordered'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>
