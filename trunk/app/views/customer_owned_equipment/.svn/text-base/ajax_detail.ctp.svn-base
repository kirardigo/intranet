<?= $form->create('', array('id' => 'CustomerOwnedEquipmentDetailForm')); ?>

<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="CustomerOwnedEquipmentSaveTop" value="Save" />
</div>
<div class="GroupBox">
	<h2>General Info</h2>
	<div class="Content">
		<div class="FormColumn">
			<?php
				echo $form->hidden('CustomerOwnedEquipment.id');
				echo $form->hidden('CustomerOwnedEquipment.account_number');
				echo $form->input('CustomerOwnedEquipment.customer_owned_equipment_id_number', array(
					'label' => 'COE #',
					'class' => 'Text75 ReadOnly',
					'readonly' => 'readonly',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('CustomerOwnedEquipment.is_active', array(
					'label' => array('class' => 'Checkbox'),
					'div' => array('style' => 'margin: 15px 0px 5px 185px;')
				));
				echo $form->input('CustomerOwnedEquipment.description', array('class' => 'Text300'));
				echo $form->input('CustomerOwnedEquipment.model_number', array(
					'label' => 'Model #',
					'class' => 'Text200'
				));
				echo $form->input('CustomerOwnedEquipment.serial_number', array(
					'label' => 'Serial #',
					'class' => 'Text200'
				));
			?>
			
			<br />
			
			<div class="FormColumn">
				<?php
					echo $form->input('CustomerOwnedEquipment.tilt_manufacturer_code', array('class' => 'Text50'));
					echo $form->input('CustomerOwnedEquipment.tilt_model_number', array('class' => 'Text150'));
					echo $form->input('CustomerOwnedEquipment.tilt_serial_number', array('class' => 'Text150'));
				?>
			</div>
			<div class="FormColumn">
				<?php
					echo $form->input('CustomerOwnedEquipment.manufacturer_seating_code', array('class' => 'Text50'));
					echo $form->input('CustomerOwnedEquipment.upholstery_color', array('class' => 'Text150'));
					echo $form->input('CustomerOwnedEquipment.frame_color', array('class' => 'Text150'));
				?>
			</div>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('CustomerOwnedEquipment.date_of_purchase', array(
					'type' => 'text',
					'label' => 'DOP',
					'class' => 'Text75'
				));
				echo $form->input('CustomerOwnedEquipment.manufacturer_frame_code', array(
					'label' => 'Frame Mfg Code',
					'class' => 'Text50'
				));
				echo $form->input('CustomerOwnedEquipment.transaction_control_number', array(
					'label' => 'TCN #',
					'class' => 'Text75'
				));
				echo $form->input('CustomerOwnedEquipment.invoice_number', array(
					'label' => 'Invoice #',
					'class' => 'Text75'
				));
				echo $form->input('CustomerOwnedEquipment.initial_carrier_number', array(
					'label' => 'Funding',
					'class' => 'Text50'
				));
				echo $form->input('CustomerOwnedEquipment.purchase_healthcare_procedure_code', array(
					'label' => 'Purchase HCPC',
					'class' => 'Text75'
				));
				echo $form->input('CustomerOwnedEquipment.pmd_classification', array(
					'label' => 'PMD Class',
					'options' => $pmdClasses,
					'empty' => true
				));
			?>
		</div>
	</div>
	<div class="ClearBoth"></div>
</div>

<div class="GroupBox">
	<h2>Warranty</h2>
	<div class="Content">
		<?php
			echo $form->input('CustomerOwnedEquipment.warranty_identification_number', array(
				'label' => 'Warranty ID',
				'class' => 'Text100',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerOwnedEquipment.warranty_extension', array(
				'label' => 'Extension',
				'class' => 'Text250',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerOwnedEquipment.warranty_expiration_date', array(
				'type' => 'text',
				'label' => 'Expiration Date',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('CustomerOwnedEquipment.warranty', array(
				'label' => 'Warranty Code',
				'class' => 'Text300'
			));
		?>
	</div>
</div>

<?php if ($id != null): ?>
<div class="GroupBox">
	<h2>Notes</h2>
	<div class="Content">
		<?php
			echo $form->input('Note.general.note', array(
				'type' => 'textarea',
				'value' => ifset($noteRecord['general']['note']),
				'class' => 'TextArea400'
			));
			
			echo $this->element('note_info', array('noteRecord' => $noteRecord['general']));
		?>
	</div>
</div>
<?php endif; ?>

<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="CustomerOwnedEquipmentSaveBottom" value="Save" />
	<?php if ($id != null): ?>
		<input type="button" class="StyledButton" id="CustomerOwnedEquipmentDelete" tabindex="32" value="Delete" style="margin-left: 20px;" />
	<?php endif; ?>
</div>

<?= $form->end(); ?>
