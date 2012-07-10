<script type="text/javascript">
	function updateDescription()
	{
		if ($F("InventorySpecialOrderDescription") == "")
		{
			new Ajax.Request("/ajax/inventory/description", {
				parameters: {
					inventory_number: $F("InventorySpecialOrderMrsInventoryNumber")
				},
				onSuccess: function(transport) {
					$("InventorySpecialOrderDescription").value = transport.responseText;
				}
			});
		}
	}
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("inventorySpecial:updated", {
				id: $F("InventorySpecialOrderId")
			});
			closeWindow();
			exit;
		<?php endif; ?>
		mrs.bindDatePicker("InventorySpecialOrderOriginalPurchaseOrderDate");
		mrs.bindDatePicker("InventorySpecialOrderDateOfPurchase");
		mrs.bindDatePicker("InventorySpecialOrderAssignedDate");
		
		$("InventorySpecialOrderMrsInventoryNumber").observe("change", updateDescription);
	});
</script>

<?= $form->create('', array('url' => "/inventorySpecialOrders/edit/{$id}")); ?>
<div class="GroupBox">
	<h2>General Info</h2>
	<div class="Content">
	<?php
		echo $form->hidden('InventorySpecialOrder.id');
		echo $form->input('InventorySpecialOrder.original_purchase_order_number', array(
			'label' => 'PO#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.original_purchase_order_date', array(
			'type' => 'text',
			'label' => 'PO Date',
			'class' => 'Text75'
		));
		echo $form->input('InventorySpecialOrder.manufacturer_inventory_number', array(
			'label' => 'MFG Inven#',
			'class' => 'Text200',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.manufacturer_code', array(
			'label' => 'MFG Code',
			'class' => 'Text50'
		));
		echo $form->input('InventorySpecialOrder.mrs_inventory_number', array(
			'label' => 'MRS Inven#',
			'class' => 'Text200'
		));
		echo $form->input('InventorySpecialOrder.description', array(
			'class' => 'Text400'
		));
		echo $form->input('InventorySpecialOrder.serial_number', array(
			'label' => 'Serial#',
			'class' => 'Text100'
		));
		echo $form->input('InventorySpecialOrder.quantity', array(
			'label' => 'Qty',
			'class' => 'Text25',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.unit_of_measure', array(
			'label' => 'UoM',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.cost', array(
			'class' => 'Text75 Right'
		));
		echo $form->input('InventorySpecialOrder.date_of_purchase', array(
			'type' => 'text',
			'label' => 'Purchase Date',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.department_code', array(
			'label' => 'Dept',
			'options' => $departments,
			'empty' => true
		));
		echo $form->input('InventorySpecialOrder.item_condition', array(
			'label' => 'Condition',
			'options' => $conditions,
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.locator', array(
			'options' => $locatorOptions,
			'empty' => true
		));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Details</h2>
	<div class="Content">
	<?php
		echo $form->input('InventorySpecialOrder.color', array(
			'label' => 'Color',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.size', array(
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.arms', array(
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.rigging', array(
			'class' => 'Text100'
		));
		
		echo $form->input('InventorySpecialOrder.wheels', array(
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.accessories', array(
			'class' => 'Text200'
		));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Allocation Info</h2>
	<div class="Content">
	<?php
		echo $form->input('InventorySpecialOrder.assigned_transaction_control_number', array(
			'label' => 'TCN',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.account_number', array(
			'label' => 'Acct#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('InventorySpecialOrder.salesman_initials', array(
			'label' => 'Salesman',
			'class' => 'Text50'
		));
		
		echo $form->input('InventorySpecialOrder.assigned_date', array(
			'type' => 'text',
			'label' => 'Date',
			'class' => 'Text75'
		));
	?>
	</div>
</div>
<?= $form->end('Save'); ?>