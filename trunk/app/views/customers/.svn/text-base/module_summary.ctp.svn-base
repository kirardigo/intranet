<?php if (!$isUpdate): ?>
	<div id="CustomersSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'CustomersSummaryForm',
				'url' => '/modules/customers/summary/1',
				'update' => 'CustomersSummaryContainer',
				'before' => 'Modules.Customers.Summary.showLoadingDialog();',
				'complete' => 'Modules.Customers.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Customer.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Customer.setup_date', array(
			'label' => 'Setup Start',
			'type' => 'text',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Customer.setup_date_end', array(
			'label' => 'Setup End',
			'type' => 'text',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Customer.zip_code', array(
			'label' => 'Zip Code*',
			'class' => 'Text75',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('CustomerBilling.long_term_care_facility_number', array(
			'label' => 'LTCF#*',
			'class' => 'Text50',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('CustomerBilling.referral_number_from_aaa_file', array(
			'label' => 'Ref#*',
			'class' => 'Text50',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('CustomerBilling.school_or_program_number_from_aaa_file', array(
			'label' => 'Prog#*',
			'class' => 'Text50',
			'maxlength' => false
		));
		
		echo $form->hidden('Customer.is_export', array('value' => 0, 'id' => 'CustomersSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'CustomersSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '* Multi-select (use commas to separate values)<br/><div style="margin: 5px 0px; clear: both">';
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<table id="CustomersSummaryTable" class="Styled" style="width: 1250px;">
	<thead>
		<tr>
			<th>Acct#</th>
			<th>PCtr</th>
			<th>Name</th>
			<th>Setup</th>
			<th class="Text100">Phone</th>
			<th>Address</th>
			<th>City</th>
			<th>Zip</th>
			<th>County</th>
			<th>Email</th>
			<th>DOB</th>
			<th>Sex</th>
			<th>LTCF#</th>
			<th>Ref#</th>
			<th>Prog#</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$address = ($row['Customer']['address_1'] != '') ? h($row['Customer']['address_1']) . '<br/>' . h($row['Customer']['address_2']) : h($row['Customer']['address_2']);
				
				echo $html->tableCells(
					array(
						h($row['Customer']['account_number']),
						h($row['Customer']['profit_center_number']),
						h($row['Customer']['name']),
						h($row['Customer']['setup_date']),
						h($row['Customer']['phone_number']),
						$address,
						h($row['Customer']['city']),
						h($row['Customer']['zip_code']),
						h($row['Customer']['county']),
						h($row['Customer']['email']),
						h(formatDate($row['CustomerBilling']['date_of_birth'])),
						h($row['CustomerBilling']['sex']),
						h($row['CustomerBilling']['long_term_care_facility_number']),
						h($row['CustomerBilling']['referral_number_from_aaa_file']),
						h($row['CustomerBilling']['school_or_program_number_from_aaa_file'])
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Customers.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Customers.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
