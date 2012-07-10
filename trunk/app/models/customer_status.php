<?php
	class CustomerStatus extends AppModel
	{
		/**
		 * Gets a list of all statuses indexed by code and with the code in the description.
		 * @return array An array of statuses.
		 */
		function getList()
		{
			$data = $this->find('list', array('fields' => array('code', 'description'), 'order' => 'code'));
			
			foreach ($data as $key => $value)
			{
				$data[$key] = $key . ' - ' . $value;
			}
			
			return $data;
		}
	}
?>