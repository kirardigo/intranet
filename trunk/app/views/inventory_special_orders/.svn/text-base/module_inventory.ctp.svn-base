<div style="margin-top: 5px;"><?= $html->link('Add New Record', 'edit', array('target' => '_blank')); ?></div>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th style="width: 20px;">&nbsp;</th>
		<th>Orig Date</th>
		<th>Orig PO#</th>
		<th>MFR Inven#</th>
		<th>MFR Code</th>
		<th>D</th>
		<th>Cond</th>
		<th style="width: 20px;">&nbsp;</th>
	</tr>
<?php
	foreach ($records as $key => $row)
	{
		echo $html->tableCells(
			array(
				$form->hidden('id', array('value' => $row['InventorySpecialOrder']['id'])) .
				$html->link($html->image('iconEdit.png'), '#', array('escape' => false, 'class' => 'InventorySpecialEditLink')),
				formatDate($row['InventorySpecialOrder']['original_purchase_order_date']),
				h($row['InventorySpecialOrder']['original_purchase_order_number']),
				h($row['InventorySpecialOrder']['manufacturer_inventory_number']),
				h($row['InventorySpecialOrder']['manufacturer_code']),
				h($row['InventorySpecialOrder']['department_code']),
				ifset($conditions[$row['InventorySpecialOrder']['item_condition']]),
				$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'InventorySpecialDeleteLink'))
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<script type="text/javascript">
	Modules.InventorySpecialOrders.Inventory.init();
</script>