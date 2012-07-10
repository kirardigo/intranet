<?php if (!$isUpdate): ?>
	<div id="ElectronicFileNotesTicklesContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if (!$isPreFiltered): ?>
<div id="UpperSection">
	<?php
		$yesNo = array(
			1 => 'Yes',
			0 => 'No'
		);
		
		echo $ajax->form('',
			'post',
			array(
				'id' => 'ElectronicFileNotesTicklesForm',
				'url' => '/modules/electronicFileNotes/tickles/1',
				'update' => 'ElectronicFileNotesTicklesContainer',
				'before' => 'Modules.ElectronicFileNotes.Tickles.showLoadingDialog();',
				'complete' => 'Modules.ElectronicFileNotes.Tickles.closeLoadingDialog();'
			)
		);
		
		echo '<div>';
		
		echo $form->input('ElectronicFileNote.profit_center_number', array(
			'label' => 'Profit Center',
			'options' => $profitCenters,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ElectronicFileNote.action_code', array(
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ElectronicFileNote.department_code', array(
			'label' => 'Dept',
			'options' => $departments,
			'empty' => '',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ElectronicFileNote.followup_date', array(
			'label' => 'FUP Date',
			'type' => 'text',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ElectronicFileNote.followup_initials', array(
			'label' => 'FUP INI'
		));
		
		echo '</div>';
		
		echo $form->hidden('ElectronicFileNote.is_export', array('value' => 0));
		
		echo '<div style="margin-top: 5px">';
		echo $form->submit('Search', array('div' => array('class' => 'Horizontal')));
		echo $form->button('Export to Excel', array('id' => 'ExportButton'));
		echo '</div>';
		
		echo $form->end();
		
		echo (!$isUpdate) ? '</div>' : '';
	?>
<?php endif; ?>
<?php if ($isUpdate): ?>

<br class="ClearBoth" />

<table id="ElectronicFileNotesTicklesTable" class="Styled" style="width: 1220px;">
	<thead>
		<tr>
			<th>PCtr</th>
			<th>Acct#</th>
			<th>H/R</th>
			<th>TCN#</th>
			<th>Invoice#</th>
			<th>Client</th>
			<th>Memo</th>
			<th>Remarks</th>
			<th>Action</th>
			<th>D</th>
			<th>FUP Date</th>
			<th>FUP INI</th>
			<th>Days</th>
		</tr>
	</thead>
	<tbody>	
		<?php
			foreach ($results as $row)
			{
				echo $html->tableCells(
					array(
						h($row['ElectronicFileNote']['profit_center_number']),
						h($row['ElectronicFileNote']['account_number']),
						h($row['ElectronicFileNote']['transaction_control_number_type']),
						h($row['ElectronicFileNote']['transaction_control_number']),
						h($row['ElectronicFileNote']['invoice_number']),
						h($row['ElectronicFileNote']['name']),
						h($row['ElectronicFileNote']['memo']),
						h($row['ElectronicFileNote']['remarks']),
						h($row['ElectronicFileNote']['action_code']),
						h($row['ElectronicFileNote']['department_code']),
						formatDate(h($row['ElectronicFileNote']['followup_date'])),
						h($row['ElectronicFileNote']['followup_initials']),
						h($row['ElectronicFileNote']['days'])
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
	Modules.ElectronicFileNotes.Tickles.initializeTable();
</script>

<?php endif; ?>

<?php if (!$isPreFiltered): ?>
<script type="text/javascript">
	Modules.ElectronicFileNotes.Tickles.addHandlers();
</script>
<?php endif; ?>

<?php if (!$isUpdate): ?>
</div>
<?php endif; ?>
