<?php
	//we need this so cake doesn't render the execution time at the end of the response
	Configure::write('debug', 0);
	
	header('Content-Type: ' . $this->data['ProcessFile']['mime_type']);
	header('Content-Disposition: attachment; filename="' . $this->data['ProcessFile']['filename'] . '";');
	
	echo $this->data['ProcessFile']['file_content'];
?>