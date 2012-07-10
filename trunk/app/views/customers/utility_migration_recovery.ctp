<?php	
	echo $form->create('', array('url' => "/customers/utilityMigrationRecovery", 'id' => 'MigrationRecoveryForm'));
	
	echo '<p>This utility will attempt to repair any recoverable issues that occurred during the processing of the Change Profit Center utility.</p>';
	
	//just a junk hidden field so this->data will be populated
	echo $form->hidden('Customer.id');
	
	echo $form->submit('Start Recovery');
	echo $form->end();
?>