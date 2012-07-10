<script type="text/javascript">
	function updateSubtotal()
	{
		var sizes = $("CorporateWearForm").select(".SizeDropDown");
		var quantities = $("CorporateWearForm").select(".QuantityDropDown");
		
		var subtotal = 0;
		
		sizes.each(function(size, i) {
			var price = parseFloat($F(size).split("|")[1], 10);
			var quantity = parseFloat($F(quantities[i]), 10);
			
			subtotal += price * quantity;
		});
		
		$("Subtotal").update(subtotal.format(2));
		$("SubtotalInput").value = subtotal.format(2);
	}
	
	function changePayment()
	{
		if ($F("payment_method").substr(0, 3) == "Mag")
		{
			$("MagnificentsWarning").show();
		}
		else
		{
			$("MagnificentsWarning").hide();
		}
	}
	
	document.observe("dom:loaded", function() {
		$("payment_method").observe("change", changePayment);
		
		$$(".SizeDropDown", ".QuantityDropDown").each(function(item) {
			item.observe("change", updateSubtotal);
		});
		
		changePayment();
		updateSubtotal();
	});
</script>

<style type="text/css">
	.CorporateWear {
		height: 275px;
		margin: 0 20px 20px 0;
	}
	
	.CorporateWear img {
		float: left;
		margin-right: 20px;
		box-shadow: 5px 5px 2px #666;
		border: 1px solid #666;
	}
	
	div.ProductInfo {
		padding: 0;
		margin: 0;
	}
	
	div.ProductInfo h2 {
		margin: 0; 
		padding: 0;
	}
</style>

<p>
	Corporate Wear may only be ordered via this form. Please do not e-mail, call, or visit the 
	Purchasing Office directly. With the exception of the Twin Set, Corporate Wear is now stocked in quantity and 
	available for immediate transfer. The Twin Set will be ordered every other week and transferred immediately upon receipt.
</p>

<?= $form->create(null, array('id' => 'CorporateWearForm', 'url' => '/corporateWear')) ?>

<?php
	/*
	echo $form->input('employee_name');
	echo $form->input('employee_id', array('label' => 'Employee ID'));
	echo $form->input('email_address');
	*/
