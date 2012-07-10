<?php
	if (!$isUpdate)
	{
		echo '<div id="AaaProfilesSummaryContainer" style="margin-top: 5px;">';
	}
	
	echo $ajax->form('',
		'post',
		array(
			'id' => 'AaaProfileSummaryForm',
			'url' => '/modules/aaaProfiles/summary/1',
			'update' => 'AaaProfilesSummaryContainer',
			'before' => 'Modules.AaaProfiles.Summary.showLoadingDialog();',
			'complete' => 'Modules.AaaProfiles.Summary.closeLoadingDialog();'
		)
	);
	
	echo $form->input('AaaProfile.aaa_number', array(
		'label' => 'AAA#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('AaaReferral.profit_center_number', array(
		'label' => 'PCtr',
		'options' => $profitCenters,
		'empty' => true,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('AaaReferral.homecare_salesman', array(
		'label' => 'HCare Sls',
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('AaaReferral.homecare_market_code', array(
		'label' => 'HCare Mkt',
		'class' => 'Text50',
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('id' => 'AaaProfileIsExport', 'value' => 0));
	echo $form->submit('Search', array('id' => 'AaaProfileSearchButton', 'class' => 'StyledButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'AaaProfileExportButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
	echo $form->button('Reset', array('id' => 'AaaProfileResetButton', 'class' => 'StyledButton'));
	echo $form->end();
	
	echo '</div>';

	echo $html->link('Add New Record', '/aaaProfiles/edit', array('target' => '_blank')); 
?>
<div style="margin-bottom: 5px;"></div>
<table id="AaaProfileResultsTable" class="Styled">
	<thead>
		<tr>
			<?php
				echo '<th>AAA#</th>';
				echo '<th>Name</th>';
				echo '<th>D</th>';
				echo '<th>PCtr</th>';	
				echo '<th>HCare Sls</th>';
				echo '<th>HCare Mkt</th>';
				echo '<th></th>';	
			?>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					$row['AaaProfile']['aaa_number'],
					ifset($row['AaaReferral']['facility_name']),
					$row['AaaProfile']['department_code'],
					ifset($row['AaaReferral']['profit_center_number']),
					ifset($row['AaaReferral']['homecare_salesman']),
					ifset($row['AaaReferral']['homecare_market_code']),
					'<input type="hidden" value="' . $row['AaaProfile']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'aaaProfileEditLink', 'escape' => false))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.AaaProfiles.Summary.init();
</script>

<?php
	if (!$isUpdate)
	{
		echo '</div>';
	}
?>