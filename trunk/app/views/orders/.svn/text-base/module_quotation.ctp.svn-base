<?php if (!$isUpdate): ?>
	<div id="OrdersQuotationContainer" style="margin-top: 5px;">
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
				'id' => 'OrderQuotationForm',
				'url' => '/modules/orders/quotation/1',
				'update' => 'OrdersQuotationContainer',
				'before' => 'Modules.Orders.Quotation.showLoadingDialog();',
				'complete' => 'Modules.Orders.Quotation.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Order.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.start_date', array(
			'type' => 'text',
			'value' => $startRange,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.end_date', array(
			'type' => 'text',
			'value' => $endRange
		));
		echo $form->input('AaaReferral.facility_name', array(
			'label' => 'Facility',
			'options' => $facilityNames,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.contact_name', array(
			'label' => 'Contact',
			'options' => $contactNames,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.group_code', array(
			'label' => 'Group Code',
			'options' => $groupCodes,
			'empty' => 'Any'
		));
		echo $form->input('AaaReferral.rehab_salesman', array(
			'label' => 'AAA Sls',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.staff_user_id', array(
			'label' => 'Staff',
			'options' => $staffInitials,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.order_type', array(
			'label' => 'Type',
			'options' => $orderTypes,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Order.is_complete', array(
			'label' => 'Complete?',
			'options' => $yesNo,
			'empty' => 'Any',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.facility_type', array(
			'label' => 'Fac Type',
			'options' => $aaaTypes,
			'empty' => true
		));
		
		echo $form->hidden('Order.is_export', array('value' => 0));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'ExportButton', 'style' => 'margin: 0px;'));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<style type="text/css">
	#CompletedTotal {
		position: absolute;
		left: 850px;
		top: 125px;
		width: 125px;
		padding: 5px;
		border: 1px solid black;
		background-color: #CCFFCC;
		text-align: right;
	}
</style>

<div id="CompletedTotal">
	<span style="font-weight: bold">Completed Total</span><br/>
	<?= number_format($completedTotal, 2) ?>
</div>

<table id="OrdersQuotationTable" class="Styled" style="width: 1800px;">
	<thead>
		<tr>
			<th width="25">S</th>
			<th>Account #</th>
			<th>Invoice #</th>
			<th>TCN #</th>
			<th>Name</th>
			<th>Prog</th>
			<th>Prog Name</th>
			<th>AAA Sls</th>
			<th>Group Code</th>
			<th>Fac Type</th>
			<th>RRRH</th>
			<th>Staff</th>
			<th>Type</th>
			<th>MOB</th>
			<th>MFG</th>
			<th>WIP Description</th>
			<th class="Center">Quote</th>
			<th class="Right">Needs Quote</th>
			<th class="Right">Eval Date</th>
			<th class="Right">Quote CCS</th>
			<th class="Right number">RTS Days</th>
			<th class="Right">Quote Date</th>
			<th class="Right number">CCS Days</th>
			<th class="Right">Quote Print</th>
			<th class="Right currency">Amount</th>
			<th class="Right currency">Invoice Amount</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$invoiceAmount = ($row['Order']['invoice_number'] == '') ? '' : number_format($row['Invoice']['amount'], 2);
				
				if ($row['Order']['evaluation_date'] != '' && $row['Order']['quote_client_care_specialist_date'] == '')
				{
					$rts = array('<span style="color: red;">' . h(ifset($row['Order']['rts_days'])) . '</span>', array('class' => 'Right'));
				}
				else
				{
					$rts = array(h(ifset($row['Order']['rts_days'])), array('class' => 'Right'));
				}
				
				echo $html->tableCells(
					array(
						h($row['Order']['status']),
						$html->link($row['Order']['account_number'], "/customers/inquiry/accountNumber:{$row['Order']['account_number']}", array('target' => '_blank')),
						h($row['Order']['invoice_number']),
						h($row['Order']['transaction_control_number']),
						h(substr($row['Order']['client_name'], 0, 20)),
						h($row['Order']['program_referral_number']),
						h(ifset($row['Order']['program_referral_name'])),
						h(ifset($row['AaaReferral']['rehab_salesman'])),
						h(ifset($row['AaaReferral']['group_code'])),
						h(ifset($row['AaaReferral']['facility_type'])),
						h($row['Order']['rehab_hospital']),
						h($row['Order']['staff_user_id']),
						h($row['Order']['order_type']),
						h($row['Order']['mobility_choice']),
						h($row['Order']['manufacturer_model']),
						h(substr($row['Order']['wip_description'], 0, 20)),
						array(h($row['Order']['quote']), array('class' => 'Center')),
						array(h($row['Order']['needs_quote_date']), array('class' => 'Right')),
						array(h($row['Order']['evaluation_date']), array('class' => 'Right')),
						array(h($row['Order']['quote_client_care_specialist_date']), array('class' => 'Right')),
						$rts,
						array(h($row['Order']['quote_completed_date']), array('class' => 'Right')),
						array(h(ifset($row['Order']['ccs_days'])), array('class' => 'Right')),
						array(h($row['Order']['quote_printed_date']), array('class' => 'Right')),
						array(h(number_format($row['Order']['grand_total'], 2)), array('class' => 'Right')),
						array(h($invoiceAmount), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Orders.Quotation.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Orders.Quotation.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
