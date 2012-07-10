<?php if (!$isUpdate): ?>
	<div id="OxygenSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'OxygenSummaryForm',
				'url' => '/modules/oxygen/summary/1',
				'update' => 'OxygenSummaryContainer',
				'before' => 'Modules.Oxygen.Summary.showLoadingDialog();',
				'complete' => 'Modules.Oxygen.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('Customer.profit_center_number', array(
			'label' => 'PCtr',
			'options' => $profitCenters,
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.account_number', array(
			'class' => 'Text100',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.osa_status', array(
			'label' => 'Status',
			'options' => $sleepStatuses,
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.osa_aaa_referral_code', array(
			'class' => 'Text75',
			'label' => 'AAA#',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.osa_setup_date_start', array(
			'type' => 'text',
			'label' => 'Setup Date Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Oxygen.osa_setup_date_end', array(
			'type' => 'text',
			'label' => 'Setup Date End',
			'class' => 'Text75'
		));
		
		echo $form->hidden('Oxygen.is_export', array('value' => 0, 'id' => 'OxygenSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'OxygenSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '</div>';
		
		echo $form->end();
	?>
</div>
<div class="ClearBoth"></div>

<?php if ($isUpdate): ?>
<table id="OxygenSummaryTable" class="Styled" style="width: 1100px;">
	<thead>
		<tr>
			<th>Acct#</th>
			<th>Name</th>
			<th>PCtr</th>
			<th>Type</th>
			<th>Pressure</th>
			<th>Status</th>
			<th>Lab#</th>
			<th>Referral#</th>
			<th style="white-space: nowrap">Setup Date</th>
			<th style="white-space: nowrap">Status Date</th>
			<th style="white-space: nowrap">Last Trx</th>
			<th>First Night</th>
			<th>1 Mo FUP</th>
			<th>3 Mo FUP</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($results as $row)
			{
				echo $html->tableCells(array(
					array(
						$html->link($row['Oxygen']['account_number'], "/customers/inquiry/accountNumber:{$row['Oxygen']['account_number']}/tab:16", array('target' => '_blank')),
						h(ifset($row['Customer']['name'])),
						h(ifset($row['Customer']['profit_center_number'])),
						ifset($oxygenTypes[$row['Oxygen']['osa_type']]),
						h($row['Oxygen']['osa_pressure_setting']),
						h($row['Oxygen']['osa_status']),
						'<span class="AaaLabTip TooltipContainer">' . h($row['Oxygen']['osa_aaa_lab_code']) . '</span>',
						'<span class="AaaReferralTip TooltipContainer">' . h($row['Oxygen']['osa_aaa_referral_code']) . '</span>',
						h($row['Oxygen']['osa_setup_date']),
						h($row['Oxygen']['osa_status_date']),
						h(ifset($row['Virtual']['last_trx_date'])),
						h($row['Oxygen']['first_night_sleep_study_date']),
						$row['Oxygen']['is_30_day_followup_returned'] ? 'Y' : '',
						$row['Oxygen']['is_90_day_followup_returned'] ? 'Y' : ''
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.Oxygen.Summary.initializeTable();
	Modules.Oxygen.Summary.addTooltips();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.Oxygen.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
