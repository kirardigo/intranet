<style type="text/css">
	#MagnificentInfo {
		width: 500px;
		border: 1px solid black;
		border-collapse: collapse;
		margin: 10px 0px;
	}
	
	#MagnificentInfo tr.Alt td {
		background-color: #e1dfd3;
	}
	
	.MagnificentLabel {
		font-weight: bold;
	}
</style>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		mrs.bindDatePicker('MagnificentReportStartDate');
		mrs.bindDatePicker('MagnificentReportEndDate');
	});
</script>

<?= $form->create('', array('url' => 'reporting')); ?>

<?php
	echo $form->input('MagnificentReport.start_date', array('div' => array('class' => 'Horizontal')));
	echo $form->input('MagnificentReport.end_date', array('div' => array('class' => 'Horizontal')));
	echo $form->input('MagnificentReport.profit_center_number', array(
		'options' => $profitCenters,
		'empty' => 'ALL',
		'label' => 'Profit Center',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MagnificentReport.department', array(
		'options' => $departments,
		'empty' => 'ALL',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('MagnificentReport.millers_family_value_id', array(
		'options' => $millersFamilyValues,
		'empty' => 'ALL'
	));
?>
<br class="ClearBoth" />

<?php
	echo $form->input('MagnificentReport.show_breakdown', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'div' => array('class' => 'Horizontal')));
	echo $form->input('MagnificentReport.show_details', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'div' => array('class' => 'Horizontal')));
	echo $form->input('MagnificentReport.include_group_effort', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'div' => array('class' => 'Horizontal')));
?>
<br class="ClearBoth" />
<?php
	echo $form->end('Results');
?>

<?php if (isset($summaryInfo)): ?>
<table id="MagnificentInfo">
	<tr>
		<td class="MagnificentLabel">Approved Nominations:</td>
		<td><?= $summaryInfo[0][0]['totalApproved'] ?></td>
	</tr>
	<tr class="Alt">
		<td class="MagnificentLabel">Rejected Nominations:</td>
		<td><?= $summaryInfo[0][0]['totalCancelled'] ?></td>
	</tr>
	<tr>
		<td class="MagnificentLabel">Total Value:</td>
		<td><?= $summaryInfo[0][0]['totalValue'] ?></td>
	</tr>
	<tr class="Alt">
		<td class="MagnificentLabel">Individual Value:</td>
		<td><?= $summaryInfo[0][0]['totalIndividualValue'] ?></td>
	</tr>
</table>
<?php endif; ?>

<?php if ($this->data['MagnificentReport']['show_breakdown']): ?>
<table class="Styled" style="margin-bottom: 10px;">
<?php
	echo $html->tableHeaders(
		array(
			'Profit Center',
			'Department',
			'Count',
			'Value'
		)
	);
	
	if ($groupInfo !== false)
	{
		foreach ($groupInfo as $row)
		{
			echo $html->tableCells(
				array(
					$row['MagnificentReport']['profit_center_name'],
					$row['MagnificentReport']['department_name'],
					$row['MagnificentReport']['totalCount'],
					$row['MagnificentReport']['totalValue'],
				),
				array(),
				array('class' => 'Alt')
			);
		}
	}
?>
</table>
<?php endif; ?>

<?php if ($this->data['MagnificentReport']['show_details']): ?>
<table class="Styled">
<?php
	echo $html->tableHeaders(
		array(
			$paginator->sort('Date', 'created'),
			$paginator->sort('Recipient', 'recipient'),
			$paginator->sort('Nominated By', 'nominator'),
			$paginator->sort('Approved By', 'approver'),
			$paginator->sort('PC', 'profit_center_number'),
			$paginator->sort('Dept', 'department'),
			$paginator->sort('Group?', 'is_group_effort'),
			$paginator->sort('MFV', 'MillersFamilyValue.name'),
			$paginator->sort('Value', 'value')
		)
	);
	
	if ($detailedInfo !== false)
	{
		foreach ($detailedInfo as $row)
		{
			echo $html->tableCells(
				array(
					h($row['MagnificentReport']['created']),
					h($row['MagnificentReport']['recipient']),
					h($row['MagnificentReport']['nominator']),
					h($row['MagnificentReport']['approver']),
					h($row['MagnificentReport']['profit_center_number']),
					h($row['MagnificentReport']['department']),
					($row['MagnificentReport']['is_group_effort']) ? 'Yes' : 'No',
					h($row['MagnificentReport']['name']),
					h($row['MagnificentReport']['value'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	}
?>
</table>
<?= $this->element('page_links'); ?>
<?php endif; ?>
