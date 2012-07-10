<?php if (!$isUpdate): ?>
	<div id="AaaMonthlySummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'AaaMonthlySummaryForm',
				'url' => '/modules/aaaReferrals/totals/1',
				'update' => 'AaaMonthlySummaryContainer',
				'before' => 'Modules.AaaReferrals.Totals.showLoadingDialog();',
				'complete' => 'Modules.AaaReferrals.Totals.closeLoadingDialog();'
			)
		);
		
		$yesNo = array(
			'1' => 'Yes',
			'0' => 'No'
		);
		
		echo $form->input('AaaMonthlySummary.aaa_number', array(
			'label' => 'AAA#*',
			'class' => 'Text100',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaMonthlySummary.date_month_start', array(
			'label' => 'Date Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaMonthlySummary.date_month_end', array(
			'label' => 'Date End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.group_code', array(
			'label' => 'Grouping Code',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('Virtual.profit_center_number', array(
			'label' => 'PCtr',
			'options' => $profitCenters,
			'empty' => true
		));
		echo '<div class="ClearBoth"></div>';
		
		echo $form->input('AaaReferral.is_active_for_rehab', array(
			'label' => 'Rehab',
			'options' => $yesNo,
			'empty' => true,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('AaaMonthlySummary.order_salesman', array(
			'label' => 'R_Sales (Quote)*',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaMonthlySummary.rehab_salesman', array(
			'label' => 'R_Sales (AAA)*',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.rehab_market_code', array(
			'label' => 'R_Mkt',
			'options' => $rehabMarketingCodes,
			'empty' => true,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.is_active_for_homecare', array(
			'label' => 'Hcare',
			'options' => $yesNo,
			'empty' => true,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('Virtual.show_all_aaa_records', array(
			'label' => 'Show Inactive AAAs',
			'options' => array(0 => 'No', 1 => 'Yes'),
			'empty' => false
		));
		
		echo $form->hidden('AaaMonthlySummary.is_export', array('value' => 0, 'id' => 'AaaMonthlySummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Reset', array('id' => 'AaaMonthlySummaryResetButton', 'div' => array('class' => 'Horizontal'), 'style' => 'margin: 0 10px 0 0;'));
		echo $form->button('Export to Excel', array('id' => 'AaaMonthlySummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '</div>';
		
		echo $form->end();
	?>
</div>
<div class="ClearBoth"></div>

<?php if ($isUpdate): ?>
<table id="AaaMonthlySummaryTable" class="Styled" style="width: 1200px;">
	<thead>
		<tr>
			<th>AAA#</th>
			<th>Contact</th>
			<th>Facility</th>
			<th>Type</th>
			<th>Grouping Code</th>
			<th>R_Mkt</th>
			<th>R_Sales</th>
			<th>Month</th>
			<th class="Right">Quote<br/>Total</th>
			<th class="Right">Quote<br/>Trend</th>
			<th class="Right">Revenue<br/>Total</th>
			<th class="Right">Revenue<br/>Trend</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($results as $row)
			{
				$salesman = empty($row['AaaMonthlySummary']['order_salesman']) ? $row['AaaMonthlySummary']['rehab_salesman'] : $row['AaaMonthlySummary']['order_salesman'];
				
				echo $html->tableCells(array(
					array(
						h($row['AaaMonthlySummary']['aaa_number']),
						h($row['AaaReferral']['contact_name']),
						h($row['AaaReferral']['facility_name']),
						h($row['AaaReferral']['facility_type']),
						h($row['AaaReferral']['group_code']),
						h($row['AaaReferral']['rehab_market_code']),
						h($salesman),
						formatDate($row['AaaMonthlySummary']['date_month']),
						array(round(h($row[0]['sum_quotes_month'])), array('class' => 'Right')),
						array(round(h($row[0]['sum_quotes_12months'])), array('class' => 'Right')),
						array(round(h($row[0]['sum_revenue_month'])), array('class' => 'Right')),
						array(round(h($row[0]['sum_revenue_12months'])), array('class' => 'Right'))
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.AaaReferrals.Totals.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.AaaReferrals.Totals.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
