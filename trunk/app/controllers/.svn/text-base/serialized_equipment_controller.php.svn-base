<?php
	class SerializedEquipmentController extends AppController
	{
		var $uses = array('SerializedEquipment');
		
		/**
		 * Get information about a given serialized equipment number.
		 * @param string $serialNumber The MRS serialized equipment number.
		 */
		function json_information($serialNumber)
		{
			$record = $this->SerializedEquipment->find('first', array(
				'contain' => array(),
				'fields' => array('product_description'),
				'conditions' => array('mrs_serial_number' => $serialNumber),
				'index' => 'A'
			));
			
			if ($record === false)
			{
				$this->set('json', array('success' => false));
			}
			else
			{
				$this->set('json', array('success' => true, 'record' => $record['SerializedEquipment']));
			}
		}
	}
?>