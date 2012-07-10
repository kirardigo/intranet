<?php
	echo $form->create('FileNoteCreate', array('url' => '/fileNotes/create', 'id' => 'FileNoteModuleForm', 'class' => 'HorizontalFields'));

	if ($before != null)
	{
		echo $before;
	}

	echo '<div class="GroupBox">';
	echo '<h2>eFN Details</h2>';
	echo '<div class="Content">';
	
	echo $form->hidden('account_number');
	
	echo $form->input('memo', array('label' => 'Subject', 'class' => 'Text150'));
	echo $form->input('remarks_1', array('label' => 'Line 2', 'class' => 'Text150'));
	echo $form->input('remarks_2', array('label' => 'Line 3', 'class' => 'Text150'));
	echo $form->input('remarks_3', array('label' => 'Line 4', 'class' => 'Text150'));
	echo $form->input('department_code', array(
		'label' => 'Department',
		'options' => $departments,
		'empty' => true
	));
	echo $form->input('action_code', array(
		'label' => 'Action Code',
		'empty' => true,
		'options' => $actionCodes
	));
	echo $form->input('invoice_number', array_merge(array('class' => 'Text60'), $invoice != null ? array('class' => 'ReadOnly', 'readonly' => 'readonly') : array()));
	
	if ($showTcnFields)
	{
		echo $form->input('transaction_control_number', array('class' => 'Text60', 'label' => 'TCN'));
		echo $form->input('transaction_control_number_file', array('class' => 'Text35', 'label' => 'TCN File'));
	}
	
	echo $form->input('followup_date', array('type' => 'text', 'label' => 'FUP Date', 'class' => 'Text75'));
	echo $form->input('followup_initials', array('label' => 'FUP INI', 'class' => 'Text35'));
	echo $form->input('priority_code', array('label' => 'Priority', 'class' => 'Text35'));
	echo $form->input('email_to', array('class' => 'Text150'));
	
	echo '</div></div>';
	
	if ($after != null)
	{
		echo $after;
	}
	
	if ($handler != null)
	{
		echo $form->hidden('handler', array('value' => $handler));
	}
	
	echo $ajax->submit('Submit', array(
		'id' => 'FileNoteModuleSubmitButton',
		'class' => 'StyledButton',
		'url' => "/modules/fileNotes/create/{$this->data['FileNoteCreate']['account_number']}", 
		'condition' => 'Modules.FileNotes.Create.onBeforePost(event)',
		'complete' => 'Modules.FileNotes.Create.onPostCompleted(request)'
	));
	
	echo $form->end();
?>
<script type="text/javascript">
	Modules.FileNotes.Create.init();
</script>