<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		
		<th>Stock Level</th>
		<th>Reorder Level</th>
		<th>Locator</th>
		<th>Ship To</th>
		<th>EOU Count</th>
		<th>BOH</th>
		<th>Rent</th>
		<th>Sales</th>
		<!--<th></th>-->
	</tr>
	
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['InventoryProfitCenter']['id'] . '" />' .
					h($row['InventoryProfitCenter']['stock_level']),
					h($row['InventoryProfitCenter']['reorder_level']),
					h($row['InventoryProfitCenter']['locator']),
					h($row['InventoryProfitCenter']['ship_to']),
					"",
					"",
					"",
					"",
					/*'<input type="hidden" value="' . $row['InventoryProfitCenter']['id'] . '" />' .
					$html->link($html->image('iconDetail.png'), '#', array('class' => 'viewLink', 'escape' => false))*/
				),
				array(),
				array('class' => 'Alt')
			);
		}		
	?>
</table>
<div id="divInventoryProfitCenterDetailView" style="margin-top:10px;"></div>