<?php 
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'TransactionsRelatedForm',
				'url' => '/modules/transactions/related',
				'update' => 'TransactionsRelatedContainer',
				'before' => 'Modules.Transactions.Related.showLoadingDialog();',
				'complete' => 'Modules.Transactions.Related.closeLoadingDialog();'
			)
		);
		
		echo '<div style="padding: 2px;">';
			echo $form->input('Transaction.period_posting_date_start', array(
				'id' => 'TransactionsRelatedPeriodPostingDateStart',
				'label' => 'Starting PPD',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.period_posting_date_end', array(
				'id' => 'TransactionsRelatedPeriodPostingDateEnd',
				'label' => 'Ending PPD',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.profit_center_number', array(
				'id' => 'TransactionsRelatedProfitCenterNumber',
				'options' => $profitCenters,
				'empty' => true,
				'label' => 'Profit Center',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.department_code', array(
				'id' => 'TransactionsRelatedDepartmentCode',
				'options' => $departments,
				'empty' => true,
				'label' => 'Department',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.invoice_number', array(
				'id' => 'TransactionsRelatedInvoiceNumber',
				'maxLength' => false,
				'label' => 'Invoice #*'
			));
		echo '</div>';
		
		echo '<div style="padding: 2px; clear: left;">';		
			echo $form->input('Transaction.transaction_type', array(
				'id' => 'TransactionsRelatedTransactionType',
				'options' => $transactionTypeList,
				'label' => 'Type*',
				'multiple' => 'multiple',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.inventory_number', array(
				'id' => 'TransactionsRelatedInventoryNumber',
				'label' => 'Inventory #*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.healthcare_procedure_code', array(
				'id' => 'TransactionsRelatedHealthCareProcedureCode',
				'label' => 'HCPC*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.general_ledger_code', array(
				'id' => 'TransactionsRelatedGeneralLedgerCode',
				'label' => 'GL Code*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.carrier_number', array(
				'id' => 'TransactionsRelatedCarrierNumber',
				'label' => 'Carr*',
				'maxlength' => false
			));
		echo '</div>';
		
		echo '* Multi-select (use commas to separate values)<br/><div style="margin: 5px 0px; clear: both">';
		echo $form->submit('Search', array('id' => 'TransactionsRelatedSubmitButton', 'div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo '<input type="reset" value="Clear" />';
		echo '</div>';
		
		echo $form->end();
	}
?>

<br style="clear: left;" />

<?php if (!$isPostback): ?>
	<div id="TransactionsRelatedContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/transactions', 
				'action' => 'related'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/transactions', 'action' => 'exportRelatedResults'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'TransactionsRelatedContainer';
	?>
	
	<br/><br/>
	
	<table id="TransactionsRelatedTable" class="Styled" style="width: 1500px;">
		<thead>
			<tr>
				<?php					
					echo $paginator->sortableHeader('PCtr', 'profit_center_number');
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo $paginator->sortableHeader('TCN#', 'transaction_control_number');
					echo $paginator->sortableHeader('DOS', 'transaction_date_of_service');
					echo $paginator->sortableHeader('Carr', 'carrier_number');
					echo $paginator->sortableHeader('Type', 'transaction_type');
					echo $paginator->sortableHeader('G/L Description', 'general_ledger_description');
					echo $paginator->sortableHeader('Inven#', 'inventory_number');
					echo $paginator->sortableHeader('Inventory Description', 'inventory_description');
					echo $paginator->sortableHeader('HCPC', 'healthcare_procedure_code');
					echo '<th>R/P</th>';
					echo $paginator->sortableHeader('Amount', 'amount');
					echo '<th>Chg</th>';
					echo '<th>Pmt</th>';
					echo '<th>Crd</th>';
					echo '<th>BU COGS</th>';
					echo $paginator->sortableHeader('COGS', 'cost_of_goods_sold');
					echo '<th>COGS Detail</th>';
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				$previousAccount = '';
				$previousInvoice = '';
				
				foreach ($transactions as $transaction)
				{
					$isSubtracted = $transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['is_amount_subtracted'];
					$amount = $isSubtracted ? number_format(h($transaction['Transaction']['amount']) * -1, 2) : number_format(h($transaction['Transaction']['amount']), 2);
					
					$cogs = '';
					$total = '';
					$buTotal = '';
					$detail = '';
					
					if ($transaction['Transaction']['account_number'] != $previousAccount || $transaction['Transaction']['invoice_number'] != $previousInvoice)
					{
						$cogs = unserialize($transaction['Transaction']['cost_of_goods_sold']);
							
						if (is_array($cogs))
						{
							$total = isset($cogs['total']) ? round($cogs['total'], 2) : '';
							$buTotal = isset($cogs['buTotal']) ? round($cogs['buTotal'], 2) : '';
							unset($cogs['total']);
							unset($cogs['buTotal']);
							
							foreach ($cogs as $cog)
							{
								$detail .= $cog['CostOfGoodsSold']['manufacturer_code'] . ': ' . $cog['CostOfGoodsSold']['manufacturer_invoice_amount'] . '<br/>';
							}
						} 
					}
					
					echo $html->tableCells(
						array(
							h($transaction['Transaction']['profit_center_number']),
							$html->div('TransactionRelatedAccountNumberTip TooltipContainer', h($transaction['Transaction']['account_number']), array(), true),
							h($transaction['Transaction']['invoice_number']),
							h($transaction['Transaction']['transaction_control_number']),
							formatDate($transaction['Transaction']['transaction_date_of_service']),
							$html->div('TransactionRelatedCarrier1Tip TooltipContainer', h($transaction['Transaction']['carrier_number']), array(), true),
							h($transactionTypes[$transaction['Transaction']['transaction_type']]['TransactionType']['description']),
							h($transaction['Transaction']['general_ledger_description']),
							h($transaction['Transaction']['inventory_number']),
							h($transaction['Transaction']['inventory_description']),
							$html->div('TransactionRelatedHCPCTip TooltipContainer', $transaction['Transaction']['healthcare_procedure_code'], array(), true),
							h($transaction['Transaction']['rental_or_purchase']),
							array($amount, array('class' => 'Right')),
							array(($transaction['Transaction']['transaction_type'] == $chargeType ? $amount : ''), array('class' => 'Right')),
							array(($transaction['Transaction']['transaction_type'] == $paymentType ? $amount : ''), array('class' => 'Right')),
							array(($transaction['Transaction']['transaction_type'] == $creditType ? $amount : ''), array('class' => 'Right')),
							array($buTotal, array('class' => 'Right')),
							array($total, array('class' => 'Right')),
							$detail
						),
						array(),
						array('class' => 'Alt')
					);
					
					$previousAccount = $transaction['Transaction']['account_number'];
					$previousInvoice = $transaction['Transaction']['invoice_number'];
				}
			?>
		</tbody>
	</table>
	
	<?= $this->element('page_links') ?>
	<br /><br />
	
	<script type="text/javascript">
		Modules.Transactions.Related.addTooltips();
	</script>
	
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Transactions.Related.addHandlers();
	</script>
<?php endif; ?>
