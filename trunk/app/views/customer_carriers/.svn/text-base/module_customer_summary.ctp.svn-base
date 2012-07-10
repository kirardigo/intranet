<?= $form->input('Customer.account_balance', array('class' => 'Text100 Right ReadOnly')); ?>

<br/>
<table id="CustomerCarrierCustomerSummary" class="Styled">
	<tr>
		<th class="Right">Balance</th>
		<th>Carr</th>
		<th>Name</th>
		<th>Type</th>
		<th>Status</th>
		<th>Claim #</th>
		<th>Last Trx Date</th>
		<th>&nbsp;</th>
	</tr>
	<?php
		foreach (ifset($results['CustomerCarrier'], array()) as $row)
		{
			echo $html->tableCells(
				array(array(
					array(h(number_format(ifset($row['Transaction']['carrier_balance_due'], 0), 2)), array('class' => 'Right')),
					h($row['carrier_number']),
					h($row['carrier_name']),
					h($row['carrier_type']),
					($row['is_active'] == 1) ? 'Y' : 'N',
					h($row['claim_number']),
					h(ifset($row['Transaction']['transaction_date_of_service'])),
					$html->link($html->image('iconDocument.png'), '#', array(
						'escape' => false,
						'title' => 'Show carrier invoices for: ' . h($row['carrier_number'])
					)) . $form->hidden('carrier_number', array('value' => $row['carrier_number']))
				)),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<script type="text/javascript">
	Modules.CustomerCarriers.CustomerSummary.addHandlers();
</script>