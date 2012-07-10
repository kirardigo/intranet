<div class="GroupBox">
	<h2>Inventory Item Copy</h2>
	<div class="Content">
	<?php
		echo $form->create('Inventory', array('id' => 'InventoryCopy', 'style' => 'margin: 0;', 'url' => '/inventory/fakeSave"'));
		echo $form->hidden('Inventory.id'); 
		
		echo '<div  style="border: 1px solid black; padding: 5px; width: 300px; margin-bottom: 5px; float: left;">';
		echo '<h2 style="margin-bottom: 5px;">Current Item</h2>';		
		echo $form->input('Inventory.inventory_number', array('readonly' => 'readonly'));
		echo $form->input('Inventory.description', array('class' => 'Text300', 'readonly' => 'readonly'));
		echo '</div>';
		
		echo '<div  style="border: 1px solid black; padding: 5px; width: 300px; margin-bottom: 5px; float: right;">';
		echo '<h2 style="margin-bottom: 5px;">New Item</h2>';		
		echo $form->input('Inventory.new_inventory_number', array());
		echo $form->input('Inventory.new_description', array('class' => 'Text300'));
		echo '</div>';
		
		echo '<br style="clear: both;" />';
		
		echo '<div style="float: right;">';
			echo $ajax->submit('Copy', array(
				'id' => 'InventoryCopyButton',
				'class' => 'StyledButton',
				'div' => array('class' => 'Horizontal'),
				'url' => "/json/inventory/copy/{$this->data['Inventory']['id']}",
				'condition' => 'Modules.Inventory.Core.copyOnBeforePost(event)',
				'complete' => 'Modules.Inventory.Core.copyOnPostCompleted(request)'
			));
			echo $form->button('Cancel', array('id' => 'CopyCancelButton', 'class' => 'StyledButton'));
		echo '</div>';
		
		echo '<br style="clear: both;" />';
		
		echo $form->end();
	?>
	</div>
</div>
<script type="text/javascript">
	$("CopyCancelButton").observe("click", Modules.Inventory.Core.closeCopyWindow);
</script>