<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x', 'prototip'), null, array(), false);
	
	$javascript->link(array(
		'scriptaculous.js?load=effects,controls',
		'tabs',
		'prototip',
		'styles',
		'window',
		'swfobject',
		'modules.js?load=orders.work_in_process,orders.quotation,orders.funding,orders.management_canton,electronic_file_notes.tickles'
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
		$$(".TempTab").invoke("hide");
		
		switch (page.id)
		{
			case "QuotationTab":
				loadModule(page, "/modules/orders/quotation");
				break;
			case "FundingTab":
				loadModule(page, "/modules/orders/funding");
				break;
			case "WorkInProcessTab": 
				loadModule(page, "/modules/orders/workInProcess");
				break;
			case "ManagementTab":
				loadModule(page, "/modules/orders/management");
				break;
			case "ManagementCantonTab":
				loadModule(page, "/modules/orders/management_canton");
				break;
		}
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("WorkInProcessTab"));
	});
	
	document.observe("funding:priorAuthRequested", function(event) {
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.pages.invoke("hide");
		$("TabStripPriorAuthsTab").addClassName("Selected").show();
		$("PriorAuthsTab").show();
		new Ajax.Updater($("PriorAuthsTab").update("Loading. Please wait..."), "/ajax/priorAuthorizations/detail/auth_num:" + encodeURIComponent(event.memo.auth_num), { evalScripts: true });
	});
	
	document.observe("funding:efnRequested", function(event) {
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.pages.invoke("hide");
		$("TabStripEFNTab").addClassName("Selected").show();
		$("EFNTab").show();
		new Ajax.Updater($("EFNTab").update("Loading. Please wait..."), "/modules/electronicFileNotes/tickles/1/tcn:" + encodeURIComponent(event.memo.tcn), { evalScripts: true });
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li><a href="#">Quotation</a></li>
	<li class="Disabled"><a href="#">Documentation</a></li>
	<li><a href="#">Funding</a></li>
	<li class="Selected"><a href="#">WIP</a></li>
	<li><a href="#">MNG Admin</a></li>
<!--	
	<li><a href="#">MNG 010</a></li>
	<li><a href="#">MNG 020</a></li>
	<li><a href="#">MNG 050</a></li>
	<li><a href="#">MNG 060</a></li>
-->
	<li id="TabStripPriorAuthsTab" class="TempTab" style="display: none;"><a href="#">Priors Auths</a></li>
	<li id="TabStripEFNTab" class="TempTab" style="display: none;"><a href="#">eFN</a></li>
</ul>

<div class="TabContainer">
	<div id="QuotationTab" class="TabPage" style="display: none;"></div>
	
	<div id="DocumentationTab" class="TabPage" style="display: none;">
		<h2>Under Construction</h2>
	</div>
	
	<div id="FundingTab" class="TabPage" style="display: none;"></div>
	
	<div id="WorkInProcessTab" class="TabPage"></div>
	
	<div id="ManagementTab" class="TabPage" style="display: none;"></div>
	
<!--
	<div id="ManagementCantonTab" class="TabPage" style="display: none;"></div>
	
	<div id="ManagementAkronTab" class="TabPage" style="display: none;">
		<h2>Under Construction</h2>
	</div>
	
	<div id="ManagementYoungstownTab" class="TabPage" style="display: none;">
		<h2>Under Construction</h2>
	</div>
	
	<div id="ManagementClevelandTab" class="TabPage" style="display: none;">
		<h2>Under Construction</h2>
	</div>
-->
	<div id="PriorAuthsTab" class="TabPage" style="display: none;"></div>
	
	<div id="EFNTab" class="TabPage" style="display: none;"></div>
</div>
