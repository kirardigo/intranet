<?php
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'InventorySummaryForm',
				'url' => '/modules/inventory/summary',
				'update' => 'InventorySummaryContainer',
				'before' => 'Modules.Inventory.Summary.showLoadingDialog();',
				'complete' => 'Modules.Inventory.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Inventory.inventory_number', array(
			'label' => 'Inventory #',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.description', array(
			'label' => 'Description',
			'class' => 'Text250',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.medicare_healthcare_procedure_code', array(
			'label' => 'HCPC',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.profit_center_number', array(
			'label' => 'PCtr',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.vendor_code', array(
			'label' => 'Vendor',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.show_discontinued', array(
			'label' => 'Show Discontinued?',
			'options' => array(0 => 'No', 1 => 'Yes')
		));
		
		echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
		echo $form->submit('Filter', array(
			'id' => 'SearchButton', 
			'div' => false, 'class' => 'StyledButton', 
			'style' => 'margin: 5px 5px 0 0;'
		));
		echo $form->button('Export', array(
			'id' => 'ExportButton', 
			'class' => 'StyledButton',
			'style' => 'margin-right: 10px;'
		));
		echo $form->button('PL Export', array(
			'id' => 'PicklistExport',
			'class' => 'StyledButton',
			'style' => 'margin-right: 10px;'
		));
		echo $form->button('Reset', array(
			'id' => 'ResetButton', 
			'class' => 'StyledButton'
		));
		
		echo $form->hidden('Virtual.is_export', array('value' => 0));
		echo $form->hidden('Virtual.is_picklist_export', array('value' => 0));
		
		echo $form->end();
		
		echo '</div>';
		
		echo $html->link('Add New Record', '/inventory/edit', array('target' => '_blank'));
	}
?>

<?php if (!$isPostback): ?>
	<div id="InventorySummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php 
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/inventory', 
			'action' => 'summary'
		),
		'params' => $this->passedArgs
	));	
	
	$paginator->options['update'] = 'InventorySummaryContainer';
?>

<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th style="width: 20px;">&nbsp;</th>';
			echo $paginator->sortableHeader('Inventory #', 'inventory_number');
			echo $paginator->sortableHeader('Description', 'description');
			echo $paginator->sortableHeader('MSRP', 'customary_rate_or_retail_sales_rate', array('class' => 'Right'));
			echo $paginator->sortableHeader('MC Sale', 'medicare_allowable_sales_rate', array('class' => 'Right'));
			echo $paginator->sortableHeader('Updated', 'last_price_date', array('class' => 'Right'));
			echo '<th style="width: 20px;">&nbsp;</th>';
			echo ($permission->check('Inventory.delete') ? '<th style="width: 20px;">&nbsp;</th>' : '');
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			$deleteLink = '';
			
			if ($permission->check('Inventory.delete'))
			{
				$deleteLink = $html->link($html->image('iconDelete.png'), '#', array('class' => 'deleteLink', 'escape' => false));
			}
			
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['Inventory']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['Inventory']['inventory_number']),
					h($row['Inventory']['description']),
					array(formatNumber($row['Inventory']['customary_rate_or_retail_sales_rate'], 2), array('class' => 'Right')),
					array(formatNumber($row['Inventory']['medicare_allowable_sales_rate'], 2), array('class' => 'Right')),
					array(formatDate($row['Inventory']['last_price_date']), array('class' => 'Right')),
					$html->link($html->image('iconDetail.png'), '#', array('class' => 'viewLink', 'escape' => false)),
					$deleteLink
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	Modules.Inventory.Summary.addHandlers();
</script>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Inventory.Summary.init();
	</script>
<?php endif; ?>