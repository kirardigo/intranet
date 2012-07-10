<?php
	if (!count($this->data['Purchase']))
	{
		echo '<div class="NoRecordNotice">No purchases for this ' . ($invoiceNumber != null ? 'invoice' : 'customer') . '.</div>';
		exit;
	}
	
	$paginator->options(
		array(
			'url' => array(
				'controller' => 'modules/purchases',
				'action' => "forCustomer/{$accountNumber}" . ($invoiceNumber != null ? "/{$invoiceNumber}" : '')
			),
			'update' => 'PurchasesForCustomerContainer'
		)
	);
	
	if (!$isUpdate)
	{
		if ($invoiceNumber != null)
		{
			echo '<h2>Showing purchases for invoice: ' . h($invoiceNumber) . '</h2>';
		}
		
		echo '<div id="PurchasesForCustomerContainer">';
	}
?>

<?= $this->element('page_links'); ?>
<table id="PurchasesForCustomerTable" class="Styled">
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
				echo $paginator->sortableHeader('Date of Service', 'date_of_service', array('class' => 'Right'));
			?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		foreach ($this->data['Purchase'] as $row)
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
					array(h($row['date_of_service']), array('class' => 'Right')),
					$html->link($html->image('iconDetail.png'), '#', array(
						'escape' => false,
						'title' => 'Show details',
						'class' => 'Detail'
					)) . $form->hidden('id', array('value' => $row['id']))
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
	$('PurchasesForCustomerDetailInfo').update();
	Modules.Purchases.ForCustomer.addHandlers();
</script>

<?php if (!$isUpdate): ?>

</div>

<div id="PurchasesForCustomerDetailInfo" style="margin-top: 20px;"></div>

<?php endif; ?>