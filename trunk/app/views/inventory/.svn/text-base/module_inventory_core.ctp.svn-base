<?php
	echo $form->create('', array('id' => 'InventoryForm', 'url' => "/inventory/fakeSave"));
	echo $form->hidden('Inventory.id');

	echo $ajax->submit('Save', array(
		'id' => 'InventoryFormSaveTop',
		'class' => 'StyledButton',
		'div' => false,
		'style' => 'margin: 0 10px 0 0;',
		'url' => "/json/inventory/edit",
		'condition' => 'Modules.Inventory.Core.onBeforePost(event)',
		'complete' => 'Modules.Inventory.Core.onPostCompleted(request)'
	));
	
	echo $form->button('Cancel', array(
		'id' => 'CancelButtonTop',
		'class' => 'StyledButton',
		'style' => 'margin-right: 10px;'
	));
	
	if ($id != null)
	{
		echo $html->link($html->image('iconCopy.png') . ' Copy This Item', '#', array(
			'id' => 'CopyButton', 
			'escape' => false
		));
	}
?>

<div class="GroupBox" style="margin-top: 10px;">
	<h2>Inventory</h2>
	<div class="Content">	
		<div class="FormColumn">
			<?php
			echo $form->hidden('Inventory.id');			
			echo $form->input('Inventory.inventory_number', array(
				'label' => 'Inventory#',
				'class' => 'Text150'
			));
			echo $form->input('Inventory.medicare_healthcare_procedure_code', array(
				'label' => 'Medicare HCPC',
				'class' => 'Text150'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.description', array(
				'class' => 'Text300'
			));
			echo $form->input('HealthcareProcedureCode.description', array(
				'label' => 'HCPC Description',
				'class' => 'Text300 ReadOnly',
				'readonly' => 'readonly'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.customary_rate_or_retail_sales_rate', array(
				'class' => 'Text50',
				'label' => 'MSRP Sale'
			));
			echo $form->input('Inventory.medicare_allowable_sales_rate', array(
				'class' => 'Text50',
				'label' => 'MC Sale'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.customary_rate_or_retail_rental_rate', array(
				'class' => 'Text50',
				'label' => 'MSRP Rental'
			));
			echo $form->input('Inventory.medicare_allowable_rental_rate', array(
				'class' => 'Text50',
				'label' => 'MC Rental'
			));
			?>
		</div>
		<div class="ClearBoth"></div>
		<?php			
			echo $form->input('Inventory.price_list_notes', array(
				'type' => 'textarea',
				'class' => 'TextArea800'	
			));
			echo $form->input('Inventory.purchasing_notes', array(
				'type' => 'textarea',
				'class' => 'TextArea800'	
			));
			echo $form->input('Inventory.general_ledger_sales_code', array(
				'label' => 'GL Sales Code',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal'),
				'style' => 'margin-right: 20px;',
				'after' => '<span class="GLDescription"></span>'
			));
			echo "<br style='clear: both;' />";
			
			echo $form->input('Inventory.general_ledger_rental_code', array(
				'label' => 'GL Rental Code',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal'),
				'style' => 'margin-right: 20px;',
				'after' => '<span class="GLDescription"></span>'
			));
			echo "<br style='clear: both;' />";
			
			echo $form->input('Inventory.manufacturer_url', array(
				'class' => 'Text400'
			));
			echo $form->input('Inventory.warranty_identification_number', array(
				'label' => 'Warranty #',
				'class' => 'Text125'
			));
		?>
	</div>
</div>

