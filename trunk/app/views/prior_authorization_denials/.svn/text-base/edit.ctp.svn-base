<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($message)): ?>
			alert("<?= $message ?>");
		<?php elseif (isset($close) && $close): ?>
			window.opener.document.fire("priorAuthorizationDenial:updated", {
				id: $F("PriorAuthorizationDenialId")
			});
			closeWindow();
			exit;
		<?php endif; ?>
		
		$("SaveButton").observe("click", function() {
			$("PriorAuthDenialEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("PriorAuthorizationDenialCode").focus();
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'PriorAuthDenialEditForm')); ?>

<div class="GroupBox" style="float: left; width: 425px; margin-right: 25px;">
	<h2>Prior Authorization Denial</h2>
	<div class="Content">
	<?php
		echo $form->input('PriorAuthorizationDenial.code', array('class' => 'Text50'));
		echo $form->input('PriorAuthorizationDenial.description', array('class' => 'Text400'));
	?>
		<br class="ClearBoth" />
	</div>
</div>
<br class="ClearBoth" />

<?php
	echo $form->hidden('PriorAuthorizationDenial.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>
