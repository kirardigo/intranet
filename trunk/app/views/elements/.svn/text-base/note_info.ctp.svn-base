<?php
	// Pass in the record reference from the Note model like this in the view:
	// 		echo $this->element('note_info', array('noteRecord' => &$this->data['Note']['your_type_here']));
	// Sample assumes this code was used in the controller:
	// 		$this->data['Note'] = $this->Note->getNotes($this->YourModelHere->generateTargetUri($id));
	if (!empty($noteRecord) && isset($noteRecord['modified_by']))
	{
		echo "Last modified by {$noteRecord['modified_by']} on ";
		echo formatDateTime($noteRecord['modified']);
	}
?>