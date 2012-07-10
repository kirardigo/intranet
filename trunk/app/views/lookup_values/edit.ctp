<?php
	echo $form->create('LookupValue', array('url' => "edit/{$lookupID}/{$id}"));
	
	echo $form->hidden('id');
	echo $form->input('code', array('class' => 'Text100'));
	echo $form->input('description', array('class' => 'Text500'));
	
	echo $form->end('Save');
?>
