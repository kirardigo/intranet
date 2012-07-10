<div class="GroupBox" id="InventoryProfitCenterDetail">
	<h2>Inventory Profit Center Detail</h2>
	<div class="Content">
		<?php
				echo $form->create('', array('url' => '/inventoryProfitCenter/fakeSave', 'id' => 'InventoryProfitCenterForm'));
				echo $form->hidden('InventoryProfitCenter.id');
				echo $form->input('InventoryProfitCenter.inventory_number', array(
					'readonly' => 'readonly'
				));
				echo $form->input('InventoryProfitCenter.profit_center_number', array(
					'options' => $editedProfitCenterRows	
				));
				echo $form->input('InventoryProfitCenter.stock_level', array(
						
				));	
				echo $form->input('InventoryProfitCenter.reorder_level', array(
					
				));	
				echo $form->input('InventoryProfitCenter.locator', array(
					'options' => $locators		
				));	
				echo $form->input('InventoryProfitCenter.ship_to', array(
					'options' => $editedProfitCenterRows
				));
		
				echo '<br />';
				echo $ajax->submit('Save', array(
					'id' => 'InventoryProfitCenterSubmitButton',
					'class' => 'StyledButton',
					'div' => array('class' => 'Horizontal'),		
					'url' => "/json/inventory_profit_center/edit/{$this->data['InventoryProfitCenter']['id']}",
					'condition' => 'Modules.InventoryProfitCenter.Core.onBeforePost(event)',
					'complete' => 'Modules.InventoryProfitCenter.Core.onPostCompleted(request)'
				));
				
				echo $form->button('Cancel', array('class' => 'StyledButton', 'id' => 'ProfitCenterCancelButton'));
				
				echo $form->end();		
		?>
	</div>
</div>	
<script type="text/javascript">
	$("ProfitCenterCancelButton").observe("click", Modules.InventoryProfitCenter.Core.hideProfitCenterDetailDiv);
	$("InventoryProfitCenterInventoryNumber").value = $("CurrentInventoryNumber").value;
</script>