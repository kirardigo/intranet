<?php if (!$isUpdate): ?>
	<div id="OrdersFundingContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'OrderFundingForm',
				'url' => '/modules/orders/funding/1',
				'update' => 'OrdersFundingContainer',
				'before' => 'Modules.Orders.Funding.showLoadingDialog();',
				'complete' => 'Modules.Orders.Funding.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Order.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.funding_pending_date', array(
			'label' => 'Fund Req',
			'options' => array('Any', 'Blank', 'Not Blank'),
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.funding_approved_date', array(
			'label' => 'Fund Aprv',
			'options' => array('Any', 'Blank', 'Not Blank'),
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.rehab_hospital', array(
			'label' => 'RRRH',
			'options' => $rehabOptions,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.order_type', array(
			'label' => 'Type',
			'options' => $orderTypes,
			'empty' => 'Any'
		));
		
		echo $form->input('Order.staff_user_id', array(
			'label' => 'Staff',
			'options' => $staffInitials,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.carrier_1_code', array(
			'label' => 'Carr 1',
			'options' => $carrier1Codes,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.carrier_2_code', array(
			'label' => 'Carr 2',
			'options' => $carrier2Codes,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.carrier_3_code', array(
			'label' => 'Carr 3',
			'options' => $carrier3Codes,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.claims_status', array(
			'label' => 'Claims',
			'options' => $claimsStatuses,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.authorization_status', array(
			'label' => 'Auth',
			'options' => $authorizationStatuses,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Order.work_in_process', array(
			'label' => 'WIP',
			'options' => $wipCodes
		));
		
		echo $form->hidden('Order.is_export', array('value' => 0, 'id' => 'OrderFundingIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'OrderFundingExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<table id="OrdersFundingTable" class="Styled" style="width: 1800px;">
	<thead>
		<tr>
			<th>PCtr</th>
			<th>Acct#</th>
			<th>TCN#</th>
			<th>RRRH</th>
			<th>Staff</th>
			<th>Name</th>
			<th>Auth</th>
			<th>Description</th>
			<th>WIP</th>
			<th>Type</th>
			<th class="Right">Total</th>
			<th>EFN FUP</th>
			<th>C1</th>
			<th>Code</th>
			<th>Group</th>
			<th>C2</th>
			<th>Code</th>
			<th>C3</th>
			<th>Code</th>
			<th>Quoted</th>
			<th>Fund_Req</th>
			<th>Fund_Aprv</th>
			<th class="Right">Days</th>
			<th>Denied</th>
			<th>Appealed</th>
			<th>Invoiced</th>
			<th>Claims</th>
			<th>VOB</th>
			<th>Ini</th>
			<th>Date</th>
			<th>Auth</th>
			<th>Ini</th>
			<th>Date</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$deniedColumn = ($row['Order']['funding_approved_date'] != '') ? array('', array('style' => 'background-color: #ccc')) : h($row['Order']['denied_date']);
				$appealedColumn = ($row['Order']['funding_approved_date'] != '') ? array('', array('style' => 'background-color: #ccc')) : h($row['Order']['appealed_date']);
				
				echo $html->tableCells(
					array(
						h($row['Order']['profit_center_number']),
						$html->link($row['Order']['account_number'], "/customers/inquiry/accountNumber:{$row['Order']['account_number']}", array('target' => '_blank')),
						array(h($row['Order']['transaction_control_number']), array('class' => 'TCN')),
						h($row['Order']['rehab_hospital']),
						h($row['Order']['staff_user_id']),
						h(substr($row['Order']['client_name'], 0, 20)),
						$html->link($row['Order']['mrs_auth_number'], '#', array('class' => 'Auth', 'title' => 'View Prior Auths')),
						h($row['Order']['wip_description']),
						h($row['Order']['work_in_process']),
						h($row['Order']['order_type']),
						array(h($row['Order']['grand_total']), array('class' => 'Right')),
						$html->link(formatDate($row['Order']['oldest_efn_followup_date']), '#', array('class' => 'EFN', 'title' => 'View EFNs')),
						h($row['Order']['carrier_1_type']),
						h($row['Order']['carrier_1_code']),
						h($row['Order']['carrier_1_group_code']),
						h($row['Order']['carrier_2_type']),
						h($row['Order']['carrier_2_code']),
						h($row['Order']['carrier_3_type']),
						h($row['Order']['carrier_3_code']),
						h($row['Order']['quote_completed_date']),
						h($row['Order']['funding_pending_date']),
						h($row['Order']['funding_approved_date']),
						h(ifset($row['Order']['funding_days'])),
						$deniedColumn,
						$appealedColumn,
						h($row['Order']['invoiced_date']),
						h($row['Order']['claims_status']),
						h($row['Order']['verification_of_benefits_status']),
						h($row['Order']['verification_of_benefits_initials']),
						h($row['Order']['verification_of_benefits_date']),
						h($row['Order']['authorization_status']),
						h($row['Order']['authorization_initials']),
						h($row['Order']['authorization_date'])
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Orders.Funding.initializeTable();
	Modules.Orders.Funding.addTableHandlers();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Orders.Funding.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
