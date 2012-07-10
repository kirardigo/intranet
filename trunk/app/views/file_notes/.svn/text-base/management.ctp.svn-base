<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'modules.js?load=file_notes.summary'
	), false);
?>

<script type="text/javascript">
	document.observe("dom:loaded", function() {
		new Ajax.Updater("ContentContainer", "/modules/fileNotes/summary", {
			evalScripts: true
		});
	});
</script>

<div id="ContentContainer">Loading...</div>