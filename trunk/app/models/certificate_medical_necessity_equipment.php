<?php
	class CertificateMedicalNecessityEquipment extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05DH';
		
		var $actsAs = array(
			'Indexable', 
			'Defraggable',
			'Migratable' => array('key' => 'account_number')
		);
	}
?>