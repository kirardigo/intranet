<?php
	echo $form->create(null, array('id' => 'InventoryAssemblyNewForm'));
	
	echo $form->input('InventoryAssembly.inventory_number_master', array(
		'id' => 'InventoryAssemblyNewMaster',
		'value' => $masterProduct, 
		'readonly' => 'readonly', 
		'class' => 'ReadOnly', 
		'label' => 'Inventory Number', 
		'div' => array('class' => 'Horizontal')
	));
	
	echo $form->input('InventoryAssembly.inventory_number_item', array(
		'id' => 'InventoryAssemblyNewItem',
		'label' => 'Assembly Item',
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal'),
	));
	
	echo '<div style="display: none;" id="InventoryAssemblyNewItem_autoComplete" class="auto_complete AutoComplete550"></div>';
	
	echo $form->input('InventoryAssembly.assembly_type', array(
		'id' => 'InventoryAssemblyNewAssemblyType', 
		'options' => $assemblyTypes,
		'label' => 'Type', 
		'div' => array('class' => 'Horizontal')
	));
	
	echo $form->input('InventoryAssembly.quantity', array(
		'id' => 'InventoryAssemblyNewQuantity', 
		'after' => $html->link($html->image('iconAdd.png', array('style' => 'vertical-align: top;')), '#', array('id' => 'InventoryAssemblyAdd', 'escape' => false)) . 
			$html->image('indicator.gif', array('id' => "InventoryAssemblyIndicator", 'style' => 'display: none;'))
	));
	
	echo $form->end();
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th>Item</th>
		<th>Description</th>
		<th>Type</th>
		<th class="Right">Quantity</th>
		<th class="Right">Cost Unit</th>
		<th class="Right">Extended Cost</th>
		<th style="width: 20px;">&nbsp;</th>
		<th style="width: 20px;">&nbsp;</th>
	</tr>
<?php
	$total = 0;
	
	foreach ($records as $i => $row)
	{
		
		echo $html->tableCells(
			array(
				$form->hidden("id_{$i}", array('value' => $row['InventoryAssembly']['id'])) . 
				h($row['InventoryAssembly']['inventory_number_item']),
				h($row['InventoryAssembly']['description']),
				h(array_key_exists($row['InventoryAssembly']['assembly_type'], $assemblyTypes) ? $assemblyTypes[$row['InventoryAssembly']['assembly_type']] : ''),
				array(number_format($row['InventoryAssembly']['quantity'], 1), array('class' => 'Right')),
				array($row['InventoryAssembly']['cost_of_goods_sold_mrs'] == null ? '' : number_format($row['InventoryAssembly']['cost_of_goods_sold_mrs'], 2), array('class' => 'Right')),
				array($row['InventoryAssembly']['cost_of_goods_sold_mrs'] == null ? '' : number_format($row['InventoryAssembly']['cost_of_goods_sold_mrs'] * $row['InventoryAssembly']['quantity'], 2), array('class' => 'Right')),
				$html->link($html->image('iconEdit.png'), '#', array('escape' => false, 'class' => 'InventoryAssemblyEditLink')),
				$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'InventoryAssemblyDeleteLink'))
			),
			array(),
			array('class' => 'Alt')
		);
		
		$total += $row['InventoryAssembly']['cost_of_goods_sold_mrs'] == null ? 0 : ($row['InventoryAssembly']['cost_of_goods_sold_mrs'] * $row['InventoryAssembly']['quantity']);
	}
?>

	<tr class="GrandTotal">
		<td colspan="5">Total Cost:</td>
		<td class="Right"><?= number_format($total, 2) ?></td>
		<td colspan="2">&nbsp;</td>
	</tr>
</table>

<br /><br />

<div id="InventoryAssemblyDetails"></div>

<script type="text/javascript">
	Modules.InventoryAssemblies.Products.init();
</script>