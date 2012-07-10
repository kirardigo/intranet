<?php if (!$isUpdate): ?>
<?= $javascript->link(array('progress_bar/jsProgressBarHandler', 'window'), false); ?>
<?=	$html->css(array('window/window', 'window/mac_os_x'), null, array(), false); ?>

<?php
	$args = '';
	
	foreach ($this->passedArgs as $arg => $value)
	{
		$args .= "/{$arg}:{$value}";
	}
?>
	
<script type="text/javascript">
	function updateTable(pe)
	{
		new Ajax.Updater("ProcessContainer", "/processes/manager/1<?= $args ?>", {
			onComplete: function() {
				applyProgressBars();
			}
		});
	}
	
	function applyProgressBars()
	{
		$$("div.ProgressBar").each(function(el) {
			if (el.hasClassName("Finished"))
			{
				new JS_BRAMUS.jsProgressBar(el, parseInt(el.innerHTML.replace("%","")), { finished: true });
			}
			else if (el.hasClassName("Cancelled"))
			{
				new JS_BRAMUS.jsProgressBar(el, parseInt(el.innerHTML.replace("%","")), { cancelled: true });
			}
			else
			{
				new JS_BRAMUS.jsProgressBar(el, parseInt(el.innerHTML.replace("%","")));
			}
		});
	}
	
	function interruptProcess(id, el)
	{
		var selectedRow = $(el).up("tr");
		
		// Remove existing highlighting and add new highlight
		$(el).up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to interrupt this process?"))
		{
			new Ajax.Request("/ajax/processes/interruptProcess/" + id);
		}
	}
	
	function removeProcess(id, el)
	{
		var selectedRow = $(el).up("tr");
		
		// Remove existing highlighting and add new highlight
		$(el).up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to remove this record and its attachments?"))
		{
			new Ajax.Request("/ajax/processes/removeProcess/" + id);
		}
	}
	
	function showOutput(id)
	{
		new Ajax.Request("/ajax/processes/output/" + id, {
			onComplete: function(transport) {
				mrs.showDialog(transport.responseText, null, 800, 400, {}, true, false);
			}
		});
	}
	
	function showFiles(id)
	{
		new Ajax.Request("/ajax/processFiles/fileList/" + id, {
			onComplete: function(transport) {
				mrs.showDialog(transport.responseText, null, 600, 350, {}, true, false);
			}
		});
	}
	
	document.observe("dom:loaded", function() {
		new PeriodicalExecuter(updateTable, .5);
		
		$("ResetButton").observe("click", function() {
			location.href = "/processes/manager/reset:1";
		});
	});
</script>

<style type="text/css">
	.ProgressBar {
		width: 120px;
	}
	.ProgressImage {
		position: absolute;
	}
	.ProgressText {
		position: relative;
		left: 38px;
		top: -1px;
		width: 40px;
		text-align: right;
		color: black;
	}
</style>

<?php
	echo $ajax->form('',
		'post',
		array(
			'url' => '/processes/manager/1',
			'update' => 'ProcessContainer',
			'complete' => 'applyProgressBars();',
			'style' => 'margin: 0px;'
		)
	);
	echo $form->input('Process.created_by', array('label' => 'User', 'div' => array('style' => 'float: left;')));
	echo $form->submit('Filter', array('div' => false, 'style' => 'float: left; margin: 10px 10px 0px 10px;'));
	echo $form->button('Reset', array('id' => 'ResetButton', 'style' => 'margin: 10px 0px 0px 0px;'));
	echo $form->end();
?>
<div class="ClearBoth"></div><br/>
<div id="ProcessContainer">
<?php endif; ?>

	<table class="Styled">
		<tr>
			<?= $paginator->sortableHeader('Application', 'name', array('class' => 'Text200')); ?>
			<?= $paginator->sortableHeader('User', 'created_by', array('class' => 'Text75')); ?>
			<?= $paginator->sortableHeader('Started', 'created', array('class' => 'Text150')); ?>
			<?= $paginator->sortableHeader('Progress', 'percent_complete', array('style' => 'width: 140px;')); ?>
			<th>Status</th>
			<th class="Text75 Center">Cancel</th>
			<th class="Text75">Output</th>
			<th class="Text50 Center">Delete</th>
		</tr>
		<?php
			foreach ($results as $row)
			{
				$doneLink = '';
				$outputLink = '';
				$fileLink = '';
				$progressBarClass = '';
				
				if ($row['Process']['is_complete'] && $row['Process']['is_interrupted'])
				{
					$progressBarClass = 'Cancelled';
				}
				else if ($row['Process']['is_complete'])
				{
					$progressBarClass = 'Finished';
				}
				
				// Using onmouseup instead of onclick to overcome AJAX content update issue which
				// can prevent onclick from firing if refresh occurs between mousedown & mouseup
				if (!$row['Process']['is_complete'] && $row['Process']['is_interruptible'] && !$row['Process']['is_interrupted'])
				{
					$doneLink = $html->link(
						$html->image('cancel.png', array('title' => 'Interrupt')),
						'#',
						array('onmouseup' => "interruptProcess({$row['Process']['id']}, this); return false;"),
						false,
						false
					);
				}
				
				// Using onmouseup instead of onclick to overcome AJAX content update issue which
				// can prevent onclick from firing if refresh occurs between mousedown & mouseup
				if ($row['Process']['output'] != '')
				{
					$outputLink = $html->link(
						$html->image('iconDocument.png', array('title' => 'Output', 'style' => 'margin-right: 10px')),
						'#',
						array('onmouseup' => "showOutput({$row['Process']['id']}); return false;"),
						false,
						false
					);
				}
				
				// Using onmouseup instead of onclick to overcome AJAX content update issue which
				// can prevent onclick from firing if refresh occurs between mousedown & mouseup
				if (count($row['ProcessFile']) > 0)
				{
					$fileLink = $html->link(
						$html->image('iconPdf.png', array('title' => 'Files', 'style' => 'margin-right: 10px')),
						'#',
						array('onmouseup' => "showFiles({$row['Process']['id']}); return false;"),
						false,
						false
					);
				}
				
				// Using onmouseup instead of onclick to overcome AJAX content update issue which
				// can prevent onclick from firing if refresh occurs between mousedown & mouseup
				$deleteLink = ($row['Process']['is_complete']) ? 
				$html->link(
					$html->image('iconDelete.png', array('title' => 'Delete')),
					'#',
					array('onmouseup' => "removeProcess({$row['Process']['id']}, this); return false;"),
					false,
					false
				) : 
				'';
				
				echo $html->tableCells(
					array(
						h($row['Process']['name']),
						h($row['Process']['created_by']),
						h(formatDateTime($row['Process']['created'])),
						'<div id="pb' . $row['Process']['id'] . '" class="ProgressBar ' . $progressBarClass . '">' . h($row['Process']['percent_complete']) . '</div>',
						h($row['Process']['status_message']),
						array($doneLink, array('class' => 'Center')),
						$outputLink . $fileLink,
						array($deleteLink, array('class' => 'Center'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</table>
	<?= $this->element('page_links'); ?>
	
<?php if (!$isUpdate): ?>
	<script type="text/javascript">
		applyProgressBars();
	</script>
	
</div>
<?php endif; ?>
