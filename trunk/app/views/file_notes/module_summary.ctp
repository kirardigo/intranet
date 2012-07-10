<?php if (!$isPostback): ?>
<div style="margin-bottom: 5px;">
<?php
	echo $ajax->form('',
		'post',
		array(
			'id' => 'FileNoteSummaryForm',
			'url' => "/modules/fileNotes/summary",
			'update' => 'FileNoteContentContainer',
			'before' => 'Modules.FileNotes.Summary.showLoadingDialog();',
			'complete' => 'Modules.FileNotes.Summary.closeLoadingDialog();'
		)
	);
	
	echo $form->input('FileNote.account_number', array(
		'label' => 'Acct#',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
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
	echo $form->input('FileNote.action_code', array(
		'label' => 'Action',
		'options' => $actionCodes,
		'empty' => true
	));
	echo '<div class="ClearBoth"></div>';
	
	echo $form->input('FileNote.created_date_start', array(
		'label' => 'Created Start',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.created_date_end', array(
		'label' => 'Created End',
		'type' => 'text',
		'class' => 'Text75',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('FileNote.created_by', array(
		'label' => 'Created By',
		'class' => 'Text50',
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
	echo $form->input('FileNote.department_code', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => true,
	));
	echo '<div class="ClearBoth"></div>';
	
	echo $form->submit('Filter', array('class' => 'StyledButton', 'style' => 'margin: 5px 5px 0 0;', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Reset', array('id' => 'FileNoteResetButton', 'class' => 'StyledButton', 'style' => 'margin: 5px 10px 0 0;'));
	echo '<span style="color: red;">Do not use these without also specifying a black labeled filter</span>';
	
	echo $form->end();
?>
	<div class="ClearBoth"></div>
</div>
<?php endif; ?>

<?php if (!$isPostback): ?>
	<div id="FileNoteContentContainer">
<?php endif; ?>

<?php
	if ($isPostback):
	
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/fileNotes', 
				'action' => "summary"
			),
			'params' => $this->passedArgs
		));
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/fileNotes', 'action' => 'exportSummaryResults'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'FileNoteContentContainer';
?>
		<table class="Styled">
			<tr>
				<?php
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo $paginator->sortableHeader('TCN', 'transaction_control_number');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo $paginator->sortableHeader('D', 'department_code');
					echo $paginator->sortableHeader('Action', 'action_code');
					echo $paginator->sortableHeader('FUP Date', 'followup_date');
					echo $paginator->sortableHeader('FUP Ini', 'followup_initials');
				?>
				<th>Memo</th>
				<?php
					echo $paginator->sortableHeader('Created By', 'created_by');
					echo $paginator->sortableHeader('Created', 'created');
				?>
				<th>&nbsp;</th>
			</tr>
			<?php
				foreach ($records as $row)
				{
					echo $html->tableCells(
						array(
							$form->hidden('id', array('value' => $row['FileNote']['original_id'])) .
							h($row['FileNote']['account_number']),
							h($row['FileNote']['transaction_control_number']),
							h($row['FileNote']['invoice_number']),
							h($row['FileNote']['department_code']),
							h($row['FileNote']['action_code']),
							formatDate($row['FileNote']['followup_date']),
							h($row['FileNote']['followup_initials']),
							h($row['FileNote']['memo']),
							h($row['FileNote']['created_by']),
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
			Modules.FileNotes.Summary.addHandlers();
		</script>
<?php endif; ?>
<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.FileNotes.Summary.init();
	</script>
<?php endif; ?>