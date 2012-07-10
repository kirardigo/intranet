<?php if (!$isUpdate): ?>
	<div id="AaaReferralsSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<div id="UpperSection">
	<?php
		echo $ajax->form('',
			'post',
			array(
				'id' => 'AaaReferralsSummaryForm',
				'url' => '/modules/aaaReferrals/summary/1',
				'update' => 'AaaReferralsSummaryContainer',
				'before' => 'Modules.AaaReferrals.Summary.showLoadingDialog();',
				'complete' => 'Modules.AaaReferrals.Summary.closeLoadingDialog();'
			)
		);
		
		$yesNo = array(
			'1' => 'Yes',
			'0' => 'No'
		);
		
		echo $form->input('AaaReferral.aaa_number', array(
			'label' => 'AAA#*',
			'class' => 'Text100',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.contact_name LIKE', array(
			'label' => 'Contact',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.facility_name LIKE', array(
			'label' => 'Facility',
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.facility_type', array(
			'label' => 'Type',
			'options' => $aaaTypes,
			'empty' => true,
			'div' => array('class' => 'Horizontal input')
		));
		echo $form->input('AaaReferral.group_code', array(
			'label' => 'Grouping Code'
		));
		echo '<div class="ClearBoth"></div>';
		
		echo $form->input('AaaReferral.county_code', array(
			'label' => 'County Code*',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.is_active_for_rehab', array(
			'label' => 'Rehab',
			'options' => $yesNo,
			'empty' => true,
			'div' => array('class' => 'Horizontal input select')
		));
		echo $form->input('AaaReferral.rehab_salesman', array(
			'label' => 'R_Sales*',
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
		echo $form->input('AaaReferral.homecare_salesman', array(
			'label' => 'H_Sales*',
			'maxlength' => false,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.homecare_market_code', array(
			'label' => 'H_Mkt',
			'options' => $homecareMarketingCodes,
			'empty' => true,
			'div' => array('class' => 'Horizontal input text')
		));
		echo $form->input('AaaReferral.is_active_for_access', array(
			'label' => 'Access',
			'options' => $yesNo,
			'empty' => true
		));
		echo $form->input('AaaReferral.address_1 LIKE', array(
			'label' => 'Address',
			'class' => 'Text200',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.phone_number LIKE', array(
			'label' => 'Phone#',
			'class' => 'Text75'
		));
		
		echo $form->hidden('AaaReferral.is_export', array('value' => 0, 'id' => 'AaaReferralsSummaryIsExport'));
		
		echo '<div style="margin: 5px 0px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal'), 'style' => 'margin: 0px !important;'));
		echo $form->button('Export to Excel', array('id' => 'AaaReferralsSummaryExportButton', 'style' => 'margin: 0 10px 0 0;', 'div' => array('class' => 'Horizontal')));
		echo '* Separate multiple values with commas';
		echo '</div>';
		
		echo $form->end();
	?>
</div>
<div class="ClearBoth"></div>

<?= $html->link('Add new AAA Referral', '/aaaReferrals/edit', array('target' => '_blank')); ?>

<?php if ($isUpdate): ?>
<table id="AaaReferralsSummaryTable" class="Styled" style="width: 1800px; margin-top: 5px;">
	<thead>
		<tr>
			<th>AAA#</th>
			<th>Contact</th>
			<th>Title</th>
			<th>Facility</th>
			<th>Type</th>
			<th>Grouping Code</th>
			<th>Address</th>
			<th>City_State</th>
			<th>Zip</th>
			<th>County</th>
			<th>Phone &amp; Ext</th>
			<th>Cell</th>
			<th>Email</th>
			<th>Rehab</th>
			<th>R_Sales</th>
			<th>R_Mkt</th>
			<th>Hcare</th>
			<th>H_Sales</th>
			<th>H_Mkt</th>
			<th>Access</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($results as $row)
			{
				echo $html->tableCells(array(
					array(
						$html->link($row['AaaReferral']['aaa_number'], "/aaaReferrals/edit/{$row['AaaReferral']['id']}", array('target' => '_blank')),
						h($row['AaaReferral']['contact_name']),
						h($row['AaaReferral']['contact_title']),
						h($row['AaaReferral']['facility_name']),
						'<span class="AaaReferralSummaryTypeTip TooltipContainer">' . $row['AaaReferral']['facility_type'] . '</span>',
						h($row['AaaReferral']['group_code']),
						h($row['AaaReferral']['address_1']),
						h($row['AaaReferral']['city_state']),
						h($row['AaaReferral']['zip_code']),
						h($row['AaaReferral']['county_name']),
						h($row['AaaReferral']['phone_number'] . (strlen($row['AaaReferral']['phone_extension']) > 0 ? ' x' : '') . $row['AaaReferral']['phone_extension']),
						h($row['AaaReferral']['cell_phone_number']),
						h($row['AaaReferral']['contact_email']),
						h($row['AaaReferral']['is_active_for_rehab'] ? 'Yes' : 'No'),
						h($row['AaaReferral']['rehab_salesman']),
						h($row['AaaReferral']['rehab_market_code']),
						h($row['AaaReferral']['is_active_for_homecare'] ? 'Yes' : 'No'),
						h($row['AaaReferral']['homecare_salesman']),
						h($row['AaaReferral']['homecare_market_code']),
						h($row['AaaReferral']['is_active_for_access'] ? 'Yes' : 'No')
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.AaaReferrals.Summary.initializeTable();
</script>

<?php endif; ?>

<script type="text/javascript">
	Modules.AaaReferrals.Summary.addHandlers();
</script>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
