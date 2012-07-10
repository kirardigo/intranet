<?php
	class CustomerOwnedEquipmentNumber extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'INFO_EQUIP_COE';
		
		var $actsAs = array(
			'Incrementable' => array(
				'fields' => array(
					'customer_owned_equipment_id_number' => array('returnIncremented' => true)
				)
			)
		);
	}
?>