<div class="GroupBox">
	<h2>Replacement or Discontinuation</h2>
	<div class="Content">	
	<?php
		echo $form->input('Inventory.replacement_or_discontinuation_inventory', array(
			'label' => 'Inventory#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.replacement_description', array(
				'class' => 'Text300 ReadOnly',
				'readonly' => 'readonly',
				'label' => 'Description',
				'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Inventory.replacement_or_discontinuation_date', array(
			'type' => 'text',
			'class' => 'Text75',
			'label' => 'Date',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('Inventory.is_discontinued', array(
			'label' => array('class' => 'Checkbox', 'text' => 'Discontinued?'),
			'div' => array('style' => 'margin-top: 12px;')
		));
	?>
		<div class="ClearBoth"></div>
	</div>
</div>
				
<?php if (!empty($this->params['pass'])): ?>
	<div>	
		<div id="divCarriers" class="Content"></div>
	</div>
<?php endif; ?>

<div class="GroupBox">
	<h2>Manufacturer Inventory</h2>
	<div class="Content">	
		<div class="FormColumn" style="border: 1px solid black; padding: 5px;">
			<h2>Vendor</h2>
			<?php			
				echo $form->input('Inventory.manufacturer_product_code', array(
					'label' => 'Inventory#',
					'class' => 'Text150'					
				));	
				echo $form->input('Inventory.manufacturer_unit_of_measure', array(
					'label' => 'UofM',
					'class' => 'Text50'
				));
				echo $form->input('Inventory.cost_of_goods_sold_mfg', array(
					'label' => 'Cost',
					'class' => 'Text50'
				));
				echo $form->input('Inventory.vendor_cost_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'label' => 'Cost Date'
				));
				echo $form->input('Inventory.last_price_amount', array(
						'type' => 'text',
						'label' => 'Last Price',
						'class' => 'Text50'
				));
				echo $form->input('Inventory.new_proposed_msrp', array(
						'type' => 'text',
						'label' => 'Proposed MSRP',
						'class' => 'Text50'
				));
			?>
		</div>
		<div class="FormColumn" style="border: 1px solid black; padding: 5px;">
			<h2>Millers</h2>
			<?php			
				echo $form->input('Inventory.inventory_number_display', array(
					'label' => 'Inventory#',
					'value' => $this->data['Inventory']['inventory_number'],
					'readonly' => 'readonly',
					'class' => 'text25'					
				));	
				echo $form->input('Inventory.retail_unit_of_measure', array(
					'label' => 'UofM',
					'class' => 'Text50'
				));
				echo $form->input('Inventory.cost_of_goods_sold_mrs', array(
					'label' => 'Cost',
					'class' => 'Text50'
				));
				echo $form->input('Inventory.cost_of_goods_sold_update_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'label' => 'COGS Date'
				));
				echo $form->input('Inventory.is_cost_of_goods', array(
					'type' => 'checkbox',
					'label' => array('text' => 'Y/N COGS', 'class' => 'Checkbox'),
					'div' => array('style' => 'margin: 3px 0;')
				));
				echo $form->input('Inventory.last_price_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'label' => 'Price Date'
				));
			?>
		</div>
		<div class="FormColumn" style="position:relative;">
			<?php			
				echo $form->input('Inventory.is_taxable', array(
					'label' => array('class' => 'Checkbox')					
				));	
				echo $form->input('Inventory.should_use_lot_tracking', array(
					'label' => array('class' => 'Checkbox')	
				));
				echo $form->input('Inventory.is_side_specific', array(
					'label' => array('class' => 'Checkbox')	
				));
				echo $form->input('Inventory.is_serialized', array(
					'label' => array('class' => 'Checkbox')
				));	
				echo $form->input('Inventory.code_sequence', array(
					'type' => 'text',
					'label' => 'Code Sequence',
					'class' => 'Text100'
				));
				echo $form->input('Inventory.picklist_automatic', array(
					'label' => 'Pick',
					'options' => $picklistAutomatic,
					'empty' => true
				));
				echo $form->input('Inventory.group_field', array(
					'type' => 'text',
					'class' => 'Text50'
				));
			?>
			<?php
				echo '<div>';
					echo '<div style="float: left;">';
						echo $form->input('Inventory.flat_rate_code', array(
							'options' => $codes,
							'style' => 'width: 400px;'
						));
							echo '</div>';
					echo '<br />';
					
				echo '</div>';
				echo '<br /><br />';	
			?>
		</div>
		
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Adapt Tech</h2>
	<div class="Content">
	<?php
		echo $form->input('Inventory.is_adapt_tech_price_list', array(
			'label' => array('class' => 'Checkbox')	
		));
		echo $form->input('Inventory.adapt_tech_net_price', array(
			'label' => 'Net Price',
			'type' => 'text',
			'class' => 'Text50'
		));	
		echo $form->input('Inventory.adapt_tech_quantity', array(
			'label' => 'Bulk Qty',
			'class' => 'Text50'
		));	
		echo $form->input('Inventory.adapt_tech_discount', array(
			'label' => 'Bulk Discount',
			'class' => 'Text50'
		));	
	?>
	</div>
</div>

<?php
	echo $ajax->submit('Save', array(
		'id' => 'InventoryFormSaveBottom',
		'class' => 'StyledButton',
		'div' => false,
		'style' => 'margin: 0 10px 0 0;',
		'url' => "/json/inventory/edit",
		'condition' => 'Modules.Inventory.Core.onBeforePost(event)',
		'complete' => 'Modules.Inventory.Core.onPostCompleted(request)'
	));
	echo $form->button('Cancel', array(
		'id' => 'CancelButtonBottom',
		'class' => 'StyledButton'
	));
	echo $form->end();
?>

<script type="text/javascript">
	Modules.Inventory.Core.init();
</script>