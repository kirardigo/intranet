<?php
	if (!count($this->data['Rental']))
	{
		echo '<div class="NoRecordNotice">No rentals for this customer.</div>';
		exit;
	}
	
	$paginator->options(
		array(
			'url' => array(
				'controller' => 'modules/rentals',
				'action' => "forCustomer/{$accountNumber}/1"
			),
			'update' => 'RentalsForCustomerContainer'
		)
	);
	
	if (!$isUpdate)
	{
		echo '<div id="RentalsForCustomerContainer">';
	}
?>

<?= $this->element('page_links'); ?>
<table id="RentalsForCustomerTable" class="Styled">
	<thead>
		<tr>
			<?php
				echo $paginator->sortableHeader('HCPC', 'healthcare_procedure_code');
				echo $paginator->sortableHeader('Inventory#', 'inventory_number');
				echo $paginator->sortableHeader('Description', 'inventory_description');
				echo $paginator->sortableHeader('Serial', 'serial_number');
				echo $paginator->sortableHeader('Carr 1', 'carrier_1_code', array('style' => 'white-space: nowrap;'));
				echo $paginator->sortableHeader('Carr 2', 'carrier_2_code', array('style' => 'white-space: nowrap;'));
				echo $paginator->sortableHeader('Carr 3', 'carrier_3_code', array('style' => 'white-space: nowrap;'));
			?>
			<th class="Right">Gross Amt</th>
			<th class="Right">Allowed Amt</th>
			<?php
				echo $paginator->sortableHeader('Setup', 'setup_date');
				echo $paginator->sortableHeader('Return', 'returned_date');
				echo $paginator->sortableHeader('FC', 'form_code');
			?>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		foreach (ifset($this->data['Rental'], array()) as $row)
		{
			$allowedAmount = $row['carrier_1_net_amount'] + $row['carrier_2_net_amount'] + $row['carrier_3_net_amount'];
			$grossAmount = $row['carrier_1_gross_amount'] + $row['carrier_2_gross_amount'] + $row['carrier_3_gross_amount'];
			
			echo $html->tableCells(
				array(
					h($row['healthcare_procedure_code']),
					h($row['inventory_number']),
					h($row['inventory_description']),
					h($row['serial_number']),
					h($row['carrier_1_code']),
					h($row['carrier_2_code']),
					h($row['carrier_3_code']),
					array(number_format($grossAmount, 2), array('class' => 'Right')),
					array(number_format($allowedAmount, 2), array('class' => 'Right')),
					h($row['setup_date']),
					h($row['returned_date']),
					h($row['form_code']),
					$html->link($html->image('iconDetail.png'), '#', array(
						'escape' => false,
						'title' => 'Show details',
						'class' => 'Detail'
					)) . $form->hidden('id', array('value' => $row['id'])),
					$html->link($html->image('iconDocument.png'), '#', array(
						'escape' => false,
						'title' => 'Show invoices with HCPC: ' . h($row['healthcare_procedure_code']),
						'class' => 'Invoice'
					)) . $form->hidden('id', array('value' => $row['id'])),
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	// Clear details when paging
	$('RentalsForCustomerDetailInfo').update();
	Modules.Rentals.ForCustomer.addHandlers();
</script>

<?php if (!$isUpdate): ?>

</div>

<div id="RentalsForCustomerDetailInfo" style="margin-top: 20px;"></div>

<?php endif; ?>