<script type="text/javascript">
	function editRow(event)
	{
		recordID = event.element().up("td").down("input").value;
		
		window.open("/hcpc/edit/" + recordID, "_blank");
		event.stop();
	}
	
	function resetFilters()
	{
		$("HcpcCode").clear();
		$("HcpcDescription").clear();
		$("HcpcPmdClass").clear();
		$("HcpcCmnCode").clear();
		$("HcpcInitialDate").clear();
		$("HcpcDiscontinuedDate").clear();
		
		$("HcpcIndexForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("HcpcInitialDate");
		mrs.bindDatePicker("HcpcDiscontinuedDate");
	
		$$(".editLink").invoke("observe", "click", editRow);
		$("SearchButton").observe("click", function() {
			$("HcpcIndexForm").submit();
		});
		
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/hcpc/management', 'id' => 'HcpcIndexForm'));
	
	echo $form->input('Hcpc.code', array(
		'label' => 'Code',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Hcpc.description', array(
		'label' => 'Description',
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Hcpc.pmd_class', array(
		'label' => 'PMD Class',
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Hcpc.cmn_code', array(
		'label' => 'CMN Code',
		'class' => 'Text100',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Hcpc.initial_date', array(
		'label' => 'Initial Date',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal'),
		'type' => 'text'
	));
	echo $form->input('Hcpc.discontinued_date', array(
		'label' => 'Discontinued Date',
		'class' => 'Text75',
		'type' => 'text'
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';
	
	echo $html->link('Add New Record', '/hcpc/add', array('target' => '_blank'));
?>

<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th>&nbsp;</th>';
			echo $paginator->sortableHeader('Code', 'code');
			echo $paginator->sortableHeader('Description', 'description');
			echo $paginator->sortableHeader('PMD Class', 'pmd_class');
			echo $paginator->sortableHeader('CMN Code', 'cmn_code');
			echo $paginator->sortableHeader('Initial Date', 'initial_date');
			echo $paginator->sortableHeader('Discontinued Date', 'discontinued_date');
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['Hcpc']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['Hcpc']['code']),
					h($row['Hcpc']['description']),
					h($row['Hcpc']['pmd_class']),
					h($row['Hcpc']['cmn_code']),
					formatDate($row['Hcpc']['initial_date']),
					formatDate($row['Hcpc']['discontinued_date'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>