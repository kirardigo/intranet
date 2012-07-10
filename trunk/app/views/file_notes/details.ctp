<script type="text/javascript">
	document.observe("dom:loaded", function() {
		new Ajax.Updater("ContentContainer", "/ajax/fileNotes/details/<?= $id ?>");
	});
</script>

<div id="ContentContainer"></div>