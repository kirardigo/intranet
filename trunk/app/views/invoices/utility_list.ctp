<script type="text/javascript">
	function deleteRecord(event)
	{
		var id;
		var row = event.element().up("tr");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/invoices/utilityDelete/" + $F("InvoiceAccountNumber") + "/" + $F(row.down("input"));
		}
		
		row.removeClassName("Highlight");
		
		event.stop();
	}

	document.observe("dom:loaded", function() {
		$("InvoiceAccountNumber").focus();
		
		$$(".DeleteLink").invoke("observe", "click", deleteRecord);
	});
</script>

<?php
	echo $form->create('', array('url' => '/invoices/utilityList'));
	echo $form->input('Invoice.account_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->submit('Search', array('style' => 'margin-top: 7px'));
	echo $form->end();
?>

<br class="ClearBoth" />
<?php if (isset($records)): ?>
Invoices for <?= $customer['Customer']['name'] ?>:
<table class="Styled" style="width: 400px;">
	<tr>
		<th class="Text2">&nbsp;</th>
		<th>DOS</th>
		<th>Invoice #</th>
		<th>TCN</th>
		<th class="Right">Amount</th>
		<th class="Text25">&nbsp;</th>
	</tr>
<?php
	if ($records == false)
	{
		echo '<tr><td colspan="5" class="Center">There are no invoices for this customer.</td></tr>';
	}
	else
	{
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					$form->hidden('Invoice.id', array('value' => $row['Invoice']['id'])) .
					$html->link($html->image('iconDelete.png', array('title' => 'Delete')), '#', array('escape' => false, 'class' => 'DeleteLink')),
					formatDate($row['Invoice']['date_of_service']),
					h($row['Invoice']['invoice_number']),
					h($row['Invoice']['transaction_control_number']),
					array(number_format($row['Invoice']['amount'], 2), array('class' => 'Right')),
					$html->link($html->image('iconEdit.png', array('title' => 'Edit')), "/invoices/utilityEdit/{$row['Invoice']['id']}", array('escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	}
?>
</table>
<?= $this->element('page_links') ?>

<br/><?= $html->link('Sort & Balance Account', "/transactions/utilityBalance/{$this->data['Invoice']['account_number']}"); ?>
<?php endif; ?>