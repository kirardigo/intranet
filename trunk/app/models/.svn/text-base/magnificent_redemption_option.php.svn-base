<?php
	class MagnificentRedemptionOption extends AppModel
	{
		var $displayField = 'description';
		
		var $validate = array(
			'description' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The description must be specified.'
				)
			),
			'value' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The value must be specified.'
				)
			)
		);
		
		/**
		 * Return a list of currently available options.
		 * @param int $value If set, only show items below this value.
		 * @return array The resultset.
		 */
		function getAvailable($value = null)
		{
			$conditions['is_active'] = 1;
			
			if ($value !== null)
			{
				$conditions['value <='] = $value;
			}
			
			$data = $this->find('all', array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array(
					'value',
					'description'
				)
			));
			
			$output = array();
			
			foreach ($data as $row)
			{
				$item = $row['MagnificentRedemptionOption'];
				$output[$item['id']] = "{$item['description']} ({$item['value']})";
			}
			
			return $output;
		}
	}
?>