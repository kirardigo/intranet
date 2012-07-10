<div class="GroupBox">
	<h2>Inventory Profit Center</h2>
	<div class="Content">
	<?php
		echo $form->create('', array('url' => '/inventoryProfitCenter/fakeSave', 'id' => 'InventoryProfitCenterForm'));
	
		echo $form->input('InventoryProfitCenter.inventory_number', array(
			'readonly' => 'readonly'
		));
		echo $form->input('InventoryProfitCenter.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $editedProfitCenterRows
		));		
		echo $form->input('InventoryProfitCenter.stock_level', array(
				
		));	
		echo $form->input('InventoryProfitCenter.reorder_level', array(
			
		));	
		echo $form->input('InventoryProfitCenter.locator', array(
				
		));	
		echo $form->input('InventoryProfitCenter.ship_to', array(
		
		));

		echo '<br />';
		echo $ajax->submit('Save', array(
			'id' => 'InventoryProfitCenterForm',
			'class' => 'StyledButton',		
			'url' => "/json/inventoryProfitCenter/edit/{$this->data['InventoryProfitCenter']['id']}",
			'condition' => 'Modules.InventoryProfitCenter.Core.onBeforePost(event)',
			'complete' => 'Modules.InventoryProfitCenter.Core.onPostCompleted(request)'
		));
		
		echo $form->end();		
	?>	
	</div>
</div>

<script type="text/javascript">
	Modules.InventoryProfitCenter.Core.init();
</script>