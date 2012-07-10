<?php 
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'InvoicesManagementForm',
				'url' => '/modules/invoices/management',
				'update' => 'InvoicesManagementContainer',
				'before' => 'Modules.Invoices.Management.showLoadingDialog();',
				'complete' => 'Modules.Invoices.Management.closeLoadingDialog();'
			)
		);
		
		echo '<div style="padding: 2px;">';
			echo $form->input('Invoice.date_of_service_start', array(
				'id' => 'InvoicesManagementDateOfServiceStart',
				'label' => 'Starting DOS',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.date_of_service_end', array(
				'id' => 'InvoicesManagementDateOfServiceEnd',
				'label' => 'Ending DOS',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			
			echo $form->input('Invoice.profit_center_number', array(
				'options' => $profitCenters,
				'empty' => 'All',
				'label' => 'PCtr',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.department_code', array(
				'options' => $departments,
				'empty' => 'All',
				'label' => 'Dept',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.account_balance', array(
				'options' => array('Non-Zero Balance', 'Credit Balance', 'Balance Due', 'All Balances'),
				'label' => 'Bal Due'
			));
		echo '</div>';
		
		echo '<div style="padding: 2px; clear: left;">';
			echo $form->input('Invoice.account_number', array(
				'label' => 'Acct #',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.invoice_number', array(
				'label' => 'Invoice #',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.transaction_control_number', array(
				'label' => 'TCN #',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.carrier_code', array(
				'label' => 'Carr #',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.team', array(
				'options' => $teamOptions,
				'empty' => 'All',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.line_1_status', array(
				'label' => 'L1',
				'options' => $line1Statuses,
				'empty' => 'All',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.line_1_initials', array(
				'label' => 'L1 INI',
				'class' => 'Text50',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.line_1_carrier_code', array(
				'label' => 'L1 Carr',
				'class' => 'Text50'
			));
		echo '</div>';
		
		echo $form->submit('Search', array('id' => 'InvoicesManagementSubmitButton', 'div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo '<input type="reset" value="Clear" />';
		echo '</div>';
		
		echo $form->end();
	}
?>

<br style="clear: left;" />

<?php if (!$isPostback): ?>
	<div id="InvoicesManagementContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/invoices', 
				'action' => 'management'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/invoices', 'action' => 'exportManagementResults'), array('style' => 'margin-right: 20px;'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'InvoicesManagementContainer';
	?>
	
	<br/><br/>
	
	<table id="InvoicesManagementTable" class="Styled" style="width: 1800px">
		<thead>
			<tr>
				<?php
					echo $paginator->sortableHeader('PCtr', 'profit_center_number');
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo $paginator->sortableHeader('D', 'department_code', array('class' => 'Center'));
					echo $paginator->sortableHeader('TCN', 'transaction_control_number');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo '<th>Auth</th>';
					echo $paginator->sortableHeader('R/P', 'rental_or_purchase', array('class' => 'Center'));
					echo $paginator->sortableHeader('DOS', 'date_of_service');
					echo $paginator->sortableHeader('BDT', 'billing_date');
					echo $paginator->sortableHeader('L1', 'line_1_status');
					echo $paginator->sortableHeader('L1 INI', 'line_1_initials');
					echo $paginator->sortableHeader('L1 Date', 'line_1_date');
					echo '<th>Days</th>';
					echo $paginator->sortableHeader('L1 Carr', 'line_1_carrier_code');
					echo $paginator->sortableHeader('L1 Amt', 'line_1_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Team', 'team');
					echo $paginator->sortableHeader('CLFUP', 'efn_followup_date');
					echo $paginator->sortableHeader('Carr1', 'carrier_1_number');
					echo $paginator->sortableHeader('Carr1_$', 'carrier_1_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Carr2', 'carrier_2_number');
					echo $paginator->sortableHeader('Carr2_$', 'carrier_2_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Carr3', 'carrier_3_number');
					echo $paginator->sortableHeader('Carr3_$', 'carrier_3_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Gross Chg', 'amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('Payments', 'payments', array('class' => 'Right'));
					echo $paginator->sortableHeader('Credits', 'credits', array('class' => 'Right'));
					echo $paginator->sortableHeader('Bal Due', 'account_balance', array('class' => 'Right'));
					echo '<th>Reimb Remarks</th>';
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($invoices as $invoice)
				{
					echo $html->tableCells(
						array(
							h($invoice['Invoice']['profit_center_number']),
							$html->link($invoice['Invoice']['account_number'], "/customers/inquiry/accountNumber:{$invoice['Invoice']['account_number']}", array('target' => '_blank', 'class' => 'Account')),
							array(h($invoice['Invoice']['department_code']), array('class' => 'Center')),
							h($invoice['Invoice']['transaction_control_number']),
							$html->link($invoice['Invoice']['invoice_number'], '#', array('class' => 'Invoice')),
							$html->link('Auth', '#', array('class' => 'Auth')),
							array(h($invoice['Invoice']['rental_or_purchase']), array('class' => 'Center')),
							formatDate($invoice['Invoice']['date_of_service']),
							formatDate($invoice['Invoice']['billing_date']),
							h($invoice['Invoice']['line_1_status']),
							h($invoice['Invoice']['line_1_initials']),
							formatDate($invoice['Invoice']['line_1_date']),
							weekdayDiff($invoice['Invoice']['line_1_date'], date('Y-m-d')),
							h($invoice['Invoice']['line_1_carrier_code']),
							array(h($invoice['Invoice']['line_1_amount']), array('class' => 'Right')),
							h($invoice['Invoice']['team']),
							$html->link(formatDate($invoice['Invoice']['efn_followup_date']), '#', array('class' => 'EFN')),
							h($invoice['Invoice']['carrier_1_code']),
							array(number_format($invoice['Invoice']['carrier_1_balance'], 2), array('class' => 'Right')),
							h($invoice['Invoice']['carrier_2_code']),
							array($invoice['Invoice']['carrier_2_balance'] != null ? number_format($invoice['Invoice']['carrier_2_balance'], 2) : '', array('class' => 'Right')),
							h($invoice['Invoice']['carrier_3_code']),
							array($invoice['Invoice']['carrier_3_balance'] != null ? number_format($invoice['Invoice']['carrier_3_balance'], 2) : '', array('class' => 'Right')),
							array($invoice['Invoice']['amount'] != null ? number_format($invoice['Invoice']['amount'], 2) : '', array('class' => 'Right')),
							array($invoice['Invoice']['payments'] != null ? number_format($invoice['Invoice']['payments'], 2) : '', array('class' => 'Right')),
							array($invoice['Invoice']['credits'] != null ? number_format($invoice['Invoice']['credits'], 2) : '', array('class' => 'Right')),
							array($invoice['Invoice']['account_balance'] != null ? number_format($invoice['Invoice']['account_balance'], 2) : '', array('class' => 'Right')),
							h($invoice['Invoice']['reimbursement_memo'])
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
		Modules.Invoices.Management.addTableHandlers();
	</script>
	
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Invoices.Management.addHandlers();
	</script>
<?php endif; ?>
