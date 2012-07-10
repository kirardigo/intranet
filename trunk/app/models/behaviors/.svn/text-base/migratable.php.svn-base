<?php

	/**
	 * This behavior is used mostly by low-level utilities that need a way to migrate records by their "primary key", so to speak. It can be used
	 * against both FU05 and filePro models. The behavior allows the user to migrate ALL records that match a given key value to another key value. Additionally,
	 * the user can specify other fields to change on every matching record at the same time (sort of like a batch update).
	 *
	 * For FU05 models that are chainable, if an unchained index exists on the key field, that index will be used instead of a standard (chained) index on that
	 * field. That way, even records that are currently orphaned from the chain will still be migrated to have the new key value.
	 *
	 * For filePro models, if an index exists on the key field being migrated, that index will be chosen automatically (as opposed to normally having to know and specify
	 * the index you wish to use).
	 *
	 * Note that for chainable models, once a migration has been run, in most cases the key field was what caused them to be placed in that specific chain. That means
	 * those records are now in the WRONG CHAIN. We will have other utilities that can correct the chains, but just be aware that this behavior does not automatically
	 * correct the chains.
	 *
	 * Due to the implications of the warnings above, this behavior's migrate method should probably never be run during business hours. You've been warned.
	 */
	class MigratableBehavior extends ModelBehavior
	{
		//defaults for the behavior
		var $_defaults = array(
			'key' => 'account_number',					//the key field that is what is primarily (and required to be changed) used for migration purposes.
			'fields' => array()							//any other fields that are allowed to be migrated as part of the migration process.
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
			
			//make sure the key field is in the list of fields allowed to be migrated
			if (!in_array($this->settings[$model->alias]['key'], $this->settings[$model->alias]['fields']))
			{
				$this->settings[$model->alias]['fields'][] = $this->settings[$model->alias]['key'];
			}
		}
		
		/**
		 * Migrates the key field on all records for the model, plus any optional, additional fields that should be migrated at the same time.
		 * @param object $model The model being operated on.
		 * @param mixed $key The value for the key field (as defined in the behavior settings for the model) to search for and migrate.
		 * @param array $to An array of field => value pairs, one of which must be the key field and the value that it should be changed to. The
		 * rest of the fields in the array are up to the user as to what else to optionally update during the migration process. Note that the
		 * key field MUST be changed during migration or else the process will simply return false and not migrate (in other words, this isn't a 
		 * general purpose update utility - it's solely used for migrating a key field to something else).
		 * @return mixed The number of records migrated on success, false otherwise.
		 */
		function migrate(&$model, $key, $to)
		{
			//rip out from the passed data only those fields which the model has defined to allow to migrate
			$to = array_intersect_key($to, array_flip($this->settings[$model->alias]['fields']));
			
			//make sure the key field is in the data to migrate
			if (!array_key_exists($this->settings[$model->alias]['key'], $to))
			{
				return false;
			}
			
			//we cannot migrate id numbers
			if (array_key_exists('id', $to))
			{
				return false;
			}
			
			//we're going to search for records with the specified value for the key field until we've migrated all of them
			$query = array(
				'fields' => array('id'), 
				'conditions' => array("{$this->settings[$model->alias]['key']}" => $key), 
				'contain' => array()
			);
			
			//for an FU05 model that has an unchained index on the key field we're changing, we'll use that index to change data so we get the orphaned records too
			if ($model->useDbConfig == 'fu05' && $model->Behaviors->enabled('Chainable') && in_array($this->settings[$model->alias]['key'], $model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes']))
			{
				$query['unchainedIndex'] = $this->settings[$model->alias]['key'];
			}
			else if ($model->useDbConfig == 'filepro')
			{
				//for filepro models, we'll choose the proper index to use if one exists for the key field
				$db = ConnectionManager::getDataSource('filepro');
				$indexes = $db->describe($model, 'indexes');
				
				//look at each index
				foreach ($indexes as $name => $index)
				{
					//if it's supported and the first field of the index is our key field, go ahead and use it
					if ($index->isSupported && $index->header['sort_info'][0]['field_name'] == $this->settings[$model->alias]['key'])
					{
						//we have to rip the 'index.' off of the index name so we only specify the index letter (i.e. 'A' or 'B').
						$query['index'] = array_pop(explode('.', $name));
					}
				}				
			}

//TODO - comment out once verified
//pr(Set::flatten($query));
//pr(Set::flatten(array($model->alias => array_merge(array('id' => '[id]'), $to))));
//$model->find('first', $query);
//return true;
			
			try
			{
				$migrated = 0;
				
				//figure out the save method to use (u05 models we save via filepro so users in filepro can see the changes right away in
				//case the migrated fields are indexed in filepro)
				$saveMethod = $model->useDbConfig == 'fu05' ? 'saveViaFilepro' : 'save';
				
				//keep searching until we run out
				while (($record = $model->find('first', $query)) !== false)
				{
					//update the record
					if ($model->$saveMethod(array($model->alias => array_merge(array('id' => $record[$model->alias]['id']), $to))) === false)
					{
						return false;
					}
					
					$migrated++;
				}
			}
			catch (Exception $ex)
			{
				return false;
			}
			
			return $migrated;
		}
	}
?>