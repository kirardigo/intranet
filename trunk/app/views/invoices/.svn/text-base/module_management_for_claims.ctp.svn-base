<?php 
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'InvoicesManagementForClaimsForm',
				'url' => '/modules/invoices/management_for_claims',
				'update' => 'InvoicesManagementForClaimsContainer',
				'before' => 'Modules.Invoices.ManagementForClaims.showLoadingDialog();',
				'complete' => 'Modules.Invoices.ManagementForClaims.closeLoadingDialog();'
			)
		);
		echo '<div style="padding: 2px;">';
			echo $form->input('Invoice.date_of_service_start', array(
				'id' => 'InvoicesManagementForClaimsDateOfServiceStart',
				'label' => 'Starting DOS',
				'class' => 'Text75',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.date_of_service_end', array(
				'id' => 'InvoicesManagementForClaimsDateOfServiceEnd',
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
				'label' => 'Bal Due',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.line_1_date', array(
				'type' => 'text',
				'id' => 'InvoiceManagementForClaimsLine1Date',
				'class' => 'Text75',
				'label' => 'L1 Date'
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
		
		echo $form->submit('Search', array('id' => 'InvoicesManagementForClaimsSubmitButton', 'div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo '<input type="reset" value="Clear" />';
		echo '</div>';
		
		echo $form->end();
	}
?>

<br style="clear: left;" />

<?php if (!$isPostback): ?>
	<div id="InvoicesManagementForClaimsContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/invoices', 
				'action' => 'management_for_claims'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export', array('controller' => 'ajax/invoices', 'action' => 'exportManagementResultsForClaims'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'InvoicesManagementForClaimsContainer';
	?>
	
	<br/><br/>
	
	<table id="InvoicesManagementForClaimsTable" class="Styled" style="width: 1200px">
		<thead>
			<tr>
				<?php
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo $paginator->sortableHeader('TCN', 'transaction_control_number');
					echo $paginator->sortableHeader('L1 Date', 'line_1_date');
					echo $paginator->sortableHeader('DOS', 'date_of_service');
					echo $paginator->sortableHeader('Bal Due', 'account_balance', array('class' => 'Right'));
					echo $paginator->sortableHeader('L1', 'line_1_status');
					echo $paginator->sortableHeader('L1 Amt', 'line_1_amount', array('class' => 'Right'));
					echo $paginator->sortableHeader('CLFUP', 'efn_followup_date');
					echo '<th>Remarks</th>';
					echo $paginator->sortableHeader('L1 INI', 'line_1_initials');
					echo $paginator->sortableHeader('Team', 'team');
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($invoices as $invoice)
				{
					echo $html->tableCells(
						array(
							$html->link($invoice['Invoice']['account_number'], "/customers/inquiry/accountNumber:{$invoice['Invoice']['account_number']}", array('target' => '_blank', 'class' => 'Account')),
							$html->link($invoice['Invoice']['invoice_number'], '#', array('class' => 'Invoice')),
							h($invoice['Invoice']['transaction_control_number']),
							formatDate($invoice['Invoice']['line_1_date']),
							formatDate($invoice['Invoice']['date_of_service']),
							array($invoice['Invoice']['account_balance'] != null ? number_format($invoice['Invoice']['account_balance'], 2) : '', array('class' => 'Right')),
							h($invoice['Invoice']['line_1_status']),
							array(h($invoice['Invoice']['line_1_amount']), array('class' => 'Right')),
							$html->link(formatDate($invoice['Invoice']['efn_followup_date']), '#', array('class' => 'EFN')),
							h($invoice['Invoice']['reimbursement_memo']),
							h($invoice['Invoice']['line_1_initials']),
							h($invoice['Invoice']['team'])
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
		Modules.Invoices.ManagementForClaims.addTableHandlers();
	</script>
	
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Invoices.ManagementForClaims.addHandlers();
	</script>
<?php endif; ?>
