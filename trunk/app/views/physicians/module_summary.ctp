<?php if (!$isUpdate): ?>
	<div id="PhysiciansSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'PhysiciansSummaryForm',
				'url' => '/modules/physicians/summary/1',
				'update' => 'PhysiciansSummaryContainer',
				'before' => 'Modules.Physicians.Summary.showLoadingDialog();',
				'complete' => 'Modules.Physicians.Summary.closeLoadingDialog();'
			)
		);
		
		$yesNo = array(
			'1' => 'Yes',
			'0' => 'No'
		);
		
		$blankOptions = array(
			'0' => 'Any',
			'1' => 'Blanks Only'
		);
		
		echo $form->input('Physician.physician_number', array(
			'label' => 'Phy#',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.name', array(
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.city', array(
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.zip_code');
		
		echo $form->input('Physician.unique_identification_number', array(
			'label' => 'UPIN#',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.medicaid_provider_number', array(
			'label' => 'ODJFS#',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.national_provider_identification_number', array(
			'label' => 'NPI#',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Physician.license_number', array(
			'label' => 'License#'
		));
		
		echo $form->input('Physician.unique_identification_number_blank', array(
			'label' => 'UPIN#',
			'options' => $blankOptions,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('Physician.medicaid_provider_number_blank', array(
			'label' => 'ODJFS#',
			'options' => $blankOptions,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('Physician.national_provider_identification_number_blank', array(
			'label' => 'NPI#',
			'options' => $blankOptions,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('Physician.license_number_blank', array(
			'label' => 'License#',
			'options' => $blankOptions
		));
		
		echo $form->hidden('Physician.is_export', array('value' => 0, 'id' => 'PhysiciansSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'PhysiciansSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '* Separate multiple values with commas';
		echo '</div>';
		
		echo $form->end();
	?>
</div>
<div class="ClearBoth"></div>

<?php if ($isUpdate): ?>
<div style="margin-bottom: 5px;"><?= $html->link('Add New Physician', '/physicians/edit', array('target' => '_blank')); ?></div>

<table id="PhysiciansSummaryTable" class="Styled" style="width: 1800px;">
	<thead>
		<tr>
			<th>Phy#</th>
			<th>Name</th>
			<th>Main Office</th>
			<th>Phone</th>
			<th>Fax</th>
			<th>Type/Spec</th>
			<th>Email</th>
			<th>Client Location</th>
			<th>UPIN#</th>
			<th>ODJFS#</th>
			<th>NPI#</th>
			<th>License#</th>
			<th>License Date</th>
			<th>Notes</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($results as $row)
			{
				$officeAddress = h($row['Physician']['address_1']);
				if ($row['Physician']['address_2'] != '')
				{
					$officeAddress .= '<br/>' . h($row['Physician']['address_2']);
				}
				$officeAddress .= '<br/>' . h($row['Physician']['city']) . ' ' . h($row['Physician']['zip_code']);
				
				$clientAddress = h($row['Physician']['location_address_1']);
				if ($row['Physician']['location_address_2'] != '')
				{
					$clientAddress .= '<br/>' . h($row['Physician']['location_address_2']);
				}
				$clientAddress .= '<br/>' . h($row['Physician']['location_city']) . ' ' . h($row['Physician']['location_zip_code']);
				
				echo $html->tableCells(array(
					array(
						$html->link($row['Physician']['physician_number'], "/physicians/edit/{$row['Physician']['id']}", array('target' => '_blank')),
						h($row['Physician']['name']),
						$officeAddress,
						h($row['Physician']['phone_number']),
						h($row['Physician']['fax_number']),
						h($row['Physician']['specialty']),
						h($row['Physician']['email']),
						$clientAddress,
						h($row['Physician']['unique_identification_number']),
						h($row['Physician']['medicaid_provider_number']),
						h($row['Physician']['national_provider_identification_number']),
						h($row['Physician']['license_number']),
						formatDate($row['Physician']['license_number_update_date']),
						'Notes App'
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Physicians.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Physicians.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
