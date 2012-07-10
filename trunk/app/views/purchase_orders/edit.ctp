<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=purchase_orders.details'
	), false);
?>

<script type="text/javascript">
	//gets the vendor name and phone
	function getVendorDetails()
	{
		new Ajax.Request("/json/vendors/poDetails/" + $("PurchaseOrderVendorCode").value, {
			onSuccess: function(transport) {
				if (transport.headerJSON.success)
				{
					$("divVendorDetails").show();				
					$("vendName").innerHTML	= transport.headerJSON.vendor.name;
					$("vendPhone").innerHTML = transport.headerJSON.vendor.phone_number;	
				}
				else
				{
					alert("Please enter a valid vendor code.");
					$("PurchaseOrderVendorCode").focus();
				}
			}
		});
	}
	
	function validateForm(event)
	{
		event.stop();
		
		if(!$$D("PurchaseOrderReceivedAcknowledgementDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderReceivedAcknowledgementDate").focus();
			return false;
		}
		
		if(!$$D("PurchaseOrderShippingAcknowledgementDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderShippingAcknowledgementDate").focus();
			return false;
		}
		
		if(!$$D("PurchaseOrderPurchaseOrderCompletionDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderPurchaseOrderCompletionDate").focus();
			return false;
		}
		
		if(!$$D("PurchaseOrderOrderDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderOrderDate").focus();
			return false;
		}
		
		valid = true;
		valid = $$R("PurchaseOrderPurchaseOrderNumber");
		valid &= $$R("PurchaseOrderDefaultAccountingCode"); 
		valid &= $$R("PurchaseOrderPurchaseOrderType");
		valid &= $$R("PurchaseOrderShippingMethod");
		valid &= $$R("PurchaseOrderVendorCode");
		valid &= $$R("PurchaseOrderConfirmedTo");
		valid &= $$R("PurchaseOrderShipToProfitCenter");
		valid &= $$R("PurchaseOrderOrderDate");
		valid &= $$R("PurchaseOrderReceivedAcknowledgementDate");		
		valid &= $$R("PurchaseOrderShippingAcknowledgementDate");
		valid &= $$R("PurchaseOrderPurchaseOrderCompletionDate");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
			
		}
	}
	
	document.observe('dom:loaded', function() {
		//load up the date pickers
		mrs.bindDatePicker("PurchaseOrderOrderDate");
		mrs.bindDatePicker("PurchaseOrderReceivedAcknowledgementDate");	
		mrs.bindDatePicker("PurchaseOrderShippingAcknowledgementDate");
		mrs.bindDatePicker("PurchaseOrderPurchaseOrderCompletionDate");
		
		//get the items on the purchase order
		poNumber = $("PurchaseOrderPurchaseOrderNumber").value;
		
		if(poNumber != "")
		{
			new Ajax.Updater("divInventoryItems", "/modules/purchase_order_details/inventory_purchase_order_items/" + poNumber, {
				evalScripts: true
			});
			
			//call to get vendor details on the page load
			getVendorDetails();
		}		
		
		//wire up the call to get the vendor detail when the user changes the code
		$("PurchaseOrderVendorCode").observe("change", getVendorDetails);
		
		$("SaveButton").observe("click", validateForm);
	});
</script>

<?php
	echo $form->create('', array('id' => 'PurchaseOrderForm', 'url' => "/purchase_orders/edit/{$id}"));
?>

<div class="GroupBox">
	<h2>Purchase Order</h2>
	<div class="Content">	
		<div class="FormColumn">
		<?php
			echo $form->hidden('PurchaseOrder.id');			
			echo $form->input('PurchaseOrder.purchase_order_number', array(
				'label' => 'PO#',
				'class' => 'Text100'
			));
			echo $form->input('PurchaseOrder.default_accounting_code', array(
				'label' => 'Acct Code',
				'class' => 'Text100'
			));
			
		?>
		</div>
		<div class="FormColumn">
			<?php
			echo $form->input('PurchaseOrder.purchase_order_type', array(
				'label' => 'PO Type',
				'class' => 'Text150',
				'div' => array('class' => 'FormColumn'),
				'options' => $poTypes
			));
			echo $form->input('PurchaseOrder.vendor_code', array(
				'label' => 'Vendor Code',
				'class' => 'Text50'
			));	
			echo $form->input('PurchaseOrder.shipping_method', array(
				'label' => 'Ship Via',
				'class' => 'Text400',
				'value' => $shipVia
			));
			?>
		</div>
		
		<div class="FormColumn">
			<div id="divVendorDetails" style="display: none;">
				<span style="display: block; float: left;">
					<label>Vendor Name</label>
					<span id="vendName"></span>
					<label style="margin-top:7px;">Vendor Phone</label>
					<span id="vendPhone"></span>
				</span>
				<span style="display: block; float: left; margin-left: 20px;">
					
				</span>
			</div>
		</div>
		
		<div class="ClearBoth"></div>
		
	</div>
</div>

<div class="GroupBox">
	<h2>Shipping Details</h2>
	<div class="Content">	
		<div class="FormColumn">
			<?php
			echo $form->input('PurchaseOrder.confirmed_to', array(
				'label' => 'Confirmed To',
				'class' => 'Text50'
			));
			echo $form->input('PurchaseOrder.ship_to_profit_center', array(
				'label' => 'Ship To Profit Center',
				'class' => 'Text50'
			));
			echo $form->input('PurchaseOrder.order_date', array(
				'type' => 'text',
				'label' => 'Order Date',
				'class' => 'Text100'
			));
			echo $form->input('PurchaseOrder.comments', array(
				'label' => 'Comments',
				'class' => 'Text500'
			));
			?>
		</div>
		
		<div class="ClearBoth"></div>
		
	</div>
</div>

<div class="GroupBox">
	<h2>Acknowledgement</h2>
	<div class="Content">	
		<div class="FormColumn">
			<?php
			echo $form->input('PurchaseOrder.received_acknowledgement_date', array(
				'type' => 'text',
				'label' => 'Received Date',
				'class' => 'Text100'
			));
			echo $form->input('PurchaseOrder.shipping_acknowledgement_date', array(
				'type' => 'text',
				'label' => 'Shipping Date',
				'class' => 'Text100'
			));
			echo $form->input('PurchaseOrder.purchase_order_completion_date', array(
				'type' => 'text',
				'label' => 'Shipping Date',
				'class' => 'Text100'
			));
			?>
		</div>
		
		<div class="ClearBoth"></div>
		
	</div>
</div>

<?php echo $html->link('Add New Record', '/purchaseOrderDetails/edit/' . $this->data['PurchaseOrder']['id'], array('target' => '_blank')); ?>
<div id="divInventoryItems"></div><br />

<?php
	echo $form->submit('Save', array(
		'id' => 'SaveButton',
		'class' => 'StyledButton',
		'div' => array('class' => 'Horizontal'),
	));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
	
?>