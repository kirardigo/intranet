<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x', 'prototip'), null, array(), false);
	
	$javascript->link(array(
		'tabs',
		'window',
		'prototip',
		'styles',
		'modules.js?load=customer_owned_equipment.management'
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
			case "COETab": 
				loadModule(page, "/modules/customerOwnedEquipment/management");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("COETab"));
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Summary</a></li>
</ul>

<div class="TabContainer">
	<div id="COETab" class="TabPage"></div>
</div>
