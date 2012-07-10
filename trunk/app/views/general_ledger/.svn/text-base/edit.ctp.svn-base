<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($message)): ?>
			alert("<?= $message ?>");
		<?php elseif (isset($close) && $close): ?>
			window.opener.document.fire("generalLedger:updated", {
				id: $F("GeneralLedgerId")
			});
			closeWindow();
			exit;
		<?php endif; ?>
		
		$("SaveButton").observe("click", function() {
			$("GeneralLedgerEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("GeneralLedgerGeneralLedgerCode").focus();
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'GeneralLedgerEditForm')); ?>

<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
	<h2>General Ledger Info</h2>
	<div class="Content">
	<?php
		echo $form->input('GeneralLedger.general_ledger_code', array(
			'label' => 'Code',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('GeneralLedger.is_active', array(
			'label' => array('class' => 'Checkbox'),
			'div' => array('class' => 'FormColumn', 'style' => 'margin-top: 12px')
		));
		echo $form->input('GeneralLedger.rental_code_or_purchase_code', array(
			'label' => 'Rent/Purchase',
			'options' => array('R' => 'Rental', 'P' => 'Purchase'),
			'empty' => true
		));
		echo $form->input('GeneralLedger.description', array(
			'class' => 'Text300'
		));
		echo $form->input('GeneralLedger.group_code', array(
			'class' => 'Text50',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('GeneralLedger.accounting_code', array(
			'label' => 'Acct Code',
			'class' => 'Text100',
			'div' => array('class' => 'FormColumn')
		));
	?>
		<br class="ClearBoth" />
	</div>
</div>
<br class="ClearBoth" />

<?php
	echo $form->hidden('GeneralLedger.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>
