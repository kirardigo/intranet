<?php
	class Physician extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05AJ';
		
		var $actsAs = array('Indexable', 'Defraggable');
		
		var $validate = array(
			'name' => array(
				'certification' => array(
					'rule' => '_validateCertification',
					'message' => 'Is a M.D., D.O., D.C., CNP, PAC or LAB?'
				),
				'required' => array(
					'rule' => array('minLength', 2),
					'message' => 'The name does not meet minimum length requirements.'
				)
			),
			'unique_identification_number' => array(
				'required' => array(
					'rule' => '_validateUPIN',
					'message' => 'UPIN must be 6 characters without spaces if defined.'
				)
			),
			'national_provider_identification_number' => array(
				'required' => array(
					'rule' => '_validateNPI',
					'message' => 'NPI must be 10 digits.'
				)
			),
			'license_number' => array(
				'required' => array(
					'rule' => '_validateLicense',
					'message' => 'License must be 8 characters without spaces or punctuation.'
				)
			)
		);
		
		/**
		 * Ensure that proper certification abbreviation shows up in name.
		 */
		function _validateCertification($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			$validCertifications = array(
				' M.D.,',
				' D.O.,',
				' D.C.,',
				' CNP,',
				' PAC,',
				' LAB,'
			);
			
			foreach ($validCertifications as $certification)
			{
				if (strpos($value, $certification) !== false)
				{
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * Ensure that UPIN is 6 characters without spaces or blank.
		 */
		function _validateUPIN($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			if ($value == '')
			{
				return true;
			}
			if (strlen($value) == 6 && strpos($value, ' ') == false)
			{
				return true;
			}
			
			return false;
		}
		
		/**
		 * Ensure that the NPI number is 10 digits.
		 */
		function _validateNPI($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			if (strlen($value) == 10 && is_numeric($value))
			{
				return true;
			}
			
			return false;
		}
		
		/**
		 * Ensure that the License number is 8 characters without spaces or punctuation.
		 */
		function _validateLicense($check)
		{
			$value = array_values($check);
			$value = $value[0];
			
			if (strlen($value) == 8 && preg_match('/[A-Z0-9]{8}/i', $value))
			{
				return true;
			}
			
			return false;
		}
		
		/**
		 * Method to find the next available physician number based on the last name.
		 * @param string $lastName The last name of the new physician.
		 * @return string The new physician number to use.
		 */
		function getSequenceNumber($lastName)
		{
			$lastName = strtoupper($lastName);
			
			$numbers = $this->find('all', array(
				'fields' => array('physician_number'),
				'conditions' => array('physician_number LIKE' => substr($lastName, 0, 2) . '%'),
				'order' => 'physician_number DESC'
			));
			
			$numberArray = Set::extract('/Physician/physician_number', $numbers);
			$match = '';
			
			//ignore manually assigned records that don't follow the numbering sequence
			//when looking for the last one used
			foreach ($numberArray as $record)
			{
				if (preg_match('/' . substr($lastName, 0, 2) . '[0-9]{2}/i', $record))
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
					$code = substr($lastName, 0, 1) . sprintf('%03d', $i);
					
					$result = $this->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'physician_number' => $code
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
					throw new Exception("Could not find an available sequence number for this physician.");
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