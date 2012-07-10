<?php
	// Build the URL for pagination based on named parameters
	$parameters = '';
	$filteredByInvoice = false;
	
	if (isset($this->params['named']['invoiceNumber']))
	{
		$parameters .= '/invoiceNumber:' . $this->params['named']['invoiceNumber'];
		$filteredByInvoice = true;
	}
	
	if (isset($this->params['named']['carrierNumber']))
	{
		$parameters .= '/carrierNumber:' . $this->params['named']['carrierNumber'];
	}
	
	if (isset($this->params['named']['transactionType']))
	{
		$parameters .= '/transactionType:' . $this->params['named']['transactionType'];
	}
	
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/invoices',
			'action' => 'ledger/' . urlencode($accountNumber) . '/1/0' . $parameters
		),
		'params' => $this->passedArgs,
		'update' => 'InvoiceLedgerModuleLedgerContainer'
	));
?>

<?php if (!$isUpdate): ?>
	<?php
		if ($filteredByInvoice)
		{
			echo '<a id="LedgerViewAllInvoices" href="#">View All Invoices</a><br/><br/>';
		}
	?>
	<table class="Styled" style="width: 400px; float: right; margin-left: 10px;">	
		<tr>
			<?= '<th class="Right">Total ' . implode('s</th><th class="Right">Total ', Set::extract('/TransactionType[is_transfer=0]/description', $transactionTypes)) . 's</th>' ?>
			<th class="Right">Total Transfers</th>
			<th class="Right">Balance</th>
		</tr>
		<tr>
			<?php
				$transfers = 0;
				$grandTotal = 0;
				
				//total up the amounts in the transactions for each transaction type,
				//but keep all types that are considered a transfer in their own grouped total
				foreach ($transactionTypes as $type)
				{
					$total = array_sum(Set::extract("/Transaction[transaction_type={$type['TransactionType']['code']}]/../0/total", $totals));
					$grandTotal += $type['TransactionType']['is_amount_subtracted'] ? ($total * -1) : $total;
	
					if (!$type['TransactionType']['is_transfer'])
					{
						echo '<td class="Right" style="white-space: nowrap;">' . number_format($total, 2) . '</td>';
					}
					else
					{
						$transfers += $total;
					}
				}
			?>
			
			<td class="Right" style="white-space: nowrap;"><?= number_format($transfers, 2) ?></td>
			<td class="Right" style="white-space: nowrap;"><?= number_format($grandTotal, 2) ?></td>
		</tr>
	</table>
	
	<?php if (isset($invoice)): ?>
		<div class="DisplayForm">
			<label>Invoice:</label><p><?= $invoice['Invoice']['invoice_number'] ?></p>
			<label>TCN:</label><p><?= $invoice['Invoice']['transaction_control_number'] ?></p>
		</div>
	<?php endif; ?>
	
	<?php if (!empty($invalidInvoices)): ?>
		<h2 class="Exception">There were one or more transactions with invoices that could not be found. The ledger will not be accurate. The invalid transactions will be marked in red.</h2>
	<?php endif; ?>
	
	<?php
		echo '<div style="clear: left;">';
		echo $form->create('', array());
		echo $form->hidden('Transaction.invoice_number');
		echo $form->input('Transaction.carrier_number', array(
			'label' => 'Carrier',
			'options' => $carriers,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Transaction.transaction_type', array(
			'label' => 'Type',
			'options' => $transactionTypeList,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->button('Filter', array('id' => 'InvoicesLedgerFilterButton', 'style' => 'margin-top: 10px;'));
		echo $form->end();
		echo '</div>';
	?>
	
	<div id="InvoiceLedgerModuleLedgerContainer" style="clear: both; margin-top: 5px">
<?php endif; ?>

<?= $this->element('page_links') ?>

<table id="InvoiceLedgerModuleLedgerTable" class="Styled">
	<tr>
		<th>Transaction Date</th>
		<?php if (!isset($invoice)): ?>
			<th>Invoice</th>
			<th>TCN</th>
		<?php endif; ?>
		<th>Carrier</th>
		<th>Type</th>
		<th>G/L Description</th>
		<?php if (isset($invoice)): ?>
			<th>Inventory Description</th>
		<?php endif; ?>
		<th>HCPC Code</th>
		<th class="Right">Transaction Amount</th>
		<th class="Right">Carrier Balance</th>
		<th>&nbsp;</th>
	</tr>
	
	<?php
		//massage types for O(1) lookup
		$transactionTypes = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}');
		$previousCarrier = null;
		
		//go through each transaction
		foreach ($transactions as $transaction)
		{
			//put a divider line between different carriers
			if ($previousCarrier !== null && $previousCarrier != $transaction['Transaction']['carrier_number'])
			{
				echo '<tr class="GroupTotal"><td colspan="' . (isset($invoice) ? "9" : "10") . '">&nbsp;</td></tr>';
			}
			
			$isSubtracted = $transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['is_amount_subtracted'];
			$amount = $isSubtracted ? ($transaction['Transaction']['amount'] * -1) : $transaction['Transaction']['amount'];
			$amountClass = $transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['is_transfer'] ? ' Transfer' : ($amount < 0 ? ' Negative' : '');
			
			//all this array_merge craziness essentially boils down to:
			//1. If we are filtered to a particular invoice, show the inventory description on each line.
			//2. If we are NOT filtered to a particular invoice, show the invoice number and TCN number on each line instead.
			echo $html->tableCells(
				array_merge(
					array(
						formatDate($transaction['Transaction']['transaction_date_of_service'])
					),
					isset($invoice) 
						? array()
						: array(h($transaction['Transaction']['invoice_number']), h($transaction['Transaction']['transaction_control_number'])),
					array(
						h($transaction['Transaction']['carrier_number']),
						h($transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['description']),
						h($transaction['Transaction']['general_ledger_description'])
					),
					isset($invoice)
						? array(h($transaction['Transaction']['inventory_description']))
						: array(),
					array(
						h($transaction['Transaction']['healthcare_procedure_code']),
						array(number_format($amount, 2), array('class' => 'Right' . $amountClass)),
						array(number_format($transaction['Transaction']['carrier_balance_due'], 2), array('class' => 'Right')),
						$html->link($html->image('iconDetail.png', array('title' => 'Show Transaction Details')), '#', array('id' => "LedgerTransaction_{$transaction['Transaction']['transaction_id']}", 'escape' => false))
					)
				),
				$transaction['Transaction']['is_invalid_invoice'] ? array('class' => 'InvalidLedgerRow') : array(),
				$transaction['Transaction']['is_invalid_invoice'] ? array('class' => 'InvalidLedgerRow') : array('class' => 'Alt')
			);
			
			$previousCarrier = $transaction['Transaction']['carrier_number'];
		}
	?>
</table>

<?= $this->element('page_links') ?>

<script type="text/javascript">
	Modules.Invoices.Ledger.initialize();
</script>

<?php if (!$isUpdate): ?>
	</div>
<?php endif; ?>