<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'modules.js?load=electronic_file_notes.tickles'
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
		loadModule($("TicklesTab"), "/modules/electronicFileNotes/tickles");
	});
</script>

<div class="ClearBoth"></div>

<div id="TicklesTab"></div>