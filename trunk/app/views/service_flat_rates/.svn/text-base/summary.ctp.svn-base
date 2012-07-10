<script type="text/javascript">
	function viewRow(event)
	{
		id = event.element().up("td").down("input").value;
		
		window.open("/serviceFlatRates/edit/" + id, "_blank");
		event.stop();
	}
	
	function resetFilters(event)
	{
		event.stop();
		$("ServiceFlatRateHcpcCode").clear();
		$("ServiceFlatRateDescription").clear();
				
		$("ServiceFlatRateForm").submit();
	}
	
	function exportData(event)
	{
		event.stop();
		$("VirtualFlatRateExport").value = 1;
		$("ServiceFlatRateForm").submit();
		$("VirtualFlatRateExport").value = 0;
	}
	
	document.observe("dom:loaded", function() {
		mrs.fixIEInputs("ServiceFlatRateForm");
		
		$$(".flatRateViewLink").invoke("observe", "click", viewRow);
		
		$("FlatRateSearchButton").observe("click", function() {
			$("ServiceFlatRateForm").submit();
		});
		
		$("FlatRateResetButton").observe("click", resetFilters);
		$("FlatRateExportButton").observe("click", exportData);
	});
</script>

<?php
	echo $form->create('', array('url' => '/serviceFlatRates/summary', 'id' => 'ServiceFlatRateForm'));
	
	echo $form->input('ServiceFlatRate.hcpc_code', array(
		'label' => 'Code',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	
	echo $form->input('ServiceFlatRate.description', array(
		'label' => 'Description',
		'class' => 'Text200'
	));
	
	echo '<br style="clear: both;" />';
	
	echo $form->hidden('Virtual.flat_rate_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'FlatRateSearchButton', 'class' => 'StyledButton', 'style' => 'margin: 0;', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'FlatRateExportButton', 'class' => 'StyledButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'FlatRateResetButton', 'class' => 'StyledButton'));
	echo $form->end();
?>

<br />
<?= $html->link('Add New Record', '/serviceFlatRates/edit', array('target' => '_blank')) ?>
<br /><br />

<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th>&nbsp;</th>';
			echo $paginator->sortableHeader('HCPC Code', 'hcpc_code');
			echo $paginator->sortableHeader('Description', 'description');
			echo '<th class="Right">MRS Rate</th>';
			echo '<th class="Right">CMS Rate</th>';
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['ServiceFlatRate']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'flatRateViewLink', 'escape' => false)),
					h($row['ServiceFlatRate']['hcpc_code']),
					h($row['ServiceFlatRate']['description']),
					array(h($row['ServiceFlatRate']['mrs_flat_rate']), array('class' => 'Right')),
					array(h($row['ServiceFlatRate']['cms_flat_rate']), array('class' => 'Right'))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>