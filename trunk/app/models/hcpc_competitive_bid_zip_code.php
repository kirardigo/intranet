<?php
	class HcpcCompetitiveBidZipCode extends AppModel
	{
		var $validate = array(
			'bid_number' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'message' => 'The bid number must be numeric.'
				),
				'unique' => array(
					'rule' => '_validateUniqueRecord',
					'message' => 'This bid number and zip code combination is already in use.'
				)
			),
			'zip_code' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The zip code is required.'
				)
			)
		);
		
		/**
		 * Validates the bid and assigned carrier together to make sure a record with the same combination doesn't already exist.
		 * @access private
		 * @param $data The data for the field.
		 * @return True if validation is successful, false otherwise.
		 */
		function _validateUniqueRecord($data)
		{
			//set up the conditions to find a record with the same bid/carrier
			$conditions = array(
				'bid_number' => $this->data[$this->alias]['bid_number'], 
				'zip_code' => $this->data[$this->alias]['zip_code']
			);
			
			//if this is for an update, exclude the record itself from the search
			if (!empty($this->id))
			{
				$conditions['id <>'] = $this->id;
			}

			return $this->field('id', $conditions) === false;
		}
	}
?>