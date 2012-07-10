<?php
	/** this is needed for the signal handling we do for record locking in the FU05 driver. */
	declare(ticks = 1);

	uses('Inflector');
	uses('Folder');
	uses('Sanitize');
	
	/**
	 * DataSource to handle interaction with FU05 non-filePro files. To enable a model
	 * to use this data source, you must set up the model's $useDbConfig variable to use
	 * a data source that has been configured for this driver in your applications database.php
	 * file. That data source must have the following keys:
	 *
	 * 		map_file_path - This should be the absolute physical path to the location where the non-filePro
	 *						map files exist.
	 * 		index_model - This is the name of the model that will be used to perform queries against the MySQL
	 *					  database that contains the FU05 indexes.
	 * 		lock_model - This is the name of the model that will be used to create locks in the MySQL
	 *					 database to provide syncronization when writing via filepro.
	 *
	 * In your models, once configured to use this datasource, they must use the $useTable variable
	 * to specify the name of the table to use (ex. FU05AJ). Make sure the table name is in all-caps.
	 *
	 * The driver also supports a special syntax for belongsTo definitions. Typically, they look like this:
	 *
	 * var $belongsTo = array('Model' => array('foreignKey' => 'my_fk_field'));
	 *
	 * Since most of FU05 belongsTo relationships don't actually relate to the parent model's id (row number)
	 * field, we need a way to specify a different field in the parent model. So, the original syntax is still
	 * supported for regular cases, but now you can also specify a relationship like so:
	 *
	 * var $belongsTo = array('Model' => array('foreignKey' => array('field' => 'my_fk_field', 'parent_field' => 'parent_id_field')));
	 *
	 * The driver supports a special key in the find() array called 'chains'. This array essentially acts
	 * like a 'contain' for pointer chains. The chain array is essentially treated like a subquery to the original
	 * find() on each matching record in order to find one or more matching records in the specified chains. If 
	 * there is are nested conditions that are applicable to the chain, and no records in the chain matches, the original
	 * record will not be brought back either.
	 *
	 * Ex:
	 *
	 * 				$this->Customer->find('all', array(
	 *					'fields' => array('Customer.id', 'Customer.account_number'),
	 *					'conditions' => array('Customer.id' => array(10, 11, 12, 13)),
	 *					'chains' => array(
	 *						'CustomerCarrier' => array(
	 *							'fields' => array('carrier_number', 'claim_number', 'carrier_type', 'is_active', 'Carrier.address_1'),
	 *							'conditions' => array('CustomerCarrier.is_active' => 'Y', 'CustomerCarrier.carrier_type' => array('N', 'S')),
	 *							'contain' => array('Carrier'),
	 *							'order' => array('carrier_number')
	 *						),
	 *						'Transaction' => array('limit' => 1),
	 * 						'Invoice'
	 *					)
	 *				));
	 *
	 * As you can see, each chain specified can be treated exactly like a nested find() call with fields, conditions,
	 * contains, orders, limits, etc. You can also specify a chain model as a value (see 'Invoice' above in example) to
	 * simply pull all available records in the chain for each outer record.
	 *
	 * There is also one extra key that can be applied in the chain definition called 'required'. Normally, if you
	 * have conditions in the chain clause and there are no matching records in chain, the original record will not
	 * be brought back either. You can specify "'required' => false" to change the behavior to bring back only those
	 * records in the chain that match, but to still bring back the original record even if no records in the chain match.
	 *
	 * Finally, the driver also supports a key called 'unchainedIndex'. This lets you specify an explicit unchained index to use when searching
	 * on a chainable model. The value for this key should be the name of the field whose unchained index will be used to search. This is typically
	 * not something you'd want to use because you mostly want to make sure you search via chains, but we need the capability to do so for some of our utilities.
	 */
	class DboFu05 extends DataSource
	{
		/** Class description, used by Cake. */
		var $description = 'FU05 DataSource';
		
		/** Keeps track of the last inserted ID (record number) into a file. */
		var $_lastInsertID = null;
		var $operators = '/(=|>|>=|<|<=|<>|!=|LIKE|BETWEEN)$/i';
		
		/** This is the byte offset where filePro begins to lock files when it is updating a record. */
		var $fileproLockOffset = 1000000000;
		
		/** 
		 * These are used by our locking mechanism to state how many times to retry when a lock 
		 * cannot be acquired, as well as how long to wait between attempts.
		 */
		var $lockRetries = 60;
		var $lockWaitInterval = 500; //milliseconds
		
		const countField = '::count';
		const deletedCharacterOffset = 0;
		const chainableDeletedCharacterOffset = 4;
		const deletedRecordCharacter = "\0";
		const ifNullPattern = "/^ifnull\(([a-z0-9._]+),\s*(.+?)\)/i";
		
		/** These constants are used to control timeout periods when writing data via filepro */
		const fileproWriteRetries = 4;
		const fileproWriteInitialWaitPeriodMicroseconds = 200000;
		const fileproWritePeriodicWaitPeriodMicroseconds = 500000;
		
		/** The name of the shell script that kicks off filepro processing to write data via filepro. */
		var $fileproWriterScript = 'filepro_writer.sh';
		
		/** 
		 * This is the prefix that is placed in front of the name of the $useTable for a mirrored FU05 model that
		 * is used when writing via filepro.
		 */
		 const mirrorPrefix = 'z';
		 
		/** 
		 * This is the suffix that is appended to the name of the $useTable for a mirrored FU05 model that
		 * is used when writing via filepro.
		 */
		const mirrorSuffix = '_MIRROR';
		
		/** Keeps cached row counts from explain statements done on FU05 indexes stored in MySQL. */
		var $_explainCache = array();
		
		/** Keeps handles to open files. */
		var $_openFileCache = array();
		
		/** Column definitions for the driver - needed by Cake. */
		var $columns = array(
			//'primary_key' => array('name' => 'NOT NULL AUTO_INCREMENT'),
			'boolean' => array('name' => 'YN', 'limit' => '1'),
			'date' => array('name' => 'MDYY', 'format' => 'Y-m-d', 'formatter' => 'date'),
			'int' => array('name' => '.0', 'limit' => '11', 'formatter' => 'intval'),
			'float' => array('name' => '.N', 'formatter' => 'floatval'),
			'string' => array('name' => '*', 'limit' => '5000')
		);
		
		/** These are the extra fields that we use when creating a mirror for an FU05 file. The mirror is used to forward write requests (insert/update/delete) to our generated filepro scripts.  */
		var $mirrorFields = array(
			'mirror_transaction_success' => array(
				'length' => 1,
				'type' => 'YN'
			),
			'mirror_action_type' => array(
				'length' => 1,
				'type' => 'ALLUP'
			),
			'mirror_filepro_record_id' => array(
				'length' => 13,
				'type' => '.0'
			)
		);
		
		/**
		 * Constructor. 
		 */
		function __construct($config = array())
		{
			parent::__construct($config);
		}
		
		/**
		 * Destructor.
		 */
		function __destruct()
		{
			//clean up open file handles
			foreach ($this->_openFileCache as $handles)
			{
				foreach ($handles as $handle)
				{
					dio_close($handle);
				}
			}
			
			parent::__destruct();
		}
		
		/**
		 * Gets the absolute path to the map file for the FU05 file.
		 * @param object $model The database model to operate on.
		 * @return string The absolute path to the map file for the given model.
		 * @access private
		 */
		function _mapPath(&$model)
		{
			return $this->config['map_file_path'] . DS . $this->fullTableName($model) . DS . 'map';
		}
		
		/**
		 * Gets the model used to query the MySQL database containing the FU05 indexes.
		 * @return object The model used for FU05 indexes.
		 * @access private
		 */
		function _indexModel()
		{
			return ClassRegistry::init($this->config['index_model']);
		}
		
		/**
		 * Method that will return all available FU05 files.
		 * @inherited
		 * @param mixed $data Not used.
		 * @return array An array of FU05 files that are available on disk.
		 */
		function listSources($data = null)
		{
			$folder = new Folder($this->config['map_file_path']);
			return array_shift($folder->read());
		}
		
		/**
		 * Method (required, not inherited) that gets the full "table" name of the FU05 file.
		 * @param object $model The model to operate on.
		 * @param bool $quote Not used.
		 * @return The full name of the FU05 file.
		 */
		function fullTableName(&$model, $quote = true)
		{
			//some methods pass the model object, while others simply pass a string.
			//if it's an object, we'll convert the table that it uses to be the full name.
			if (is_object($model))
			{
				return $model->useTable;
			}
			
			return $model;
		}
		
		/**
		 * Method that gets the schema of the model. For FU05, this 
		 * is the information that is stored in a map file.
		 * @inherited
		 * @param object $model The model to describe.
		 * @param string $type NEW - This argument can be one of the following:
		 * 		null (default) - returns the fields from the map (behaves just like MySQL driver)
		 * 		all - returns a hash of data_path, record_length, and fields
		 *		fields - returns the same as passing null
		 *		data_path - returns the path to the DAT file for the table
		 * 		record_length  - returns the value of the record length of a single record
		 * 		indexes - returns an array of indexes, indexed by field name
		 * @return array The model schema.
		 */
		function describe(&$model, $type = null)
		{
			$cache = parent::describe($model);

			if ($cache !== null)
			{
				return ($type === null || $type == '' ? $cache['fields'] : ($type == 'all' ? $cache : $cache[$type]));
			}
			
			$data = array_filter(explode("\n", file_get_contents($this->_mapPath($model))));
			$fields = array();

			//pop off the headers
			$recordLength = array_shift(array_slice(explode(':', array_shift($data)), 1, 1));
			$dataPath = array_shift(explode(':', array_shift($data)));
			$position = 0;
			
			//go through each line in the map
			foreach ($data as $i => $line)
			{
				//skip blank lines
				if ($line != '')
				{
					//extract the field definition
					$parts = array_map('trim', array_combine(
						array('name', 'length', 'type'), 
						array_slice(explode(':', $line), 0, 3)
					));
					
					//if the field is part of a group, rip out the group name from the field name
					if (preg_match('/^([a-z][0-9]+)\)(.*)$/i', $parts['name'], $matches))
					{
						$parts['name'] = trim($matches[2]);
					}
					
					//unique names (and empty names) if we run into duplicates
					while ($parts['name'] == '' || is_numeric($parts['name']) || array_key_exists($parts['name'], $fields))
					{
						$j = 1;
						
						if (preg_match('/_([0-9]+)$/', $parts['name'], $matches))
						{
							$parts['name'] = preg_replace("/_{$matches[1]}$/", '', $parts['name']);
							$j = $matches[1] + 1;
						}
						
						$parts['name'] = $parts['name'] . '_' . $j;
					}
					
					//make sure we have a length, even if it's empty
					if ($parts['length'] == '')
					{
						$parts['length'] = 0;
					}
					
					//make sure we have a type, even if it's empty
					if ($parts['type'] == '')
					{
						$parts['type'] = '*';
					}
					
					//place it in a hash that mimics the MySQL driver plus our own fields we need
					$fields[$parts['name']] = array(
						'ordinal' => $i + 1,
						'position' => $position,
						'fileproType' => $parts['type'],
						'type' => $this->column($parts['type']),
						'null' => false,
						'default' => '',
						'length' => $parts['length']
					);
					
					$position += $parts['length'];
				}
			}
			
			//now for the indexes
			$indexes = array();
			$indexModel = $this->_indexModel();
			$indexTables = array_values(Set::flatten($indexModel->query("show tables like 'index_" . strtolower($model->useTable) . "%'", false)));
			
			//index all of the index tables by field name
			foreach ($indexTables as $table)
			{
				$indexes[str_replace('index_' . strtolower($model->useTable) . '_', '', $table)] = $table;
			}

			$description = array('data_path' => $dataPath, 'record_length' => $recordLength, 'fields' => $fields, 'indexes' => $indexes);
			$this->__cacheDescription($this->fullTableName($model), $description);

			return ($type === null || $type == '' ? $description['fields'] : ($type == 'all' ? $description : $description[$type]));
		}
		
		/**
		 * Gets the model used to create locks in MySQL for writing via filepro.
		 * @return object The model used for locking.
		 * @access private
		 */
		function _lockModel()
		{
			return ClassRegistry::init($this->config['lock_model']);
		}
		
		/**
		 * Gets the physical record length for the FU05 model.
		 * @param object The model to operate on.
		 * @return number The length of a single record.
		 */
		function recordLength(&$model)
		{
			return $this->describe($model, 'record_length');
		}
		
		/**
		 * Gets the absolute path to the FU05 model's DAT file.
		 * @param object The model to operate on.
		 * @return number The absolute path of the FU05 DAT file.
		 */
		function dataPath(&$model)
		{
			return $this->describe($model, 'data_path');
		}
		
		/**
		 * Determines the equivalent PHP data type that matches a data type in the FU05 map file. 
		 * @inherited
		 */
		function column($real) 
		{ 
			$real = strtolower($real);
			
			if ($real == 'yn' || $real == 'yesno')
			{
				return 'boolean';
			}
			else if ($real == 'mdyy')
			{
				return 'date';
			}
			else if ($real == '.0')
			{
				return 'int';
			}
			else if (preg_match('/^\.[1-9]$/', $real))
			{
				return 'float';
			}
			
			return 'string';
		}
		
		/**
		 * Creates a new record in the FU05 file.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $fields An array of fields to save.
		 * @param array $values An array of values for the specified fields. 
		 * @return bool True if the creation was successful, false otherwise.
		 */
		function create(&$model, $fields = null, $values = null)
		{
			//forward the request to filepro if necessary
			if ($model->_saveViaFilepro)
			{
				return $this->_createViaFilepro($model, $fields, $values);
			}
				
			$schema = $this->describe($model, 'all');
			
			//if we have no fields specified, we'll write all that are currently
			//set in the model data
			if ($fields === null) 
			{
				unset($fields, $values);
				$fields = array_keys($model->data);
				$values = array_values($model->data);
			}
			
			//create the buffer
			$buffer = $this->_createRecordBuffer($model, $fields, $values);
			
			try
			{
				//write the buffer to the end of the file (no locking necessary on an append).
				$f = $this->_openFile($schema['data_path'], true);
				
				if ($f === false)
				{
					return false;
				}
				
				if (dio_write($f, $buffer) <= 0)
				{
					return false;
				}
				
				//figure out what record we inserted
				$position = dio_seek($f, 0, SEEK_CUR);
				$this->_lastInsertID = $position / $schema['record_length'];

				//set the inserted ID on the model
				$model->setInsertID($this->_lastInsertID);
				$model->id = $this->_lastInsertID;
				
				//update the indexes
				$this->_insertIndexRecords($model, $model->id, $fields, $values);
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Creates a new record in the FU05 file but via filepro. This can be used when filepro indexes need to be kept
		 * in sync with our own. Used internally when a model uses the saveViaFilepro method in our app_model.
		 * @param object $model The model to operate on.
		 * @param array $fields An array of fields to save.
		 * @param array $values An array of values for the specified fields. 
		 * @return bool True if the creation was successful, false otherwise.
		 */
		function _createViaFilepro(&$model, $fields = null, $values = null)
		{
			$schema = $this->describe($model, 'all');
			
			//if we have no fields specified, we'll write all that are currently
			//set in the model data
			if ($fields === null) 
			{
				unset($fields, $values);
				$fields = array_keys($model->data);
				$values = array_values($model->data);
			}

			try
			{
				//grab a mirrored model
				$mirror = $this->createMirrorModel($model);
				
				//verify that the mirror and FU05 file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} FU05 model is out-of-sync with mirror!");
					return false;
				}
				
				//set up our mirror fields
				$extraFields = array(
					'mirror_transaction_success' => null,
					'mirror_action_type' => 'I'
				);
				
				$mirror->create();
					
				//forward the save, including our mirror fields, to the mirror
				$mirror->save(array($mirror->alias => array_merge(
					array_combine($fields, $values),
					$extraFields
				)));
					
				//kick off the process to do the insert for the record
				$this->_invokeFileproWriterScript($mirror->useTable, $mirror->id, true);
				
				//grab the transaction status and filepro record ID out of the mirror
				$result = $mirror->find('first', array(
					'fields' => array('mirror_transaction_success', 'mirror_filepro_record_id'), 
					'conditions' => array('id' => $mirror->id),
					'contain' => array()
				));
				
				if (!$result[$mirror->alias]['mirror_transaction_success'])
				{
					return false;
				}

				//grab the ID of the record that was inserted
				$this->_lastInsertID = $result[$mirror->alias]['mirror_filepro_record_id'];
				
				//set the inserted ID on the model
				$model->setInsertID($this->_lastInsertID);
				$model->id = $this->_lastInsertID;
				
				//update the indexes
				$this->_insertIndexRecords($model, $model->id, $fields, $values);
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Creates a string that will be one entire record in the DAT file for the given model.
		 * @param object $model The model to operate on.
		 * @param array $fields An array of fields to specify values for .
		 * @param array $values An array of values for the specified fields. 
		 * @return string The string that would be one entire record for the model.
		 * @access private
		 */
		function _createRecordBuffer(&$model, $fields = array(), $values = array())
		{
			$schema = $this->describe($model, 'all');
			$buffer = array();
			
			$fields = $this->_unqualifiedNames($model, $fields);

			//go through each field in the schema
			foreach ($schema['fields'] as $name => $field)
			{
				//by default we just fill in a blank value
				$value = $this->_formatValueForRecord('', $field);
				
				//if we can find this field in the array that was specified...
				if (($i = array_search($name, $fields)) !== false)
				{
					//set the value to be whatever value was specified
					$value = $this->_formatValueForRecord($values[$i], $field);
				}
				
				//set the value in the buffer
				$buffer[$field['ordinal']] = $value;
			}
			
			//sort the buffer by field numbers
			ksort($buffer);
			
			//throw the whole buffer together in one big string
			return implode('', $buffer);
		}
		
		/**
		 * Creates a string that will be one entire deleted record in the DAT file for the given model.
		 * @param object $model The model to operate on.
		 * @return string The string that would be one entire deleted record for the model.
		 * @access private
		 */
		function _createDeletedRecordBuffer(&$model)
		{
			$buffer = $this->_createRecordBuffer($model);
			$offset = $this->_deletedCharacterOffset($model);
			return substr($buffer, 0, $offset) . DboFu05::deletedRecordCharacter . substr($buffer, $offset + 1);
		}
		
		/**
		 * Determines the offset in a record where the deleted character will occur if a record is deleted.
		 * @param object $model The model to operate on.
		 * @return numeric The deleted character offset.
		 */
		function _deletedCharacterOffset(&$model)
		{
			return $model->Behaviors->enabled('Chainable') ? DboFu05::chainableDeletedCharacterOffset : DboFu05::deletedCharacterOffset;
		}
		
		/**
		 * Returns an unqualified (i.e. table-less) name of a field. If a table is specified on the field
		 * but it does not belong to the specified model, null is returned.
		 * @param object $model The model to operate on.
		 * @param string $field The field to get the unqualified name of.
		 * @access private
		 * @return The unqualified field name.
		 */
		function _unqualifiedName(&$model, $field)
		{
			$name = explode('.', $field);
			return count($name) == 1 ? $name[0] : (count($name) == 2 && $name[0] == $model->alias ? $name[1] : null);
		}
		
		/**
		 * Works just like _unqualifiedName but on an array of fields. Any field that isn't for the model
		 * is not returned in the resulting array.
		 * @param object $model The model to operate on.
		 * @param array $fields An array of field names to get the unqualified names of.
		 * @access private
		 * @return An array with the unqualified names.
		 */
		function _unqualifiedNames(&$model, $fields)
		{
			if (!is_array($fields))
			{
				return array();
			}
			
			$names = array();
			
			foreach ($fields as $field)
			{
				$name = $this->_unqualifiedName($model, $field);
				
				if ($name != null)
				{
					$names[] = $name;
				}
			}
			
			return $names;
		}
		
		/**
		 * Returns a qualified (i.e. table included) name of a field. If a table is specified on the field
		 * already, the value is returned as-is. The exception to this is the 'or' and 'and' fields, which
		 * are considered keywords for field names.
		 * @param object $model The model to operate on.
		 * @param string $field The field to get the qualified name of.
		 * @access private
		 * @return The qualified field name.
		 */
		function _qualifiedName(&$model, $field)
		{
			$name = explode('.', $field);
			return count($name) == 1 && !in_array($field, array(DboFu05::countField, 'or', 'and')) ? ($model->alias . '.' . $field) : $field;
		}
		
		/**
		 * Works just like _qualifiedName but on an array of fields.
		 * @param object $model The model to operate on.
		 * @param array $fields An array of field names to get the qualified names of.
		 * @access private
		 * @return An array with the qualified names.
		 */
		function _qualifiedNames(&$model, $fields)
		{
			$names = array();
			
			foreach ($fields as $field)
			{
				$names[] = $this->_qualifiedName($model, $field);
			}
			
			return $names;
		}
		
		/**
		 * Reads one or more records from the FU05 model.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $queryData An array with the following keys: conditions, fields, order, limit, and page.
		 * @return array An array of matching records.
		 */
		function read(&$model, $queryData = array(), $recursive = null)
		{
			$schema = $this->describe($model, 'all');
	
			//see what fields we have for this model only
			$modelFields = $this->_unqualifiedNames($model, $queryData['fields']);
			
			//if no fields were specified, we grab them all (i.e. "select *")
			if (empty($queryData['fields']) || is_array($queryData['fields']) && empty($modelFields))
			{
				$queryData['fields'] = array_keys($schema['fields']);
			}
			
			//if we have an array of fields (only time we wouldn't is on a find('count')),
			//we'll fully qualify everything so there's no mistaking what model each field is for.
			if (is_array($queryData['fields']))
			{
				$queryData['fields'] = $this->_qualifiedNames($model, $queryData['fields']);
			}

			//qualify all unqualified conditional field names with the name of the model
			//so that we guarantee that when we recursively read records that the conditions aren't applied again
			//to the parent model if they have a field with the same name
			if (!empty($queryData['conditions']))
			{
				$queryData['conditions'] = array_combine($this->_qualifiedNames($model, array_keys($queryData['conditions'])), array_values($queryData['conditions']));
			}
			
			//grab the matching records
			$resultSet = $this->_fetchAll($model, $queryData, $recursive);

			//let the model know if anything went wrong
			if ($resultSet === false) 
			{
				$model->onError();
				return false;
			}
			
			return $resultSet;
		}
		
		/**
		 * Escapes field names - not used by the driver, but necessary because the Model class uses it.
		 * @inherited
		 * @param string $data
		 */
		function name($data)
		{
			return is_array($data) ? $data[0] : $data;
		}
		
		function _normalizeOrder($order)
		{
			$result = array();
			
			if (!is_array($order))
			{
				//if there is no order, return an empty array
				if ($order == '')
				{
					return $result;
				}
				
				//otherwise put the single value into an array
				$order = array($order);
			}
			
			//go through each value...
			foreach ($order as $i => $value)
			{
				$field = $value;
				
				//if the key is not numeric, that means it's actually
				//the field name and the value is the sort direction
				if (!is_numeric($i))
				{
					$field = $i . ' ' . $value;
				}
				
				$result[] = $field;
			}
			
			return $result;
		}
		
		/**
		 * Gets all records that satistfy the given query.
		 * @param object $model The model to operate on.
		 * @param array $queryData An array with the following keys: conditions, fields, order, limit, and page.
		 * @return array An array of matching records.
		 */
		function _fetchAll(&$model, $queryData, $recursive = null)
		{
			$fields = $queryData['fields'];
			$conditions = $queryData['conditions'];
			$limit = $queryData['limit'];
			$page = $queryData['page'];
			$offset = ($page != null && $limit != null) ? ($page - 1) * $limit : null;
			$order = $this->_normalizeOrder($queryData['order'][0]);
			$hasOrder = !empty($order);
			$countOnly = false;
			$chains = isset($queryData['chains']) ? $queryData['chains'] : array();
			$unchainedIndex = isset($queryData['unchainedIndex']) ? $queryData['unchainedIndex'] : null;

			//short-circuit illogical cases
			if ($limit !== null && $limit == 0)
			{
				return array();
			}
			
			//grab our schema
			$schema = $this->describe($model, 'all');
			
			//if an explicit, unchained index was specified, remove it from the query data now so it doesn't get applied to parent find() calls when joining data together
			if ($unchainedIndex != null)
			{
				unset($queryData['unchainedIndex']);
			}
			
			//make sure the recursion level is set in one way or another
			if ($recursive === null && isset($queryData['recursive'])) 
			{
				$recursive = $queryData['recursive'];
			}
			else if ($recursive === null)
			{
				$recursive = $model->recursive;
			}
			
			//ensure that fields is actually an array
			if (!is_array($fields))
			{
				//find('count') internally sends one field - the field returned by
				//our calculate method. If we see it, we'll note that we're only doing
				//a count
				if ($fields == DboFu05::countField)
				{
					$countOnly = true;
					$fields = array();
				}
				else
				{
					$fields = array($fields);
				}
			}
			
			//short circuit full counts if we can grab one from an index. We can do this because
			//we know that an index contains the value of every non-deleted record in the U05 file.
			//Therefore, if we're doing a count and have no conditions whatsoever, we can just take the
			//number of records in the index.
			if ($countOnly && empty($conditions) && $model->Behaviors->enabled('Indexable') && count($schema['indexes']) > 0 && (empty($chains) || !$this->hasOtherConditions($model, $conditions, $chains)))
			{
				$table = $schema['indexes'][array_pop(array_keys($schema['indexes']))];
				$count = $this->_indexModel()->query("select count(*) as the_count from {$table}", false);
				return array('0' => array('0' => array('count' => $count[0][0]['the_count'])));
			}

			if ($recursive > -1)
			{
				//automatically pull foreign keys on parent models that have a condition
				foreach ($model->belongsTo as $parent => $data) 
				{
					$parentModel = ClassRegistry::init($parent);
					
					if ($this->_hasConditionalClauses($parentModel, $this->_createConditionalClauses($parentModel, $this->describe($parentModel, 'all'), $conditions)))
					{
						$fields[] = $model->alias . '.' . (is_string($data['foreignKey']) ? $data['foreignKey'] : $data['foreignKey']['field']);
					}
				}
			}
							
			//automatically pull pointer fields for any specified chains
			if ($model->Behaviors->enabled('ChainOwner'))
			{
				foreach ($chains as $modelName => $config)
				{
					if (is_numeric($modelName))
					{
						$modelName = $config;
					}
					
					$fields[] = $model->alias . '.' . $model->Behaviors->ChainOwner->settings[$model->alias][$modelName];
				}	
			}

			//extract just this model's fields
			$fields = $this->_unqualifiedNames($model, array_unique($fields));
			
			$clauses = array();
			$resultSet = array();
			
			//if we have conditions we need to convert them into clauses that we 
			//can test against as we read records
			if (!empty($conditions))
			{
				$clauses = $this->_createConditionalClauses($model, $schema, $conditions);
			}
		
			$indexMatches = null;
			$lowerBound = null;
			$upperBound = null;
			$idMatches = null;
			
			//see if the criteria has an id field and it's an equality test, because if so,
			//we'll keep track of what those IDs are. In many cases, searching by ID could
			//be a lot faster than even searching through indexes.
			if (array_key_exists('id', $clauses))
			{
				foreach (array_keys($clauses['id']) as $operator)
				{
					if ($operator == '=')
					{
						$idMatches = is_array($clauses['id'][$operator]['condition']) ? $clauses['id'][$operator]['condition'] : array($clauses['id'][$operator]['condition']);
					}
					else if (strtolower($operator) == 'between')
					{
						$idMatches = array();
						
						for ($i = $clauses['id'][$operator]['condition'][0]; $i <= $clauses['id'][$operator]['condition'][1]; $i++)
						{
							$idMatches[] = $i;
						}
					}
				}
			}
			
			$orderApplied = false;
			$limitApplied = false;

			//try to use indexes to get the results
			if (($bestIndex = $this->_findBestIndex($model, $schema, $clauses, $order, $unchainedIndex)) != null)
			{
				//go ahead and pull the records that match the condition against
				//that index as long as it's not worse than searching by ID (if we can)
				if ($idMatches === null || count($idMatches) > $bestIndex['rows'])
				{
					$result = $this->_createIndexOrderClause($model, $schema, $bestIndex, $order);
					$orderApplied = $result['orderApplied'];
					$limitClause = '';
					
					//if the index used was a condition, remove that condition from the clauses 
					//since now there's no reason to test for it
					if ($bestIndex['isCondition'])
					{
						unset($clauses[$bestIndex['field']]);
					}
										
					//if this is a count, see if we can short circuit the whole thing here
					if ($countOnly && empty($clauses) && !$this->_hasOtherClauses($model, $conditions, $chains, $recursive))
					{
						$count = $this->_indexModel()->query("
							select count(1) as the_count 
							from `{$bestIndex['table']}`
							where {$bestIndex['clause']}
						", false);
						
						return array('0' => array('0' => array('count' => $count[0][0]['the_count'])));
					}
					
					//see if we can apply the limit in MySQL
					if ($limit != null && $orderApplied && empty($clauses) && !$this->_hasOtherClauses($model, $conditions, $chains, $recursive))
					{
						$limitApplied = true;
						$limitClause = "limit {$offset}, {$limit}";
					}
					
					$indexMatches = $this->_indexModel()->query("
						select `{$bestIndex['table']}`.record_number
						from `{$bestIndex['table']}`
						{$result['joinClause']}
						where {$bestIndex['clause']}
						{$result['orderClause']}
						{$limitClause}
					", false);
								
					$indexMatches = Set::extract($indexMatches, "{n}.{$bestIndex['table']}.record_number");

					//uncomment and comment out the unset above to test performance times without the index
					//$indexMatches = null;
				}
			}

			//if we couldn't find an index, see if the conditions contains the id field,
			//because if it does we can still cut down the search time dramatically in certain cases
			if ($indexMatches === null && array_key_exists('id', $clauses))
			{
				foreach (array_keys($clauses['id']) as $operator)
				{
					switch (strtolower($operator))
					{
						case '=':
						case 'between':
							$indexMatches = $idMatches;
							unset($clauses['id'][$operator]);
							break;
						case '>':
							$lowerBound = $clauses['id'][$operator]['condition'] + 1;
							break;
						case '>=':
							$lowerBound = $clauses['id'][$operator]['condition'];
							break;
						case '<':
							$upperBound = $clauses['id'][$operator]['condition'] - 1;
							break;
						case '<=':
							$upperBound = $clauses['id'][$operator]['condition'];
							break;
					}
				}
			}
			
			//for a chainable model, if we:
			// 1. could not use an index 
			// 2. can't short circuit id tests 
			// 3. have conditions to search against
			//we are not going to allow the search to happen. Chains should always be searched either through an index,
			//which were guaranteed to be built by following chains, or they should be searched by id. Otherwise you can
			//get records back that are orphans - those that are not marked as deleted, but could still be returned if 
			//they match certain criteria. These records should never be allowed to be returned because they are junk data.
			if ($indexMatches === null && $lowerBound === null && $upperBound === null && !empty($clauses) && $model->Behaviors->enabled('Chainable'))
			{
				throw new Exception("Access denied. Attempt to access {$model->alias} records outside of the chain.");
			}
			
			//pr(array('index' => $indexMatches, 'lower' => $lowerBound, 'upper' => $upperBound, 'clauses' => $clauses));
			
			//after opening the file we always seek to the start of the file because this may be a cached handle
			$f = $this->_openFile($schema['data_path']);
			
			if ($f === false)
			{
				throw new Exception("Unable to open U05 file {$schema['data_path']}");
			}
			
			if (dio_seek($f, 0, SEEK_SET) == -1)
			{
				throw new Exception("Unable to seek in U05 file {$schema['data_path']}");
			}
			
			$recordsFound = 0;
			$currentIndexRecord = 0;
			
			//skip past records we know can't match if we have a lower bound
			if ($lowerBound != null)
			{	
				if (dio_seek($f, (max(1, $lowerBound) - 1) * $schema['record_length'], SEEK_SET) == -1)
				{
					throw new Exception("Unable to seek in U05 file {$schema['data_path']}");
				}
			}

			//go through the file until we hit the end, our page limit (if we're not ordering results), or 
			//we hit all of the records that were found in the index
			while ($indexMatches === null || $currentIndexRecord < count($indexMatches))
			{
				if ($indexMatches != null)
				{
					//skip past illogical cases where the record number is invalid
					if ($indexMatches[$currentIndexRecord] < 1)
					{
						$currentIndexRecord++;
						continue;
					}
					
					if (dio_seek($f, ($indexMatches[$currentIndexRecord] - 1) * $schema['record_length'], SEEK_SET) == -1)
					{
						throw new Exception("Unable to seek in U05 file {$schema['data_path']}");
					}
					
					$currentIndexRecord++;
				}
				
				$record = dio_read($f, $schema['record_length']);
				
				//we've hit the end of file if the read returned null
				if ($record === null)
				{
					break;
				}
				
				$match = true;
				
				//grab the record ID
				$id = floor(dio_seek($f, 0, SEEK_CUR) / $schema['record_length']);
				
				if ($id < 0)
				{
					throw new Exception("Unable to seek to read ID in {$schema['data_path']}.");
				}
				
				//stop searching if we have an upper bound and we've gone beyond it
				if ($upperBound != null && $id > $upperBound)
				{
					break;
				}
				
				//skip deleted rows
				if (substr($record, $this->_deletedCharacterOffset($model), 1) == DboFu05::deletedRecordCharacter)
				{
					continue;
				}

				//if we satisfied all of the conditions...
				if ($this->_recordMatches($record, $id, $clauses))
				{
					//init the record with the "id" - the row number
					$data = array($model->alias => array('id' => $id));

					//pull the fields out that were specified
					foreach ($fields as $field)
					{
						//only pull a field if it's actually in the schema
						if (array_key_exists($field, $schema['fields']))
						{
							$data[$model->alias][$field] = $this->_phpValue(rtrim(substr($record, $schema['fields'][$field]['position'], $schema['fields'][$field]['length'])), $schema['fields'][$field]['type']);
						}
					}
					
					//find any parents
					$parents = $this->_findParentRecords($model, $data, $queryData, $recursive);
					
					//if the return value is false, it means that conditions in the query didn't match
					//the parent, therefore the original record does not match anymore, so we're skipping over it
					if ($parents === false)
					{
						continue;
					}
					
					$chainedRecords = array();
					
					//find any chains
					if ($model->Behaviors->enabled('ChainOwner') && !empty($chains))
					{
						$chainedRecords = $this->_findChainedRecords($model, $data, $chains);
						
						//if the return value is false, it means that conditions in the chain didn't match,
						//therefore the original record does not match anymore, so we're skipping over it
						if ($chainedRecords === false)
						{
							continue;
						}
					}
					
					$recordsFound++;
					
					//if we're paging results and don't have to apply an order and we haven't hit the proper page yet,
					//just ignore the record unless the limit has already been applied
					if ((!$hasOrder || $orderApplied) && $offset != null && !$limitApplied && $offset > $recordsFound - 1)
					{
						continue;
					}
					
					//if we're only doing a count, there's nothing else to do for this record
					if ($countOnly)
					{
						continue;
					}
					
					//add the parents to the data
					$newRecord = array_merge($data, $parents);
					
					//add chainables to the data
					if (!empty($chains))
					{
						$newRecord = array_merge($newRecord, $chainedRecords);
					}
					
					//add the record to the result set
					$resultSet[] = $newRecord;
										
					//if we have a limit that hasn't already been applied and we've hit it, we're done, unless 
					//we still have to order the results
					if ((!$hasOrder || $orderApplied) && $limit != null && !$limitApplied && count($resultSet) == $limit)
					{
						break;
					}
				}
			}

			//if we're only doing a count, return it in the same structure that database drivers do
			if ($countOnly)
			{
				return array('0' => array('0' => array('count' => $recordsFound)));
			}

			//order the results if necessary
			if ($hasOrder && !$orderApplied)
			{
				$resultSet = $this->_mergeSort($model, $resultSet, $order);

				//if we had to do an order by AND a paged result set, we have to select the proper page
				//at this point
				if ($limit != null && !$limitApplied)
				{
					$resultSet = array_slice($resultSet, $offset, $limit);
				}
			}

			return $resultSet;
		}
		
		/**
		 * Attempts to find the best index to use basesd on a set of conditional clauses.
		 * @param object $model The model to operate on.
		 * @param array $schema The model schema.
		 * @param array $clauses A set of conditional clauses created by _createConditionalClauses.
		 * @param mixed $order The order being applied to the results, if any.
		 * @param string $unchainedIndex This is used to override the default index usage mechanism on chainable models so that
		 * an unchained index (one that is built by scanning the full table instead of following chains) can be used instead. If specified,
		 * this should be the name of the field to use the unchained index for.
		 * @return array An array containing information about the best index, or null if an index
		 * was not found. The array contains the following keys:
		 * 		'table' - The name of the MySQL inddex table
		 * 		'field' - The field used for the index
		 * 		'rows' - The estimated number of rows to have to examine when pulling the results
		 * 		'operator' - The SQL operator to use when pulling the data
		 * 		'value' - The escaped SQL value(s) to use when pulling the data
		 * 		'before' - Any potential string that should be written before the value
		 * 		'after' - Any potential string that should be written after the value
		 * 		'isCondition' - True if the index being used was from a condition, false if from an ordered field
		 * Ex:
		 * 		"select * from the_table where value {$operator} {$before}{$value}{$after}"
		 */
		function _findBestIndex(&$model, $schema, $clauses, $order, $unchainedIndex = null)
		{
			//make sure this model has indexes
			if (!$model->Behaviors->enabled('Indexable') || ($unchainedIndex == null && count($schema['indexes']) == 0))
			{
				return null;
			}
			
			$indexModel = $this->_indexModel();
			$bestIndex = null;
			
			//chop off asc/desc qualifiers	
			foreach ($order as $i => $field)
			{
				if (strtolower(substr($field, -4)) == ' asc')
				{
					$order[$i] = substr($field, 0, -4);
				}
				else if (strtolower(substr($field, -5)) == ' desc')
				{
					$order[$i] = substr($field, 0, -5);
				}
			}
			
			//take out ifnull tests if there are any and have it just be the field name
			foreach ($order as $i => $field)
			{
				if (preg_match(DboFu05::ifNullPattern, $field, $matches))
				{
					$order[$i] = $matches[1];
				}
			}

			//remove any fields that aren't from this model
			$order = array_filter($this->_unqualifiedNames($model, $order));
			
			//we're going to add a fake index so we can index id searches
			//NOTE - this is commented out because chainable models search through chains by following ID pointers,
			//and we can't yet guarantee that everything that is inserted into a U05 file has been added to our 
			//indexes. This has the potential to cause corrupted chains.
			//$schema['indexes']['id'] = $schema['indexes'][array_shift(array_keys($schema['indexes']))];
			
			//figure out if we're going to just use the indexes from the schema of the model, or if we are using an explicit unchained index
			$indexes = $unchainedIndex == null ? $schema['indexes'] : array($unchainedIndex => $model->indexName($unchainedIndex, false, true));
			
			//look at each index...
			foreach ($indexes as $field => $table)
			{
				$clause = '';
				$found = false;
				$isCondition = true;
				
				//see if the field that is indexed is being tested by one of the clauses or in the order
				//and craft the condition to use against the index
				if (array_key_exists($field, $clauses))
				{	
					$parts = array();
					
					foreach ($clauses[$field] as $op => $clause)
					{
						$operator = $op;
						$value = '';
						$before = '';
						$after = '';
											
						if (($operator == '=' || $operator == '<>' || $operator == '!=') && is_array($clause['condition']))
						{
							$operator = $operator == '=' ? 'in' : 'not in';
							$before = '(';
							$after = ')';
						}
						
						if ($operator == 'BETWEEN')
						{
							$before = $this->_sqlValue($clause['condition'][0], $field == 'id' ? 'int' : $schema['fields'][$field]['type']);
							$value = ' and ';
							$after = $this->_sqlValue($clause['condition'][1], $field == 'id' ? 'int' : $schema['fields'][$field]['type']);
						}
						else if (is_array($clause['condition']))
						{
							$converted = array();
							
							foreach ($clause['condition'] as $element)
							{
								$converted[] = $this->_sqlValue($element, $field == 'id' ? 'int' : $schema['fields'][$field]['type']);
							}
							
							$value = implode(', ', $converted);
						}
						else
						{
							$value = $this->_sqlValue($clause['condition'], $field == 'id' ? 'int' : $schema['fields'][$field]['type']);
							
							if (strtolower($value) == 'null')
							{
								if ($operator == '=')
								{
									$operator = 'is';
								}
								else if ($operator == '<>' || $operator == '!=')
								{
									$operator = 'is not';
								}
							}
						}
						
						$parts[] = "`{$table}`." . ($field == 'id' ? 'record_number' : 'value') ." {$operator} {$before}{$value}{$after}";
					}
					
					$clause = implode(' and ', $parts);					
					$found = true;
				}
				else if (in_array($field, $order))
				{
					//if we find an index on a field that's ordered, we just want all records back,
					//so we use a clause that's always true. This way we get all results 
					//back from the index so we have an order pre-applied so we don't have to do 
					//the ordering ourselves later. This also lets us apply limits on the fly 
					//instead of after pulling all the data.
					$clause = '1 = 1';
					$found = true;
					$isCondition = false;
				}
				
				//if we were able to find the field somewhere, run the explain to get the 
				//count of records that would match
				if ($found)
				{
					$explain = "explain select record_number from `{$table}` where {$clause}";
					$hash = md5($explain);
					$rows = 0;
					
					if (array_key_exists($hash, $this->_explainCache))
					{
						$rows = $this->_explainCache[$hash];
					}
					else
					{
						$rows = Set::extract($indexModel->query($explain), '0.0.rows');
						$this->_explainCache[$hash] = $rows;
					}
					
					//if this index results in fewer rows needing to be tested, we'll capture it for later
					if ($bestIndex === null || $bestIndex['rows'] > $rows)
					{
						$bestIndex = array(
							'table' => $table,
							'field' => $field,
							'rows' => $rows,
							'clause' => $clause,
							'isCondition' => $isCondition
						);
					}
				}
			}
			
			return $bestIndex;
		}
		
		/**
		 * Creates an order by clause in SQL form that can be used when looking up indexes in MySQL.
		 * @param object $model The model to operate on.
		 * @param array $schema The model schema.
		 * @param array $chosenIndex The index being used (the result from a _findBestIndex call).
		 * @return mixed $order The order that the results should be in.
		 * @return array An array containing the following keys:
		 * 		'joinClause' - Any necessary join clauses in SQL form.
		 * 		'orderClause' - The order by clause in SQL form.
		 * 		'orderApplied' - True or false depending on if we were able to craft a fully covered
		 * 						 order by. If there are fields in the order that don't have a corresponding
		 * 						 index in MySQL, we can't fully apply the order. In that case, the clause
		 * 						 contains the order by up to the point at which we couldn't find an index.
		 */
		function _createIndexOrderClause($model, $schema, $chosenIndex, $order)
		{
			$result = array(
				'joinClause' => '',
				'orderClause' => '',
				'orderApplied' => false
			);
			
			$orderApplied = true;
			$joinClause = '';
			$orderClause = 'order by record_number';
			
			//only if we have an explicit order do we need to craft the order statement
			if (!empty($order))
			{
				$joins = array();
				$orders = array();

				//go through each field adding it to the clause if we can
				foreach ($order as $field)
				{
					$ifNullTest = false;
					$direction = 'asc';
					$nullValue = null;
					
					//chop off qualifiers see if the field is to be sorted descending
					if (strtolower(substr($field, -4)) == ' asc')
					{
						$field = substr($field, 0, -4);
					}
					else if (strtolower(substr($field, -5)) == ' desc')
					{
						$direction = 'desc';
						$field = substr($field, 0, -5);
					}
					
					//take out ifnull tests if there are any and have it just be the field name
					if (preg_match(DboFu05::ifNullPattern, $field, $matches))
					{
						$field = $matches[1];
						$nullValue = $matches[2];
						$ifNullTest = true;
					}
					
					$field = $this->_unqualifiedName($model, $field);

					if ($field == null || !array_key_exists($field, $schema['indexes']))
					{
						//the first field we find that isn't covered, we need to stop,
						//since applying any more will get results in a bad order
						$orderApplied = false;
						break;
					}
					
					//determine the join and order clause for the field
					$table = $schema['indexes'][$field];

					if ($table != $chosenIndex['table'])
					{
						$joins[] = "inner join `{$table}` on `{$chosenIndex['table']}`.record_number = `{$table}`.record_number";					
					}
					
					$orders[] = $ifNullTest ? "ifnull(`{$table}`.value, {$nullValue}) {$direction}" : "`{$table}`.value {$direction}";
				}
				
				//craft the clause
				$joinClause = implode(' ', $joins);
				
				if (count($orders) > 0)
				{
					$orderClause = 'order by ' . implode(', ', $orders);
				}
			}

			$result['joinClause'] = $joinClause;
			$result['orderClause'] = $orderClause;
			$result['orderApplied'] = $orderApplied;
			
			return $result;
		}
		
		/**
		 * Finds all chained records of the given record going to whatever level of recursion
		 * is specified.
		 * @param object $model The model to operate on.
		 * @param array $record The record to find parents for. It should be in the form of a record
		 * that would be the result of a find() call.
		 * @param array $queryData An array of query information, in the same form that is given to the
		 * read() method.
		 * @param int $recursive The level of recursion to walk up the parent hierarchy. Cooresponds exactly
		 * to the regular Cake model levels of recursion.
		 * @return mixed Returns an array of parent records (if any were found) in the exact same format
		 * as a find() call. If there were conditions in the query that pertain to a parent record and a parent
		 * record does not match the conditions, false is returned.
		 */
		function _findParentRecords(&$model, $record, $queryData, $recursive)
		{
			$parents = array();
			
			//we make sure to limit for finding parents because we're always looking for one record
			$queryData['limit'] = 1;
			$queryData['page'] = 1;
			
			//remove any ordering so that it will be applied after ALL parents are found for all records
			$queryData['order'][0] = array();

			if ($recursive > -1)
			{
				//go through each parent
				foreach ($model->belongsTo as $parent => $data) 
				{
					$parentModel =& $model->{$parent};
					
					//make sure the parent is also a FU05 file
					if ($model->useDbConfig == $parentModel->useDbConfig) 
					{
						$parentRecord = array();
						
						//see if we have conditions we need to test against the parent
						$hasParentClauses = $this->_hasConditionalClauses($parentModel, $this->_createConditionalClauses($parentModel, $this->describe($parentModel, 'all'), $queryData['conditions']));
						$foreignKey = is_string($data['foreignKey']) ? $data['foreignKey'] : $data['foreignKey']['field'];
						$parentField = is_string($data['foreignKey']) ? "id" : $data['foreignKey']['parent_field'];
											
						if ($hasParentClauses)
						{
							//if so, we better have a value for the foreign key or else the record cannot match
							if (trim($record[$model->alias][$foreignKey]) !== '')
							{
								//go find the parent
								$queryData['conditions'][$parentModel->alias . ".{$parentField}"] = $record[$model->alias][$foreignKey];
								$parentRecord = $this->read($parentModel, $queryData, $recursive - 1);
								$isCountOnly = !is_array($queryData['fields']);
								
								//if we find it, great, if not it means the parent didn't match the conditions, which
								//makes our record not match either
								if ($isCountOnly)
								{
									$parentRecord = $parentRecord[0][0]['count'] == 1 ? true : false;
								}
								else
								{
									$parentRecord = !empty($parentRecord) ? $parentRecord[0][$parentModel->alias] : false;
								}
							}
							else
							{
								$parentRecord = false;
							}
						}
						else if (isset($record[$model->alias][$foreignKey]) && trim($record[$model->alias][$foreignKey]) !== '')
						{
							//if we don't have parent clauses but we are pulling the foreign key and we have
							//one on the record, go ahead and pull the parent record if we can
							$queryData['conditions'][$parentModel->alias . ".{$parentField}"] = $record[$model->alias][$foreignKey];
							$parentRecord = $this->read($parentModel, $queryData, $recursive - 1);
							$parentRecord = !empty($parentRecord) ? $parentRecord[0][$parentModel->alias] : array();						
						}
				
						//if the record listed a parent but the parent wasn't found because of
						//conditions not matching, then the record that was passed in no longer matches either
						if ($parentRecord === false)
						{
							return false;
						}
						else
						{
							//save the parent record
							$parents[$parentModel->alias] = $parentRecord;
						}
					}
				}
			}
			
			return $parents;
		}
		
		/**
		 * Finds all chained records of the given record going to whatever level of recursion
		 * is specified.
		 * @param object $model The model to operate on.
		 * @param array $record The record to find chains for. It should be in the form of a record
		 * that would be the result of a find() call.
		 * @param array $chains An array of chain specifications to search through. They can contain 
		 * the same keys as a find() array.
		 * @return mixed Returns an array of chained records (if any were found) indexed by each chainable
		 * model. If there were conditions in the query that pertain to a chained record and no chained records
		 * match the conditions, false is returned.
		 */
		function _findChainedRecords(&$model, $record, $chains)
		{
			$matches = array();
			
			//go through each chain
			foreach ($chains as $modelName => $query) 
			{
				if (is_numeric($modelName))
				{
					$modelName = $query;
					$query = array();
				}
				
				//default the conditions (if any) to be required
				$query = array_merge(array('required' => true), $query);
				$chainModel = ClassRegistry::init($modelName);
				
				//make sure the chain model is also a FU05 file
				if ($model->useDbConfig != $chainModel->useDbConfig) 
				{
					return false;
				}
				
				$parentRecord = array();
				
				//see if we have conditions we need to test against the chain
				$hasChainClauses = $this->_hasConditionalClauses($chainModel, $this->_createConditionalClauses($chainModel, $this->describe($chainModel, 'all'), isset($query['conditions']) ? $query['conditions'] : array()));
				$pointerField = $model->Behaviors->ChainOwner->settings[$model->alias][$modelName];
				$chainedRecords = array();

				if ($hasChainClauses && $query['required'])
				{
					//if so, we better have a value for the pointer or else the record cannot match
					if (trim($record[$model->alias][$pointerField]) !== '')
					{
						//go find the chain
						$chain = $chainModel->findChain($record[$model->alias][$pointerField], $query);
						
						//if we find matches, great, if not it means the chain didn't match the conditions, which
						//makes our record not match either
						$chainedRecords = !empty($chain) ? $chain : false;
					}
					else
					{
						$chainedRecords = false;
					}
				}
				else if (isset($record[$model->alias][$pointerField]) && trim($record[$model->alias][$pointerField]) !== '')
				{
					//if we either don't have chain clauses or the conditions aren't required, but we are pulling 
					//the pointer field and we have one on the record, go ahead and pull the chain if we can
					$chainedRecords = $chainModel->findChain($record[$model->alias][$pointerField], $query);
				}
		
				//if the chain wasn't found because of conditions not matching, then the record 
				//that was passed in no longer matches either
				if ($chainedRecords === false)
				{
					return false;
				}
				else
				{
					$converted = array();
					
					//save the chain
					foreach ($chainedRecords as $piece)
					{
						$row = $piece[$chainModel->alias];
						$keys = array_diff(array_keys($piece), array($chainModel->alias));
						
						foreach ($keys as $key)
						{
							$row[$key] = $piece[$key];
						}
												
						$converted[] = $row;
					}
					
					$matches[$chainModel->alias] = $converted;
				}
			}
			
			return $matches;
		}
		
		/**
		 * Implementation of a merge sort that works with our FU05 records.
		 * @param object $model The model to operate on.
		 * @param array $records An array of records in the form of a find() array.
		 * @param array $order An array of fields to order the results by.
		 * @param bool Used internally for schema caching. Do not use.
		 * @return The records, in sorted order.
		 * @access private
		 */
		function _mergeSort(&$model, &$records, &$order, $reset = true)
		{
			static $schemaCache = array();
			
			if (empty($schemaCache) || $reset)
			{
				$schemaCache = array($model->alias => $model->schema());
			}
			
			//a 0 or 1 element array is automatically sorted
			if (count($records) <= 1)
			{
				return $records;
			}
			
			//split the array in halves
			$left = array_slice($records, 0, count($records) / 2);
			$right = array_slice($records, count($records) / 2);

			//merge sort both halves and then merge them together
			return $this->_merge($model, $schemaCache, $this->_mergeSort($model, $left, $order, false), $this->_mergeSort($model, $right, $order, false), $order);
		}
		
		/**
		 * Merges two halves of a merge sort together in proper order.
		 * @param object $model The model to operate on.
		 * @param array $schemaCache The cache of model schema.
		 * @param array $left The left half of the array to merge.
		 * @param array $right The right half of the array to merge.
		 * @param array $order An array of fields to order the results by.
		 * @return The merged array.
		 * @access private
		 */
		function _merge(&$model, &$schemaCache, &$left, &$right, &$order)
		{
			$merged = array();

			while (!empty($left) && !empty($right))
			{
				$comparison = $this->_mergeCompare($model, $schemaCache, $left[0], $right[0], $order);

				if ($comparison > 0)
				{
					$merged[] = array_shift($right);
				}
				else
				{
					$merged[] = array_shift($left);
				}
			}
			
			while (!empty($left))
			{
				$merged[] = array_shift($left);
			}
			
			while (!empty($right))
			{
				$merged[] = array_shift($right);
			}
							
			return $merged;
		}
		
		/**
		 * Compares two records during a merge sort to see which one is greater than the other.
		 * @param object $model The model to operate on.
		 * @param array $schemaCache The cache of model schema.
		 * @param array $left The first record to compare.
		 * @param array $right The second record to compare.
		 * @param array $order An array of fields to order the results by.
		 * @return numeric: 
		 * 		1 if $left > $right
		 * 		-1 if $left <= $right
		 * 		note that what constitutes "less/greater than" depends on if the order is ascending or descending
		 * @access private
		 */
		function _mergeCompare(&$model, &$schemaCache, &$left, &$right, &$order)
		{
			//go through each field in the order clause
			foreach ($order as $i => $field)
			{
				$ascending = true;
				$alias = $model->alias;
				$ifNullTest = false;
				$nullValue = null;

				//chop off qualifiers and see if the field is to be sorted descending
				if (strtolower(substr($field, -4)) == ' asc')
				{
					$field = substr($field, 0, -4);
				}
				else if (strtolower(substr($field, -5)) == ' desc')
				{
					$ascending = false;
					$field = substr($field, 0, -5);
				}
				
				//take out ifnull tests if there are any and have it just be the field name
				if (preg_match(DboFu05::ifNullPattern, $field, $matches))
				{
					$field = $matches[1];
					$nullValue = $matches[2];
					$ifNullTest = true;
				}
				
				//see if we have a model qualifier on the field name
				if (strpos($field, '.') !== false)
				{
					$parts = explode('.', $field);
					$alias = $parts[0];
					$field = $parts[1];
					
					//if we don't have this model's schema cached yet, go get it now
					if (!array_key_exists($alias, $schemaCache))
					{
						$name = $this->_resolveModelAlias($model, $alias);
						
						if ($name == null)
						{
							throw new Exception("Unknown model alias: {$alias}");
						}
						
						$schemaCache[$alias] = ClassRegistry::init($name)->schema();
					}
				}
				
				$leftValue = isset($left[$alias][$field]) ? $left[$alias][$field] : '';
				$rightValue = isset($right[$alias][$field]) ? $right[$alias][$field] : '';
				
				//if this is an ifnull test, remove the quotes around the string, if any
				if ($ifNullTest && preg_match("/^['\"].*['\"]$/", $nullValue))
				{
					$nullValue = substr($nullValue, 1, strlen($nullValue) - 2);
				}

				//massage values to sort correctly, taking any ifnull test into account
				$leftValue = $this->_comparableValue($ifNullTest && $leftValue === null ? $nullValue : $leftValue, $field == 'id' ? 'int' : $schemaCache[$alias][$field]['type']);
				$rightValue = $this->_comparableValue($ifNullTest && $rightValue === null ? $nullValue : $rightValue, $field == 'id' ? 'int' : $schemaCache[$alias][$field]['type']);
				
				if (is_string($leftValue))
				{
					if ($ascending ? (strcasecmp($leftValue, $rightValue) > 0) : (strcasecmp($rightValue, $leftValue) > 0))
					{
						return 1;
					}
					else if ($leftValue == $rightValue && $i < count($order) - 1)
					{
						//if the values are equal and it's not the last field to be ordered,
						//then we need to check the next field in the order
						continue;
					}
					else
					{
						return -1;
					}
				}
				else
				{ 
					//perform the comparison
					if ($ascending ? ($leftValue > $rightValue) : ($rightValue > $leftValue))
					{
						return 1;
					}
					else if ($leftValue == $rightValue && $i < count($order) - 1)
					{
						//if the values are equal and it's not the last field to be ordered,
						//then we need to check the next field in the order
						continue;
					}
					else
					{
						return -1;
					}
				}
			}
		}
		
		/**
		 * Resolves a model alias to an model name that can be used by ClassRegistry::init.
		 * @param object $model The model whose hierarchy will be examined.
		 * @param string $alias The alias name to look for.
		 * @return string The name of the model, or null if one with the specified alias couldn't be found.
		 */
		function _resolveModelAlias(&$model, $alias)
		{
			//go through each parent
			foreach ($model->belongsTo as $parent => $data)
			{
				$m = ClassRegistry::init($parent);
				
				//see if this parent's alias is the one we're looking for
				if ($m->alias == $alias)
				{
					return $parent;
				}
				
				//if it wasn't, try and walk up this parent's hierarchy
				$found = $this->_resolveModelAlias($m, $alias);
				
				//if we found it up higher in the hierarchy, return it
				if ($found != null)
				{
					return $found;
				}
			}
			
			//we couldn't find it at all
			return null;
		}

		/**
		 * Creates conditional clauses that can be used by _recordMatches to determine if a record
		 * matches all of the conditions.
		 * @param object $model The model to operate on.
		 * @param array $schema The model schema to operate on.
		 * @param array $conditions The conditions that would be passed to a find() call.
		 * @return array An array of clauses, indexed by unqualified field names.
		 * @access private
		 */
		function _createConditionalClauses(&$model, $schema, $conditions)
		{
			$clauses = array();
			
			if (empty($conditions))
			{
				return $clauses;
			}

			foreach ($conditions as $field => $condition)
			{
				$field = $this->_unqualifiedName($model, $field);
				
				if ($field === null)
				{
					continue;
				}
				
				if (strtolower($field) == 'or')
				{
					$clauses['or'] = $this->_createConditionalClauses($model, $schema, $condition);
				}
				else if (strtolower($field) == 'and')
				{
					$clauses['and'] = $this->_createConditionalClauses($model, $schema, $condition);
				}
				else 
				{
					$operator = '=';
					
					//see if there is an operator specified at the end of the field name
					if (preg_match($this->operators, $field, $matches))
					{
						//if so, make sure we use that operator and remove it from the field name
						$operator = $matches[1];
						$field = trim(substr($field, 0, strlen($field) - strlen($operator)));
					}

					//we only apply the clause if it's for a field in the table, or for the ID (record number)
					if (array_key_exists($field, $schema['fields']) || $field == 'id')
					{
						//save the info we need for the clause for later, indexed by operator since we may
						//have multiple conditions on the same field with different operators (i.e. >= x and <= y)
						$clauses[$field][strtoupper($operator)] = array(
							'field' => $field == 'id' ? null : $schema['fields'][$field], 
							'condition' => $condition
						);
					}
					else
					{
						throw new Exception("Invalid condition. Field {$field} does not exist in table {$model->alias}.");
					}
				}
			}

			return $clauses;
		}
		
		/**
		 * Determines if the given model has any conditional clauses.
		 * @param object $model The model to operate on.
		 * @param array $clauses An array of clauses created by a call to _createConditionalClauses
		 * on the same model.
		 * @return bool True if any clauses apply to the model, false otherwise.
		 */
		function _hasConditionalClauses(&$model, $clauses)
		{
			//first the easy test
			if (empty($clauses))
			{
				return false;
			}
			
			//now go through each clause (the only reason we have to do this is because it's possible
			//to have an 'or' or 'and' but nothing nested inside of it).
			foreach ($clauses as $key => $clause)
			{
				//if the key is 'or' or 'and', we have to recurse to see if their condition is empty
				if (((string)$key == 'or' || (string)$key == 'and'))
				{
					//if the nested conditions end up having something in them, then we can stop now
					if ($this->_hasConditionalClauses($model, $clause))
					{
						return true;
					}
				}
				else
				{
					//if we find any other field in the clauses, that means there are conditions
					return true;
				}
			}
			
			//the only time we should get here is if there is was ONLY an 'or' and/or an 'and' key, but none of
			//the nested conditions had clauses either, which means there are no conditions.
			return false;
		}
		
		/**
		 * Determines if there are conditions for any other related or chained model in the passed
		 * conditions and chains.
		 * @param object $model The model to check.
		 * @param array $conditions The conditions to check (in the form of a find() conditions array).
		 * @param array $chains The chains to search, if any.
		 * @param int $recursive The current model recursive setting.
		 * @param bool $includeSelf Used internally for recursion. Do not use.
		 * @return bool True if there are other clauses, false otherwise.
		 */
		function _hasOtherClauses(&$model, $conditions, $chains, $recursive, $includeSelf = false)
		{
			if ($recursive > -1)
			{
				//go through the parent hierarchy looking for conditions
				foreach ($model->belongsTo as $parent => $data) 
				{
					$parentModel = ClassRegistry::init($parent);
					
					if ($this->_hasOtherClauses($parentModel, $conditions, null, $recursive - 1, true))
					{
						return true;
					}
				}
			}
			
			//go through the chain hierarchy if we have one
			if ($model->Behaviors->enabled('ChainOwner'))
			{
				if ($chains != null)
				{
					foreach ($chains as $modelName => $config)
					{
						if (is_numeric($modelName))
						{
							$modelName = $config;
							$config = array();
						}
						
						$chainModel = ClassRegistry::init($modelName);
						$chainRecursive = isset($config['contain']) && empty($config['contain']) ? -1 : $chainModel->recursive;
						
						if ($this->_hasOtherClauses($chainModel, isset($config['conditions']) ? $config['conditions'] : array(), isset($config['chains']) ? $config['chains'] : array(), $chainRecursive, true))
						{
							return true;
						}
					}	
				}
			}
			
			//check the model itself if we're supposed to
			if ($includeSelf)
			{
				return $this->_hasConditionalClauses($model, $this->_createConditionalClauses($model, $this->describe($model, 'all'), $conditions));
			}
			
			return false;
		}
		
		/**
		 * Determines if a record matches the given clauses.
		 * @param string $record The string that would be one entire record for the model.
		 * @param numeric $recordNumber The record number of the specified record.
		 * @param array $clauses An array of clauses created by a call to _createConditionalClauses.
		 * @param bool $matchAny Used internally during recursion. Dictates AND vs. OR logic
		 * @return bool True if the record matches all conditions, false otherwise.
		 * @access private
		 */
		function _recordMatches($record, $recordNumber, $clauses, $matchAny = false)
		{
			$matches = true;

			//test each conditional clause against the row
			foreach ($clauses as $key => $clause)
			{
				if ((string)$key == 'or')
				{
					$matches = $this->_recordMatches($record, $recordNumber, $clause, true);
				}
				else if ((string)$key == 'and')
				{
					$matches = $this->_recordMatches($record, $recordNumber, $clause);
				}
				else
				{
					//if the user is searching on the ID field, use the record number
					if ($key == 'id')
					{
						$value = $recordNumber;
					}
					else
					{
						//otherwise grab the value of the field involved in the clause, removing insignificant spaces.
						//Even though there may be multiple clauses for the field, the definition on all of them is the 
						//same, so we just grab the first one.
						$field = $clause[array_shift(array_keys($clause))]['field'];
						$value = rtrim(substr($record, $field['position'], $field['length']));
					}
					
					//go through each clause on the field and make sure they all match
					foreach ($clause as $operator => $part)
					{
						//perform the proper operator against the actual value and the conditional value
						$partMatches = $this->_compareRecordValue($this->_phpValue($value, $part['field']['type']), $part['condition'], $operator, $part['field']);						
						$matches = $matchAny ? $partMatches : ($matches && $partMatches);
						
						if (!$matches && !$matchAny)
						{
							//if we're not doing an OR, and if we don't satisfy the condition for this operator, 
							//just short-circuit since the record doesn't match
							break;
						}
						else if ($matches && $matchAny)
						{
							//if we have multiple operator clauses on the field but we're ORing, we short circuit 
							//here if we match on any operator test
							break;
						}
					}
				}
				
				//we now perform the same short circuit tests at the individual field level that we also performed
				//at the operator level within a field to short cicuit AND and OR tests correctly
				if (!$matches && !$matchAny)
				{
					//if we're not doing an OR, and if we don't satisfy the condition, 
					//just short-circuit since the record doesn't match
					break;
				}
				else if ($matches && $matchAny)
				{
					//if we do have a match, and we're matching any condition (an OR), 
					//then we can short-circuit because we satisfy it
					break;
				}
			}

			return $matches;
		}
		
		/**
		 * Compares a value against another using the specified operator.
		 & @param mixed $value The value to compare. Should have been run through $this->_phpValue.
		 * @param mixed $against The value to compare against. This can also be an array,
		 * which will equate to OR semantics. As long as the value matches one of the items
		 * in the array, it will consider it a match. These values will be coming from PHP normally,
		 * but if they coming from U05, make sure to do a $this->_phpValue on them before passing
		 * to this function.
		 * @param string $operator The operator to compare the two values with.
		 * @param array $schema The schema of the column that the values are being compared from.
		 * @access private
		 */
		function _compareRecordValue($value, $against, $operator, $schema)
		{
			$matches = false;
			
			//(we won't have schema for the pseudo 'id' column)
			if ($schema == null)
			{
				$schema = array('type' => 'int');
			}
			
			$value = $this->_comparableValue($value, $schema['type']);
			
			//between tests are treated differently
			if (strtolower($operator) == 'between')
			{
				$from = $this->_comparableValue($against[0], $schema['type']);
				$to = $this->_comparableValue($against[1], $schema['type']);

				return (is_string($from) ? strcasecmp($value, $from) >= 0 : $value >= $from) 
					&& (is_string($to) ? strcasecmp($value, $to) <= 0 : $value <= $to);
			}
			else if (is_array($against))
			{
				//IN and NOT IN clauses are treated differently
				if ($operator == '=')
				{
					//for IN, the first match we succeed on makes the whole thing match
					foreach ($against as $comparedValue)
					{
						$comparedValue = $this->_comparableValue($comparedValue, $schema['type']);
						$matches = is_string($value) ? strcasecmp($value, $comparedValue) == 0 : $value === $comparedValue;
						
						if ($matches)
						{
							return true;
						}
					}
					
					return false;
				}
				else if ($operator == '<>' || $operator == '!=')
				{
					//for NOT IN, we have to match ALL of the elements in the array
					foreach ($against as $comparedValue)
					{
						$comparedValue = $this->_comparableValue($comparedValue, $schema['type']);
						$matches = is_string($value) ? strcasecmp($value, $comparedValue) <> 0 : $value !== $comparedValue;
					
						if (!$matches)
						{
							return false;
						}
					}
					
					return true;
				}
				else
				{
					throw new Exception("Invalid use of array in condition. Arrays are only supported for equality and inequality tests.");
				}
			}
	
			$against = $this->_comparableValue($against, $schema['type']);
			
			switch (strtolower($operator))
			{
				case '=':
					$matches = is_string($value) ? strcasecmp($value, $against) == 0 : $value === $against;
					break;
				case '>':
				
					if ($value === null || $against === null)
					{
						$matches = false;
					}
					else
					{
						$matches = is_string($value) ? strcasecmp($value, $against) > 0 : $value > $against;
					}
					
					break;
				case '>=':
				
					if ($value === null || $against === null)
					{
						$matches = false;
					}
					else
					{
						$matches = is_string($value) ? strcasecmp($value, $against) >= 0 : $value >= $against;
					}
					
					break;
				case '<':
				
					if ($value === null || $against === null)
					{
						$matches = false;
					}
					else
					{
						$matches = is_string($value) ? strcasecmp($value, $against) < 0 : $value < $against;
					}
					
					break;
				case '<=':
				
					if ($value === null || $against === null)
					{
						$matches = false;
					}
					else
					{
						$matches = is_string($value) ? strcasecmp($value, $against) <= 0 : $value <= $against;
					}
					
					break;
				case '<>':
				case '!=':
					$matches = is_string($value) ? strcasecmp($value, $against) <> 0 : $value !== $against;
					break;
				case 'like':
				
					//LIKE only works on strings
					if (!is_string($against))
					{
						$matches = false;
						break;
					}
					
					//if the LIKE expression is blank, just do an equality test
					if ($against == '')
					{
						$matches = $value == $against;
						break;
					}
					
					//prep our regular expression to have wildcards at the start or end depending
					//on the LIKE expression
					$before = substr($against, 0, 1) == '%' ? '.*' : '';
					$after = substr($against, strlen($against) - 1, 1) == '%' ? '.*' : '';
					
					//strip off the leading % if we have one
					if ($before != '')
					{
						$against = substr($against, 1);
					}

					//strip off the trailing % if we have one
					if ($after != '')
					{
						$against = substr($against, 0, strlen($against) - 1);
					}
					
					//now use a regular expression to match the value
					$matches = preg_match("/^{$before}" . preg_quote($against) . "{$after}$/i", $value);
					
					break;
			}
				
			return $matches;
		}
		
		/**
		 * Normally used by a driver to build a SQL expression that can be used for 
		 * aggregate functions (i.e. count, max, min, etc.). However, for our driver, we're only
		 * going to support 'count'.
		 * @param object $model The model to operate on.
		 * @param string $function Lowercase name of the function, must be 'count'.
		 * @param array $params Not used by our driver.
		 * @return string A special column name known by the driver to calculate a count.
		 * @access public
		 */
		function calculate(&$model, $function, $params = array())
		{
			if ($function == 'count')
			{
				return DboFu05::countField;
			}
			
			return null;
		}
	
		/**
		 * Updates all records matching the given conditions.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $fields The fields to update.
		 * @param array $values The new values for the fields being updated.
		 * @param array $conditions The conditions that a row must satisfy in order to be updated.
		 * @return bool True on success, false on failure.
		 */
		function update(&$model, $fields = array(), $values = null, $conditions = null) 
		{
			//forward the request to filepro if necessary
			if ($model->_saveViaFilepro)
			{
				return $this->_updateViaFilepro($model, $fields, $values, $conditions);
			}
			
			$schema = $this->describe($model, 'all');

			//make sure we have valid conditions
			if ($conditions === null)
			{
				$conditions = array();
			}

			//if the model has its ID set, add it to the conditions (since Model->save will actually
			//remove it from the conditions because the id field technically doesn't exist in FU05 - it WILL
			//however have set the model's ID by this point)
			if (!empty($model->id))
			{
				$conditions['id'] = $model->id;
			}

			//find the records that match the conditions
			$matches = $model->find('all', array('fields' => array('id'), 'conditions' => $conditions));
			
			//if we have no matches we're done
			if (count($matches) == 0)
			{
				return true;
			}
			
			try
			{
				$combined = array();

				//I'm modeling this after the dbo_source implementation - we're basically
				//combining fields and values into a single key => value pair array			
				if ($values === null) 
				{
					$combined = $fields;
				} 
				else 
				{
					$combined = array_combine($fields, $values);
				}

				//open for read/write
				$f = $this->_openFile($schema['data_path']);
				
				if ($f === false)
				{
					return false;
				}
				
				foreach ($matches as $match)
				{
					//seek to the proper spot in the file
					$id = $match[$model->alias]['id'];
					
					if (dio_seek($f, ($id - 1) * $schema['record_length'], SEEK_SET) == -1)
					{
						return false;
					}
					
					//lock the record
					if (!$this->_lockRecord($model, $f, $schema['data_path'], $id, $schema['record_length']))
					{
						//if we can't acquire a lock, we have to give up
						return false;
					}
					
					//read the current record
					$record = dio_read($f, $schema['record_length']);
					
					if ($record === null)
					{
						return false;
					}

					//update the record's values
					foreach ($combined as $field => $value)
					{
						$field = $this->_unqualifiedName($model, $field);

						if ($field === null)
						{
							continue;
						}
						
						//make sure the field exists
						if (array_key_exists($field, $schema['fields']))
						{
							//set the value to be whatever value was specified
							$value = $this->_formatValueForRecord($value, $schema['fields'][$field]);
							
							//overwrite that part of the buffer
							$record = substr($record, 0, $schema['fields'][$field]['position']) . $value . substr($record, $schema['fields'][$field]['position'] + $schema['fields'][$field]['length']);
						}
					}
										
					//seek back to the beginning of the record and overwrite it
					if (dio_seek($f, ($id - 1) * $schema['record_length'], SEEK_SET) == -1)
					{
						return false;
					}
					
					if (dio_write($f, $record) <= 0)
					{
						return false;
					}
					
					//unlock the record
					$this->_unlockRecord($model, $f, $id, $schema['record_length']);
					
					//update the indexes
					$this->_updateIndexRecords($model, $id, array_keys($combined), array_values($combined));
				}
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Updates a record in the FU05 file but via filepro. This can be used when filepro indexes need to be kept
		 * in sync with our own. Used internally when a model uses the saveViaFilepro method in our app_model.
		 * @param object $model The model to operate on.
		 * @param array $fields The fields to update.
		 * @param array $values The new values for the fields being updated.
		 * @param array $conditions The conditions that a row must satisfy in order to be updated.
		 * @return bool True on success, false on failure.
		 */
		function _updateViaFilepro(&$model, $fields = array(), $values = null, $conditions = null) 
		{
			//make sure we have valid conditions
			if ($conditions === null)
			{
				$conditions = array();
			}

			//if the model has its ID set, add it to the conditions (since Model->save will actually
			//remove it from the conditions because the id field technically doesn't exist in filepro - it WILL
			//however have set the model's ID by this point)
			if (!empty($model->id))
			{
				$conditions['id'] = $model->id;
			}

			//find the records that match the conditions
			$matches = $model->find('all', array('fields' => array("{$model->alias}.id"), 'conditions' => $conditions));
			
			//if we have no matches we're done
			if (count($matches) == 0)
			{
				return true;
			}
			
			try
			{
				//grab a mirrored model
				$mirror = $this->createMirrorModel($model);		
				
				//verify that the mirror and U05 file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} FU05 model is out-of-sync with mirror!");
					return false;
				}
					
				$combined = array();

				//I'm modeling this after the dbo_source implementation - we're basically
				//combining fields and values into a single key => value pair array			
				if ($values === null) 
				{
					$combined = $fields;
				} 
				else 
				{
					$combined = array_combine($fields, $values);
				}
				
				//go through each match
				foreach ($matches as $match)
				{
					//lock the model
					if (!$this->_lockForWrite($model, $match[$model->alias]['id']))
					{
						//if we can't acquire a lock, we have to give up
						return false;
					}
					
					//pull the whole record to make sure we have the latest information
					$data = $model->find('first', array('conditions' => array('id' => $match[$model->alias]['id']), 'contain' => array()));

					//set up our mirror fields
					$extraFields = array(
						'mirror_transaction_success' => null,
						'mirror_action_type' => 'U',
						'mirror_filepro_record_id' => $match[$model->alias]['id']
					);
					
					//remove the id so it doesn't try to actually update a mirror record
					unset($data[$model->alias]['id']);
					
					$mirror->create();
					
					//save every field from the original record while overriding anything that was supposed to be updated, and also include our mirror fields
					$mirror->save(array($mirror->alias => array_merge(
						$data[$model->alias], 
						$combined,
						$extraFields
					)));
					
					//kick off the process to do the update for the record
					$this->_invokeFileproWriterScript($mirror->useTable, $mirror->id);
					
					//if we updated successfully then we need to update our indexes
					$success = $mirror->field('mirror_transaction_success', array('id' => $mirror->id));
					
					if ($success)
					{
						$this->_updateIndexRecords($model, $match[$model->alias]['id'], array_keys($combined), array_values($combined));
					}

					//unlock the model
					$this->_unlockAfterWrite($model, $match[$model->alias]['id']);
					
					//stop processing if we failed to update one of the records
					if (!$success)
					{
						return false;
					}
				}
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Deletes all records matching the given conditions.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $conditions The conditions that a row must satisfy in order to be deleted.
		 * @return bool True on success, false on failure.
		 */
		function delete(&$model, $conditions = null)
		{
			//forward the request to filepro if necessary
			if ($model->_deleteViaFilepro)
			{
				return $this->_deleteViaFilepro($model, $conditions);
			}
			
			$schema = $this->describe($model, 'all');

			//make sure we have valid conditions
			if ($conditions === null)
			{
				$conditions = array();
			}
		
			//if the model has its ID set, add it to the conditions (since Model->save will actually
			//remove it from the conditions because the id field technically doesn't exist in FU05 - it WILL
			//however have set the model's ID by this point)
			if (!empty($model->id))
			{
				$conditions['id'] = $model->id;
			}
			
			//prevent a delete with no conditions
			if (empty($conditions))
			{
				return false;
			}

			//find the records that match the conditions
			$matches = $model->find('all', array('fields' => array('id'), 'conditions' => $conditions));

			//if we have no matches we're done
			if (count($matches) == 0)
			{
				return true;
			}
			
			try
			{
				//open for read/write
				$f = $this->_openFile($schema['data_path']);
				
				if ($f === false)
				{
					return false;
				}
				
				foreach ($matches as $match)
				{
					//seek to the proper spot in the file
					$id = $match[$model->alias]['id'];
					
					if (dio_seek($f, ($id - 1) * $schema['record_length'], SEEK_SET) == -1)
					{
						return false;
					}
					
					//lock the record
					if (!$this->_lockRecord($model, $f, $schema['data_path'], $id, $schema['record_length']))
					{
						//if we can't acquire a lock, we have to give up
						return false;
					}
					
					//create a new deleted record buffer
					$record = $this->_createDeletedRecordBuffer($model);
					
					//overwrite the record
					if (dio_write($f, $record) <= 0)
					{
						return false;
					}
					
					//unlock the record
					$this->_unlockRecord($model, $f, $id, $schema['record_length']);
					
					//update the indexes
					$this->_deleteIndexRecords($model, $id);
				}
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Deletes a record in the FU05 file but via filepro. This can be used when filepro indexes need to be kept
		 * in sync with our own. Used internally when a model uses the deleteViaFilepro method in our app_model.
		 * @param object $model The model to operate on.
		 * @param array $conditions The conditions that a row must satisfy in order to be deleted.
		 * @return bool True on success, false on failure.
		 */
		function _deleteViaFilepro(&$model, $conditions = null)
		{
			//make sure we have valid conditions
			if ($conditions === null)
			{
				$conditions = array();
			}

			//if the model has its ID set, add it to the conditions (since Model->save will actually
			//remove it from the conditions because the id field technically doesn't exist in U05 files - it WILL
			//however have set the model's ID by this point)
			if (!empty($model->id))
			{
				$conditions['id'] = $model->id;
			}
			
			//prevent a delete with no conditions
			if (empty($conditions))
			{
				return false;
			}

			//find the records that match the conditions
			$matches = $model->find('all', array('fields' => array("{$model->alias}.id"), 'conditions' => $conditions));
			
			//if we have no matches we're done
			if (count($matches) == 0)
			{
				return true;
			}
			
			try
			{
				//grab our schema for later
				$schema = $this->describe($model, 'all');
				
				//grab a mirrored model
				$mirror = $this->createMirrorModel($model);
				
				//verify that the mirror and filepro file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} FU05 model is out-of-sync with mirror!");
					return false;
				}
				
				//go through each match
				foreach ($matches as $match)
				{
					//lock the model
					if (!$this->_lockForWrite($model, $match[$model->alias]['id']))
					{
						//if we can't acquire a lock, we have to give up
						return false;
					}
					
					$mirror->create();
					
					//for a delete, we just need to save the extra mirror fields to let the process know what to delete
					$mirror->save(array($mirror->alias => array(
						'mirror_transaction_success' => null,
						'mirror_action_type' => 'D',
						'mirror_filepro_record_id' => $match[$model->alias]['id']
					)));
					
					//kick off the process to do the delete for the record
					$this->_invokeFileproWriterScript($mirror->useTable, $mirror->id);
					
					//if the delete was successful, we need to mark the record deleted in a way U05 understands, 
					//and also update our indexes
					$success = $mirror->field('mirror_transaction_success', array('id' => $mirror->id));
										
					if ($success)
					{
						//open for read/write
						$f = $this->_openFile($schema['data_path']);
						
						if ($f === false)
						{
							return false;
						}

						//seek to the proper spot in the file
						$id = $match[$model->alias]['id'];

						if (dio_seek($f, ($id - 1) * $schema['record_length'], SEEK_SET) == -1)
						{
							return false;
						}
						
						//create a new deleted record buffer
						$record = $this->_createDeletedRecordBuffer($model);
						
						//overwrite the record
						if (dio_write($f, $record) <= 0)
						{
							return false;
						}
						
						//update the indexes
						$this->_deleteIndexRecords($model, $id);
					}
					
					//unlock the model
					$this->_unlockAfterWrite($model, $match[$model->alias]['id']);
					
					//stop processing if we failed to delete one of the records
					if (!$success)
					{
						return false;
					}
				}
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
		}
		
		/**
		 * Formats a value for writing to an FU05 file.
		 * @param string $value The value to format.
		 * @param array $schema The schema for the field that the value is for.
		 * @access private
		 * @return string A formatted value.
		 */
		function _formatValueForRecord($value, $schema)
		{
			if ($value === null)
			{
				$value = '';
			}
			else
			{
				switch ($schema['type'])
				{
					case 'boolean':
						//coerse the value to Y, N, or blank
						$value = ($value === true || $value === 1 || $value === '1')
							? 'Y' 
							: (
								($value === false || $value === 0 || $value === '0')
								? 'N' 
								: (
									trim($value) === '' 
									? '' 
									: (
										(bool)$value 
										? 'Y' 
										: 'N'
									)
								)
							);
						break;
					case 'date':
						if ($value !== '')
						{
							$value = trim($value);
							
							//as long as the date is in YYYY-mm-dd, coerce it to MMDDYYYY
							if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
							{
								$value = substr($value, 5, 2) . substr($value, 8, 2) . substr($value, 0, 4);
							}
							//if the date is in mm/dd/YYYY, coerce it to MMDDYYYY
							else if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value))
							{
								$value = substr($value, 0, 2) . substr($value, 3, 2) . substr($value, 6, 4);
							}
							else
							{
								$value = '';
							}
						}
						break;
					case 'int':
					case 'float':
						if ($value !== '')
						{
							//trim the value, pad it with spaces on the left, and then trim to the length of the field 
							$value = substr(str_pad(trim($value), $schema['length'], ' ', STR_PAD_LEFT), 0, $schema['length']);
						}
						
						break;
					default:
						if ($value !== '')
						{
							//trim to the length of the field
							$value = substr($value, 0, $schema['length']);
							
							//respect the ALLUP filepro constraint that uppercases the value before saving
							if ($schema['fileproType'] == 'ALLUP' || $schema['fileproType'] == 'UP')
							{
								$value = strtoupper($value);
							}
						}
						
						break;
				}
			}
			
			//pad the value to the length of the field
			return str_pad($value, $schema['length']);
		}
		
		/**
		 * Returns a value that came from FU05 into its converted form as defined in the schema
		 * that can then be used in PHP (basically this is to convert values from FU05 before returning
		 * them from a find() call).
		 * @param string $value The value to get the PHP value for.
		 * @param string $type The type that the value should be.
		 * @access private
		 * @return mixed A converted value.
		 */
		function _phpValue($value, $type)
		{
			switch ($type)
			{
				case 'date':
					//for dates, we want to return those as yyyy-mm-dd
					return trim($value) === '' ? null : (substr($value, 4) . '-' . substr($value, 0, 2) . '-' . substr($value, 2, 2));
				default:
					//just use comparable for now
					return $this->_comparableValue($value, $type);
			}
		}
		
		/**
		 * Takes a value and returns a massaged version that can be used to compare two values of the same type.
		 * This should be used against PHP values - for values that come from U05, they should pass through $this->_phpValue
		 * first.
		 * @param mixed $value The value to convert.
		 * @param string $type The type that the value should be.
		 * @return mixed The comparable form of the value.
		 */
		function _comparableValue($value, $type)
		{			
			switch ($type)
			{
				case 'boolean':
					$value = $value == 'Y' ? true : ($value == 'N' ? false : (trim($value) === '' ? null : (bool)$value));
					break;
				case 'date':
					$value = trim($value) === '' 
						? null 
						: (
							preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
							? (substr($value, 0, 4) . substr($value, 5, 2) . substr($value, 8, 2))
							: (
								preg_match('/^\d{8}$/', $value)
								? (substr($value, 4) . substr($value, 0, 4))
								: null
							)
						);
					break;
				case 'int':
					$value = trim($value);
					$value = $value === '' ? null : (is_numeric($value) ? (int)$value : null);
					break;
				case 'float':
					$value = trim($value);
					$value = $value === '' ? null : (is_numeric($value) ? (float)$value : null);
					break;
			}
					
			return $value;
		}
		
		/**
		 * Takes a value and returns a massaged version that can be used in a SQL statement (mostly
		 * used when looking up indexes).
		 * @param mixed $value The value to convert.
		 * @param string $type The type that the value should be.
		 * @return mixed The escaped SQL form of the value.
		 */
		function _sqlValue($value, $type)
		{
			switch ($type)
			{
				case 'boolean':
					$value = $value == 'Y' ? 1 : ($value == 'N' ? 0 : (trim($value) === '' ? 'NULL' : ((bool)$value ? 1 : 0)));
					break;
				case 'date':
					if (trim($value) === '')
					{
						$value = 'NULL';
					}
					else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
					{
						$value = "'" . Sanitize::escape($value) . "'";
					}
					else
					{
						$value = 'NULL';
					}
					break;
				case 'int':
					$value = is_numeric($value) ? (int)$value : 'NULL';
					break;
				case 'float':
					$value = is_numeric($value) ? (float)$value : 'NULL';
					break;
				default:
					$value = "'" . Sanitize::escape($value) . "'";
					break;	
			}
					
			return $value;
		}
		
		/**
		 * Takes a given set of schema (for a single field from the describe() method) and returns
		 * the SQL type for the column.
		 * @param array $schema The schema for a single column.
		 * @return string The SQL type for the column.
		 */
		function _toSqlType($schema)
		{
			switch ($schema['type'])
			{
				case 'boolean':
					return 'bool';
				case 'date':
					return 'date';
				case 'int':
					return 'int';
				case 'float':
					preg_match('/^\.([1-9])$/', $schema['fileproType'], $matches);
					return "decimal({$schema['length']}, {$matches[1]})";
				default:
					return "varchar({$schema['length']})";
			}
		}
		
		/**
		 * Opens a particular file and returns the file handle, or returns a previously cached file handle. The
		 * file will be opened for read/write access. 
		 * 
		 * IMPORTANT: Because you may get back a previously cached handle, never assume that the current file position
		 * pointer is at the beginning of the file. Also, since these handles are cached, do NOT call dio_close on
		 * a returned handle.
		 *
		 * @param string $path The full, physical path to the file.
		 * @param bool $forAppend States whether or not to open the file for appending. False by default.
		 * @return handle A handle to the file that can be used by other dio_* operations.
		 */
		function _openFile($path, $forAppend = false)
		{
			if (!isset($this->_openFileCache[$path]))
			{
				$this->_openFileCache[$path] = array();
			}
			
			$key = $forAppend ? 'a' : 'w';
			
			if(!isset($this->_openFileCache[$path][$key]))
			{
				$this->_openFileCache[$path][$key] = dio_open($path, $forAppend ? (O_WRONLY | O_APPEND) : O_RDWR);
			}
			
			return $this->_openFileCache[$path][$key];
		}
		
		/**
		 * Performs row-level locking on an FU05 file. The function will wait indefinitely until a 
		 * lock can be acquired.
		 * @param Model The model the lock is being applied to.
		 * @param numeric $file The file handle of the file to lock (created by dio_open).
		 * @param string $path The full path to the file, including the file name.
		 * @param numeric $recordNumber The record to lock.
		 * @param numeric $recordLength The length of the record.
		 * @return boolean True if the lock was acquired, false otherwise.
		 */
		function _lockRecord(&$model, $file, $path, $recordNumber, $recordLength)
		{
			//don't apply a lock to a model who has already had a manual lock already placed on the same record
			if ($model->Behaviors->enabled('Lockable') && $model->isLocked($recordNumber))
			{
				return true;
			}
		
			$start = ($recordNumber - 1) * $recordLength;
			$result = -1;
			//pr(date('H:i:s') . " - locking {$recordNumber} ({$start} - " . ($start + $recordLength - 1) . ')');

			//try and lock the real region that we want
			for ($i = 0; $i < $this->lockRetries; $i++)
			{
				$result = dio_fcntl($file, F_SETLK, array(
					'type' => F_WRLCK,
					'whence' => SEEK_SET, 
					'start' => (string)$start,  //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
					'length' => (int)$recordLength //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)
				));
				
				//if we didn't get the lock, go to sleep a bit before trying again
				if ($result == -1)
				{
					usleep($this->lockWaitInterval * 1000);
				}
				else
				{
					//if we got the lock, we're free to short-circuit
					break;
				}
			}
						
			//if we can't acquire the lock we want, we're done
			if ($result == -1)
			{
				$this->_syslog("Failed to acquire lock on record {$recordNumber} of file: {$path}");
				return false;
			}
			
			//reset the lock result
			$result = -1;
			
			//now we lock the crazy extended region that filePro locks so that it will respect our lock as well
			for ($i = 0; $i < $this->lockRetries; $i++)
			{
				$result = dio_fcntl($file, F_SETLK, array(
					'type' => F_WRLCK,
					'whence' => SEEK_SET, 
					'start' => (string)($this->fileproLockOffset + $recordNumber), //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
					'length' => (int)1 //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)
				));
				
				//if we didn't get the lock, go to sleep a bit before trying again
				if ($result == -1)
				{
					usleep($this->lockWaitInterval * 1000);
				}
				else
				{
					//if we got the lock, we're free to short-circuit
					break;
				}
			}
			
			//if we can't acquire the filePro lock, we have to unlock the original region we locked before
			//returning false
			if ($result == -1)
			{
				$this->_syslog("Failed to acquire extended filePro lock on record {$recordNumber} of file: {$path}");
				
				dio_fcntl($file, F_SETLK, array(
					'type' => F_UNLCK,
					'whence' => SEEK_SET, 
					'start' => (string)$start, //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
					'length' => (int)$recordLength //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)
				));
				
				return false;
			}
			
			//pr(date('H:i:s')  . " acquired lock");
			
			return true;
		}
		
		/**
		 * Unlocks a row-level lock on an FU05 file.
		 * @param Model The model the lock is being applied to.
		 * @param numeric $file The file handle of the file to unlock (created by dio_open).
		 * @param numeric $recordNumber The record to unlock.
		 * @param numeric $recordLength The length of the record.
		 */
		function _unlockRecord(&$model, $file, $recordNumber, $recordLength)
		{
			//don't unlock a record on a model that has acquired a manual lock on the same record
			if ($model->Behaviors->enabled('Lockable') && $model->isLocked($recordNumber))
			{
				return;
			}
			
			$start = ($recordNumber - 1) * $recordLength;
			
			//pr(date('H:i:s') . " - unlocking {$recordNumber} ({$start} - " . ($start + $recordLength - 1) . ')');
			
			//first remove the real lock
			dio_fcntl($file, F_SETLK, array(
				'type' => F_UNLCK,
				'whence' => SEEK_SET, 
				'start' => (string)$start, //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
				'length' => (int)$recordLength //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)			
			));
			
			//then remove the crazy extended filePro lock
			dio_fcntl($file, F_SETLK, array(
				'type' => F_UNLCK,
				'whence' => SEEK_SET, 
				'start' => (string)($this->fileproLockOffset + $recordNumber), //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES 
				'length' => (int)1 //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)			
			));
		}
		
		/**
		 * Locks the model for writing using MySQL for the lock - this is only used when writing records via filepro.
		 * @param object $model The model to operate on.
		 * @param int $id The ID of the record to lock.
		 * @return boolean True if the lock was acquired, false otherwise.
		 */
		function _lockForWrite(&$model, $id)
		{
			$lock = Sanitize::escape($model->useTable . '.' . $id) . '.write_lock';
			$lockModel = $this->_lockModel();
			$acquired = false;

			try
			{
				//try to acquire the lock for as many times as we're configured for
				for ($i = 0; $i < $this->lockRetries; $i++)
				{
					$result = $lockModel->query("select get_lock('{$lock}', 60) as locked");
					$result = $result[0][0]['locked'];
					
					//if we got the lock, we're free to short-circuit
					if ($result == 1)
					{
						$acquired = true;
						break;
					}
					else
					{
						//if we didn't get the lock, go to sleep a bit before trying again
						usleep($this->lockWaitInterval * 1000);
					}
				}
							
				//if we can't acquire the lock we want log it to the sys log before we give up
				if (!$acquired)
				{
					$this->_syslog("Failed to acquire MySQL lock on: {$model->useTable}");
				}
			}
			catch (Exception $ex)
			{
				//release the lock (it doesn't matter if we didn't get the lock to begin with, it'll just return null if we don't have one)
				$lockModel->query("do release_lock('{$lock}')");
			}
			
			return $acquired;
		}
		
		/**
		 * Unlocks a MySQL lock for the model after writing - this is only used when writing records via filepro.
		 * @param object $model The model to operate on.
		 * @param int $id The ID of the record to unlock.
		 */
		function _unlockAfterWrite(&$model, $id)
		{
			$lock = Sanitize::escape($model->useTable . '.' . $id) . '.write_lock';
			$this->_lockModel()->query("do release_lock('{$lock}')");
		}
		
		/**
		 * Returns the ID (record number) of the most recently inserted row.
		 * @inherited
		 * @param object $source Not used.
		 * @return int The ID (record number) of the most recently inserted row, or null
		 * if no record has been inserted yet.
		 */
		function lastInsertId($source = null) 
		{ 
			return $_lastInsertID; 
		}
		
		/**
		 * Creates index records for the specified FU05 record.
		 * @param object $model The model to operate on.
		 * @param numeric $recordNumber The record number to insert indexes for.
		 * @param array $fields An array of field names to insert indexes for.
		 * @param array $values A cooresponding array of values to insert into the indexes.
		 * @access private
		 */
		function _insertIndexRecords(&$model, $recordNumber, $fields, $values)
		{
			if (!$model->Behaviors->enabled('Indexable'))
			{
				return;
			}
			
			$schema = $this->describe($model);
			
			//because the user might not necessarily provide values for all
			//fields when inserting a new record, we have to ensure that every
			//field is filled out here before we can update indexes. That way
			//we guarantee that every index is updated.
			foreach ($schema as $field => $definition)
			{
				if (!in_array($field, $fields))
				{
					$fields[] = $field;
					$values[] = '';
				}
			}

			//now we're safe to update the indexes
			$this->_updateIndexRecords($model, $recordNumber, $fields, $values);
		}
		
		/**
		 * Updates index records for the specified FU05 record.
		 * @param object $model The model to operate on.
		 * @param numeric $recordNumber The record number to update indexes for.
		 * @param array $fields An array of field names to update indexes for.
		 * @param array $values A cooresponding array of values to update in the indexes.
		 * @access private
		 */
		function _updateIndexRecords(&$model, $recordNumber, $fields, $values)
		{
			if (!$model->Behaviors->enabled('Indexable'))
			{
				return;
			}
			
			$indexes = $this->describe($model, 'indexes');
			$indexModel = $this->_indexModel();
			
			//go through each field being updated
			foreach ($fields as $i => $field)
			{
				//see if there's an index for that field
				if (array_key_exists($field, $indexes))
				{
					$value = Sanitize::escape($values[$i]);
					
					//if there is, update the value in the index
					$indexModel->query("
						insert into `{$indexes[$field]}` (value, record_number) values
						('{$value}', {$recordNumber}) on duplicate key update value = '{$value}'
					", false);
				}
				
				//see if there is an unchained index for the field if we're dealing with a chainable model
				if ($model->Behaviors->enabled('Chainable') && in_array($field, $model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes']))
				{
					$value = Sanitize::escape($values[$i]);
					$table = $model->indexName($field, false, true);
					
					//if there is, update the value in the index
					$indexModel->query("
						insert into `{$table}` (value, record_number) values
						('{$value}', {$recordNumber}) on duplicate key update value = '{$value}'
					", false);
				}
			}
		}
		
		/**
		 * Deletes index records for the specified FU05 record.
		 * @param object $model The model to operate on.
		 * @param numeric $recordNumber The record number to delete indexes for.
		 * @access private
		 */
		function _deleteIndexRecords(&$model, $recordNumber)
		{
			if (!$model->Behaviors->enabled('Indexable'))
			{
				return;
			}
			
			$indexes = $this->describe($model, 'indexes');
			$indexModel = $this->_indexModel();
			
			//just go through the indexes and remove the specified record number's index records.
			foreach ($indexes as $table)
			{
				$indexModel->query("delete from `{$table}` where record_number = {$recordNumber}", false);
			}
			
			//for chainable models, remove the record from any unchained indexes
			if ($model->Behaviors->enabled('Chainable'))
			{
				foreach ($model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes'] as $field)
				{
					$table = $model->indexName($field, false, true);
					$indexModel->query("delete from `{$table}` where record_number = {$recordNumber}", false);
				}
			}
		}
		
		/**
		 * Defrags all FU05 files and rebuilds all indexes.
		 * @return string A log of the rebuilding process.
		 */
		function rebuild()
		{
			$out = '';
			$start = microtime(true);
			
			//grab all available models
			$models = Configure::listObjects('model');
			
			//first we defrag any defraggable models
			foreach ($models as $modelName)
			{
				$model = ClassRegistry::init($modelName);
				
				if ($model->Behaviors->enabled('Defraggable'))
				{
					$mark = microtime(true);
					$out .= date('m/d/Y H:i:s') . " - Defragging: {$model->useTable}.\n";
					
					$model->defrag(false);
					
					$out .= date('m/d/Y H:i:s') . " - Successfully defragged: {$model->useTable} (" . (microtime(true) - $mark) . " seconds).\n";
				}
			}

			//now we rebuild the indexes on all models. The reason we rebuild for all models instead of only those
			//that have been defragged is because otherwise we wouldn't get the changes that were initiated from filePro.
			//Remember, this driver is just for the web, but people are still using filePro to insert/update/delete records
			//to these same FU05 files.
			foreach ($models as $modelName)
			{	
				$model = ClassRegistry::init($modelName);
				
				//make sure the model uses MySQL indexes
				if ($model->Behaviors->enabled('Indexable'))
				{
					$mark = microtime(true);
					$out .= date('m/d/Y H:i:s') . " - Rebuilding indexes for: {$model->useTable}.\n";
					
					$model->rebuildIndexes();

					$out .= date('m/d/Y H:i:s') . " - Successfully rebuilt indexes. (" . (microtime(true) - $mark) . " seconds).\n";
				}
			}
			
			$out .= date('m/d/Y H:i:s') . " -  Done (" . (microtime(true) - $start) . " seconds).\n";
			
			return $out;
		}
		
		/**
		 * Writes a message to the system log for eMRS.
		 * @param string $message The message to write.
		 */
		function _syslog($message)
		{
			openlog('emrs', LOG_ODELAY, LOG_USER);
			syslog(LOG_WARNING, $message);
			closelog();
		}
		
		/**
		 * Creates a model that is used to mirror the FU05 model passed in. Used for inserting, updating, and deleting with via filepro.
		 * @param object $model The model to create the mirror for.
		 */
		function createMirrorModel(&$model)
		{
			return ClassRegistry::init(array(
				'class' => DboFu05::mirrorPrefix . $model->name . DboFu05::mirrorSuffix, 
				'alias' => $model->name . DboFu05::mirrorSuffix, 
				'table' => DboFu05::mirrorPrefix . $model->useTable . DboFu05::mirrorSuffix,
				'ds' => 'fu05'
			));
		}
		
		/**
		 * Used to verify the schema of an FU05 model against its mirror to make sure the two are identical.
		 * @param object $model The source U05 model.
		 * @param object $mirror The U05 mirror model.
		 * @return bool True if the mirror is identical, false otherwise.
		 */
		function _verifyMirror(&$model, &$mirror)
		{
			//grab schema from both models but be sure to get a fresh copy of both schemas and not from the cache
			$cached = $this->cacheSources;
			$this->cacheSources = false;

			$schema = $this->describe($model, 'all');
			$mirrorSchema = $this->describe($mirror, 'all');
			
			//reset the cache sources values of the driver
			$this->cacheSources = $cached;
			
			//make sure the record length matches (we can't just compare against recordLength() because of the possibility of unshrunken files)
			$sourceLength = 0;
			
			//go through all the fields in the model to sum up the record length
			foreach ($schema['fields'] as $field => $definition)
			{
				$sourceLength += $definition['length'];
			}
			
			if ($sourceLength != $mirrorSchema['record_length'] - array_sum(Set::extract($this->mirrorFields, '{s}.length')))
			{
				return false;
			}
			
			//grab the field names
			$fields = array_keys($schema['fields']);
			$mirrorFields = array_keys($mirrorSchema['fields']);
			
			//make sure we have the same number of fields (ignoring the special fields that we add to the mirror)
			if (count($fields) != count($mirrorFields) - count($this->mirrorFields))
			{
				return false;
			}
			
			foreach ($fields as $field)
			{				
				//make sure the field exists
				if (!array_key_exists($field, $mirrorSchema['fields']))
				{
					return false;
				}
				
				//make sure it's the same ordinal
				if ($schema['fields'][$field]['ordinal'] != $mirrorSchema['fields'][$field]['ordinal'])
				{
					return false;
				}
				
				//make sure it's in the same position
				if ($schema['fields'][$field]['position'] != $mirrorSchema['fields'][$field]['position'])
				{
					return false;
				}
				
				//make sure it's type same type
				if ($schema['fields'][$field]['fileproType'] != $mirrorSchema['fields'][$field]['fileproType'])
				{
					return false;
				}
				
				//make sure it's the same length
				if ($schema['fields'][$field]['length'] != $mirrorSchema['fields'][$field]['length'])
				{
					return false;
				}
			}
			
			return true;			
		}
		
		/**
		 * Executes the shell script used by the driver to trigger inserts, updates, and deletes via filepro after inserting records into a mirror table.
		 * @param string $mirrorTable The name of the mirror table to work on (physical table name, case-sensitive).
		 * @param int $mirrorRecordID The ID of the record in the mirror file containing the data to process.
		 * @param bool $highSpeed Pass true to have the process not use any sort of wait periods and timeouts to try and wait for the filepro processing
		 * to end gracefully. Typically you only want to do this if you can guarantee that no one will be in the filepro tables that you're writing to, because
		 * in the case of a locked record in filepro, you could be waiting indefinitely.
		 */		
		function _invokeFileproWriterScript($mirrorTable, $mirrorRecordID, $highSpeed = false)
		{			
			exec(sprintf('%s %s %s %s %s %s' . ($highSpeed ? ' 1' : ''),
				escapeshellarg(APP . 'vendors' . DS . 'shells' . DS . $this->fileproWriterScript),
				escapeshellarg($mirrorTable),
				escapeshellarg($mirrorRecordID),
				escapeshellarg(DboFu05::fileproWriteRetries),
				escapeshellarg(DboFu05::fileproWriteInitialWaitPeriodMicroseconds),
				escapeshellarg(DboFu05::fileproWritePeriodicWaitPeriodMicroseconds)
			));
		}
	}
?>