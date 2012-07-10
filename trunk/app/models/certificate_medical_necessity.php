<?php
	class CertificateMedicalNecessity extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'CMN_mngmt';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
	}
?>