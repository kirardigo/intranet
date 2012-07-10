<?php if (!$isPostback): ?>
<div style="margin-bottom: 3px;">
	<div style="width: 20px; float: right;">
		<?= $html->link(
			$html->image('iconAdd.png', array('style' => 'margin: 12px 0 0 0;')),
			'#',
			array('escape' => false, 'id' => 'FileNoteCreateLink')
		); ?>
	</div>
<?php
	echo $ajax->form('',
		'post',
		array(
			'id' => 'FileNoteClientForm',
			'url' => "/modules/fileNotes/forCustomer/{$accountNumber}/0",
			'update' => 'FileNoteContentContainer',
			'before' => 'Modules.FileNotes.ForCustomer.showLoadingDialog();',
			'complete' => 'Modules.FileNotes.ForCustomer.closeLoadingDialog();'
		)
	);
	
	echo $form->input('FileNote.transaction_control_number', array(
		'label' => 'TCN',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.invoice_number', array(
		'label' => 'Invoice#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.followup_date_start', array(
		'label' => 'Followup Start',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.followup_date_end', array(
		'label' => 'Followup End',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.followup_initials', array(
		'label' => 'Initials',
		'class' => 'Text50',
		'div' => array('class' => 'Horizontal')
	));
	
	echo $form->submit('Filter', array('style' => 'margin: 10px 5px 0 0;', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'FileNoteResetButton', 'style' => 'margin: 10px 0 0 0;'));
	
	echo $form->end();
?>
	<div class="ClearBoth"></div>
</div>
<?php endif; ?>

<?php if (!$isPostback): ?>
	<div id="FileNoteContentContainer">
<?php endif; ?>

<?php
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/fileNotes', 
			'action' => "forCustomer/{$accountNumber}/0"
		),
		'params' => $this->passedArgs,
		'update' => 'FileNoteContentContainer'
	));
?>

	<table class="Styled">
		<tr>
			<?php
				echo $paginator->sortableHeader('TCN', 'transaction_control_number');
				echo $paginator->sortableHeader('Invoice#', 'invoice_number');
				echo $paginator->sortableHeader('D', 'department_code');
				echo $paginator->sortableHeader('Action', 'action_code');
				echo $paginator->sortableHeader('FUP Date', 'followup_date');
				echo $paginator->sortableHeader('FUP Ini', 'followup_initials');
			?>
			<th>Memo</th>
			<?= $paginator->sortableHeader('Created', 'created'); ?>
			<th>&nbsp;</th>
		</tr>
		<?php
			foreach ($records as $row)
			{
				echo $html->tableCells(
					array(
						$form->hidden('id', array('value' => $row['FileNote']['original_id'])) .
						h($row['FileNote']['transaction_control_number']),
						h($row['FileNote']['invoice_number']),
						h($row['FileNote']['department_code']),
						h($row['FileNote']['action_code']),
						formatDate($row['FileNote']['followup_date']),
						h($row['FileNote']['followup_initials']),
						h($row['FileNote']['memo']),
						formatDate($row['FileNote']['created']),
						$html->link($html->image('iconDetail.png'), '#', array(
							'escape' => false,
							'title' => 'Show details',
							'class' => 'FileNoteDetail'
						))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</table>
	<?= $this->element('page_links'); ?>
	
	<div id="FileNoteDetailContainer" style="margin-top: 10px;"></div>
	
	<script type="text/javascript">
		Modules.FileNotes.ForCustomer.addHandlers();
	</script>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.FileNotes.ForCustomer.init("<?= $accountNumber ?>");
	</script>
<?php endif; ?>