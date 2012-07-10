<?= $form->create('', array('id' => 'OxygenForCustomerForm')) ?>

<div class="GroupBox FormColumn" style="width: 350px; height: 450px;">
	<h2>Oxygen Information</h2>
	<div class="Content">
	<?php
		$yndField = array(
			'Y' => 'Yes',
			'N' => 'No',
			'D' => 'Does Not Apply'
		);
		
		echo $form->input('Oxygen.equipment_type', array(
			'label' => 'Type of O2 System',
			'options' => $equipmentTypes,
			'empty' => true
		));
		echo $form->input('Oxygen.oxygen_flow_rate', array(
			'label' => 'Flow Rate',
			'class' => 'Text50'
		));
		echo $form->input('Oxygen.frequency_of_use', array(
			'label' => 'Usage Requirement',
			'options' => $usageRequirements,
			'empty' => true
		));
		echo $form->input('Oxygen.duration', array(
			'label' => 'Hours Per Day',
			'class' => 'Text25'
		));
		echo $form->input('Oxygen.delivery_method', array(
			'options' => $deliveryMethods,
			'empty' => true
		));
		echo $form->input('Oxygen.is_portable_oxygen', array(
			'label' => 'Portable O2',
			'options' => $yndField,
			'empty' => ''
		));
		echo $form->input('Oxygen.conserving_device', array(
			'label' => 'Conserving Device',
			'options' => $yndField,
			'empty' => ''
		));
		echo $form->input('Oxygen.backup_oxygen_unit', array(
			'label' => array(
				'text' => 'Backup System',
				'class' => 'Checkbox'
			),
			'div' => array('style' => 'margin: 4px 0px;')
		));
		echo $form->input('Oxygen.backup_waived', array(
			'label' => array(
				'text' => 'Backup Waiver on File',
				'class' => 'Checkbox'
			),
			'div' => array('style' => 'margin: 4px 0px;')
		));
		echo $form->input('Oxygen.lab_initial_date_ordered_or_renewal', array(
			'type' => 'text',
			'label' => 'Initial Date Ordered',
			'class' => 'Text75'
		));
		
		echo $ajax->submit('Save', array(
			'id' => 'OxygenSave',
			'class' => 'StyledButton',
			'style' => 'margin-left: 0px; margin-top: 80px;',
			'url' => "/modules/oxygen/oxygenForCustomer/{$accountNumber}", 
			'condition' => 'Modules.Oxygen.OxygenForCustomer.onBeforePost(event)',
			'complete' => 'Modules.Oxygen.OxygenForCustomer.onPostCompleted(request)'
		));
		
		echo $form->hidden('Oxygen.id');
	?>
	</div>
</div>
	
<div class="GroupBox FormColumn" style="width: 350px;">
	<h2>Test Facility</h2>
	<div class="Content">
	<?php
		echo $form->input('Oxygen.lab_facility_where_tested', array(
			'label' => 'Facility',
			'class' => 'Text300'
		));
		echo $form->input('Oxygen.lab_test_facility_address_1', array(
			'label' => 'Address 1',
			'class' => 'Text300'
		));
		echo $form->input('Oxygen.lab_test_facility_address_2', array(
			'label' => 'Address 2',
			'class' => 'Text300'
		));
		echo $form->input('Oxygen.lab_test_facility_city', array(
			'label' => 'City',
			'class' => 'Text200'
		));
		echo $form->input('Oxygen.lab_test_facility_state', array(
			'label' => 'State',
			'class' => 'Text25',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.lab_test_facility_zip', array(
			'label' => 'Zip',
			'class' => 'Text100'
		));
	?>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 350px;">
	<h2>Test Results</h2>
	<div class="Content">
	<?php
		echo $form->input('Oxygen.date_test_performed', array(
			'type' => 'text',
			'label' => 'Date Test Performed',
			'class' => 'Text75'
		));
		echo $form->input('Oxygen.lab_test_conditions', array(
			'label' => 'Test Condition',
			'options' => $testConditions,
			'empty' => true
		));
		echo $form->input('Oxygen.oxygen_saturation', array(
			'class' => 'Text50'
		));
		echo $form->input('Oxygen.lab_clinical_findings', array(
			'label' => 'Clinical Findings',
			'options' => $clinicalFindings,
			'empty' => true
		));
		echo $form->input('Oxygen.lab_inpatient_outpatient_indicator', array(
			'label' => 'In Pat/Out Pat',
			'class' => 'Text25'
		));
	?>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 350px;">
	<h2>Test Results on 4 1pm</h2>
	<div class="Content">
	<?php
		echo $form->input('Oxygen.lab_arterial_blood_gas_on_4_1pm', array(
			'label' => 'ABG Result',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.lab_oxygen_saturation_on_4_1pm', array(
			'label' => 'Saturation',
			'class' => 'Text50'
		));		
	?>
	</div>
</div>

<br class="ClearBoth" />
<h2>Active Oxygen Rentals</h2>
<table class="Styled" id="OxygenOxygenRentalTable">
	<thead>
		<tr>
			<th>Inventory#</th>
			<th>Description</th>
			<th>HCPC</th>
			<th>Setup Date</th>
			<th class="Right"># Months</th>
			<th>Rx Date</th>
			<th class="Right">Rx Months</th>
			<th class="Right">Total Gross</th>
			<th class="Right">Total Net</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($rentals as $row)
			{
				$totalGross = $row['Rental']['carrier_1_gross_amount'] 
					+ $row['Rental']['carrier_2_gross_amount'] 
					+ $row['Rental']['carrier_3_gross_amount'];
				$totalNet = $row['Rental']['carrier_1_net_amount'] 
					+ $row['Rental']['carrier_2_net_amount'] 
					+ $row['Rental']['carrier_3_net_amount'];
				
				echo $html->tableCells(
					array(
						h($row['Rental']['inventory_number']),
						h($row['Rental']['inventory_description']),
						h($row['Rental']['healthcare_procedure_code']),
						h($row['Rental']['setup_date']),
						array(h($row['Rental']['number_of_rental_months']), array('class' => 'Right')),
						h($row['Rental']['prescription_date']),
						array(h($row['Rental']['prescription_duration']), array('class' => 'Right')),
						array(number_format($totalGross, 2), array('class' => 'Right')),
						array(number_format($totalNet, 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>
<br/><br/>

<?= $form->end(); ?>

<script type="text/javascript">
	Modules.Oxygen.OxygenForCustomer.addHandlers();
	Modules.Oxygen.OxygenForCustomer.initializeTable("OxygenOxygenRentalTable");
</script>