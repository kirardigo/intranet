<?= $form->create('', array('id' => 'PurchasesDetailForm')); ?>
	
<div class="GroupBox" style="height: 185px;">
	<h2>General Info</h2>
	<div class="Content">
		<?php
			echo $form->input('Purchase.inventory_number', array(
				'class' => 'Text200',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.inventory_description', array('class' => 'Text300'));
		?>
		
		<br class="ClearBoth" />
		
		<div class="FormColumn">
			<?php
				echo $form->input('Purchase.serial_number', array(
					'label' => 'Serial #',
					'class' => 'Text100'
				));
				echo $form->input('Purchase.date_of_service', array(
					'type' => 'text',
					'label' => 'DOS',
					'class' => 'Text75'
				));
				echo $form->input('Purchase.invoice_number', array(
					'label' => 'Invoice #',
					'class' => 'Text75'
				));
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('Purchase.department_code', array(
					'label' => 'Dept',
					'class' => 'Text25'
				));
				echo $form->input('Purchase.service_to_date', array(
					'type' => 'text',
					'class' => 'Text75'
				));
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('Purchase.place_of_service', array(
					'label' => 'POS',
					'class' => 'Text100'
				));
				echo $form->input('Purchase.quantity', array('class' => 'Text50'));
				echo $form->input('Purchase.millers_authorization_number', array(
					'label' => 'MRS Auth',
					'class' => 'Text100'
				));
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('Purchase.salesman_number', array(
					'label' => 'Salesman',
					'class' => 'Text50'
				));
				echo $form->input('Purchase.is_taxable', array(
					'label' => array('text' => 'Is Taxable?', 'class' => 'Checkbox'),
					'div' => array('style' => 'margin: 12px 0px 5px;')
				));
				echo $form->input('Purchase.prior_authorization_number', array(
					'label' => 'Prior Auth',
					'class' => 'Text150'
				));
			?>
		</div>
	</div>
</div>

<br class="ClearBoth" />

<div class="GroupBox">
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
						h($this->data['Purchase']["carrier_{$i}_code"]),
						array(number_format($this->data['Purchase']["carrier_{$i}_net_amount"], 2), array('class' => 'Right')),
						array(number_format($this->data['Purchase']["carrier_{$i}_gross_amount"], 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
		</table>
	</div>
</div>

<br class="ClearBoth" />

<div class="GroupBox">
	<h2>Physician</h2>
	<div class="Content">
		<?php
			echo $form->input('Purchase.physician_equipment_code', array(
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
			echo $form->input('Purchase.prescription_date', array(
				'type' => 'text',
				'label' => 'Rx Date',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.prescription_duration', array(
				'label' => 'Rx Months',
				'class' => 'Text25'
			));
		?>
		
		<br class="ClearBoth" />
		
		<?php
			echo $form->input('Purchase.healthcare_procedure_code', array(
				'type' => 'text',
				'label' => 'HCPC',
				'class' => 'Text100',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('HealthcareProcedureCode.description', array(
				'label' => 'HCPC Description',
				'class' => 'Text500',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.form_code', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.assignment_status_code', array(
				'label' => 'Assign',
				'class' => 'Text25'
			));
		?>
		
		<br class="ClearBoth" />
		
		<?php
			echo $form->input('Purchase.modifier_1', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Procedure.modifier_2', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.modifier_3', array(
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Purchase.modifier_4', array('class' => 'Text25'));
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