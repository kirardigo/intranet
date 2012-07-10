<?php
	echo $form->create('', array('id' => 'CarrierSearchForm', 'onsubmit' => 'return false;'));
	echo $form->input('Carrier.search', array(
		'label' => false,
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	echo '<div style="display: none;" id="Carrier_autoComplete" class="auto_complete AutoComplete550"></div>';
	echo '<label for="CarrierSearch">Add Carrier</label>';
	echo $form->end();
?>

<table id="CustomerCarriersForCustomer_CarrierTable" class="Styled">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th style="white-space: nowrap">Carr #</th>
			<th>Type</th>
			<th>Status</th>
			<th style="white-space: nowrap">Claim #</th>
			<th>Insured Name</th>
			<th>Insured DOB</th>
			<th>S/W</th>
			<th>Term Date</th>
			<th style="white-space: nowrap">Seq #</th>
			<th>Source Pmt</th>
			<th>Ins Code</th>
			<th>Carr Name</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		if ($results !== false)
		{
			foreach ($results['CustomerCarrier'] as $row)
			{
				$class = '';
				$altClass = 'Alt';
				
				if ($row['insurance_type_code'] === '' && !isset($editID))
				{
					$editID = $row['id'];
					$class = 'Highlight';
					$altClass = 'Alt Highlight';
				}
				
				echo $html->tableCells(
					array(
						$form->hidden('id', array('value' => $row['id'])) .
						$html->link($html->image('iconDelete.png'), '#', array(
							'escape' => false,
							'title' => 'Remove customer carrier',
							'class' => 'Delete'
						)),
						h($row['carrier_number']),
						h($row['carrier_type']),
						$row['is_active'] ? 'Y' : 'N',
						h($row['claim_number']),
						h($row['insuree_name']),
						h($row['policy_holder_date_of_birth']),
						h($row['signature_authorization_on_file']),
						h($row['policy_termination_date']),
						h($row['sequence_number']),
						h($row['source_of_payment_for_claim']),
						h($row['insurance_type_code']),
						h($row['carrier_name']),
						$html->link($html->image('iconDetail.png'), '#', array(
							'escape' => false,
							'title' => 'Show details',
							'class' => 'Detail'
						))
					),
					array('class' => $class),
					array('class' => $altClass)
				);
			}
		}
	?>
	</tbody>
</table>

<div id="CustomerCarriersForCustomer_DetailInfo" style="margin-top: 10px;"></div>

<script type="text/javascript">
	Modules.CustomerCarriers.ForCustomer.initializeSortableTable();
	Modules.CustomerCarriers.ForCustomer.addHandlers();
	<?php
		if (isset($editID))
		{
			echo "Modules.CustomerCarriers.ForCustomer.launchEdit({$editID});";
		}
	?>
</script>
