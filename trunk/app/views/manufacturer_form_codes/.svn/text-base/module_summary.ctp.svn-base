<?php
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'ManufacturerFormCodeSummaryForm',
				'url' => '/modules/manufacturerFormCodes/summary',
				'update' => 'ManufacturerFormCodeSummaryContainer',
				'before' => 'Modules.ManufacturerFormCodes.Summary.showLoadingDialog();',
				'complete' => 'Modules.ManufacturerFormCodes.Summary.closeLoadingDialog();'
			)
		);
		
		echo $form->input('ManufacturerFormCode.form_code', array(
			'label' => 'Form Code',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ManufacturerFormCode.sequence_number', array(
			'label' => 'Seq#',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('ManufacturerFormCode.sequence_description', array(
			'label' => 'Description',
			'class' => 'Text250',
			'div' => array('class' => 'Horizontal')
		));
		
		echo '<div class="ClearBoth"></div>';
		echo '<div style="margin: 5px 0 10px;">';
		
		echo $form->hidden('Virtual.is_export', array('value' => 0));
		echo $form->submit('Search', array('id' => 'SearchButton', 'div' => false, 'style' => 'margin: 0;'));
		echo $form->button('Export', array('id' => 'FormCodeExportButton', 'class' => 'StyledButton'));
		echo $form->button('Reset', array('id' => 'FormCodeResetButton', 'class' => 'StyledButton'));
		echo $form->end();
		
		echo '</div>';
		
		echo $html->link('Add New Record', '/manufacturerFormCodes/edit', array('target' => '_blank'));
	}
?>

<?php if (!$isPostback): ?>
	<div id="ManufacturerFormCodeSummaryContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php 
	$paginator->options(array(
		'url' => array(
			'controller' => 'modules/manufacturerFormCodes', 
			'action' => 'summary'
		),
		'params' => $this->passedArgs
	));	
	
	$paginator->options['update'] = 'ManufacturerFormCodeSummaryContainer';
?>

<div style="margin-bottom: 5px;"></div>
<table id="ResultsTable" class="Styled">
	<tr>
		<?php
			echo '<th style="width: 20px;">&nbsp;</th>';
			echo $paginator->sortableHeader('Form Code', 'form_code');
			echo $paginator->sortableHeader('Seq#', 'sequence_number');
			echo $paginator->sortableHeader('Description', 'sequence_description');
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['ManufacturerFormCode']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['ManufacturerFormCode']['form_code']),
					h($row['ManufacturerFormCode']['sequence_number']),
					h($row['ManufacturerFormCode']['sequence_description'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	Modules.ManufacturerFormCodes.Summary.addHandlers();
</script>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.ManufacturerFormCodes.Summary.init();
	</script>
<?php endif; ?>