<?php
	$html->css(array('tabs'), null, array(), false);
	
	$javascript->link(array(
		'modules.js?load=invoices.batch'
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
	
	Event.observe(window, "load", function() {
		loadModule($("BatchInvoiceTab"), "/modules/invoices/batch");
	});
</script>

<div class="ClearBoth"></div>

<div id="BatchInvoiceTab"></div>