<div class="GroupBox">
	<h2>Edit Assembly Item</h2>
	<div class="Content">
		<?php
			echo $form->create('', array('url' => "/inventoryAssemblies/fakeSave", 'id' => 'InventoryAssembliesEditForm'));
			
			echo $form->hidden('InventoryAssembly.id');
			
			echo $form->input('InventoryAssembly.inventory_number_item', array(
				'label' => 'Assembly Item',
				'class' => 'Text100 ReadOnly',
				'readonly' => 'readonly'
			));
			
			echo $form->input('InventoryAssembly.assembly_type', array('label' => 'Type', 'options' => $assemblyTypes));
			echo $form->input('InventoryAssembly.quantity');
			
			echo $ajax->submit('Save', array(
				'id' => 'InventoryAssembliesEditFormSave',
				'class' => 'StyledButton',
				'url' => "/json/inventoryAssemblies/edit/{$this->data['InventoryAssembly']['id']}",
				'condition' => 'Modules.InventoryAssemblies.Products.onBeforeEditPost(event)',
				'complete' => 'Modules.InventoryAssemblies.Products.onEditPostCompleted(request)'
			));	
			
			echo $form->end();
		?>
	</div>
</div>