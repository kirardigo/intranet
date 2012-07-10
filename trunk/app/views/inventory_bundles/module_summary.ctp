<?php
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'InventoryBundlesForm',
				'url' => '/modules/inventoryBundles/summary',
				'update' => 'InventoryBundlesSummaryContainer',
				'before' => 'Modules.InventoryBundles.Summary.showLoadingDialog();',
				'complete' => 'Modules.InventoryBundles.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('InventoryBundle.inventory_number_master', array(
			'label' => 'Master#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventoryBundle.inventory_number_item', array(
			'label' => 'Item#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo '<div class="ClearBoth"></div>';
		
		echo $form->hidden('Virtual.is_export', array('value' => 0));
		
		echo $form->submit('Filter', array('div' => false, 'class' => 'StyledButton', 'style' => 'margin: 5px 0 0 0;'));
		echo $form->button('Export', array('id' => 'InventoryBundleExportButton', 'class' => 'StyledButton'));
		echo $form->button('Reset', array('id' => 'InventoryBundleResetButton', 'class' => 'StyledButton'));
		
		echo $form->end();
	}
?>

<?php if (!$isPostback): ?>
	<div id="InventoryBundlesSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php 
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/inventoryBundles', 
			'action' => 'summary'
		),
		'params' => $this->passedArgs
	));	
	
	$paginator->options['update'] = 'InventoryBundlesSummaryContainer';
?>

<a href="/inventoryBundles/edit" target="_blank">Add New Record</a>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th style="width: 20px;">&nbsp;</th>
		<?= $paginator->sortableHeader('Master#', 'inventory_number_master'); ?>
		<th>Description</th>
		<?= $paginator->sortableHeader('Seq', 'invoicing_sequence'); ?>
		<?= $paginator->sortableHeader('Item#', 'inventory_number_item'); ?>
		<th>Description</th>
		<th style="width: 20px;">&nbsp;</th>
	</tr>
<?php
	foreach ($records as $key => $row)
	{
		echo $html->tableCells(
			array(
				$form->hidden('id', array('value' => $row['InventoryBundle']['id'])) .
				$html->link($html->image('iconEdit.png'), '#', array('escape' => false, 'class' => 'InventoryBundleEditLink')),
				h($row['InventoryBundle']['inventory_number_master']),
				h($descriptions[$row['InventoryBundle']['inventory_number_master']]),
				h($row['InventoryBundle']['invoicing_sequence']),
				h($row['InventoryBundle']['inventory_number_item']),
				h($descriptions[$row['InventoryBundle']['inventory_number_item']]),
				$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'InventoryBundleDeleteLink'))
			),
			array(),
			array('class' => 'Alt Auto')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>

<script type="text/javascript">
	Modules.InventoryBundles.Summary.addHandlers();
</script>
	
<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.InventoryBundles.Summary.init();
	</script>
<?php endif; ?>
