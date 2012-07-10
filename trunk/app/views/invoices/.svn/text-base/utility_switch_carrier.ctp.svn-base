<script type="text/javascript">
	function loadCarrierForm(event)
	{
		$("InvoiceOldCarrierCode").value = event.element().innerHTML;
		$("InvoiceCarrierNumber").value = event.element().up("tr").down("td").innerHTML;
		$("CarrierFormContainer").show();
		event.stop();
	}
	
	function validateCarrierForm(event)
	{
		if ($F("InvoiceCarrierCode") == "")
		{
			$("InvoiceCarrierCode").addClassName("FieldError");
			alert("Carrier number must be specified.");
			$("InvoiceCarrierCode").focus();
			event.stop();
		}
	}
	
	document.observe("dom:loaded", function() {
		$("InvoiceAccountNumber").focus();
		$$(".CarrierLink").invoke("observe", "click", loadCarrierForm);
		$("CarrierForm").observe("submit", validateCarrierForm);
	});
</script>

<?php
	echo $form->create('', array('url' => '/invoices/utilitySwitchCarrier'));
	echo $form->input('Invoice.account_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->input('Invoice.invoice_number', array(
		'class' => 'Text100',
		'div' => array('class' => 'input text Horizontal')
	));
	echo $form->submit('Search', array('style' => 'margin-top: 7px'));
	echo $form->end();
?>

<br class="ClearBoth" />
<?php if (isset($invoice)): ?>
	<table class="Styled" style="width: 300px;">
		<tr>
			<th></th>
			<th>Carrier</th>
			<th class="Right">Balance</th>
		</tr>
		<?php
			for ($i = 1; $i <= 3; $i++)
			{
				echo $html->tableCells(
					array(
						"{$i}",
						$html->link($invoice['Invoice']["carrier_{$i}_code"], '#', array('class' => 'CarrierLink')),
						array(number_format(h($invoice['Invoice']["carrier_{$i}_balance"]), 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</table>
	<div id="CarrierFormContainer" style="display: none; margin-top: 20px;">
		<?php
			echo $form->create('', array('url' => '/invoices/utilitySwitchCarrier', 'id' => 'CarrierForm'));
			echo $form->hidden('Invoice.account_number');
			echo $form->hidden('Invoice.invoice_number');
			echo $form->hidden('Invoice.carrier_number');
			echo $form->input('Invoice.old_carrier_code', array(
				'label' => 'Old Carrier Number',
				'class' => 'ReadOnly',
				'div' => array('class' => 'input text Horizontal')
			));
			echo $form->input('Invoice.carrier_code', array(
				'label' => 'Carrier Number',
				'class' => 'Text100',
				'div' => array('class' => 'input text Horizontal')
			));
			echo $form->submit('Modify', array('style' => 'margin-top: 7px'));
			echo $form->end();
		?>
	</div>
<?php endif; ?>