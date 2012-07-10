<script type="text/javascript">
	function editRecord(event)
	{
		event.stop();
		
		var row = event.element().up("tr");
		var recordID = row.down("td").down("input").value;
		
		window.open("/hcpcModifiers/edit/" + recordID, "_blank");
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("HcpcModifierEffectiveDate");
		mrs.bindDatePicker("HcpcModifierTerminationDate");
	
		$$(".editLink").invoke("observe", "click", editRecord);
		
		$("ResetButton").observe("click", function() { 
			var form = $("HcpcIndexForm");
			form.select("input[type=text]").invoke("clear"); 
			form.submit();
		});
	});
</script>

<?php
	echo $form->create('', array('url' => '/hcpcModifiers/summary', 'id' => 'HcpcIndexForm'));
	
	echo $form->input('HcpcModifier.modifier', array(
		'label' => 'Modifier',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('HcpcModifier.category', array(
		'label' => 'Category',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal'),
		'type' => 'text'
	));
	echo $form->input('HcpcModifier.effective_date', array(
		'label' => 'Effective Date',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal'),
		'type' => 'text'
	));
	echo $form->input('HcpcModifier.termination_date', array(
		'label' => 'Termination Date',
		'class' => 'Text75',
		'type' => 'text'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';
	
	echo $html->link('Add New Record', '/hcpcModifiers/edit', array('target' => '_blank'));
?>

<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th>&nbsp;</th>';
			echo $paginator->sortableHeader('Modifier', 'modifier');
			echo $paginator->sortableHeader('Description', 'description');
			echo $paginator->sortableHeader('Category', 'category');
			echo $paginator->sortableHeader('Level', 'level');
			echo $paginator->sortableHeader('Code', 'code');
			echo $paginator->sortableHeader('Effective Date', 'effective_date');
			echo $paginator->sortableHeader('Termination Date', 'termination_date');
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					
					'<input type="hidden" value="' . $row['HcpcModifier']['id'] . '" />' . $html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['HcpcModifier']['modifier']),
					h($row['HcpcModifier']['description']),
					h($row['HcpcModifier']['category']),
					h($row['HcpcModifier']['level']),
					h($row['HcpcModifier']['code']),
					formatDate($row['HcpcModifier']['effective_date']),
					formatDate($row['HcpcModifier']['termination_date'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>