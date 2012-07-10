<?php

	/**
	 * This behavior is used to log any changes to an account number field in a model. The before and after values are stored, as well as a stack trace
	 * of the current process to be able to track down what is responsible for changing the account number.
	 */
	class CustomerAccountAuditableBehavior extends ModelBehavior
	{
		//defaults for the behavior
		var $_defaults = array(
			'field' => 'account_number'				//the name of the account number field in the model.
		);
		
		/**
		 * Initializes the behavior when attaching to a model.
		 * @param object $model The model being configured.
		 * @param array $config The initialization data.
		 */
		function setup(&$model, $config = array())
		{
			//behaviors are singletons so settings need indexed by model
			$this->settings[$model->alias] = array_merge($this->_defaults, $config);
		}
		
		/**
		 * Triggers automatically before the model is saved. This is where we log any changes to the customer account number.
		 */
		function beforeSave(&$model)
		{
			try
			{
				//see if the user has the account number set and it's for an update
				if ($model->id !== false && isset($model->data[$model->alias][$this->settings[$model->alias]['field']]))
				{
					//if so, look up the current account number
					$current = $model->field($this->settings[$model->alias]['field'], array('id' => $model->id));
					
					//if it's changing, log it
					if ($current !== false && $current != $model->data[$model->alias][$this->settings[$model->alias]['field']])
					{						
						$log = ClassRegistry::init('CustomerAccountAudit');
						
						//grab the stack trace
						$trace = debug_backtrace();
						
						//rip off the behavior part of the stack
						array_shift($trace);
						array_shift($trace);
						array_shift($trace);
						
						//turn it into plaintext
						$trace = implode("\n", Set::format($trace, '%s: %s() [line %s]', array('/file', '/function', '/line')));
						
						$log->create();
						$log->save(array('CustomerAccountAudit' => array(
							'model' => $model->alias,
							'record_id' => $model->id,
							'before' => $current, 
							'after' => $model->data[$model->alias][$this->settings[$model->alias]['field']], 
							'trace' => $trace
						)));
					}
				}
			}
			catch (Exception $ex) { /* swallow errors */ }
			
			return true;
		}
	}
?>