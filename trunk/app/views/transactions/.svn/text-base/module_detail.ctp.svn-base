
<div id="TransactionDetailModuleContainer" class="GroupBox">
	<h2>Transaction Info</h2>
	<div class="Content">
	
		<div class="FormColumn">
			<?php
				echo $form->input('Transaction.account_number');
				echo $form->input('Transaction.invoice_number');
				echo $form->input('Transaction.transaction_date_of_service', array('type' => 'text'));
				echo $form->input('Transaction.general_ledger_description', array('class' => 'Text250'));
				echo $form->input('Transaction.transaction_type');
				echo $form->input('Transaction.account_balance');
				echo $form->input('Transaction.carrier_number');
				echo $form->input('Transaction.carrier_balance_due');
				echo $form->input('Transaction.quantity');
				echo $form->input('Transaction.inventory_number');
				echo $form->input('Transaction.inventory_description', array('class' => 'Text250'));
				echo $form->input('Transaction.healthcare_procedure_code');
				echo $form->input('Transaction.inventory_group_code');
				echo $form->input('Transaction.profit_center_number');
				echo $form->input('Transaction.salesman_number');
				echo $form->input('Transaction.department_code');
				echo $form->input('Transaction.unique_identification_number');
				echo $form->input('Transaction.cost_1');
				echo $form->input('Transaction.cost_2');
				echo $form->input('Transaction.physical_creation_date', array('type' => 'text'));
				echo $form->input('Transaction.period_posting_date', array('type' => 'text'));
				echo $form->input('Customer.is_deleted', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
				echo $form->input('Transaction.transaction_control_number');
				echo $form->input('Transaction.transaction_control_number_file');
				echo $form->input('Transaction.rental_or_purchase');
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('Transaction.serial_number');
				echo $form->input('Transaction.general_ledger_code', array('label' => 'G/L Code'));
				echo $form->input('Transaction.is_field_updated', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
				echo $form->input('Transaction.is_cost_of_goods_verified', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
				echo $form->input('Transaction.physician_number');
				echo $form->input('Transaction.client_zip_code');
				echo $form->input('Transaction.long_term_care_facility_number');
				echo $form->input('Transaction.referral_number_from_aaa_file');
				echo $form->input('Transaction.should_post_transaction_to_aaa_file', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
				echo $form->input('Transaction.cost_of_goods_sold');
				echo $form->input('Transaction.diagnosis_code_1');
				echo $form->input('Transaction.diagnosis_code_2');
				echo $form->input('Transaction.diagnosis_code_3');
				echo $form->input('Transaction.diagnosis_code_4');
				echo $form->input('Transaction.ship');
				echo $form->input('Transaction.driver_or_technician_initials_1');
				echo $form->input('Transaction.is_field_updated_2', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
				echo $form->input('Transaction.cash_reference_number');
				echo $form->input('Transaction.created_by');
				echo $form->input('Transaction.created', array('type' => 'text'));
				echo $form->input('Transaction.modified_by');
				echo $form->input('Transaction.modified', array('type' => 'text'));
			?>
		</div>
		
		<br style="clear: left;" />
	</div>
</div>

<!-- this is throwaway and toggles everything read-only -->
<script type="text/javascript">
	$("TransactionDetailModuleContainer").select("input").each(function(input) {
		input.addClassName("ReadOnlyClient").setAttribute("readOnly", "readOnly"); 
		
		if (input.type == "checkbox")
		{
			input.setAttribute("disabled", "disabled");
		}
	});
</script>