<?php
	class CarrierProviderNumbersController extends AppController
	{
		/**
		 * Add a provider.
		 */
		function json_addProvider($carrierNumber, $profitCenter, $number)
		{
			$saveData['CarrierProviderNumber'] = array(
				'carrier_number' => $carrierNumber,
				'profit_center' => $profitCenter,
				'number' => $number
			);
			
			$success = $this->CarrierProviderNumber->save($saveData);
			
			$this->set('json', array('success' => ($success !== false), 'id' => $this->CarrierProviderNumber->id));
		}
		
		/**
		 * Remove a provider.
		 * @param int $id The ID of the record to remove.
		 */
		function json_removeProvider($id)
		{
			$success = $this->CarrierProviderNumber->delete($id);
			
			$this->set('json', array('success' => $success));
		}
	}
?>