<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'scriptaculous.js?load=effects,controls',
		'tabs',
		'window',
		'modules.js?load=distributor_orders.summary,distributor_order_lines.summary'
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
			case "HeaderTab": 
				loadModule(page, "/modules/distributorOrders/summary");
				break;
			case "DetailsTab": 
				loadModule(page, "/modules/distributorOrderLines/summary");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("HeaderTab"));
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Header</a></li>
	<li><a href="#">Details</a></li>
</ul>

<div class="TabContainer">
	<div id="HeaderTab" class="TabPage"></div>
	<div id="DetailsTab" class="TabPage"></div>
</div>
