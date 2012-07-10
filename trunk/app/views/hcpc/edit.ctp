<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=hcpc.hcpc_carriers,hcpc.hcpc,hcpc.hcpc_icd9'
	), false);
?>

<script type="text/javascript">
	var loadedModules = $A();
	var code = "<?= $this->data['Hcpc']['code'] ?>";
	
	function loadModule(page, url, args)
	{
		if (loadedModules.indexOf(page.id) == -1)
		{
			var parameters = arguments[3] || {};
			var callback = arguments[4] || Prototype.K;
			
			new Ajax.Updater(
				page.update("Loading. Please wait..."), 
				url + "/" + args.collect(function(a) { return encodeURIComponent(a); }).join("/"), 
				{ 
					parameters: parameters,
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

	function changeTab(page)
	{
		switch (page.id)
		{
			case "HcpcTab": 
				loadModule(page, "/modules/hcpc/hcpc", [ code ]);
				break;
			case "ICD9CrosswalksTab": 
				loadModule(page, "/modules/hcpc/icd9_crosswalks", [ code ]);
				break;
			case "HcpcCarriersTab":
				loadModule(page, "/modules/hcpc/hcpc_carriers", [ code ]);
				break;
		}
	}
	
	function unloadModule(page)
	{
		loadedModules = loadedModules.without(page.id);
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("HcpcTab"));
	});
	
	document.observe("hcpc:reloadTab", function(event) {
		page = $(event.memo.tab);
		unloadModule(page);
		Tabs.select($$(".TabPage").indexOf(page));
	});
</script>

<div class="ClearBoth"></div><br/>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Hcpc</a></li>
	<li><a href="#">ICD9 Crosswalks</a></li>
	<li><a href="#">HCPC Carriers</a></li>
</ul>

<div class="TabContainer">
	<div id="HcpcTab" class="TabPage"></div>
	<div id="ICD9CrosswalksTab" class="TabPage" style="display: none;"></div>
	<div id="HcpcCarriersTab" class="TabPage" style="display: none;"></div>
</div>