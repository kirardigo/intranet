
<div style="width: 95%; margin: 0 auto;">
	<h2>Please select 1 or more invoices.</h2>
	
	<table id="InvoiceSelectionModuleInvoicesTable" class="Styled">
		<thead>
			<tr>
				<th><input type="checkbox" value="" id="InvoiceSelectionModuleSelectAllCheckbox" /></th>
				<th>Invoice</th>
				<th>Date of Service</th>
				<?= $carrierNumber != null ? '<th class="Right">Amount</th>' : '' ?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($invoices as $i => $invoice)
				{
					$balance = '';
					
					if ($carrierNumber != null)
					{
						foreach (array('1', '2', '3') as $i)
						{
							if ($invoice['Invoice']["carrier_{$i}_code"] == $carrierNumber)
							{
								$balance = number_format($invoice['Invoice']["carrier_{$i}_balance"], 2);
								break;
							}
						}
					}
					
					echo $html->tableCells(
						array_merge(
							array(
								'<input type="checkbox" value="' . h($invoice['Invoice']['invoice_number']) . '" />',
								h($invoice['Invoice']['invoice_number']),
								formatDate($invoice['Invoice']['date_of_service'])
							),
							$carrierNumber != null ? array(array($balance, array('class' => 'Right'))) : array()
						),
						array(),
						array('class' => 'Alt')
					);
				}
			?>
		</tbody>
	</table>
			
	<br /><br />

	<?= $form->button('Copy', array('id' => 'InvoiceSelectionModuleCopyButton')) ?>
</div>

<script type="text/javascript">
	Modules.Invoices.Selection.initialize();
</script>