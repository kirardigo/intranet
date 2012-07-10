<script type="text/javascript">
	function deleteRecord(event)
	{
		var row = event.element().up("tr");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/transactions/utilityDelete/" + $F("TransactionAccountNumber") + "/" + $F(row.down("input"));
		}
		
		row.removeClassName("Highlight");
		
		event.stop();
	}
	
	document.observe("dom:loaded", function() {
		$("TransactionAccountNumber").focus();
		
		$$(".DeleteLink").invoke("observe", "click", deleteRecord);
	});
</script>

<?php
	echo $form->create('', array('url' => '/transactions/utilityList'));
	echo $form->input('Transaction.account_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->input('Transaction.invoice_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->submit('Search', array('style' => 'margin-top: 7px'));
	echo $form->end();
?>

<br class="ClearBoth" />
<?php if (isset($records)): ?>
Transactions for <?= $customer['Customer']['name'] ?>:
<table class="Styled" style="width: 600px;">
	<tr>
		<th class="Text2">&nbsp;</th>
		<th>DOS</th>
		<th>Invoice #</th>
		<th>G/L</th>
		<th>Type</th>
		<th class="Right">Amount</th>
		<th>Carrier</th>
		<th class="Text25">&nbsp;</th>
	</tr>
<?php
	if ($records == false)
	{
		echo '<tr><td colspan="5" class="Center">There are no transactions for this customer.</td></tr>';
	}
	else
	{
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					$form->hidden('Transaction.id', array('value' => $row['Transaction']['id'])) .
					$html->link($html->image('iconDelete.png', array('title' => 'Delete')), '#', array('escape' => false, 'class' => 'DeleteLink')),
					formatDate($row['Transaction']['transaction_date_of_service']),
					h($row['Transaction']['invoice_number']),
					h($row['Transaction']['general_ledger_code']),
					$transactionTypes[$row['Transaction']['transaction_type']],
					array(number_format($row['Transaction']['amount'], 2), array('class' => 'Right')),
					h($row['Transaction']['carrier_number']),
					$html->link($html->image('iconEdit.png', array('title' => 'Edit')), "/transactions/utilityEdit/{$row['Transaction']['id']}", array('escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	}
?>
</table>
<?= $this->element('page_links') ?>

<br/><?= $html->link('Sort & Balance Account', "/transactions/utilityBalance/{$this->data['Transaction']['account_number']}"); ?>
<?php endif; ?>
