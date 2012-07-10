<?php
	class Carrier extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05BF';
		
		var $actsAs = array('Indexable', 'Defraggable');
		
		/**
		 * Method to find the next available carrier number based on the carrier name.
		 * @param string $name The carrier name.
		 * @return string The new carrier number to use.
		 */
		function getSequenceNumber($name)
		{
			$lastName = strtoupper($name);
			
			$numbers = $this->find('all', array(
				'fields' => array('carrier_number'),
				'conditions' => array('carrier_number LIKE' => substr($name, 0, 2) . '%'),
				'order' => 'carrier_number DESC'
			));
			
			$numberArray = Set::extract('/Carrier/carrier_number', $numbers);
			$match = '';
			
			//ignore manually assigned records that don't follow the numbering sequence
			//when looking for the last one used
			foreach ($numberArray as $record)
			{
				if (preg_match('/' . substr($name, 0, 2) . '[0-9]{2}/i', $record))
				{
					$match = $record;
					break;
				}
			}
			
			// if we have already issued the 99th physician number for that prefix,
			// switch to 1 letter + 3 digits. we HAVE TO scan through testing for an
			// available number because of the way these cases were handled manually.
			if (substr($match, 2, 2) == "99")
			{
				$i = 1;
				$available = false;
				
				while(!$available && $i < 1000)
				{
					$code = substr($name, 0, 1) . sprintf('%03d', $i);
					
					$result = $this->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'carrier_number' => $code
						)
					));
					
					if ($result === false)
					{
						$available = true;
					}
					
					$i++;
				}
				
				if (!$available)
				{
					throw new Exception("Could not find an available sequence number for this carrier.");
				}
			}
			else
			{
				$code = substr($match, 0, 2) . sprintf('%02d', (int)substr($match, 2, 2) + 1);
			}
			
			return $code;
		}
	}
?>