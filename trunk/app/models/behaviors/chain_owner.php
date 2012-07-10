<?php
	/**
	 * Behavior for models that contain pointers to a chain.
	 * The syntax is as follows:
	 *
	 * var $actsAs = array(
	 * 		'ChainOwner' => array(
	 * 			'Chain Model' => 'pointer field in owner'
	 * 		)
	 * );
	 * 
	 * You can have multiple chain models. As an example, this is 
	 * the Customer chain owner definition:
	 *
	 * 		'ChainOwner' => array(
	 * 			'CustomerCarrier' => 'carrier_pointer',
	 *			'Invoice' => 'invoice_pointer',
	 *			'Transaction' => 'transaction_pointer'
	 * 		)
	 */
	class ChainOwnerBehavior extends ModelBehavior
	{
		/**
		 * Initialize the behavior.
		 * @param object &$model Reference to current model.
		 * @param array $config Array of initialization data.
		 */
		function setup(&$model, $config = array())
		{
			// ChainOwner will always need to be lockable, even if not explicitly specified
			if (!$model->Behaviors->enabled('Lockable'))
			{
				$model->Behaviors->attach('Lockable');
			}
			
			$this->settings[$model->alias] = array();
			
			foreach ($config as $modelName => $field)
			{
				$this->settings[$model->alias][$modelName] = $field;
			}
		}
		
		/**
		 * Before delete callback
		 * @param object $model model using this behavior
		 * @param boolean $cascade If true records that depend on this record will also be deleted
		 * @return boolean True if the operation should continue, false if it should abort
		 */
		function beforeDelete(&$model, $cascade = true)
		{
			$success = true;
			
			if (!$model->lock($model->id))
			{
				return false;
			}
			
			// Loop through all chains that this model points to
			foreach ($this->settings[$model->alias] as $modelName => $field)
			{
				// Delete all records in the chain, bitwise-and the results to return value
				$chainClass = ClassRegistry::init($modelName);
				$success &= $chainClass->deleteChain($model->id);
				
				// If records were not deleted, unlock and return
				if (!$success)
				{
					$model->unlock($model->id);
					return false;
				}
			}
			
			$model->unlock($model->id);
			return true;
		}
	}
?>