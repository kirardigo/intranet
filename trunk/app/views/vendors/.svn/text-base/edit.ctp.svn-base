<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("vendor:updated", {
				id: $F("VendorId")
			});
			closeWindow();
		<?php endif; ?>
		
		mrs.bindDatePicker("VendorPriceListDate");
		mrs.bindMailto(
			"VendorSalesmanEmail",
			"VendorCustomerServiceEmail"
		);
		mrs.bindPhoneFormatting(
			"VendorPhoneNumber",
			"VendorFaxNumber",
			"VendorSalesmanPhoneNumber",
			"VendorSalesmanCellPhone"
		);
		
		$("SaveButton").observe("click", function() {
			$("VendorEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("VendorVendorCode").focus();
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'VendorEditForm')); ?>

<div class="GroupBox" style="width: 600px;">
	<h2>Vendor Info</h2>
	<div class="Content">
		<div class="FormColumn">
		<?php
			echo $form->input('Vendor.name', array('class' => 'Text300'));
			echo $form->input('Vendor.address_1', array('class' => 'Text300'));
			echo $form->input('Vendor.address_2', array('class' => 'Text300'));
			echo $form->input('Vendor.city', array(
				'class' => 'Text250',
				'label' => 'City, State'
			));
			echo $form->input('Vendor.zip_code', array('class' => 'Text100'));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Vendor.vendor_code', array(
				'label' => 'Code',
				'class' => 'Text75'
			));
			echo $form->input('Vendor.millers_account_number_with_vendor', array(
				'label' => 'Vendor Acct#',
				'class' => 'Text200'
			));
			echo $form->input('Vendor.accounts_payable_code', array(
				'label' => 'MRS A/P#',
				'class' => 'Text150'
			));
		?>
		</div>
		<br class="ClearBoth" /><br/>
		<?php
			echo $form->input('Vendor.web_address', array('class' => 'Text400'));
			echo $form->input('Vendor.login', array('class' => 'Text250', 'div' => array('class' => 'FormColumn')));
			echo $form->input('Vendor.password', array(
				'type' => 'text',
				'class' => 'Text250'
			));
		?>
	</div>
</div>

<div class="FormColumn">
	<div class="GroupBox" style="width: 425px;">
		<h2>Customer Service</h2>
		<div class="Content">
		<?php
			echo $form->input('Vendor.contact', array(
				'label' => 'Name',
				'class' => 'Text300'
			));
			echo $form->input('Vendor.phone_number', array(
				'label' => 'Phone',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Vendor.fax_number', array(
				'label' => 'Fax',
				'class' => 'Text75'
			));
			echo $form->input('Vendor.customer_service_email', array(
				'label' => 'Email',
				'class' => 'Text300'
			));
			echo $form->input('Note.general.note', array(
				'label' => 'General Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['general']));
			echo $form->input('Note.ordering.note', array(
				'label' => 'Ordering Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['ordering']));
			echo $form->input('Note.shipping.note', array(
				'label' => 'Shipping Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['shipping']));
		?>
		</div>
	</div>
	
	<div class="GroupBox" style="width: 425px;">
		<h2>Pricing</h2>
		<div class="Content">
		<?php
			echo $form->input('Vendor.price_list_date', array(
				'type' => 'text',
				'class' => 'Text100'
			));
			echo $form->input('Vendor.terms', array('class' => 'Text300'));
			echo $form->input('Note.discount.note', array(
				'label' => 'Discount Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['discount']));
		?>
		</div>
	</div>
</div>

<div class="FormColumn">
	<div class="GroupBox" style="width: 425px;">
		<h2>Salesman</h2>
		<div class="Content">
		<?php
			echo $form->input('Vendor.salesman', array(
				'label' => 'Name',
				'class' => 'Text300'
			));
			echo $form->input('Vendor.salesman_phone_number', array(
				'label' => 'Phone',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Vendor.salesman_cell_phone', array(
				'label' => 'Cell',
				'class' => 'Text75'
			));
			echo $form->input('Vendor.salesman_email', array(
				'label' => 'Email',
				'class' => 'Text300'
			));
			echo $form->input('Note.salesman.note', array(
				'label' => 'Salesman Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['salesman']));
		?>
		</div>
	</div>
	
	<div class="GroupBox" style="width: 425px;">
		<h2>Group Purchasing</h2>
		<div class="Content">
		<?php
			echo $form->input('Vendor.group_purchasing_option_po_memo', array(
				'label' => 'MED Grp Purchasing',
				'class' => 'Text400'
			));
			echo $form->input('Note.purchasing.note', array(
				'label' => 'Group Purchasing Notes',
				'type' => 'textbox',
				'class' => 'TextArea400'
			));
			echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['purchasing']));
		?>
		</div>
	</div>
</div>
<div class="ClearBoth"></div>

<?php
	echo $form->hidden('Vendor.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>
