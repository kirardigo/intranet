<script type="text/javascript">
	function validateForm(event)
	{
		event.stop();
		
		valid = true;
		
		$("CarrierNewForm").select(".FieldError").invoke("removeClassName", "FieldError");
		
		if ($F("CarrierName") == "")
		{
			$("CarrierName").addClassName("FieldError");
			valid = false;
		}
		if ($F("CarrierAddress1") == "")
		{
			$("CarrierAddress1").addClassName("FieldError");
			valid = false;
		}
		if ($F("CarrierCity") == "")
		{
			$("CarrierCity").addClassName("FieldError");
			valid = false;
		}
		if ($F("CarrierZipCode") == "")
		{
			$("CarrierZipCode").addClassName("FieldError");
			valid = false;
		}
		if ($F("CarrierPhoneNumber") == "")
		{
			$("CarrierPhoneNumber").addClassName("FieldError");
			valid = false;
		}
		if ($F("CarrierStatementType") == "")
		{
			$("CarrierStatementType").addClassName("FieldError");
			valid = false;
		}
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			$("CarrierNewForm").submit();
		}
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindPhoneFormatting("CarrierPhoneNumber");
		
		$("SaveButton").observe("click", validateForm);
		
		$("CarrierStatementType").observe("change", function() {
			if ($F("CarrierStatementType") == "")
			{
				$("CarrierGroupCode").clear();
			}
			else
			{
				new Ajax.Request("/json/carriers/getStatementTypeGroupCode/" + $F("CarrierStatementType"), {
					onSuccess: function(transport) {
						$("CarrierGroupCode").value = transport.headerJSON.groupCode;
					}
				});
			}
		});
	});
</script>

<?php
	echo $form->create('', array('url' => '/carriers/create', 'id' => 'CarrierNewForm'));
?>
<div class="GroupBox">
	<h2>Address</h2>
	<div class="Content">
	<?php
		echo $form->input('Carrier.name', array(
			'class' => 'Text300'
		));
		echo $form->input('Carrier.address_1', array(
			'class' => 'Text300'
		));
		echo $form->input('Carrier.address_2', array(
			'class' => 'Text300'
		));
		echo $form->input('Carrier.city', array(
			'label' => 'City/State',
			'class' => 'Text250',
			'div' => array('class' => 'FormColumn')
		));
		echo $form->input('Carrier.zip_code', array(
			'class' => 'Text100'
		));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Other</h2>
	<div class="Content">
	<?php
		echo $form->input('Carrier.phone_number', array(
			'label' => 'CSR/IVR Phone',
			'class' => 'Text100'
		));
		echo $form->input('Carrier.statement_type', array(
			'label' => 'Stmt Type',
			'options' => $statementTypes,
			'empty' => true,
			'style' => 'width: 400px;'
		));
		echo $form->input('Carrier.group_code', array(
			'readonly' => 'readonly',
			'class' => 'Text50 ReadOnly'
		));
	?>
	</div>
</div>

<?php
	echo $form->button('Save', array('id' => 'SaveButton'));
	echo $form->end();
?>
