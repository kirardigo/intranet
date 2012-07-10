<?php if (!$isUpdate): ?>
	<div id="DistributorOrdersSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'DistributorOrdersSummaryForm',
				'url' => '/modules/distributorOrders/summary/1',
				'update' => 'DistributorOrdersSummaryContainer',
				'before' => 'Modules.DistributorOrders.Summary.showLoadingDialog();',
				'complete' => 'Modules.DistributorOrders.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('DistributorOrder.order_number', array(
			'label' => 'Order#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.account_number', array(
			'label' => 'Acct#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.invoice_number', array(
			'label' => 'Invoice#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.bill_to_aaa_number', array(
			'label' => 'Bill',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_to_aaa_number', array(
			'label' => 'Ship',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.order_date_start', array(
			'label' => 'Order Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.order_date_end', array(
			'label' => 'Order End',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.print_date_start', array(
			'label' => 'Print Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.print_date_end', array(
			'label' => 'Print End',
			'type' => 'text',
			'class' => 'Text75'
		));
		
		echo $form->input('DistributorOrder.has_return_authorization_number', array(
			'label' => 'R/A',
			'options' => array('N', 'Y'),
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_to_code', array(
			'label' => 'Code',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_to_zip_code', array(
			'label' => 'Zip',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.order_status', array(
			'label' => 'Order Status',
			'options' => $orderStatuses,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_salesman', array(
			'label' => 'Sls',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_region', array(
			'label' => 'Region',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.ship_market', array(
			'label' => 'Market',
			'class' => 'Text50'
		));
		
		echo $form->hidden('DistributorOrder.is_export', array('value' => 0, 'id' => 'DistributorOrdersSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'DistributorOrdersSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<table id="DistributorOrdersSummaryTable" class="Styled" style="width: 1200px;">
	<thead>
		<tr>
			<th>Order#</th>
			<th>Acct#</th>
			<th>Invoice#</th>
			<th>Sls</th>
			<th>R</th>
			<th>M</th>
			<th>Date</th>
			<th>DOS</th>
			<th class="Right">Days</th>
			<th>R/A</th>
			<th>Bill</th>
			<th>Ship</th>
			<th>Code</th>
			<th>Name</th>
			<th>Zip</th>
			<th>PO#</th>
			<th class="Right">%</th>
			<th class="Right">Total</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				echo $html->tableCells(array(
					array(
						h($row['DistributorOrder']['order_number']),
						$html->link($row['DistributorOrder']['account_number'], "/customers/inquiry/accountNumber:{$row['DistributorOrder']['account_number']}", array('target' => '_blank')),
						h($row['DistributorOrder']['invoice_number']),
						h($row['DistributorOrder']['ship_salesman']),
						h($row['DistributorOrder']['ship_region']),
						h($row['DistributorOrder']['ship_market']),
						formatDate($row['DistributorOrder']['order_date']),
						formatDate($row['DistributorOrder']['date_of_service']),
						array(ifset($row['DistributorOrder']['days']), array('class' => 'Right')),
						$row['DistributorOrder']['has_return_authorization_number'] ? 'Y' : 'N',
						h($row['DistributorOrder']['bill_to_aaa_number']),
						h($row['DistributorOrder']['ship_to_aaa_number']),
						h($row['DistributorOrder']['ship_to_code']),
						h($row['DistributorOrder']['ship_to_name']),
						h($row['DistributorOrder']['ship_to_zip_code']),
						h($row['DistributorOrder']['purchase_order_number']),
						array(h($row['DistributorOrder']['discount']), array('class' => 'Right')),
						array(h($row['DistributorOrder']['invoice_total']), array('class' => 'Right'))
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.DistributorOrders.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.DistributorOrders.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
