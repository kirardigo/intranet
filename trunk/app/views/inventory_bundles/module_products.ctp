<?php
	echo $form->hidden('InventoryBundle.master', array('value' => $masterProduct));
	echo $form->input('InventoryBundle.new', array(
		'label' => false,
		'class' => 'Text100',
		'after' => $html->image('iconAdd.png', array('id' => 'InventoryBundleAdd')) . 
			$html->image('indicator.gif', array('id' => "InventoryBundleIndicator", 'style' => 'display: none;'))
	));
	echo '<div style="display: none;" id="InventoryBundleNew_autoComplete" class="auto_complete AutoComplete550"></div>';
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th>Number</th>
		<th>Description</th>
		<th style="width: 20px;">&nbsp;</th>
		<th style="width: 20px;">&nbsp;</th>
		<th style="width: 20px;">&nbsp;</th>
	</tr>
<?php
	foreach ($records as $key => $row)
	{
		$upArrow = ($key == 0) ? '' : $html->link($html->image('iconArrowUp.png'), '#', array('escape' => false, 'class' => 'InventoryBundleMoveUpLink'));
		$downArrow = ($key == count($records) - 1) ? '' : $html->link($html->image('iconArrowDown.png'), '#', array('escape' => false, 'class' => 'InventoryBundleMoveDownLink'));
		
		echo $html->tableCells(
			array(
				$form->hidden('id', array('value' => $row['InventoryBundle']['id'])) . 
				h($row['InventoryBundle']['inventory_number_item']),
				h($row['InventoryBundle']['description']),
				$upArrow,
				$downArrow,
				$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'InventoryBundleDeleteLink'))
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<script type="text/javascript">
	Modules.InventoryBundles.Products.init();
</script>