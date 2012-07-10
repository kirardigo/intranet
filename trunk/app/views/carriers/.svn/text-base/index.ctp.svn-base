<script type="text/javascript">
	function editRecord(event)
	{
		event.stop();
		
		var row = event.element().up("tr");
		var recordID = row.down("td").down("input").value;
		
		window.open("/carriers/edit/" + recordID, "_blank");
	}
	
	function resetForm(event)
	{
		event.stop();
		
		$("CarrierCarrierNumber").clear();
		$("CarrierName").clear();
		$("CarrierCarrierNameWhenBrowsingByGroup").clear();
		$("CarrierPhoneNumber").clear();
		
		$("CarrierIndexForm").submit();
	}
	
	document.observe("dom:loaded", function() {
		$$(".editLink").invoke("observe", "click", editRecord);
		$("ResetButton").observe("click", resetForm);
	});
</script>

<?php
	echo $form->create('', array('url' => 'index', 'id' => 'CarrierIndexForm'));
	echo $form->input('Carrier.carrier_number', array(
		'class' => 'Text50',
		'label' => 'Carr#',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Carrier.name', array(
		'class' => 'Text200',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Carrier.carrier_name_when_browsing_by_group', array(
		'label' => 'Browse Name',
		'class' => 'Text200',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Carrier.phone_number', array(
		'label' => 'CSR Phone',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton'));
	echo '</div>';
	
	echo $form->end();
	
	echo $html->link('Create New Carrier', '/carriers/create');
?>

<table class="Styled" style="margin-top: 5px;">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Carr#', 'carrier_number');
			echo $paginator->sortableHeader('Name', 'name');
			echo $paginator->sortableHeader('Browse Name', 'carrier_name_when_browsing_by_group');
			echo $paginator->sortableHeader('CSR Phone', 'phone_number');
			echo $paginator->sortableHeader('Addr1', 'address_1');
			echo $paginator->sortableHeader('City, State', 'city');
			echo $paginator->sortableHeader('VOB Phone', 'vob_phone_number');
			echo $paginator->sortableHeader('Auth Phone', 'auth_phone_number');
		?>
	</tr>
	<?php
		foreach ($pagedData as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" id="recordID" value="' . $row['Carrier']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['Carrier']['carrier_number']),
					h($row['Carrier']['name']),
					h($row['Carrier']['carrier_name_when_browsing_by_group']),
					h($row['Carrier']['phone_number']),
					h($row['Carrier']['address_1']),
					h($row['Carrier']['city']),
					h($row['Carrier']['vob_phone_number']),
					h($row['Carrier']['auth_phone_number'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>

<?= $this->element('page_links'); ?>