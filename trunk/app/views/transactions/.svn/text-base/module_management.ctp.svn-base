<?php 
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'TransactionsManagementForm',
				'url' => '/modules/transactions/management',
				'update' => 'TransactionsManagementContainer',
				'before' => 'Modules.Transactions.Management.showLoadingDialog();',
				'complete' => 'Modules.Transactions.Management.closeLoadingDialog();'
			)
		);
		
		echo '<div style="padding: 2px;">';
			echo $form->input('Transaction.period_posting_date_start', array(
				'id' => 'TransactionsManagementPeriodPostingDateStart',
				'label' => 'Starting PPD',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.period_posting_date_end', array(
				'id' => 'TransactionsManagementPeriodPostingDateEnd',
				'label' => 'Ending PPD',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.transaction_date_of_service_start', array(
				'id' => 'TransactionsManagementDateOfServiceStart',
				'label' => 'Starting DOS',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.transaction_date_of_service_end', array(
				'id' => 'TransactionsManagementDateOfServiceEnd',
				'label' => 'Ending DOS',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.profit_center_number', array(
				'id' => 'TransactionsManagementProfitCenterNumber',
				'options' => $profitCenters,
				'empty' => true,
				'label' => 'Profit Center',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.department_code', array(
				'id' => 'TransactionsManagementDepartmentCode',
				'options' => $departments,
				'empty' => true,
				'label' => 'Department',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.rental_or_purchase', array(
				'id' => 'TransactionsRentalOrPurchase',
				'options' => $rentalPurchase,
				'empty' => true,
				'label' => 'R/P'
			));
		echo '</div>';
		
		echo '<div style="padding: 2px; clear: left;">';
			echo $form->input('Transaction.account_number', array(
				'id' => 'TransactionsManagementAccountNumber',
				'label' => 'Acct #',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.invoice_number', array(
				'id' => 'TransactionsManagementInvoiceNumber',
				'label' => 'Invoice #*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.referral_number_from_aaa_file', array(
				'id' => 'TransactionsManagementReferralNumber',
				'label' => 'Referral*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.long_term_care_facility_number', array(
				'id' => 'TransactionsManagementLtcfNumber',
				'label' => 'LTCF*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.physician_number', array(
				'id' => 'TransactionsManagementPhysicianNumber',
				'label' => 'Phy Code*',
				'maxlength' => false
			));
		echo '</div>';
		
		echo '<div style="padding: 2px; clear: left;">';		
			echo $form->input('Transaction.transaction_type', array(
				'id' => 'TransactionsManagementTransactionType',
				'options' => $transactionTypes,
				'label' => 'Type*',
				'multiple' => 'multiple',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.inventory_number', array(
				'id' => 'TransactionsManagementInventoryNumber',
				'label' => 'Inventory #*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.healthcare_procedure_code', array(
				'id' => 'TransactionsManagementHealthCareProcedureCode',
				'label' => 'HCPC*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.general_ledger_code', array(
				'id' => 'TransactionsManagementGeneralLedgerCode',
				'label' => 'GL Code*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.carrier_number', array(
				'id' => 'TransactionsManagementCarrierNumber',
				'label' => 'Carr*',
				'maxlength' => false,
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Transaction.salesman_number', array(
				'id' => 'TransactionsManagementSalesmanNumber',
				'label' => 'Slsman'
			));
		echo '</div>';
				
		echo '* Multi-select (use commas to separate values)<br/><div style="margin: 5px 0px; clear: both">';
		echo $form->submit('Search', array('id' => 'TransactionsManagementSubmitButton', 'div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo '<input type="reset" value="Clear" />';
		echo '</div>';
		
		echo $form->end();
	}
?>

<br style="clear: left;" />

<?php if (!$isPostback): ?>
	<div id="TransactionsManagementContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/transactions', 
				'action' => 'management'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/transactions', 'action' => 'exportManagementResults'));
		echo '<br/>';
		echo $paginator->link('Export to Excel with Contact Info', array('controller' => 'ajax/transactions', 'action' => 'exportContactResults'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'TransactionsManagementContainer';
	?>
	
	<br/><br/>
	
	<table id="TransactionsManagementTable" class="Styled">
		<thead>
			<tr>
				<?php					
					echo $paginator->sortableHeader('PCtr', 'profit_center_number');
					echo $paginator->sortableHeader('D', 'department_code', array('class' => 'Center'));
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo $paginator->sortableHeader('Setup', 'setup_date');
					echo $paginator->sortableHeader('BZ', 'competitive_bid_zip_code_flag');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo $paginator->sortableHeader('TCN', 'transaction_control_number');
					echo $paginator->sortableHeader('PPD', 'period_posting_date');
					echo $paginator->sortableHeader('DOS', 'transaction_date_of_service');
					echo $paginator->sortableHeader('R/P', 'rental_or_purchase', array('class' => 'Center'));
					echo $paginator->sortableHeader('T', 'transaction_type');
					echo $paginator->sortableHeader('Qty', 'quantity', array('class' => 'Right'));
					echo $paginator->sortableHeader('Inven#', 'inventory_number');
					echo $paginator->sortableHeader('HCPC', 'healthcare_procedure_code');
					echo $paginator->sortableHeader('BH', 'competitive_bid_hcpc_flag');
					echo $paginator->sortableHeader('Inv_Grp', 'inventory_group_code');
					echo $paginator->sortableHeader('GL_Code', 'general_ledger_code');
					echo $paginator->sortableHeader('Inven_Desc', 'inventory_description');
					echo $paginator->sortableHeader('Serial#', 'serial_number');
					echo $paginator->sortableHeader('Carr1', 'carrier_1_number');
					echo $paginator->sortableHeader('Carr1_$', 'carrier_1_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Carr2', 'carrier_2_number');
					echo $paginator->sortableHeader('Carr2_$', 'carrier_2_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Carr3', 'carrier_3_number');
					echo $paginator->sortableHeader('Carr3_$', 'carrier_3_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Total', 'total_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Slsman', 'salesman_number');
					echo $paginator->sortableHeader('AAA', 'referral_number_from_aaa_file', array('class' => 'Right'));
					echo $paginator->sortableHeader('LTCF', 'long_term_care_facility_number', array('class' => 'Right'));
					echo $paginator->sortableHeader('Type', 'long_term_care_facility_type');
					echo $paginator->sortableHeader('Phy_Code', 'physician_number', array('class' => 'Right'));
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($transactions as $transaction)
				{
					echo $html->tableCells(
						array(
							h($transaction['Transaction']['profit_center_number']),
							array(h($transaction['Transaction']['department_code']), array('class' => 'Center')),
							$html->div('TransactionManagementAccountNumberTip TooltipContainer', $transaction['Transaction']['account_number'], array(), true),
							$transaction['Transaction']['setup_date'] != null ? date('m/Y', strtotime($transaction['Transaction']['setup_date'])) : '',
							h($transaction['Transaction']['competitive_bid_zip_code_flag']),
							h($transaction['Transaction']['invoice_number']),
							h($transaction['Transaction']['transaction_control_number']),
							formatDate($transaction['Transaction']['period_posting_date']),
							formatDate($transaction['Transaction']['transaction_date_of_service']),
							array(h($transaction['Transaction']['rental_or_purchase']), array('class' => 'Center')),
							h($transactionTypes[$transaction['Transaction']['transaction_type']]),
							array($transaction['Transaction']['quantity'] != null ? number_format($transaction['Transaction']['quantity'], 0) : '', array('class' => 'Right')),
							$html->div('TransactionManagementInventoryTip TooltipContainer', $transaction['Transaction']['inventory_number'], array(), true),
							$html->div('TransactionManagementHCPCTip TooltipContainer', $transaction['Transaction']['healthcare_procedure_code'], array(), true),
							h($transaction['Transaction']['competitive_bid_hcpc_flag']),
							h($transaction['Transaction']['inventory_group_code']),
							$html->div('TransactionManagementGeneralLedgerTip TooltipContainer', $transaction['Transaction']['general_ledger_code'], array(), true),
							h($transaction['Transaction']['inventory_description']),
							h($transaction['Transaction']['serial_number']),
							$html->div('TransactionManagementCarrier1Tip TooltipContainer', $transaction['Transaction']['carrier_1_number'], array(), true),
							array(number_format($transaction['Transaction']['carrier_1_amount'], 2), array('class' => 'Right')),
							$html->div('TransactionManagementCarrier2Tip TooltipContainer', $transaction['Transaction']['carrier_2_number'], array(), true),
							array($transaction['Transaction']['carrier_2_amount'] != null ? number_format($transaction['Transaction']['carrier_2_amount'], 2) : '', array('class' => 'Right')),
							$html->div('TransactionManagementCarrier3Tip TooltipContainer', $transaction['Transaction']['carrier_3_number'], array(), true),
							array($transaction['Transaction']['carrier_3_amount'] != null ? number_format($transaction['Transaction']['carrier_3_amount'], 2) : '', array('class' => 'Right')),
							array(number_format($transaction['Transaction']['total_amount'], 2), array('class' => 'Right')),
							h($transaction['Transaction']['salesman_number']),
							array($html->div('TransactionManagementAAATip TooltipContainer', $transaction['Transaction']['referral_number_from_aaa_file'], array(), true), array('class' => 'Right')),
							array($html->div('TransactionManagementLTCFTip TooltipContainer', $transaction['Transaction']['long_term_care_facility_number'], array(), true), array('class' => 'Right')),
							h($transaction['Transaction']['long_term_care_facility_type']),
							array($html->div('TransactionManagementPhysicianTip TooltipContainer', $transaction['Transaction']['physician_number'], array(), true), array('class' => 'Right'))
						),
						array(),
						array('class' => 'Alt')
					);
				}
			?>
		</tbody>
	</table>
	
	<?= $this->element('page_links') ?>
	<br /><br />
	
	<script type="text/javascript">
		Modules.Transactions.Management.addTooltips();
	</script>
	
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Transactions.Management.addHandlers();
	</script>
<?php endif; ?>
