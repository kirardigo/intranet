<?php
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'AaaCallSummaryForm',
				'url' => '/modules/aaaCalls/summary',
				'update' => 'AaaCallsSummaryContainer',
				'before' => 'Modules.AaaCalls.Summary.showLoadingDialog();',
				'complete' => 'Modules.AaaCalls.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('AaaCall.aaa_number', array(
			'label' => 'AAA#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaCall.call_date_start', array(
			'label' => 'Call Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaCall.call_date_end', array(
			'label' => 'Call End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaCall.next_call_date_start', array(
			'label' => 'Next Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaCall.next_call_date_end', array(
			'label' => 'Next End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaCall.completed', array(
			'options' => array(
				0 => 'Not Complete',
				1 => 'Complete'
			),
			'empty' => true
		));
		echo '<div class="ClearBoth"></div>';
		echo $form->input('AaaReferral.profit_center_number', array(
			'label' => 'PCtr',
			'options' => $profitCenters,
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.homecare_salesman', array(
			'id' => 'HomecareCallSalesman',
			'label' => 'HCare Sls',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('AaaReferral.homecare_market_code', array(
			'id' => 'HomecareCallMarketCode',
			'label' => 'HCare Mkt',
			'options' => $marketCodes,
			'empty' => true
		));
		
		echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
		echo $form->submit('Search', array('id' => 'AaaCallSearchButton', 'class' => 'StyledButton', 'div' => array('class' => 'Horizontal')));
		echo $form->button('Reset', array('id' => 'AaaCallResetButton', 'class' => 'StyledButton'));
		echo $form->end();
		
		echo $html->link('Add New Record', '/aaaCalls/edit', array('target' => '_blank'));
	}
?>

<?php if (!$isPostback): ?>
	<div id="AaaCallsSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/aaaCalls', 
				'action' => 'summary'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/aaaCalls', 'action' => 'exportSummaryResults'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'AaaCallsSummaryContainer';
	?>
	
	<br/><br/>
	
	<div style="margin-bottom: 5px;"></div>
	<table id="AaaCallResultsTable" class="Styled">
		<thead>
			<tr>
				<?php
					echo $paginator->sortableHeader('AAA#', 'aaa_number');
					echo $paginator->sortableHeader('Precall Goal', 'precall_goal');
					echo $paginator->sortableHeader('Call Date', 'call_date');
					echo '<th>Thank You</th>';
					echo '<th>Type</th>';
					echo $paginator->sortableHeader('Staff', 'sales_staff_initials');
					echo $paginator->sortableHeader('Name', 'facility_name');
					echo $paginator->sortableHeader('PCtr', 'profit_center_number');
					echo $paginator->sortableHeader('HCare Sls', 'homecare_salesman');
					echo $paginator->sortableHeader('HCare Mkt', 'homecare_market_code');
					echo $paginator->sortableHeader('Next Call', 'next_call_date');
					echo $paginator->sortableHeader('Completed', 'followup_complete_date');
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
						h($row['AaaCall']['aaa_number']),
						h($row['AaaCall']['precall_goal']),
						formatDate($row['AaaCall']['call_date']),
						($row['AaaCall']['follow_up_thank_you'] == 1 ? ' Yes' : ' No'),
						'<span class="HomecareCallCallTypeTip TooltipContainer">' . $row['AaaCall']['call_type'] . '</span>',
						h($row['AaaCall']['sales_staff_initials']),
						h(ifset($row['AaaCall']['facility_name'])),
						h(ifset($row['AaaCall']['profit_center_number'])),
						h(ifset($row['AaaCall']['homecare_salesman'])),
						'<span class="HomecareCallMarketCodeTip TooltipContainer">' . ifset($row['AaaCall']['homecare_market_code']) . '</span>',
						formatDate($row['AaaCall']['next_call_date']),
						formatDate($row['AaaCall']['followup_complete_date']),
						'<input type="hidden" value="' . $row['AaaCall']['original_id'] . '" />' .
						$html->link($html->image('iconEdit.png'), '#', array('class' => 'AaaCallEditLink', 'escape' => false))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
		</tbody>
	</table>

	<?= $this->element('page_links') ?>
	<br /><br />
	
	<script type="text/javascript">
		Modules.AaaCalls.Summary.detailsLoaded();
	</script>
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.AaaCalls.Summary.init();
	</script>
<?php endif; ?>