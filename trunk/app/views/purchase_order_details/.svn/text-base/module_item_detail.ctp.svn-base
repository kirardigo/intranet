<?php
	echo $form->create('', array('id' => 'InventoryForm', 'url' => "/purchase_order_details/edit/{$poId}"));	
?>

<div class="GroupBox">
	<h2>Item Details</h2>
	<div class="Content">		
		<?php
		echo $form->hidden('PurchaseOrderDetail.id');			
		echo $form->input('PurchaseOrderDetail.purchase_order_number', array(
			'label' => 'Purchase Order #',
			'class' => 'Text100',
			'value' => $poValues['PurchaseOrder']['purchase_order_number']
		));
		echo $form->input('PurchaseOrderDetail.inventory_number', array(
			'label' => 'Inventory #',
			'class' => 'Text150'
		));
				
		echo $form->input('PurchaseOrderDetail.inventory_description', array(
			'class' => 'Text400'
		));
		echo $form->input('PurchaseOrderDetail.manufacturer_product_code', array(
			'label' => 'Product Code',
			'class' => 'Text150'
		));
					
		echo $form->input('PurchaseOrderDetail.unit_of_measure', array(
			'class' => 'Text75',
			'label' => 'Unit of Measure',
			'value' => $uom
		));
		echo $form->input('PurchaseOrderDetail.ship_to_profit_center_number', array(
			'class' => 'Text50',
			'label' => 'Ship To Profit Center'
		));
					
		echo $form->input('PurchaseOrderDetail.order_date', array(
			'class' => 'Text75',
			'label' => 'Order Date',
			'value' => $poValues['PurchaseOrder']['order_date']
		));
		echo $form->input('PurchaseOrderDetail.acknowledgement_date', array(
			'type' => 'text',
			'label' => 'Acknowledgment Date',
			'class' => 'Text75'
		));
					
		echo $form->input('PurchaseOrderDetail.quantity_ordered', array(
			'class' => 'Text50'	
		));
		echo $form->input('PurchaseOrderDetail.quantity_received', array(
			'class' => 'Text50'	
		));
		echo $form->input('PurchaseOrderDetail.quantity_back_ordered', array(
			'class' => 'Text50'				
		));			
		echo $form->input('PurchaseOrderDetail.cost', array(
			'label' => 'Cost',
			'class' => 'Text125'	
		));
		echo $form->input('PurchaseOrderDetail.account_number', array(
			'label' => 'Account #',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AccountNameView', array(
			'class' => 'Text300',
			'readonly' => 'readonly',
			'label' => false,
			'style' => 'position: relative; top: 15px'
		));
		echo '<br style="clear: both;" />';
		echo '<div id="PurchaseOrderDetailAccountNumber_autoComplete" style="display: none;" class="auto_complete"></div>';
		
		echo $form->input('PurchaseOrderDetail.accounting_code', array(
			'class' => 'Text100'
		));
		echo $form->input('PurchaseOrderDetail.salesman_number', array(
			'class' => 'Text50'
		));
		echo $form->input('PurchaseOrderDetail.transaction_control_number', array(
			'label' => 'TCN',
			'class' => 'Text25'
		));
		echo $form->input('PurchaseOrderDetail.transaction_control_number_file', array(
			'TCN' => 'TCN File',
			'class' => 'Text25'
		));
		echo $form->input('PurchaseOrderDetail.special_order_for', array(
			'class' => 'Text50'
		));
		echo $form->input('PurchaseOrderDetail.created_by_and_on', array(
			'class' => 'Text150'
		));
		echo $form->input('PurchaseOrderDetail.comments_3', array(
			'label' => 'Comments',
			'class' => 'Text400'
		));
	?>
	<br  />
	<?php
		echo $form->submit('Save', array(
			'id' => 'DetailSaveButton',
			'class' => 'StyledButton',
			'div' => array('class' => 'Horizontal'),
		));
		echo $form->button('Cancel', array('id' => 'CancelButton'));
		echo $form->end();
	?>
	</div>
</div>	
<script type="text/javascript">
	Modules.PurchaseOrderDetails.Core.init();
</script>