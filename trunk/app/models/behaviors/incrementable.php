<?php

	/**
	 * This behavior is for filepro models that have fields like a "next free number" or "last number used" field
	 * where we need the ability to pull the number, increment it, and save it back all as one "transaction".
	 *
	 * To use the behavior, you have to define what fields can be incremented. Ex:
	 *
	 * $actsAs = array(
	 * 		'Incrementable' => array(
	 * 			'fields' => array(
	 * 				'name_of_field' => array(
	 * 					'prefixLength' => 0,
	 * 					'returnIncremented' => false
	 * 				)
	 *			)
	 * 		);
	 * );
	 *
	 * The 'fields' array should be indexed by field names. The keys in the fields array are:
	 * 		'prefixLength' 		- This is the length of the value that should be considered a prefix that will not
	 * 							  be considered when incrementing the value. For example, "A123" with a prefixLength of 1
	 * 							  would be incremented to "A124". If there is no prefix, this should be set to zero.
	 * 		'returnIncremented' - This is to change the behavior of what is returned when incrementing the value. If false,
	 * 							  the value returned is the value before it is incremented. This would be for situations where
	 * 							  the value stored in the record is treated like a "next free number". If this is true, the 
	 * 							  value returned is the value after it was incremented. This would be for situations where the
	 * 							  value stored in the record is treated like a "last number used".
	 *
	 * Neither key in the fields array for an individual field is required. If ommitted, prefixLength is zero, and returnIncremented
	 * is false. So a very simple setup would be:
	 *
	 * $actsAs = array(
	 * 		'Incrementable' => array('fields' => array('name_of_field'));
	 * );
	 *
	 */
	class IncrementableBehavior extends ModelBehavior
	{
		//defaults for the behavior
		var $_defaults = array(
			'fields' => array() //these are the definitions of the fields that can be incremented for the model
		);
		
		/**
		 * Initializes the behavior when attaching to a model.
		 * @param object $model The model being configured.
		 * @param array $config The initialization data.
		 */
		function setup(&$model, $config = array())
		{
			$config = array_merge($this->_defaults, $config);	
			$fields = array_keys($config['fields']);
				
			foreach ($fields as $field)
			{
				//if we have no key but just a value (i.e. they just gave a field name), move the name into a key
				if (is_numeric($field))
				{
					$name = $config['fields'][$field];
					$config['fields'][$name] = array();
					unset($config['fields'][$field]);
					$field = $name;
				}

				//default prefix length if not specified
				if (!isset($config['fields'][$field]['prefixLength']))
				{
					$config['fields'][$field]['prefixLength'] = 0;
				}
				
				//default return incremented if not specified
				if (!isset($config['fields'][$field]['returnIncremented']))
				{
					$config['fields'][$field]['returnIncremented'] = false;
				}
			}
			
			//behaviors are singletons so settings need indexed by model
			$this->settings[$model->alias] = $config;
		}
		
		/**
		 * Increments one of the incrementable fields in the model.
		 * @param object $model (implied) The model the behavior is attached to.
		 * @param string $field The name of the field to increment (must be defined in the behavior settings).
		 * @param int $id The ID of the record to increment. By default, this is record 1. Most of the time, next free
		 * numbers are stored in a file that only contains one record.
		 * @return mixed The pre or post-incremented number, depending on the behavior settings for the field, or false if 
		 * the value couldn't be incremented.
		 */
		function increment(&$model, $field, $id = 1)
		{
			//this behavior is only for filepro models
			if ($model->useDbConfig != 'filepro')
			{
				return false;
			}
			
			//make sure the field has been configured
			if (!array_key_exists($field, $this->settings[$model->alias]['fields']))
			{
				return false;
			}
			
			$db = ConnectionManager::getDataSource($model->useDbConfig);
			return $db->increment($model, $id, $field, $this->settings[$model->alias]['fields'][$field]['prefixLength'], $this->settings[$model->alias]['fields'][$field]['returnIncremented']);
		}
	}
?>