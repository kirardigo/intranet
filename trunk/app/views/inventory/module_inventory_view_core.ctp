<?php
	echo $form->create('', array('id' => 'InventoryViewForm', 'url' => "/inventory/fakeSave"));
	echo $form->hidden('Inventory.id');
?>
<div class="GroupBox">
	<h2>Inventory</h2>
	<div class="Content">	
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.inventory_number', array(
				'class' => 'Text100'
			));
			echo $form->input('Inventory.medicare_healthcare_procedure_code', array(
				'class' => 'Text100'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.description', array(
				'class' => 'Text100'
			));
			echo $form->input('Inventory.place_holder_Description', array(
				'class' => 'Text100'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.insurance_allowable_sales_rate', array(
				'class' => 'Text100',
				'label' => 'MSRP Sale'
			));
			echo $form->input('Inventory.place_holder_MC20Sale', array(
				'class' => 'Text100',
				'label' => 'MC Sale'
			));
			?>
		</div>
		<div class="FormColumn">
			<?php			
			echo $form->input('Inventory.insurance_allowable_rental_rate', array(
				'class' => 'Text100',
				'label' => 'MSRP Rental'
			));
			echo $form->input('Inventory.place_holder_MC20Rent', array(
				'class' => 'Text100',
				'label' => 'MC Rental'
			));
			?>
		</div>
		<div class="FormColumn" style="width: 100%">
			<br /><br />
			<?php			
			echo $form->input('Inventory.price_list_notes', array(
				'class' => 'TextArea800'	
			));
			echo $form->input('Inventory.purchasing_notes', array(
				'class' => 'TextArea800'	
			));
			echo $form->input('Inventory.general_ledger_sales_code', array(
				
			));
			echo $form->input('Inventory.general_ledger_rental_code', array(
				
			));
			echo $form->input('Inventory.manufacturer_url', array(
				
			));
			?>
		</div>
		<div class="FormColumn">
			<br /><br />
			<?php
			echo $form->input('Inventory.warranty_identification_number', array(
				
			));
			?>
		</div>

		<div class="FormColumn" style="clear:both;"></div>
		<div class="FormColumn">
		<br /><br />
			<?php
			echo $form->input('Inventory.replacement_or_discontinuation_inventory', array(
				'label' => 'Repl. or Disc Inventory' 
			));
			?>
		</div>
		<div class="FormColumn">
		<br /><br />
			<?php
				echo $form->input('Inventory.replacement_or_discontinuation_date', array(
				 	'type' => 'text'
				));
			?>
		</div>

		<div class="FormColumn">
			
		</div>
		<div class="FormColumn">
			
		</div>
		<br style="clear: both;" /><br />
	</div>
</div>

<div class="GroupBox">
	<h2>Carriers</h2>
	<div id="divCarriersView" class="Content"></div>	
</div>

<div class="GroupBox">
	<h2>Inventory Profit Center</h2>
	<div id="divProfitCenterView" class="Content"></div>
</div>

<div class="GroupBox">
	<h2>Manufacturer Inventory</h2>
	<div class="Content">	
		<div class="FormColumn">
			<?php			
				echo $form->input('Inventory.manufacturer_product_code', array(
					'label' => 'Inventory #'					
				));	
				echo $form->input('Inventory.manufacturer_unit_of_measure', array(
					'label' => 'UofM'
				));
				echo $form->input('Inventory.cost_of_goods_sold_mfg', array(
					'label' => 'Cost'
				));
				echo $form->input('Inventory.vendor_cost_date', array(
					'type' => 'text',
					'label' => 'Cost Date'
				));
				echo '&nbsp;';
				
				echo $form->input('Inventory.last_price_date', array(
						'type' => 'text',
						'label' => 'Last Price'
				));
				echo $form->input('Inventory.new_proposed_mrsp', array(
						'type' => 'text',
						'label' => 'Proposed MSRP'
				));
			?>
		</div>
		<div class="FormColumn">
			<?php			
				echo $form->input('Inventory.inventory_number_display', array(
					'label' => 'Inventory #',
					'value' => $this->data['Inventory']['inventory_number'],
					'readonly' => 'readonly'					
				));	
				echo $form->input('Inventory.retail_unit_of_measure', array(
					'label' => 'UofM'
				));
				echo $form->input('Inventory.cost_of_goods_sold', array(
					'label' => 'Cost'
				));
				echo $form->input('Inventory.cost_of_goods_sold_updated_date', array(
						'type' => 'text',
						'label' => 'Cost Date'
				));
				echo $form->input('Inventory.open_18', array(
						'type' => 'text',
						'label' => 'Y/N Cogs'
				));
				echo $form->input('Inventory.open_8', array(
						'type' => 'text',
						'label' => 'Last Date'
				));
			?>
		</div>
		<div class="FormColumn">
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
				echo $form->input('Inventory.code_sequence', array(
					'type' => 'text',
					'label' => 'Code Sequence'
				));
				echo $form->input('Inventory.is_picklist_report', array(
					'type' => 'text'
				));
				echo $form->input('Inventory.group_field', array(
					'type' => 'text'
				));
				echo $form->input('Inventory.flat_rate_code', array(
					'type' => 'text'
				));
				echo $form->input('Inventory.is_serialized', array(
					'label' => array('class' => 'Checkbox')
				));
			?>
		</div>
		
		<br style="clear: both;" />
	</div>
</div>

<div class="GroupBox">
	<h2>Adapt Tech</h2>
<div class="Content">
	<?php
		echo $form->input('Inventory.is_adapt_tech_price_list', array(
			'label' => array('class' => 'Checkbox')	
		));
		echo $form->input('Inventory.new_proposed_msrp', array(
			'label' => 'Net Price',
			'type' => 'text'
		));	
		echo $form->input('Inventory.adapt_tech_quantity', array(
			'label' => 'Buld Qty'
		));	
		echo $form->input('Inventory.adapt_tech_discount', array(
			'label' => 'Build Discount'
		));	
	?>
</div>

<?php
	echo $form->end();
?>

<script type="text/javascript">
	//make the form look readonly
	mrs.disableControls("InventoryViewForm");
	
	//the HCPC code to lookup against
	var hcpcCode = $("InventoryMedicareHealthcareProcedureCode").value;
	
	//the call the get the HCPC Carriers recors
	new Ajax.Updater(
		$("divCarriersView").update(), 
		"/modules/hcpc/hcpc_carriers/" + hcpcCode + "/readonly:1/showcode:0",
		{
			evalScripts: true
		}
	);
	
	//the call to get the profit center records
	new Ajax.Updater(
		$("divProfitCenterView").update(), 
		"/ajax/inventory_profit_center/profit_center_summary_view/" + hcpcCode + "/readonly:1/showcode:0",
		{
			evalScripts: true
		}
	);
</script>