<?php if (!$isUpdate): ?>
	<div id="RentalsSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'RentalsSummaryForm',
				'url' => '/modules/rentals/summary/1',
				'update' => 'RentalsSummaryContainer',
				'before' => 'Modules.Rentals.Summary.showLoadingDialog();',
				'complete' => 'Modules.Rentals.Summary.closeLoadingDialog();'
			)
		);
		
		$yesNo = array(
			1 => 'Yes',
			0 => 'No'
		);
		
		echo $form->input('Rental.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'empty' => 'All Medical',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.setup_date', array(
			'label' => 'Setup Start',
			'type' => 'text',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.setup_date_end', array(
			'label' => 'Setup End',
			'type' => 'text',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.returned_date', array(
			'label' => 'Return Start',
			'type' => 'text',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.returned_date_end', array(
			'label' => 'Return End',
			'type' => 'text',
			'class' => 'Text100'
		));
		
		echo '<div style="margin-top: 5px">';
		
		echo $form->input('Rental.department_code', array(
			'label' => 'Dept',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.place_of_service', array(
			'label' => 'POS',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.healthcare_procedure_code', array(
			'label' => 'HCPC*',
			'class' => 'Text100',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.inventory_number', array(
			'label' => 'Inven#',
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.6_point_classification', array(
			'label' => 'Class',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.carrier_code', array(
			'label' => 'Carr',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.diagnosis_pointer', array(
			'label' => 'ICD9',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		
		echo $form->input('Rental.general_ledger_code', array(
			'label' => 'G/L',
			'class' => 'Text50'
		));
		
		echo '</div>';
		
		echo $form->hidden('Rental.is_export', array('value' => 0, 'id' => 'RentalsSummaryIsExport'));
		echo $form->hidden('Rental.is_mrs_export', array('value' => 0, 'id' => 'RentalsSummaryIsMrsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'RentalsSummaryExportButton', 'style' => 'margin-right: 10px;'));
		echo $form->button('Export MRS', array('id' => 'RentalsSummaryMrsExportButton', 'style' => 'margin-right: 10px;'));
		echo $form->button('Reset', array('id' => 'RentalsSummaryResetButton', 'style' => 'margin-right: 10px;'));
		echo '*Separate muliple values with commas';
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php if ($isUpdate): ?>
</div>
<div class="ClearBoth"></div>

<table id="RentalsSummaryTable" class="Styled" style="width: 1900px;">
	<thead>
		<tr>
			<th>Acct#</th>
			<th>PCtr</th>
			<th>CB Zip</th>
			<th>CB</th>
			<th>D</th>
			<th>A</th>
			<th>Setup</th>
			<th>Return</th>
			<th>Mo#</th>
			<th>POS</th>
			<th>Qty</th>
			<th>HCPC</th>
			<th>G/L</th>
			<th>Inven#</th>
			<th>Description</th>
			<th>MRS#</th>
			<th>Class</th>
			<th class="Right">Total Net</th>
			<th>Carr 1</th>
			<th class="Right">Net 1</th>
			<th class="Right">Gross 1</th>
			<th>Carr 2</th>
			<th class="Right">Net 2</th>
			<th class="Right">Gross 2</th>
			<th>Carr 3</th>
			<th class="Right">Net 3</th>
			<th class="Right">Gross 3</th>
			<th>ICD9</th>
			<th>ICD9</th>
			<th>ICD9</th>
			<th>ICD9</th>
			<th>Capped</th>
			<th>DM</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				$totalNet = $row['Rental']['carrier_1_net_amount'] + $row['Rental']['carrier_2_net_amount'] + $row['Rental']['carrier_3_net_amount'];
				
				echo $html->tableCells(array(
					array(
						$html->link($row['Rental']['account_number'], "/customers/inquiry/accountNumber:{$row['Rental']['account_number']}", array('target' => '_blank')),
						h($row['Rental']['profit_center_number']),
						($row['Rental']['competitive_bid_zip'] ? 'Y' : ''),
						($row['Rental']['competitive_bid_hcpc'] ? 'Y' : ''),
						h($row['Rental']['department_code']),
						h($row['Rental']['assignment_status_code']),
						h($row['Rental']['setup_date']),
						h($row['Rental']['returned_date']),
						h($row['Rental']['number_of_rental_months']),
						h($row['Rental']['place_of_service']),
						h($row['Rental']['quantity']),
						h($row['Rental']['healthcare_procedure_code']),
						h($row['Rental']['general_ledger_code']),
						h($row['Rental']['inventory_number']),
						h($row['Rental']['inventory_description']),
						h($row['Rental']['serial_number']),
						h($row['Rental']['6_point_classification']),
						array(number_format($totalNet, 2), array('class' => 'Right')),
						h($row['Rental']['carrier_1_code']),
						array(number_format(h($row['Rental']['carrier_1_net_amount']), 2), array('class' => 'Right')),
						array(number_format(h($row['Rental']['carrier_1_gross_amount']), 2), array('class' => 'Right')),
						h($row['Rental']['carrier_2_code']),
						array(($row['Rental']['carrier_2_code']) ? number_format(h($row['Rental']['carrier_2_net_amount']), 2) : '', array('class' => 'Right')),
						array(($row['Rental']['carrier_2_code']) ? number_format(h($row['Rental']['carrier_2_gross_amount']), 2) : '', array('class' => 'Right')),
						h($row['Rental']['carrier_3_code']),
						array(($row['Rental']['carrier_3_code']) ? number_format(h($row['Rental']['carrier_3_net_amount']), 2) : '', array('class' => 'Right')),
						array(($row['Rental']['carrier_3_code']) ? number_format(h($row['Rental']['carrier_3_gross_amount']), 2) : '', array('class' => 'Right')),
						h(ifset($row['Rental']['icd9_1'])),
						h(ifset($row['Rental']['icd9_2'])),
						h(ifset($row['Rental']['icd9_3'])),
						h(ifset($row['Rental']['icd9_4'])),
						h($row['Rental']['capped_status']),
						h($row['Oxygen']['respiratory_code'])
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Rentals.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Rentals.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
