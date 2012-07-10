<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x', 'prototip'), null, array(), false);
	
	$javascript->link(array(
		'scriptaculous.js?load=effects,controls',
		'tabs',
		'prototip',
		'styles',
		'window',
		'modules.js?load=aaa_referrals.summary,aaa_referrals.totals,aaa_profiles.summary,aaa_calls.summary'
	), false);
?>

<script type="text/javascript">
	var loadedModules = $A();
	
	function loadModule(page, url)
	{
		if (loadedModules.indexOf(page.id) == -1)
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
			
			loadedModules.push(page.id);
		}
	}
	
	function unloadModule(page)
	{
		loadedModules = loadedModules.without(page.id);
	}
	
	function changeTab(page)
	{
		switch (page.id)
		{
			case "AaaCallTab":
				loadModule(page, "/modules/aaa_calls/summary");
				break;
			case "AaaProfileTab":
				loadModule(page, "/modules/aaa_profiles/summary");
				break;
			case "SummaryTab": 
				loadModule(page, "/modules/aaa_referrals/summary");
				break;
			case "TotalsTab": 
				loadModule(page, "/modules/aaa_referrals/totals");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("SummaryTab"));
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Summary</a></li>
	<li><a href="#">Totals</a></li>
	<li><a href="#">HCare Call</a></li>
	<li><a href="#">HCare Profile</a></li>
</ul>

<div class="TabContainer">
	<div id="SummaryTab" class="TabPage"></div>
	<div id="TotalsTab" class="TabPage"></div>
	<div id="AaaCallTab" class="TabPage"></div>
	<div id="AaaProfileTab" class="TabPage"></div>
</div>
