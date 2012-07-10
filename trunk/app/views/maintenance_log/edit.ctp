<script type="text/javascript">
	function verifySerialNumber()
	{
		new Ajax.Request("/json/serializedEquipment/information/" + $F("MaintenanceLogSerializedEquipmentNumber"), {
			onSuccess: function(transport) {
				if (transport.headerJSON.success)
				{
					$("SerialEquipmentName").innerHTML = transport.headerJSON.record.product_description;
				}
				else
				{
					alert("This MRS# is not valid.");
					$("MaintenanceLogSerializedEquipmentNumber").clear().focus();
					$("SerialEquipmentName").innerHTML = "";
				}
			}
		});
	}
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($message) && $message): ?>
			alert("<?= $message ?>");
		<?php endif; ?>
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("maintenanceLog:updated", {
				id: $F("MaintenanceLogId")
			});
			closeWindow();
			exit;
		<?php endif; ?>
		mrs.bindDatePicker("MaintenanceLogDateOfService");
		$("MaintenanceLogSerializedEquipmentNumber").observe("blur", verifySerialNumber);
		$("MaintenanceLogSerializedEquipmentNumber").select();
		$("CloseButton").observe("click", closeWindow);
	});
</script>
<?php
	echo $form->create('', array('url' => "/maintenanceLog/edit/{$id}"));
?>
<div class="GroupBox" style="width: 600px;">
	<h2>Maintenance Log</h2>
	<div class="Content">
	<?php
		$mrsInfo = '<span id="SerialEquipmentName">' . ifset($this->data['SerializedEquipment']['product_description']) . '</span>';
		
		if (isset($this->data['SerializedEquipment']['date_of_sale']) && $this->data['SerializedEquipment']['date_of_sale'] != '')
		{
			$mrsInfo .= ' <span id="SerialEquipmentWarning">(* ' . $this->data['SerializedEquipment']['date_of_sale'] . ' *)</span>';
		}
		
		echo $form->hidden('MaintenanceLog.id');
		echo $form->input('MaintenanceLog.serialized_equipment_number', array(
			'label' => 'MRS#',
			'class' => 'Text100',
			'style' => 'margin-right: 20px;',
			'after' => $mrsInfo
		));
		echo $form->input('MaintenanceLog.profit_center_number', array(
			'label' => 'PCtr',
			'options' => $profitCenters,
			'empty' => true
		));
		echo $form->input('MaintenanceLog.staff_initials', array(
			'label' => 'Ini',
			'class' => 'Text50'
		));
		echo $form->input('MaintenanceLog.account_number', array(
			'label' => 'Account#',
			'class' => 'Text75',
			'style' => 'margin-right: 20px;',
			'after' => ifset($this->data['Customer']['name'])
		));
		echo $form->input('MaintenanceLog.date_of_service', array(
			'type' => 'text',
			'class' => 'Text75'
		));
		echo $form->input('MaintenanceLog.invoice_number', array(
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		if ($id == null)
		{
			echo $form->hidden('MaintenanceLog.maintenance_type', array(
				'value' => 'M'
			));
			echo $form->input('MaintenanceLog.maintenance_type_text', array(
				'label' => 'Type',
				'value' => ifset($maintenanceTypes['M']),
				'class' => 'Text100 ReadOnly',
				'readonly' => 'readonly',
				'div' => array('class' => 'Horizontal')
			));
		}
		else
		{
			echo $form->hidden('MaintenanceLog.maintenance_type');
			echo $form->input('MaintenanceLog.maintenance_type_text', array(
				'label' => 'Type',
				'value' => ifset($maintenanceTypes[$this->data['MaintenanceLog']['maintenance_type']]),
				'class' => 'Text100 ReadOnly',
				'readonly' => 'readonly',
				'div' => array('class' => 'Horizontal')
			));
		}
		
		if ($id != null)
		{
			echo $form->input('MaintenanceLog.created', array(
				'type' => 'text',
				'readonly' => 'readonly',
				'class' => 'Text100 ReadOnly',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('MaintenanceLog.created_by', array(
				'readonly' => 'readonly',
				'class' => 'Text50 ReadOnly'
			));
		}
		
		echo '<div class="ClearBoth"></div>';
		
		echo $form->input('MaintenanceLog.is_cleaned', array(
			'label' => array(
				'text' => 'Cleaned?',
				'class' => 'Checkbox'
			),
			'div' => array('style' => 'margin: 5px 0;')
		));
		echo $form->input('MaintenanceLog.comment', array(
			'label' => 'Action',
			'options' => $maintenanceActions
		));
		echo $form->input('MaintenanceLog.notes_1', array(
			'label' => 'Notes',
			'class' => 'Text500'
		));
		echo $form->input('MaintenanceLog.notes_2', array(
			'label' => false,
			'class' => 'Text500'
		));
		echo $form->input('MaintenanceLog.hours', array(
			'class' => 'Text75'
		));
		echo $form->input('MaintenanceLog.oxygen_percent', array(
			'label' => 'O2 %',
			'class' => 'Text50'
		));
		echo $form->input('MaintenanceLog.is_liter_flow_acceptable', array(
			'label' => array(
				'text' => 'Liter Flow OK?',
				'class' => 'Checkbox'
			),
			'div' => array('style' => 'margin: 5px 0;')
		));
	?>
	</div>
</div>

<?php
	if ($id == null)
	{
		echo $form->hidden('Virtual.is_continue', array('value' => 1));
		echo $form->submit('Continue', array('id' => 'ContinueButton', 'class' => 'StyledButton', 'style' => 'margin: 0;', 'div' => false));
		echo $form->button('Exit', array('id' => 'CloseButton', 'class' => 'StyledButton'));
	}
	else
	{
		echo $form->submit('Save', array('id' => 'SaveButton', 'style' => 'margin: 0;'));
	}
	
	echo $form->end();
?>