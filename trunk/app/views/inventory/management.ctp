<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'tabs',
		'window',
		'modules.js?load=inventory.summary,inventory_special_orders.summary,inventory_bundles.summary'
	), false);
?>

<script type="text/javascript">
	var loadedModules = $A();
	
	function loadModule(page, url)
	{
		if (loadedModules.indexOf(url) == -1)
		{
			var callback = arguments[3] || Prototype.K;
			
			new Ajax.Updater(
				page.update("Loading. Please wait..."), 
				url, 
				{ 
					evalScripts: true, 
					onComplete: function(transport) {
						//invoke a custom callback handler if we have one
						callback(transport);
					}
				}
			);
			
			loadedModules.push(url);
		}
	}
	
	function changeTab(page)
	{
		switch (page.id)
		{
			case "InventoryTab": 
				loadModule(page, "/modules/inventory/summary");
				break;
			case "SpecialsTab": 
				loadModule(page, "/modules/inventorySpecialOrders/summary");
				break;
			case "BundlesTab": 
				loadModule(page, "/modules/inventoryBundles/summary");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("InventoryTab"));
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Inventory</a></li>
	<li><a href="#">Specials</a></li>
	<li><a href="#">Bundles</a></li>
</ul>

<div class="TabContainer">
	<div id="InventoryTab" class="TabPage"></div>
	<div id="SpecialsTab" class="TabPage"></div>
	<div id="BundlesTab" class="TabPage"></div>
</div>
