<div style="margin: 5px 0;">
	<?= $html->link("Add New Record", '#', array('class' => 'addLink')); ?>
</div>

<table id="ResultsTable" class="Styled">
	<tr>
		<th></th>
		<th>Profit Center #</th>
		<th>Stock Level</th>
		<th>Reorder Level</th>
		<th>Locator</th>
		<th>Ship To</th>
		<th>Rent</th>
		<th>Sales</th>
		<th>EOY Count</th>
		<th></th>
	</tr>
	
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['InventoryProfitCenter']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['InventoryProfitCenter']['profit_center_number']),
					h($row['InventoryProfitCenter']['stock_level']),
					h($row['InventoryProfitCenter']['reorder_level']),
					h($row['InventoryProfitCenter']['locator']),
					h($row['InventoryProfitCenter']['ship_to']),
					h($row['InventoryProfitCenter']['rental_count']),
					h($row['InventoryProfitCenter']['sale_count']),
					h(ifset($row['InventoryProfitCenter']['eoy'], 0)),
					'<input type="hidden" value="' . $row['InventoryProfitCenter']['id'] . '" />' .
					$html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}		
	?>
</table>
<div id="InventoryProfitCenterDetail" style="margin-top:10px;"></div>

<script type="text/javascript">
	Modules.InventoryProfitCenter.Core.init();
</script>