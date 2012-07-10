<?php
	class CertificateMedicalNecessityAnswer extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'CMN_answers';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
	}
?>