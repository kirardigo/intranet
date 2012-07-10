<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=hcpc.hcpc_carriers'
	), false);
?>

<script type="text/javascript">
	//load up the hcpc view module
	
	document.observe("dom:loaded", function() {
		new Ajax.Updater(
			$("InventoryItemContent").update("Loading. Please wait..."), 
			"/modules/inventory/inventory_view_core/<?= h($id) ?>",
			{
				evalScripts: true
			}
		);
	});	
</script>

<div id="InventoryItemContent"></div>