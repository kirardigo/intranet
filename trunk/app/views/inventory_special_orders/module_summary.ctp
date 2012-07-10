<?php
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'InventorySpecialsForm',
				'url' => '/modules/inventorySpecialOrders/summary',
				'update' => 'InventorySpecialsSummaryContainer',
				'before' => 'Modules.InventorySpecialOrders.Summary.showLoadingDialog();',
				'complete' => 'Modules.InventorySpecialOrders.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('InventorySpecialOrder.original_purchase_order_number', array(
			'label' => 'PO#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.po_date_start', array(
			'label' => 'PO Date Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.po_date_end', array(
			'label' => 'PO Date End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.manufacturer_inventory_number', array(
			'label' => 'MFG Inven#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.mrs_inventory_number', array(
			'label' => 'MRS Inven#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.assigned_date_start', array(
			'label' => 'Allocated Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.assigned_date_end', array(
			'label' => 'Allocated End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.department_code', array(
			'label' => 'Dept',
			'options' => $departments,
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		
		echo '<div class="ClearBoth"></div>';
		
		echo $form->hidden('Virtual.is_export', array('value' => 0));
		
		echo $form->submit('Filter', array('div' => false, 'class' => 'StyledButton', 'style' => 'margin: 5px 5px 0 0;'));
		echo $form->button('Export', array('id' => 'InventorySpecialExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 5px;'));
		echo $form->button('Reset', array('id' => 'InventorySpecialResetButton', 'class' => 'StyledButton'));
		
		echo $form->end();
		
		echo '<div style="margin-top: 10px;">' . $html->link('Add New Record', 'edit', array('target' => '_blank')) . '</div>';
	}
?>

<?php if (!$isPostback): ?>
	<div id="InventorySpecialsSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php 
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/inventorySpecialOrders', 
			'action' => 'summary'
		),
		'params' => $this->passedArgs
	));	
	
	$paginator->options['update'] = 'InventorySpecialsSummaryContainer';
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th style="width: 20px;">&nbsp;</th>
		<?= $paginator->sortableHeader('PO#', 'original_purchase_order_number'); ?>
		<?= $paginator->sortableHeader('PO Date', 'original_purchase_order_date'); ?>
		<?= $paginator->sortableHeader('MFG Inven#', 'manufacturer_inventory_number'); ?>
		<?= $paginator->sortableHeader('MFG Code', 'manufacturer_code'); ?>
		<?= $paginator->sortableHeader('MRS Inven#', 'mrs_inventory_number'); ?>
		<?= $paginator->sortableHeader('D', 'department_code'); ?>
		<?= $paginator->sortableHeader('Cond', 'item_condition'); ?>
		<th style="width: 20px;">&nbsp;</th>
	</tr>
<?php
	foreach ($records as $key => $row)
	{
		echo $html->tableCells(
			array(
				$form->hidden('id', array('value' => $row['InventorySpecialOrder']['id'])) .
				$html->link($html->image('iconEdit.png'), '#', array('escape' => false, 'class' => 'InventorySpecialEditLink')),
				h($row['InventorySpecialOrder']['original_purchase_order_number']),
				formatDate($row['InventorySpecialOrder']['original_purchase_order_date']),
				h($row['InventorySpecialOrder']['manufacturer_inventory_number']),
				h($row['InventorySpecialOrder']['manufacturer_code']),
				h($row['InventorySpecialOrder']['mrs_inventory_number']),
				h($row['InventorySpecialOrder']['department_code']),
				ifset($conditions[$row['InventorySpecialOrder']['item_condition']], $row['InventorySpecialOrder']['item_condition']),
				$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'InventorySpecialDeleteLink'))
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<?= $this->element('page_links'); ?>

<script type="text/javascript">
	Modules.InventorySpecialOrders.Summary.addHandlers();
</script>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.InventorySpecialOrders.Summary.init();
	</script>
<?php endif; ?>
