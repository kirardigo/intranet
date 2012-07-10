<?php
	/**
	 * Behavior for FU05 models to allow them to lock and unlock segments of their DAT file.
	 */
	class LockableBehavior extends ModelBehavior
	{
		/**
		 * Initialize the behavior.
		 * @param object &$model Reference to current model.
		 * @param array $config Array of initialization data.
		 */
		function setup(&$model, $config = array())
		{
			$this->settings[$model->alias] = array();
		}
		
		/**
		 * Locks a particular record for a model.
		 * @param (implied) The model the behavior is attached to.
		 * @param int $id The ID of the record to lock.
		 * @return boolean True if the lock was acquired, false otherwise.
		 */
		function lock(&$model, $id)
		{
			if ($model->useDbConfig != 'fu05')
			{
				throw new Exception('The Lockable behavior only works with FU05 models!');
			}
			
			$db = ConnectionManager::getDataSource($model->useDbConfig);
			$path = $db->describe($model, 'data_path');
			
			//open the file if necessary
			if (!isset($this->settings[$model->alias]['file']))
			{
				$f = dio_open($path, O_RDWR);
				
				if ($f === false)
				{
					return false;
				}
				
				$this->settings[$model->alias]['file'] = $f;
			}
			
			$file = $this->settings[$model->alias]['file'];
			$length = $db->describe($model, 'record_length');
			
			//lock the record if it isn't already
			if (!$this->isLocked($model, $id))
			{
				if (!$db->_lockRecord($model, $file, $path, $id, $length))
				{
					return false;
				}
				
				//start a reference count
				$this->settings[$model->alias]["lock_{$id}"] = 1;
			}
			else
			{
				//if it's already locked just increment the reference count
				$this->settings[$model->alias]["lock_{$id}"] += 1;
			}
			
			return true;
		}
		
		/**
		 * Checks to see if a given record is already locked.
		 * @param (implied) The model the behavior is attached to.
		 * @param int $id The ID of the record to check.
		 * @return True if the record is already locked, false otherwise.
		 */
		function isLocked(&$model, $id)
		{
			return isset($this->settings[$model->alias]["lock_{$id}"]);
		}
		
		/**
		 * Unlocks a particular record for a model.
		 * @param (implied) The model the behavior is attached to.
		 * @param int $id The ID of the record to unlock.
		 */
		function unlock(&$model, $id)
		{
			//unlock the record if necessary
			if (isset($this->settings[$model->alias]["lock_{$id}"]))
			{
				//decrement our reference count
				$this->settings[$model->alias]["lock_{$id}"] -= 1;
				
				//if there are still more references to this record being locked,
				//just short circuit because we don't want to release the lock yet
				if ($this->settings[$model->alias]["lock_{$id}"] > 0)
				{
					return;
				}
				
				$db = ConnectionManager::getDataSource($model->useDbConfig);
				$length = $db->describe($model, 'record_length');
				$file = $this->settings[$model->alias]['file'];
				
				//clear our reference count (we have to do this before trying to unlock the record
				//because the driver will inspect Lockable models to respect existing locks)
				unset($this->settings[$model->alias]["lock_{$id}"]);
				
				//now truly unlock the record
				$db->_unlockRecord($model, $file, $id, $length);
			}
		}
	}
?>