?>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/l520.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Female Port Authority</h2>
		<p>
			Three Button, Short Sleeve<br />
			Logo: White/Vegas Gold<br />
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.0', array('value' => 'L520')) ?>
		<?= $form->input('color.0', array('label' => 'Color', 'options' => array('Burgundy' => 'Burgundy', 'Dark Green' => 'Dark Green'))) ?>
		<?= $form->input('size.0', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|15.25' => 'Small - $15.25',
				'Medium|15.25' => 'Medium - $15.25',
				'Large|15.25' => 'Large - $15.25',
				'XLarge|15.25' => 'XLarge - $15.25',
				'2XL|15.95' => '2XL - $15.95', 
				'3XL|19.50' => '3XL - $19.50', 
				'4XL|20.95' => '4XL - $20.95'
			))) ?>
		<?= $form->input('quantity.0', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/cs402p.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Male/Female Cornerstone Industrial Pique Polo</h2>
		<p>
			Three Button, Short Sleeve<br />
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.1', array('value' => 'CS402P')) ?>
		<?= $form->input('color.1', array('label' => 'Color', 'options' => array('Burgundy w/Pocket' => 'Burgundy w/Pocket', 'Dark Green w/Pocket' => 'Dark Green w/Pocket'))) ?>
		<?= $form->input('size.1', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|17.75' => 'Small - $17.75',
				'Medium|17.75' => 'Medium - $17.75',
				'Large|17.75' => 'Large - $17.75',
				'XLarge|17.75' => 'XLarge - $17.75',
				'2XL|19.50' => '2XL - $19.50', 
				'3XL|20.95' => '3XL - $20.95', 
				'4XL|24.50' => '4XL - $24.50'
			))) ?>
		<?= $form->input('quantity.1', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/038-013.1.jpg') ?>
	<?= $html->image('corporate_wear/038-013.2.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Female Twin Set</h2>
		<p>
			Button Down, Long Sleeve w/ Short Sleeve Shell<br />
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 40</i></p>
		
		<?= $form->hidden('product_code.2', array('value' => '038-013')) ?>
		<?= $form->input('color.2', array('label' => 'Color', 'options' => array('Burgundy' => 'Burgundy'))) ?>
		<?= $form->input('size.2', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|41.95' => 'Small - $41.95',
				'Medium|41.95' => 'Medium - $41.95',
				'Large|41.95' => 'Large - $41.95',
				'XLarge|41.95' => 'XLarge - $41.95',
				'2XL|51.35' => '2XL - $51.35', 
				'3XL|51.35' => '3XL - $51.35'
			))) ?>
		<?= $form->input('quantity.2', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/790-010.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Male Jersey Stitch Sweater</h2>
		<p>
			V Neck, Long Sleeve<br />
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 30</i></p>
		
		<?= $form->hidden('product_code.3', array('value' => '790-010')) ?>
		<?= $form->input('color.3', array('label' => 'Color', 'options' => array('Black' => 'Black'))) ?>
		<?= $form->input('size.3', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|28.50' => 'Small - $28.50',
				'Medium|28.50' => 'Medium - $28.50',
				'Large|28.50' => 'Large - $28.50',
				'XLarge|28.50' => 'XLarge - $28.50',
				'2XL|35.20' => '2XL - $35.20', 
				'3XL|35.20' => '3XL - $35.20', 
				'4XL|35.20' => '4XL - $35.20'
			))) ?>
		<?= $form->input('quantity.3', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/jst62.jpg') ?>
	
	<div class="ProductInfo" style="float: left; margin-right: 25px;">
		<h2>Male Sport Tek Wind Pullover</h2>
		<p>
			V Neck, Long Sleeve<br />
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.4', array('value' => 'JST62')) ?>
		<?= $form->input('color.4', array('label' => 'Color', 'options' => array('Navy Blue w/White' => 'Navy Blue w/White'))) ?>
		<?= $form->input('size.4', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|19.00' => 'Small - $19.00',
				'Medium|19.00' => 'Medium - $19.00',
				'Large|19.00' => 'Large - $19.00',
				'XLarge|19.00' => 'XLarge - $19.00',
				'2XL|20.95' => '2XL - $20.95', 
				'3XL|24.50' => '3XL - $24.50', 
				'4XL|26.50' => '4XL - $26.50'
			))) ?>
		<?= $form->input('quantity.4', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
	
	<div class="ProductInfo">
		<h2>Adjustable Hat</h2>
		
		<p><br /><br /></p>
			
		<p><i>Magnifi&cent;ents = 10</i></p>
		
		<?= $form->hidden('product_code.5', array('value' => 'STC11')) ?>
		<?= $form->input('color.5', array('label' => 'Color', 'options' => array('Navy/White' => 'Navy/White'))) ?>
		<?= $form->input('size.5', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'|6.50' => '$6.50'
			))) ?>
		<?= $form->input('quantity.5', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/s615.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Male Port Authority</h2>
		<p>
			Easy Care, Long Sleeve<br />
			Logo:<br />
			Green/Vegas Gold for Light Green<br />
			Black/Vegas Gold for Light Charcoal
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.6', array('value' => 'S615')) ?>
		<?= $form->input('color.6', array('label' => 'Color', 'options' => array('Light Green w/White' => 'Light Green w/White', 'Light Charcoal w/White' => 'Light Charcoal w/White'))) ?>
		<?= $form->input('size.6', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'Small|23.00' => 'Small - $23.00',
				'Medium|23.00' => 'Medium - $23.00',
				'Large|23.00' => 'Large - $23.00',
				'XLarge|23.00' => 'XLarge - $23.00',
				'2XL|24.75' => '2XL - $24.75', 
				'3XL|28.25' => '3XL - $28.25', 
				'4XL|29.75' => '4XL - $29.75'
			))) ?>
		<?= $form->input('quantity.6', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/k500ls.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Men's Silk Long Sleeve Polo</h2>
		<p>
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.7', array('value' => 'K500LS')) ?>
		<?= $form->input('color.7', array('label' => 'Color', 'options' => array('Navy' => 'Navy'))) ?>
		<?= $form->input('size.7', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'XSmall|17.75' => 'XSmall - $17.75',
				'Small|17.75' => 'Small - $17.75',
				'Medium|17.75' => 'Medium - $17.75',
				'Large|17.75' => 'Large - $17.75',
				'XLarge|17.75' => 'XLarge - $17.75',
				'2XL|19.50' => '2XL - $19.50', 
				'3XL|22.95' => '3XL - $22.95', 
				'4XL|24.50' => '4XL - $24.50'
			))) ?>
		<?= $form->input('quantity.7', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/l500ls.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Women's Silk Long Sleeve Polo</h2>
		<p>
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.11', array('value' => 'L500LS')) ?>
		<?= $form->input('color.11', array('label' => 'Color', 'options' => array('Navy' => 'Navy'))) ?>
		<?= $form->input('size.11', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'XSmall|17.75' => 'XSmall - $17.75',
				'Small|17.75' => 'Small - $17.75',
				'Medium|17.75' => 'Medium - $17.75',
				'Large|17.75' => 'Large - $17.75',
				'XLarge|17.75' => 'XLarge - $17.75',
				'2XL|19.50' => '2XL - $19.50', 
				'3XL|22.95' => '3XL - $22.95', 
				'4XL|24.50' => '4XL - $24.50'
			))) ?>
		<?= $form->input('quantity.11', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/lsw283.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Women's Long Sleeve Crew Neck Sweater</h2>
		<p>
			Logo: White/Navy
		</p>
		
		<p><i>Magnifi&cent;ents = 30</i></p>
		
		<?= $form->hidden('product_code.8', array('value' => 'LSW283')) ?>
		<?= $form->input('color.8', array('label' => 'Color', 'options' => array('Light Blue' => 'Light Blue'))) ?>
		<?= $form->input('size.8', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'XSmall|27.95' => 'XSmall - $27.95',
				'Small|27.95' => 'Small - $27.95',
				'Medium|27.95' => 'Medium - $27.95',
				'Large|27.95' => 'Large - $27.95',
				'XLarge|27.95' => 'XLarge - $27.95',
				'2XL|29.75' => '2XL - $29.75', 
				'3XL|33.25' => '3XL - $33.25', 
				'4XL|34.95' => '4XL - $34.95'
			))) ?>
		<?= $form->input('quantity.8', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/l562.1.jpg') ?>
	<?= $html->image('corporate_wear/l562.2.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Women's Silk 3/4 Sleeve Sport Shirt</h2>
		<p>
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 20</i></p>
		
		<?= $form->hidden('product_code.9', array('value' => 'L562')) ?>
		<?= $form->input('color.9', array('label' => 'Color', 'options' => array('Light Pink' => 'Light Pink', 'Navy' => 'Navy'))) ?>
		<?= $form->input('size.9', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'XSmall|17.75' => 'XSmall - $17.75',
				'Small|17.75' => 'Small - $17.75',
				'Medium|17.75' => 'Medium - $17.75',
				'Large|17.75' => 'Large - $17.75',
				'XLarge|17.75' => 'XLarge - $17.75',
				'2XL|19.50' => '2XL - $19.50', 
				'3XL|22.95' => '3XL - $22.95', 
				'4XL|24.50' => '4XL - $24.50'
			))) ?>
		<?= $form->input('quantity.9', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<div class="CorporateWear">
	<?= $html->image('corporate_wear/j754.jpg') ?>
	
	<div class="ProductInfo">
		<h2>Challenger Jacket</h2>
		<p>
			Zippered Teklon Nylon Jacket w/Fleece Lining<br />
			Logo: White/Vegas Gold
		</p>
		
		<p><i>Magnifi&cent;ents = 40</i></p>
		
		<?= $form->hidden('product_code.10', array('value' => 'J754')) ?>
		<?= $form->input('color.10', array('label' => 'Color', 'options' => array('Blue' => 'Blue'))) ?>
		<?= $form->input('size.10', array('label' => 'Size', 'class' => 'SizeDropDown', 'options' => array(
				'XSmall|42.95' => 'XSmall - $42.95',
				'Small|42.95' => 'Small - $42.95',
				'Medium|42.95' => 'Medium - $42.95',
				'Large|42.95' => 'Large - $42.95',
				'XLarge|42.95' => 'XLarge - $42.95',
				'2XL|44.95' => '2XL - $44.95', 
				'3XL|47.95' => '3XL - $47.95', 
				'4XL|49.95' => '4XL - $49.95'
			))) ?>
		<?= $form->input('quantity.10', array('label' => 'Quantity', 'class' => 'QuantityDropDown', 'options' => array('0' => '0', '1' => '1', '2' => '2', '3' => '3'))) ?>
	</div>
</div>

<br style="clear:both;" /><br />

<?php
	echo $form->input('user_id', array(
		'readonly' => 'readonly',
		'class' => 'ReadOnly',
		'div' => array('style' => 'margin-bottom: 5px;')
	));
	echo $form->input('payment_method', array(
		'escape' => false, 
		'options' => array(
			'Weekly Pay Deduction (Four Equal Payments) ' => 'Weekly Pay Deduction (Four Equal Payments) ', 
			'Commission Pay Deduction (One Payment)' => 'Commission Pay Deduction (One Payment)',
			'Magnificents' => 'Magnifi&cent;ents *'
		)
	));
?>

<p id="MagnificentsWarning" style="display:none;">
	<i>* You must first redeem Magnifi&cent;ents and then place this order. You are not able to combine a 
	Magnifi&cent;ents order with a Payroll Deduction order; the Magnifi&cent;ents order must be placed separately.</i>
</p>

<h2>Order Subtotal: <span id="Subtotal"></span></h2>

<?
	echo $form->hidden('subtotal', array('id' => 'SubtotalInput'));
	echo $form->submit("Submit");
	echo $form->end() 
?>