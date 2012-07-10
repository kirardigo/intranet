<?php if (!$isUpdate): ?>
	<div id="DistributorOrderLinesSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'DistributorOrderLinesSummaryForm',
				'url' => '/modules/DistributorOrderLines/summary/1',
				'update' => 'DistributorOrderLinesSummaryContainer',
				'before' => 'Modules.DistributorOrderLines.Summary.showLoadingDialog();',
				'complete' => 'Modules.DistributorOrderLines.Summary.closeLoadingDialog();'
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
			'id' => 'DistributorOrderLineOrderDateStart',
			'label' => 'Order Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.order_date_end', array(
			'id' => 'DistributorOrderLineOrderDateEnd',
			'label' => 'Order End',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.print_date_start', array(
			'id' => 'DistributorOrderLinePrintDateStart',
			'label' => 'Print Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrder.print_date_end', array(
			'id' => 'DistributorOrderLinePrintDateEnd',
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
		
		echo $form->input('DistributorOrderLine.inventory_number', array(
			'label' => 'AT#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('DistributorOrderLine.general_ledger_code', array(
			'label' => 'Grp',
			'class' => 'Text75',
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
		
		echo $form->hidden('DistributorOrderLine.is_export', array('value' => 0, 'id' => 'DistributorOrderLinesSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'DistributorOrderLinesSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<table id="DistributorOrderLinesSummaryTable" class="Styled" style="width: 1600px;">
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
			<th class="Right">%</th>
			<th>Line</th>
			<th class="Right">Qty</th>
			<th>AT#</th>
			<th>Description</th>
			<th class="Right">Gross</th>
			<th class="Right">Net</th>
			<th class="Right">Ext</th>
			<th>Grp</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$ext = $row['DistributorOrderLine']['net'] * $row['DistributorOrderLine']['quantity'];
				
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
						array($row['DistributorOrder']['discount'], array('class' => 'Right')),
						h($row['DistributorOrderLine']['line_number']),
						array(h($row['DistributorOrderLine']['quantity']), array('class' => 'Right')),
						h($row['DistributorOrderLine']['inventory_number']),
						h($row['DistributorOrderLine']['description']),
						array(h($row['DistributorOrderLine']['cost']), array('class' => 'Right')),
						array(number_format($row['DistributorOrderLine']['net'], 2), array('class' => 'Right')),
						array(number_format($ext, 2), array('class' => 'Right')),
						h($row['DistributorOrderLine']['general_ledger_code'])
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.DistributorOrderLines.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.DistributorOrderLines.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
