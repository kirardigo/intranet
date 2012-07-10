<?php
	$html->css('tabs', null, array(), false);
	$javascript->link('tabs', false);
	
	function field($form, $fields, $key, $size = null)
	{
		return $form->input('DefaultFile.' . $key, array('maxlength' => $fields[$key]['length'], 'label' => $fields[$key]['label'], 'size' => $size != null ? $size : $fields[$key]['length']));
	}
?>

<?php if (isset($error)): ?>
	<p class="Warning">There was a problem updating the default file. Please try saving again.</p>
<?php endif; ?>

<?= $form->create('DefaultFile', array('url' => '/defaults/index')) ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">General</a></li>
	<li><a href="#">Codes</a></li>
	<li><a href="#">G/L</a></li>
</ul>
	
<div class="TabContainer">
	<div class="TabPage">
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'name');
				echo field($form, $fields, 'address');
				echo field($form, $fields, 'city');
				echo field($form, $fields, 'state');
				echo field($form, $fields, 'zip');
				echo field($form, $fields, 'last_invoice_generated');
				echo field($form, $fields, 'is_running_compiled');
				echo field($form, $fields, 'claim_edit_indicator');
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'company_name');
				echo field($form, $fields, 'printer_condense_on', 9);
				echo field($form, $fields, 'printer_condense_off', 9);
				echo field($form, $fields, 'not_otherwise_classified');
				echo field($form, $fields, 'clerk_number');
				echo field($form, $fields, 'cert_on_emc');
				echo field($form, $fields, 'place_of_service');
				echo field($form, $fields, 'test_production_indicator');
				
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'medicare_carrier_1');
				echo field($form, $fields, 'medicare_carrier_2');
				echo field($form, $fields, 'medicare_carrier_3');
				echo field($form, $fields, 'submission_number');
				echo field($form, $fields, 'line_item_control_numbers');
				echo field($form, $fields, 'use_bank_code');
				echo field($form, $fields, 'receiver_id');
			?>
		</div>
	</div>
	<div class="TabPage" style="display: none;">
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'oxygen_billing_code_1');
				echo field($form, $fields, 'oxygen_billing_code_2');
				echo field($form, $fields, 'oxygen_billing_code_3');
				echo field($form, $fields, 'sender_code');
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'co_insurance_form_code');
				echo field($form, $fields, 'primary_form_code');
				echo field($form, $fields, 'maintenance_fee_form_code');
			?>
		</div>
	</div>
	<div class="TabPage" style="display: none;">
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'tax_gl_code');
				echo field($form, $fields, 'purchase_credit_gl_code');
				echo field($form, $fields, 'rental_credit_gl_code');
				echo field($form, $fields, 'payment_gl');
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo field($form, $fields, 'pos_discount_gl');
				echo field($form, $fields, 'gl_maintenance_fee_code');
				echo field($form, $fields, 'current_post_period');
			?>
		</div>
	</div>
</div>
<br />

<?php
	echo $form->submit('Update', array('id' => 'SaveButton', 'div' => false));
	echo $form->end();
?>