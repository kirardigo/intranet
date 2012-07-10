<?php
	class ChainableBehavior extends ModelBehavior
	{
		// Default value of key for chaining behavior
		var $_defaults = array(
			'chainField' => 'next_record_pointer',	// Specify which field points to the next record
			'ownerModel' => null,					// Specify the model that points to chain head
			'ownerField' => null,					// Specify the field containing the chain head pointer
			'sortOrder' => 'id',					// Specify a sort order when adding records to chain
			'sortDirection' => 'DESC',				// Specify the sort direction
			'saveViaFilepro' => false,				// Specify whether to insert/update/delete via filePro for FU05 files
			'unchainedIndexes' => array()			// Specify an array of fields that should have indexes built that DON'T follow the chains. Only takes effect if the Indexable behavior is also on the model.
		);
		
		/**
		 * Initialize the behavior.
		 * @param object &$model Reference to current model.
		 * @param array $config Array of initialization data.
		 */
		function setup(&$model, $config = array())
		{
			if (!is_array($config))
			{
				$config = array('type' => $config);
			}
			
			$settings = array_merge($this->_defaults, $config);
			
			// Ensure behavior has been appropriately configured.
			if ($settings['ownerModel'] == null || $settings['ownerField'] == null)
			{
				die("Must configure model & field that point to start of pointer chain.");
			}
			
			// Behaviors are singletons so settings array needs indexed by model
			$this->settings[$model->alias] = $settings;
		}
		
		/**
		 * Before delete callback
		 * @param object $model model using this behavior
		 * @param boolean $cascade If true records that depend on this record will also be deleted
		 * @return boolean True if the operation should continue, false if it should abort
		 */
		function beforeDelete(&$model, $cascade = true)
		{
			extract($this->settings[$model->alias]);
			
			$parentClass = ClassRegistry::init($ownerModel);
			
			// Intermediate steps may effect this, so cache value
			$currentRecord = $model->id;
			
			// Setup values to rebuild the chain in reverse
			$reverseWalkID = $currentRecord;
			
			// Loop to find the parent record (the parent model record that points to the 
			// head of the chain) for this chain
			while ($reverseWalkID !== false)
			{
				$currentHeadOfChain = $reverseWalkID;
				$reverseWalkID = $model->field('id', array($chainField => $reverseWalkID));
			}
			
			$parentRecord = $parentClass->field('id', array($ownerField => $currentHeadOfChain));
			
			// We can only lock if we found a parent record
			if ($parentRecord !== false)
			{
				if (!$parentClass->lock($parentRecord))
				{
					return false;
				}
			}
			
			// Fetch the id that record to be deleted points to
			$nextRecord = $model->field($chainField, array('id' => $currentRecord));
			
			// Fetch the id of the record that points to the record to be deleted
			$previousRecord = $model->field('id', array($chainField => $currentRecord));
			
			// If head of chain, set parent to point to next record
			if ($previousRecord === false)
			{
				if ($parentRecord !== false)
				{
					$saveData[$ownerModel] = array(
						'id' => $parentRecord,
						$ownerField => $nextRecord
					);
					
					$parentClass->save($saveData);
				}
			}
			// Else assign chain field of previous record to point to next record
			else
			{
				$saveData[$model->alias] = array(
					'id' => $previousRecord,
					$chainField => $nextRecord
				);
				
				$model->save($saveData);
			}
			
			// Reset model information
			$model->create();
			$model->id = $currentRecord;
			
			if ($parentRecord !== false)
			{
				$parentClass->unlock($parentRecord);
			}
			
			return true;
		}
		
		/**
		 * Add a record to the pointer chain referred to by the parent record.
		 * @param int $parentID The ID of the parent model record that points to the head of the chain.
		 * @param array $data An array of typical save data.
		 * @return bool Returns false on error.
		 */
		function _append(&$model, $parentID, $data)
		{
			extract($this->settings[$model->alias]);
			
			$parentClass = ClassRegistry::init($ownerModel);
			
			if (!$parentClass->lock($parentID))
			{
				return false;
			}
			
			try
			{
				// Grab old start of chain and have new record point to it
				$startOfChain = $parentClass->field($ownerField, array('id' => $parentID));
				$data[$model->alias][$chainField] = $startOfChain;
				
				// Save the chainable record
				$model->create();
				
				if ($saveViaFilepro)
				{
					if (!$model->saveViaFilepro($data))
					{
						throw new Exception('Could not save the chain record');
					}
				}
				else
				{
					if (!$model->save($data))
					{
						throw new Exception('Could not save the chain record');
					}
				}
				
				// If successfully added a new record, have the parent class point to it
				$saveData[$ownerModel] = array(
					'id' => $parentID,
					$ownerField => $model->id
				);
				
				$parentClass->create();
				if (!$parentClass->save($saveData))
				{
					throw new Exception('Could not save the parent record');
				}
			}
			catch (Exception $ex)
			{
				$parentClass->unlock($parentID);
				return false;
			}
			
			$parentClass->unlock($parentID);
			return true;
		}
		
		/**
		 * Insert record at a conditional point in the chain based on sort order.
		 * @param int $parentID The ID of the parent model record that points to the head of the chain.
		 * @param array $data An array of typical save data.
		 * @return bool Returns false on error.
		 */
		function _insertAt(&$model, $parentID, $data)
		{
			extract($this->settings[$model->alias]);
			
			$parentClass = ClassRegistry::init($ownerModel);
			
			if (!$parentClass->lock($parentID))
			{
				return false;
			}
			
			try
			{
				$startOfChain = $parentClass->field($ownerField, array('id' => $parentID));
				$schema = $model->schema();
				$db = ConnectionManager::getDataSource('fu05');
				
				// Setup to start with the first record in the chain
				$currentRecord[$model->alias][$chainField] = $startOfChain;
				$previousRecord = false;
				
				// Loop through the chain
				while ($currentRecord !== false && $currentRecord[$model->alias][$chainField] != 0)
				{
					$currentRecord = $model->find('first', array(
						'contain' => array(),
						'fields' => array(
							'id',
							$chainField,
							$sortOrder
						),
						'conditions' => array(
							'id' => $currentRecord[$model->alias][$chainField]
						)
					));
					
					// Represents an unexpected break in the chain
					if ($currentRecord === false)
					{
						throw new Exception('Unexpected break in the chain');
					}
					
					if ($schema[$sortOrder]['type'] == 'date')
					{
						$data[$model->alias][$sortOrder] = databaseDate($data[$model->alias][$sortOrder]);
						$currentRecord[$model->alias][$sortOrder] = databaseDate($currentRecord[$model->alias][$sortOrder]);
					}
					
					// If new record should be inserted prior to current element
					if ($this->_shouldInsertHere($db->_comparableValue($data[$model->alias][$sortOrder], $schema[$sortOrder]['type']), $db->_comparableValue($currentRecord[$model->alias][$sortOrder], $schema[$sortOrder]['type']), $sortDirection))
					{
						// Point the new record to the current record and save
						$data[$model->alias][$chainField] = $currentRecord[$model->alias]['id'];
						
						$model->create();
						
						if ($saveViaFilepro)
						{
							if (!$model->saveViaFilepro($data))
							{
								throw new Exception('Could not save the chain record');
							}
						}
						else
						{
							if (!$model->save($data))
							{
								throw new Exception('Could not save the chain record');
							}
						}
						
						$newRecordID = $model->id;
						
						// Handle saving first record in chain by updating owner model
						if ($previousRecord === false)
						{
							$saveData[$ownerModel] = array(
								'id' => $parentID,
								$ownerField => $newRecordID
							);
							
							$parentClass->create();
							if (!$parentClass->save($saveData))
							{
								throw new Exception('Could not save the parent record');
							}
						}
						// Handle saving record in the middle of the chain
						else
						{
							$previousRecord[$model->alias][$chainField] = $newRecordID;
							
							$model->create();
							if (!$model->save($previousRecord))
							{
								throw new Exception('Could not save the previous record');
							}
						}
						
						// Return references to new record and immediate neighbors in the chain
						$parentClass->unlock($parentID);
						return array(
							'before' => (isset($previousRecord[$model->alias]['id'])) ? $previousRecord[$model->alias]['id'] : false,
							'current' => $newRecordID,
							'after' => $currentRecord[$model->alias]['id']
						);
					}
					
					// Maintain information of previous record
					$previousRecord = $currentRecord;
				}
				
				if ($previousRecord !== false)
				{
					// If still not set, it should be the last element in the chain
					$data[$model->alias][$chainField] = 0;
					
					$model->create();
					
					if ($saveViaFilepro)
					{
						if (!$model->saveViaFilepro($data))
						{
							throw new Exception('Could not save the chain record');
						}
					}
					else
					{
						if (!$model->save($data))
						{
							throw new Exception('Could not save the chain record');
						}
					}
					
					$newRecordID = $model->id;
					
					// Update the previous record to point to it
					$previousRecord[$model->alias][$chainField] = $newRecordID;
					
					$model->create();
					if (!$model->save($previousRecord))
					{
						throw new Exception('Could not save the parent record');
					}
					
					// Return references to new record and immediate neighbors in the chain
					$parentClass->unlock($parentID);
					return array(
						'before' => $previousRecord[$model->alias]['id'],
						'current' => $newRecordID,
						'after' => false
					);
				}
				else
				{
					// No elements were in the chain, save record
					$model->create();
					
					if ($saveViaFilepro)
					{
						if (!$model->saveViaFilepro($data))
						{
							throw new Exception('Could not save the chain record');
						}
					}
					else
					{
						if (!$model->save($data))
						{
							throw new Exception('Could not save the chain record');
						}
					}
					
					$newRecordID = $model->id;
					
					// Then update the parent model
					$saveData[$ownerModel] = array(
						'id' => $parentID,
						$ownerField => $newRecordID
					);
					
					$parentClass->create();
					if (!$parentClass->save($saveData))
					{
						throw new Exception('Could not save the parent record');
					}
					
					// Return references to new record and immediate neighbors in the chain
					$parentClass->unlock($parentID);
					return array(
						'before' => false,
						'current' => $newRecordID,
						'after' => false
					);
				}
			}
			catch (Exception $ex)
			{
				$parentClass->unlock($parentID);
				return false;
			}
		}
		
		/**
		 * Determine whether a record belongs before value in chain based on sort direction.
		 * @param int $parentID The ID of the parent model record that points to the head of the chain.
		 * @param mixed $newRecordValue The sort value from the new record.
		 * @param mixed $chainRecordValue The sort value from the chain record.
		 * @param string $sortDirection The SQL sort direction.
		 * @return bool
		 */
		function _shouldInsertHere($newRecordValue, $chainRecordValue, $sortDirection)
		{
			// TODO: We know that this is case sensitive and that if we ever perform mid-chain
			//  insertions for strings, we would need to modify this code accordingly.
			if (strtolower($sortDirection) == 'desc')
			{
				if ($newRecordValue >= $chainRecordValue)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				if ($newRecordValue < $chainRecordValue)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		/**
		 * A simple method that will add a record to the chain at the proper spot.
		 * @param int $parentID The ID of the parent model record that points to the head of the chain.
		 * @param array $data An array of typical save data.
		 * @return bool Returns false on error.
		 */
		function addToChain(&$model, $parentID, $data)
		{
			extract($this->settings[$model->alias]);
			
			if ($sortOrder != 'id' || strtolower($sortDirection) != 'desc')
			{
				$returnValue = $this->_insertAt($model, $parentID, $data);
			}
			else
			{
				$returnValue = $this->_append($model, $parentID, $data);
			}
			
			return $returnValue;
		}
		
		/**
		 * Delete a chain of records (Usually invoked by ChainOwner behavior)
		 * @param object &$model Reference to current model.
		 * @param int $parentID The ID of the parent model record that points to the head of the chain.
		 */
		function deleteChain(&$model, $parentID)
		{
			extract($this->settings[$model->alias]);
			
			$parentModel = ClassRegistry::init($ownerModel);
			
			if (!$parentModel->lock($parentID))
			{
				return false;
			}
			
			// Fetch the starting ID of the chain
			$startOfChain = $parentModel->field($ownerField, array('id' => $parentID));
			
			$chainedRecords = $this->_getChainIDs($model, $startOfChain);
			
			// Delete all records from the chain
			$result = $model->deleteAll(array(
				'id' => $chainedRecords
			));
			
			// Zero out the parent pointer if items were deleted 
			if ($result)
			{
				$parentModel->create();
				$parentModel->save(array($ownerModel => array(
					'id' => $parentID,
					$ownerField => 0
				)));
			}
			
			$parentModel->unlock($parentID);
			
			return $result;
		}
		
		/**
		 * Extension to the find function in the parent class, allows for traversing pointer chains.
		 * @param object &$model Reference to current model.
		 * @param int $id The ID of the starting record of a chain.
		 * @param array $query A query in the same format as a Model->find() array.
		 */
		function findChain(&$model, $id, $query = array())
		{
			extract($this->settings[$model->alias]);
			
			// We cannot chain without the model specifying a field to chain from.
			if ($chainField == null)
			{
				return false;
			}
			
			// Find IDs in the chain and then do a find('all') against those.
			// This will allow all values in the chain to be searched even if
			// a particular element in the chain does not match the criteria.
			$records = $this->_getChainIDs($model, $id);
			$query['conditions']["{$model->alias}.id"] = $records;
			
			// If there's nothing in the chain, we don't need to do a query
			if (empty($records))
			{
				return array();
			}
			
			// Default the sort order to follow the chain.
			$query['order'] = ifset($query['order'], array("{$sortOrder} {$sortDirection}", "id {$sortDirection}"));
			
			return $model->find('all', $query);
		}
		
		/**
		 * Return the id list of all records in the chain.
		 * @param object &$model Reference to current model.
		 * @param array $startingID The ID of the starting record in the chain.
		 * @return array An array of IDs in the chain.
		 */
		function _getChainIDs(&$model, $startingID)
		{
			extract($this->settings[$model->alias]);
			
			$results = array();
			
			// Only fetch the ids & the pointer at this point
			$fields = array(
				'contain' => array(),
				'fields' => array(
					'id',
					$chainField
				),
				'conditions' => array(
					'id' => $startingID
				)
			);
			
			// Fetch the first record
			$data = $model->find('first', $fields);
			
			if ($data !== false)
			{
				$results[] = $data[$model->alias]['id'];
			
				// Retrieve next record until the chain ends.
				while ($data[$model->alias][$chainField] != 0)
				{
					$fields['conditions']['id'] = $data[$model->alias][$chainField];
					
					$data = $model->find('first', $fields);
				
					if ($data !== false)
					{
						//watch out for cyclical references - if we find one we just stop and
						//return the chain that we have so far
						if (in_array($data[$model->alias]['id'], $results))
						{
							try
							{
								$this->log("Cyclical reference detected in {$model->alias}, record {$data[$model->alias]['id']}!");
							}
							catch (Exception $ex) {}
							
							break;
						}
						
						$results[] = $data[$model->alias]['id'];
					}
				}
			}
			
			return $results;
		}
	}
?>