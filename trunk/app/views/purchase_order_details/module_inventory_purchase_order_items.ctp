<div style="margin-bottom: 5px;"></div>

<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th style="width: 20px;">Inventory #</th>';
			echo '<th style="width: 20px;">Manufacturer #</th>';
			echo '<th style="width: 20px;">Description</th>';
			echo '<th style="width: 20px;">Quantity Ordered</th>';
			echo '<th style="width: 20px;">&nbsp;</th>';
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					h($row['PurchaseOrderDetail']['inventory_number']),
					h($row['PurchaseOrderDetail']['manufacturer_product_code']),
					h($row['PurchaseOrderDetail']['inventory_description']),
					h($row['PurchaseOrderDetail']['quantity_ordered']),
					'<input type="hidden" value="' . $row['PurchaseOrderDetail']['id'] . '" />' .
					$html->link($html->image('iconDetail.png'), '#', array('class' => 'editLink', 'id' => 'editLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<script type="text/javascript">
	Modules.PurchaseOrders.Details.init();
</script>