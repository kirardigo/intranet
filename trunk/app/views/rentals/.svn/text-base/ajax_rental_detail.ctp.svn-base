<?= $form->create('', array('id' => 'RentalsDetailForm')); ?>

<div class="GroupBox" style="height: 185px;">
	<h2>General Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Rental.inventory_number', array(
				'class' => 'Text200',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.inventory_description', array('class' => 'Text300'));
		?>
		
		<br class="ClearBoth" />
		
		<div class="FormColumn">
		<?php
			echo $form->input('Rental.serial_number', array('class' => 'Text100'));
			echo $form->input('Rental.setup_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
			echo $form->input('Rental.returned_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
		?>
		</div>
		
		<div class="FormColumn">
		<?php
			echo $form->input('Rental.quantity', array('class' => 'Text50'));
			echo $form->input('Rental.service_to_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
			echo $form->input('Rental.last_invoiced_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
		?>
		</div>
		
		<div class="FormColumn">
		<?php
			echo $form->input('Rental.department_code', array(
				'label' => 'Dept',
				'class' => 'Text25'
			));
			echo $form->input('Rental.rental_day', array('class' => 'Text25'));
			echo $form->input('Rental.number_of_rental_months', array(
				'label' => 'Months Rented',
				'class' => 'Text25'
			));
		?>
		</div>
		
		<div class="FormColumn">
		<?php
			echo $form->input('Rental.place_of_service', array('class' => 'Text25'));
			echo $form->input('Rental.staff_initials', array(
				'label' => 'Salesman',
				'class' => 'Text50'
			));
			echo $form->input('Rental.is_taxable', array(
				'type' => 'checkbox',
				'label' => array('text' => 'Is Taxable?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 12px 0px 5px;')
			));
		?>
		</div>
	</div>
</div>

<br class="ClearBoth" />

<div class="GroupBox" style="float: right; width: 190px;">
	<h2>Respiratory Followup</h2>
	<div class="Content">
		<?php
			echo $form->input('Rental.followup_code', array(
				'label' => 'Code',
				'class' => 'Text25'
			));
			echo $form->input('Rental.followup_cycle', array(
				'label' => 'Cycle',
				'class' => 'Text25'
			));
			echo $form->input('Rental.followup_date', array(
				'type' => 'text',
				'label' => 'Date',
				'class' => 'Text75'
			));
			echo $form->input('Rental.followup_invoice_number', array(
				'label' => 'Invoice #',
				'class' => 'Text75'
			));
			echo $form->input('Rental.restart_flag', array('class' => 'Text25'));
			echo $form->input('Rental.restart_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
		?>
	</div>
</div>

<div class="GroupBox" style="width: 715px;">
	<h2>Carriers</h2>
	<div class="Content">
		<table class="Styled" style="width: 400px;">
			<tr>
				<th>Carrier</th>
				<th class="Right">Allowed Amount</th>
				<th class="Right">Gross Amount</th>
			</tr>
		<?php
			for($i = 1; $i <= 3; $i++)
			{
				echo $html->tableCells(
					array(
						h($this->data['Rental']["carrier_{$i}_code"]),
						array(number_format($this->data['Rental']["carrier_{$i}_net_amount"], 2), array('class' => 'Right')),
						array(number_format($this->data['Rental']["carrier_{$i}_gross_amount"], 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
		</table>
	</div>
</div>

<div class="GroupBox" style="width: 715px;">
	<h2>Capped Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Rental.6_point_classification', array(
				'label' => 'Class',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.capped_status', array(
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.capped_rental_date', array(
				'type' => 'text',
				'label' => 'Capped Date',
				'class' => 'Text75'
			));
			
			echo $form->input('Rental.maintenance_date', array(
				'type' => 'text',
				'label' => 'M & S Date',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.maintenance_frequency', array(
				'label' => 'M & S Freq',
				'class' => 'Text50'
			));
			
			echo $form->input('Rental.capped_memo', array('class' => 'Text300'));
		?>
	</div>
</div>

<div class="GroupBox" style="width: 715px;">
	<h2>Physician</h2>
	<div class="Content">
		<?php
			echo $form->input('Rental.physician_equipment_code', array(
				'label' => 'Number',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Physician.name', array('class' => 'Text250'));
		?>
	</div>
</div>

<br class="ClearBoth" />
		
<div class="GroupBox">
	<h2>Billing Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Rental.prescription_date', array(
				'type' => 'text',
				'label' => 'Rx Date',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.prescription_duration', array(
				'label' => 'Rx Months',
				'class' => 'Text25'
			));
		?>
		
		<br class="ClearBoth" />
		
		<?php
			echo $form->input('Rental.healthcare_procedure_code', array(
				'type' => 'text',
				'label' => 'HCPC',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('HealthcareProcedureCode.description', array(
				'class' => 'Text500',
				'label' => 'HCPC Description',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.form_code', array(
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.assignment_status_code', array(
				'label' => 'Assign',
				'class' => 'Text25'
			));
		?>
		
		<br class="ClearBoth" />
		
		<?php
			echo $form->input('Rental.modifier_1', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.modifier_2', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.modifier_3', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Rental.modifier_4', array(
				'class' => 'Text25'
			));
		?>
	</div>
</div>

<br class="ClearBoth" />

<div class="GroupBox">
	<h2>Diagnoses</h2>
	<div class="Content">
		<?php
			for ($i = 1; $i <= 4; $i++)
			{
				echo $form->input("Diagnosis.{$i}.code", array(
					'label' => false,
					'class' => 'Text50',
					'before' => "{$i}. ",
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input("Diagnosis.{$i}.description", array(
					'label' => false,
					'class' => 'Text200'
				));
			}
		?>
	</div>
</div>

<br class="ClearBoth" />

<?= $form->end(); ?>