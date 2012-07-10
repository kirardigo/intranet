<?php
	$table = ($closedInvoices ? 'Closed' : 'Open') . 'InvoicesTable';
?>

<?php if (!$closedInvoices && isset($rental)): ?>
	<h2>Filtered by transactions with HCPC: <?= h($rental['Rental']['healthcare_procedure_code']) ?> and DOS of <?= $rental['Rental']['setup_date'] ?> to <?= $rental['Rental']['returned_date'] != null ? $rental['Rental']['returned_date'] : date('m/d/Y') ?></h2>
<?php elseif (!$closedInvoices && $carrierNumber != null): ?>
	<h2>Filtered by carrier: <?= h($carrierNumber) ?></h2>
<?php endif; ?>

<table class="Styled NoBorder" style="margin: 1px;" id="<?= $table ?>">
	<thead>
		<tr>
			<th>Invoice</th>
			<th>TCN</th>
			<th>Dept</th>
			<th>Date</th>
			<th class="Right">Amt</th>
			<th class="Carrier1">Carr 1</th>
			<th class="Carrier1 Right">Balance 1</th>
			<th class="Carrier2">Carr 2</th>
			<th class="Carrier2 Right">Balance 2</th>
			<th class="Carrier3">Carr 3</th>
			<th class="Carrier3 Right">Balance 3</th>
			<th>L1 Status</th>
			<th>L1 Date</th>
			<th class="Right">L1 Amount</th>
			<?= $showPurchasesLink ? '<th>&nbsp;</th>' : '' ?>
			<?= $showEditL1InformationLink ? '<th>&nbsp;</th>' : '' ?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			function carrierLink($html, $code, $clickable)
			{
				return $code == '' ? '' : ($clickable ? $html->link($code, '#', array('class' => 'Carrier')) : h($code));
			}
			
			foreach ($invoices as $i => $invoice)
			{
				echo $html->tableCells(
					array_merge(
						array(
							h($invoice['Invoice']['invoice_number']),
							h($invoice['Invoice']['transaction_control_number']),
							h($invoice['Invoice']['department_code']),
							formatDate($invoice['Invoice']['date_of_service']),
							array(h(number_format($invoice['Invoice']['amount'], 2)), array('class' => 'Right')),
							array(carrierLink($html, $invoice['Invoice']['carrier_1_code'], $clickableCarriers), array('class' => 'Carrier1')),
							array(h(number_format($invoice['Invoice']['carrier_1_balance'], 2)), array('class' => 'Right Carrier1')),
							array(carrierLink($html, $invoice['Invoice']['carrier_2_code'], $clickableCarriers), array('class' => 'Carrier2')),
							array(h(number_format($invoice['Invoice']['carrier_2_balance'], 2)), array('class' => 'Right Carrier2')),
							array(carrierLink($html, $invoice['Invoice']['carrier_3_code'], $clickableCarriers), array('class' => 'Carrier3')),
							array(h(number_format($invoice['Invoice']['carrier_3_balance'], 2)), array('class' => 'Right Carrier3')),
							h($invoice['Invoice']['line_1_status']),
							formatDate($invoice['Invoice']['line_1_date']),
							array(h(number_format($invoice['Invoice']['line_1_amount'], 2)), array('class' => 'Right')),
						),
						$showPurchasesLink ? array($html->link($html->image('iconPurchases.png'), '#', array('class' => 'Purchases', 'title' => 'Purchases', 'escape' => false))) : array(),
						$showEditL1InformationLink ? array($html->link($html->image('iconEdit.png'), '#', array('class' => 'L1', 'title' => 'Edit L1 Information', 'escape' => false))) : array(),
						array(
							$html->link($html->image('iconLedger.png'), '#', array('class' => 'Ledger', 'title' => 'Ledger', 'escape' => false))
						)
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Invoices.ForCustomer.addHandlers("<?= $table ?>");
</script>

<?php if (!$closedInvoices): ?>
	<script type="text/javascript">
		Modules.Invoices.ForCustomer.initializeOpenInvoices();
	</script>
	
	<br /><br />
	<input type="checkbox" class="DenyReadOnly" onclick="Modules.Invoices.ForCustomer.toggleAgedOpenBalances('<?= $accountNumber?>', this.checked, <?= $rentalID != null ? $rentalID : 'null' ?>, <?= $carrierNumber != null ? "'{$carrierNumber}'" : 'null' ?>);" /> <span class="Checkbox">Show Aged Open Balances</span>
	<input type="checkbox" class="DenyReadOnly" onclick="Modules.Invoices.ForCustomer.toggleClosedInvoices('<?= $accountNumber?>', this.checked, <?= $clickableCarriers ? 'true' : 'false' ?>, <?= $showPurchasesLink ? 'true' : 'false' ?>, <?= $showEditL1InformationLink ? 'true' : 'false' ?>, <?= $rentalID != null ? $rentalID : 'null' ?>, <?= $carrierNumber != null ? "'{$carrierNumber}'" : 'null' ?>);" /> <span class="Checkbox">Show Closed Invoices</span>

	<div id="AgedOpenBalanceContainer"></div>
	
	<br /><br />

	<div id="ClosedInvoicesContainer"></div>
<?php endif; ?>