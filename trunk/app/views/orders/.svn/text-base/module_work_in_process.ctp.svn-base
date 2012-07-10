<?php if (!$isUpdate): ?>
	<div id="OrdersWorkInProcessContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		$yesNo = array(
			1 => 'Yes',
			0 => 'No'
		);
		
		echo $ajax->form('',
			'post',
			array(
				'id' => 'OrderWorkInProcessForm',
				'url' => '/modules/orders/workInProcess/1',
				'update' => 'OrdersWorkInProcessContainer',
				'before' => 'Modules.Orders.WorkInProcess.showLoadingDialog();',
				'complete' => 'Modules.Orders.WorkInProcess.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Order.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.rehab_hospital', array(
			'label' => 'RRRH',
			'options' => $rehabOptions,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.is_complete', array(
			'label' => 'Complete?',
			'options' => $yesNo,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.is_scheduled', array(
			'label' => 'Scheduled?',
			'options' => $yesNo,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.work_completed_date_start', array(
			'label' => 'Completed Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.work_completed_date_end', array(
			'label' => 'Completed End',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.staff_user_id', array(
			'label' => 'Sls_conpl',
			'id' => 'WIPStaffUserId',
			'options' => $staffInitials,
			'empty' => 'Any'
		));
		
		echo $form->hidden('Order.is_export', array('value' => 0));
		
		echo '<div style="margin-top: 5px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal')));
		echo $form->button('Export to Excel', array('id' => 'ExportButton'));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
	<?php if ($showSummary): ?>
	<table class="Styled" style="margin: 10px 10px 0px 0px;">
		<tr>
			<th>&nbsp;</th>
			<th class="Right">WIP</th>
			<th class="Right">Scheduled</th>
			<th class="Right">Completed</th>
			<th class="Right">Credits</th>
			<th class="Right">Sch Adj</th>
			<th class="Right">Budget</th>
			<th class="Right">Revenue MTD</th>
		</tr>
		<tr>
			<td>Current Month</td>
			<td class="Right"><?= number_format($totals['wipTotal'], 0); ?></td>
			<td class="Right"><?= number_format($totals['currentScheduled'], 0); ?></td>
			<td class="Right"><?= number_format($totals['currentCompleted'], 0); ?></td>
			<td class="Right"><?= number_format($totals['creditTotal'], 0); ?></td>
			<td class="Right"><?= number_format($totals['currentScheduled'] + $totals['creditTotal'], 0); ?></td>
			<td class="Right"><?= number_format($totals['budgetTotal'], 0); ?></td>
			<td class="Right"><?= number_format($totals['revenueTotal'], 0); ?></td>
		</tr>
		<tr class="Alt">
			<td>Next Month</td>
			<td class="Right">&nbsp;</td>
			<td class="Right"><?= number_format($totals['nextScheduled'], 0); ?></td>
			<td class="Right" colspan="5">&nbsp;</td>
		</tr>
	</table>
	<?php endif; ?>
</div>

<br class="ClearBoth" />

<table id="OrdersWorkInProcessTable" class="Styled" style="width: 1400px;">
	<thead>
		<tr>
			<th>Acct#</th>
			<th>TCN</th>
			<th>Client</th>
			<th>Prog</th>
			<th>LTCF</th>
			<th>Type</th>
			<th class="Text150">Description</th>
			<th>FIP</th>
			<th>WIP</th>
			<th>Approved</th>
			<th>Days</th>
			<th>Auth Exp</th>
			<th>Ordered</th>
			<th>Received</th>
			<th>OK</th>
			<th class="Right">WIP Amt</th>
			<th>Scheduled</th>
			<th>Completed</th>
			<th>Invoice</th>
			<th class="Right">Invoice Amt</th>
			<th class="Right">Variance</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$invoiceAmount = ($row['Order']['invoice_number'] != '') ? h(number_format($row['Invoice']['amount'], 2)) : '';
				$variance = ($row['Order']['invoice_number'] != '') ? number_format($row['Invoice']['amount'] - $row['Order']['wip_amount'], 2) : '';
				
				echo $html->tableCells(
					array(
						$html->link($row['Order']['account_number'], "/customers/inquiry/accountNumber:{$row['Order']['account_number']}", array('target' => '_blank')),
						h($row['Order']['transaction_control_number']),
						h(substr($row['Order']['client_name'], 0, 20)),
						$html->div('OrderWorkInProcessProgramTip TooltipContainer', $row['Order']['program_referral_number'], array(), true),
						$html->div('OrderWorkInProcessLTCFTip TooltipContainer', $row['Order']['long_term_care_facility_number'], array(), true),
						h($row['Order']['order_type']),
						h(substr($row['Order']['wip_description'], 0, 20)),
						$row['Order']['is_foam_in_place'] ? 'Y' : 'N',
						h($row['Order']['work_in_process']),
						h($row['Order']['funding_approved_date']),
						h(ifset($row['Order']['days_old'])),
						h($row['PriorAuthorization']['date_expiration']),
						h($row['Order']['equipment_ordered_date']),
						h($row['Order']['equipment_received_date']),
						$row['Order']['is_ok_to_schedule'] ? 'Y' : 'N',
						array(h(number_format($row['Order']['wip_amount'], 2)), array('class' => 'Right')),
						h($row['Order']['work_scheduled_date']),
						h($row['Order']['work_completed_date']),
						h($row['Order']['invoice_number']),
						array($invoiceAmount, array('class' => 'Right')),
						array($variance, array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Orders.WorkInProcess.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Orders.WorkInProcess.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
