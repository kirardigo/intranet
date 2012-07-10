<?= $javascript->link('scriptaculous.js?load=effects,controls', false); ?>
<script type="text/javascript">
	function submitForm(event)
	{
		event.stop();
		
		new Ajax.Request("/json/inventoryBundles/add", {
			parameters: {
				"data[InventoryBundle][id]": $F("InventoryBundleId"),
				"data[InventoryBundle][inventory_number_master]": $F("InventoryBundleInventoryNumberMaster"),
				"data[InventoryBundle][inventory_number_item]": $F("InventoryBundleInventoryNumberItem")
			},
			onSuccess: function(transport)
			{
				if (transport.headerJSON.success)
				{
					$("InventoryBundleId").clear();
					$("InventoryBundleInventoryNumberItemDescription").update();
					$("InventoryBundleInventoryNumberItem").clear().focus();
				}
				else
				{
					alert(transport.headerJSON.message);
				}
			}
		});
	}
	
	function searchCallback(input)
	{
		return "data[Inventory][search]=" + $F(input);
	}
	
	function updateDescription(input)
	{
		if ($F(input) != "")
		{
			new Ajax.Updater(input.id + "Description", "/ajax/inventory/description", {
				parameters: { inventory_number: $F(input) }
			});
		}
	}

	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("inventoryBundle:updated");
			closeWindow();
		<?php endif; ?>
		
		$("SaveButton").observe("click", submitForm);
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		new Ajax.Autocompleter("InventoryBundleInventoryNumberMaster", "InventoryMaster_autoComplete", "/ajax/inventory/autoComplete", {
			minChars: 3,
			indicator: "InventoryMasterIndicator",
			callback: searchCallback,
			afterUpdateElement: updateDescription
		});
		mrs.fixAutoCompleter("InventoryBundleInventoryNumberMaster");
		
		new Ajax.Autocompleter("InventoryBundleInventoryNumberItem", "InventoryItem_autoComplete", "/ajax/inventory/autoComplete", {
			minChars: 3,
			indicator: "InventoryItemIndicator",
			callback: searchCallback,
			afterUpdateElement: updateDescription
		});
		mrs.fixAutoCompleter("InventoryBundleInventoryNumberItem");
		
		updateDescription($("InventoryBundleInventoryNumberMaster"));
		updateDescription($("InventoryBundleInventoryNumberItem"));
		
	});
</script>

<?= $form->create('', array('url' => "/inventoryBundles/fake", 'id' => 'InventoryBundleEditForm')); ?>

<div class="GroupBox" style="width: 500px;">
	<h2>Inventory Bundle</h2>
	<div class="Content">
		<table style="border-collapse: collapse; width: 100%;">
			<tr>
				<td style="width: 150px;">
					<?php
						echo $form->input('InventoryBundle.inventory_number_master', array(
							'label' => 'Master#',
							'class' => 'Text100',
							'after' => $html->image('indicator.gif', array('id' => "InventoryMasterIndicator", 'style' => 'display: none;'))
						));
					?>
				</td>
				<td id="InventoryBundleInventoryNumberMasterDescription" style="padding-top: 10px;"></td>
			</tr>
		</table>
		<?= '<div style="display: none;" id="InventoryMaster_autoComplete" class="auto_complete AutoComplete550"></div>'; ?>
		<table style="border-collapse: collapse; width: 100%;">
			<tr>
				<td style="width: 150px;">
					<?php
						echo $form->input('InventoryBundle.inventory_number_item', array(
							'label' => 'Item#',
							'class' => 'Text100',
							'after' => $html->image('indicator.gif', array('id' => "InventoryItemIndicator", 'style' => 'display: none;'))
						));
					?>
				</td>
				<td id="InventoryBundleInventoryNumberItemDescription" style="padding-top: 10px;"></td>
			</tr>
		</table>
		<?= '<div style="display: none;" id="InventoryItem_autoComplete" class="auto_complete AutoComplete550"></div>'; ?>
	</div>
</div>

<?php
	echo $form->hidden('InventoryBundle.id');
	echo $form->submit('Continue', array('id' => 'SaveButton', 'style' => 'margin: 0;', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>