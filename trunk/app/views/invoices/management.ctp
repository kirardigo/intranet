<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x', 'prototip'), null, array(), false);
	
	$javascript->link(array(
		'tabs',
		'window',
		'prototip',
		'styles',
		'modules.js?load=invoices.management,invoices.management_for_claims,electronic_file_notes.tickles,prior_authorizations.for_customer'
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
			case "InvoiceTab":
				loadModule(page, "/modules/invoices/management");
				break;
			case "InvoiceForClaimsTab":
				loadModule(page, "/modules/invoices/management_for_claims");
				break;
		}
	}
	
	document.observe("invoices:efnRequested", function(event) {
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.pages.invoke("hide");
		$("TabStripEFNTab").addClassName("Selected").show();
		$("EFNTab").show();
		new Ajax.Updater($("EFNTab").update("Loading. Please wait..."), "/modules/electronicFileNotes/tickles/1/accountNumber:" + encodeURIComponent(event.memo.accountNumber) + "/invoiceNumber:" + encodeURIComponent(event.memo.invoiceNumber), { evalScripts: true });
	});
	
	document.observe("invoices:detailRequested", function(event) {
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.pages.invoke("hide");
		$("TabStripDetailTab").addClassName("Selected").show();
		$("DetailTab").show();
		new Ajax.Updater($("DetailTab").update("Loading. Please wait..."), "/modules/invoices/details/" + encodeURIComponent(event.memo.accountNumber) + "/" + encodeURIComponent(event.memo.invoiceNumber), { evalScripts: true });
	});
	
	document.observe("invoices:authRequested", function(event) {
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.pages.invoke("hide");
		$("TabStripAuthTab").addClassName("Selected").show();
		$("AuthTab").show();
		new Ajax.Updater($("AuthTab").update("Loading. Please wait..."), "/modules/priorAuthorizations/forCustomer/" + encodeURIComponent(event.memo.accountNumber) + "/1/invoiceNumber:" + encodeURIComponent(event.memo.invoiceNumber), { evalScripts: true });
	});
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("InvoiceTab"));
	});
</script>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
	<li><a href="#">Claims</a></li>
	<li id="TabStripEFNTab" class="TempTab" style="display: none;"><a href="#">eFN</a></li>
	<li id="TabStripDetailTab" class="TempTab" style="display: none;"><a href="#">Details</a></li>
	<li id="TabStripAuthTab" class="TempTab" style="display: none;"><a href="#">Prior Auths</a></li>
</ul>

<div class="TabContainer">
	<div id="InvoiceTab" class="TabPage"></div>
	<div id="InvoiceForClaimsTab" class="TabPage"></div>
	<div id="EFNTab" class="TabPage" style="display: none;"></div>
	<div id="DetailTab" class="TabPage" style="display: none;"></div>
	<div id="AuthTab" class="TabPage" style="display: none;"></div>
</div>