<?= $html->link('Create New Record', '#', array('id' => 'CreateCustomerOwnedEquipmentLink')); ?>
<table id="CustomerOwnedEquipmentForCustomerTable" class="Styled" style="margin-top: 5px;">
	<thead>
		<tr>
			<th>COE #</th>
			<th>Serial #</th>
			<th>Model</th>
			<th>Description</th>
			<th>MFG Frame</th>
			<th>MFG Tilt</th>
			<th>DOP</th>
			<th>TCN</th>
			<th>Invoice #</th>
			<th>Funding</th>
			<th>Active?</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		if (!count($this->data))
		{
			echo '<tr><td colspan="13"><div class="NoRecordNotice">No customer owned equipment for this customer.</div></td></tr>';
		}
		else
		{
			foreach ($this->data as $row)
			{
				$ledgerLink = '';
				
				if ($row['CustomerOwnedEquipment']['invoice_number'] != '')
				{
					$ledgerLink = $html->link($html->image('iconLedger.png'), '#', array(
						'escape' => false,
						'title' => 'Show ledger for invoice: ' . h($row['CustomerOwnedEquipment']['invoice_number']),
						'class' => 'Ledger'
					)) . $form->hidden('invoice_number', array('value' => $row['CustomerOwnedEquipment']['invoice_number']));
				}
				
				echo $html->tableCells(
					array(
						h($row['CustomerOwnedEquipment']['customer_owned_equipment_id_number']),
						h($row['CustomerOwnedEquipment']['serial_number']),
						h($row['CustomerOwnedEquipment']['model_number']),
						h($row['CustomerOwnedEquipment']['description']),
						h($row['CustomerOwnedEquipment']['manufacturer_frame_code']),
						h($row['CustomerOwnedEquipment']['tilt_manufacturer_code']),
						h($row['CustomerOwnedEquipment']['date_of_purchase']),
						h($row['CustomerOwnedEquipment']['transaction_control_number']),
						h($row['CustomerOwnedEquipment']['invoice_number']),
						h($row['CustomerOwnedEquipment']['initial_carrier_number']),
						$html->link($row['CustomerOwnedEquipment']['is_active'] ? 'Yes' : 'No', '#', array(
							'alt' => 'Toggle status',
							'class' => 'COEActiveLink'
						)),
						$html->link($html->image('iconDetail.png'), '#', array(
							'escape' => false,
							'title' => 'Show details',
							'class' => 'Detail'
						)) . $form->hidden('id', array('value' => $row['CustomerOwnedEquipment']['id'])),
						$ledgerLink
					),
					array(),
					array('class' => 'Alt')
				);
			}
		}
	?>
	</tbody>
</table>

<div id="CustomerOwnedEquipmentForCustomerDetailInfo" style="margin-top: 10px"></div>

<script type="text/javascript">
	Modules.CustomerOwnedEquipment.ForCustomer.initializeSortableTable();
	Modules.CustomerOwnedEquipment.ForCustomer.addHandlers();
	
	<?php
		if ($load == 'new')
		{
			alert("Not yet implemented");
			//echo 'Modules.CustomerOwnedEquipment.ForCustomer.onRecordCreated();';
		}
		else if ($load != '')
		{
			echo  "Modules.CustomerOwnedEquipment.ForCustomer.selectRecord({$load});";
		}
	?>
</script>
