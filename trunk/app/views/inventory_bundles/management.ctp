<?php
	$html->css(array('window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'modules.js?load=inventory_bundles.summary'
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
			case "BundlesTab": 
				loadModule(page, "/modules/inventoryBundles/summary");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		changeTab($("BundlesTab"));
	});
</script>

<div class="ClearBoth"></div>

<div id="BundlesTab" class="TabPage"></div>
