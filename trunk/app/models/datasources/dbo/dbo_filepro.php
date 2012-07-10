<?php
	uses('Inflector');
	uses('Folder');
	uses('Sanitize');
	
	//needed for indexing
	App::import('Vendor', 'filepro/b_plus_tree');
	App::import('Vendor', 'filepro/filepro_key_file');
	App::import('Vendor', 'filepro/filepro_index_controller');
	
	//needed for writing records through FU05 mirrors
	App::import('Model', 'User');
		
	/**
	 * DataSource to handle interaction with filePro files. To enable a model
	 * to use this data source, you must set up the model's $useDbConfig variable to use
	 * a data source that has been configured for this driver in your applications database.php
	 * file. That data source must have the following keys:
	 *
	 * 		file_path - This should be the absolute physical path to the location where the filePro files exist.
	 * 		lock_model - This is the name of the model that will be used to create locks in the MySQL
	 *					 database to provide syncronization when writing to a filepro file.
	 *
	 * In your models, once configured to use this datasource, they must use the $useTable variable
	 * to specify the name of the table to use (ex. ORD_MEMO). Make sure the table name is in all-caps.
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
	 * Finally, the driver also supports a key called 'index'. This lets you specify which filepro index to use when searching
	 * the data. Simply give the number or letter of the index (ex. 'A') for the index to use. You can use the 
	 * /brian/indexes/[modelName] action to see what fields the indexes use.
	 */
	class DboFilepro extends DataSource
	{
		/** Class description, used by Cake. */
		var $description = 'filePro DataSource';
		
		/** Keeps track of the last inserted ID (record number) into a file. */
		var $_lastInsertID = null;
		
		/** This is the byte offset where filePro begins to lock files when it is updating a record. */
		var $fileproLockOffset = 16;
		
		/** 
		 * These are used by our locking mechanism to state how many times to retry when a lock 
		 * cannot be acquired, as well as how long to wait between attempts.
		 */
		var $lockRetries = 60;
		var $lockWaitInterval = 500; //milliseconds
		
		/** The header length on each non-deleted record in the key file. */
		var $keyFileHeaderLength = 20;
		
		const countField = '::count';
		const ifNullPattern = "/^ifnull\(([a-z0-9._]+),\s*(.+?)\)/i";
		
		/** These constants are used to control timeout periods when writing data to filepro */
		const fileproWriteRetries = 4;
		const fileproWriteInitialWaitPeriodMicroseconds = 200000;
		const fileproWritePeriodicWaitPeriodMicroseconds = 500000;
		
		/** The name of the shell script that kicks off filepro processing to write data to filepro. */
		var $fileproWriterScript = 'filepro_writer.sh';
		
		/** 
		 * This is the prefix that is placed in front of the name of the $useTable for a filepro model that will give it the name of 
		 * the mirrored FU05 model that will be used for writing to the filepro file.
		 */
		 const mirrorPrefix = 'z';
		 
		/** 
		 * This is the suffix that is appended to the name of the $useTable for a filepro model that will give it the name of 
		 * the mirrored FU05 model that will be used for writing to the filepro file.
		 */
		const mirrorSuffix = '_MIRROR';
		
		/** Valid operators supported by the driver */
		var $operators = '/(=|>|>=|<|<=|<>|!=|LIKE|BETWEEN)$/i';
		
		/** Keeps handles to open files. */
		var $_openFileCache = array();
		
		/** The user cache model used to translate UIDs to usernames. */
		var $_cacheModel;
		
		/** The driver's own cache of UID -> username mapping. */
		var $_userCache = array();
		
		/** The driver's own cache of bad usernames (those that can't resolve to a UID). */
		var $_invalidUsers = array();
		
		/** Column definitions for the driver - needed by Cake. */
		var $columns = array(
			//'primary_key' => array('name' => 'NOT NULL AUTO_INCREMENT'),
			'boolean' => array('name' => 'YN', 'limit' => '1'),
			'date' => array('name' => 'MDYY', 'format' => 'Y-m-d', 'formatter' => 'date'),
			'int' => array('name' => '.0', 'limit' => '11', 'formatter' => 'intval'),
			'float' => array('name' => '.N', 'formatter' => 'floatval'),
			'string' => array('name' => '*', 'limit' => '5000'),
			'time' => array('name' => 'TIME', 'format' => 'Y-m-d', 'formatter' => 'date'),
		);
		
		/** These are fields that can be fetch in a record but are actually stored in the header (i.e. they aren't defined in the map file). */
		var $headerFields = array(
			'created' => array(
				'ordinal' => -1,
				'position' => -1,
				'fileproType' => 'time',
				'type' => 'time',
				'null' => false,
				'default' => '',
				'length' => 2
			),
			'created_by' => array(
				'ordinal' => -1,
				'position' => -1,
				'fileproType' => '.0',
				'type' => 'string', //actually stored as an int (uid) in the record header, but we're going to translate them to usernames, especially since the indexes store them that way.
				'null' => false,
				'default' => '',
				'length' => 8
			),
			'modified' => array(
				'ordinal' => -1,
				'position' => -1,
				'fileproType' => 'time',
				'type' => 'time',
				'null' => false,
				'default' => '',
				'length' => 2
			),
			'modified_by' => array(
				'ordinal' => -1,
				'position' => -1,
				'fileproType' => '.0',
				'type' => 'string', //actually stored as an int (uid) in the record header, but we're going to translate them to usernames, especially since the indexes store them that way.
				'null' => false,
				'default' => '',
				'length' => 8
			)
		);
		
		/** These are the extra fields that we use when creating a mirror for a filePro file. The mirror is used to forward write requests (insert/update/delete) to our generated filepro scripts.  */
		var $mirrorFields = array(
			'mirror_created' => array(
				'length' => 8,
				'type' => 'MDYY'
			),
			'mirror_created_by' => array(
				'length' => 8,
				'type' => '.0'
			),
			'mirror_modified' => array(
				'length' => 8,
				'type' => 'MDYY'
			),
			'mirror_modified_by' => array(
				'length' => 8,
				'type' => '.0'
			),
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
			parent::__destruct();
		}
		
		/**
		 * Gets the absolute path to the map file for the filePro file.
		 * @param object $model The database model to operate on.
		 * @return string The absolute path to the map file for the given model.
		 * @access private
		 */
		function _mapPath(&$model)
		{
			return $this->config['file_path'] . DS . $this->fullTableName($model) . DS . 'map';
		}
		
		/**
		 * Gets the path to where the index files are located for the model.
		 * @param object $model The database model to operate on.
		 * @return string The absolute path to the directory where the indexes are located for the given model.
		 * @access private
		 */
		function _indexPath($model)
		{
			return $this->config['file_path'] . DS . $this->fullTableName($model);
		}
		
		/**
		 * Method that will return all available filePro files.
		 * @inherited
		 * @param mixed $data Not used.
		 * @return array An array of filePro files that are available on disk.
		 */
		function listSources($data = null)
		{
			$folder = new Folder($this->config['file_path']);
			return array_shift($folder->read());
		}
		
		/**
		 * Method (required, not inherited) that gets the full "table" name of the filePro file.
		 * @param object $model The model to operate on.
		 * @param bool $quote Not used.
		 * @return The full name of the filePro file.
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
		 * Method that gets the schema of the model. For filePro, this 
		 * is the information that is stored in a map file.
		 * @inherited
		 * @param object $model The model to describe.
		 * @param string $type NEW - This argument can be one of the following:
		 * 		null (default) - returns the fields from the map (behaves just like MySQL driver)
		 * 		all - returns a hash of key_path, record_path, key_length, data_length, and fields
		 *		fields - returns the same as passing null
		 * 		key_path - returns the path to the key file for the table
		 *		data_path - returns the path to the data file for the table
		 * 		key_length  - returns the value of the record length of a single record in the key file
		 * 		data_length  - returns the value of the record length of a single record in the data file
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

			//pop off the header
			$header = explode(':', array_shift($data));
			
			$keyLength = $this->keyFileHeaderLength + $header[1];
			$keyPath = $this->config['file_path'] . DS . $this->fullTableName($model) . DS . 'key';
			$dataLength = $header[2];
			$dataPath = $this->config['file_path'] . DS . $this->fullTableName($model) . DS . 'data';
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
						$parts['group'] = $matches[1];
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
						'group' => isset($parts['group']) ? $parts['group'] : null,
						'fileproType' => $parts['type'],
						'type' => $this->column($parts['type']),
						'null' => false,
						'default' => '',
						'length' => $parts['length'],
						'segment' => $position >= $keyLength - $this->keyFileHeaderLength ? 'data' : 'key'
					);
					
					$position += $parts['length'];
				}
			}
			
			//include the header field schema definitions
			$fields = $fields + $this->headerFields;			
			
			//now for the indexes
			$indexes = $this->_getIndexes($model, $fields);

			$description = array('key_path' => $keyPath, 'data_path' => $dataPath, 'key_length' => $keyLength, 'data_length' => $dataLength, 'fields' => $fields, 'indexes' => $indexes);
			$this->__cacheDescription($this->fullTableName($model), $description);

			return ($type === null || $type == '' ? $description['fields'] : ($type == 'all' ? $description : $description[$type]));
		}
		
		/**
		 * Gets all available indexes for the filePro model.
		 * @param object $model The model to load indexes for.
		 * @param array An array of field definitions for the model.
		 * @return array An aray of indexes indexed by their name (index.A, index.B, etc.)
		 */
		function _getIndexes(&$model, $fields)
		{
			$indexes = array();
			$indexPath = $this->_indexPath($model);
			$f = new Folder($indexPath);
			$files = $f->find('index.[A-Z]');

			foreach ($files as $file)
			{
				$indexes[$file] = new FileproIndexController($indexPath . DS . $file, $fields);
			}
			
			return $indexes;
		}
		
		/**
		 * Gets the model used to create locks in MySQL for writing to the filepro files.
		 * @return object The model used for locking.
		 * @access private
		 */
		function _lockModel()
		{
			return ClassRegistry::init($this->config['lock_model']);
		}
		
		/**
		 * Gets the physical record length of the key file for the filePro model.
		 * @param object The model to operate on.
		 * @return number The length of a single record in the key file.
		 */
		function keyLength(&$model)
		{
			return $this->describe($model, 'key_length');
		}
		
		/**
		 * Gets the absolute path to the filePro model's key file.
		 * @param object The model to operate on.
		 * @return number The absolute path of the filePro key file.
		 */
		function keyPath(&$model)
		{
			return $this->describe($model, 'key_path');
		}
		
		/**
		 * Gets the physical record length of the data file for the filePro model.
		 * @param object The model to operate on.
		 * @return number The length of a single record in the key file.
		 */
		function dataLength(&$model)
		{
			return $this->describe($model, 'data_length');
		}
		
		/**
		 * Gets the absolute path to the filePro model's data file.
		 * @param object The model to operate on.
		 * @return number The absolute path of the filePro key file.
		 */
		function dataPath(&$model)
		{
			return $this->describe($model, 'data_path');
		}
		
		/**
		 * Determines the equivalent PHP data type that matches a data type in the filePro map file. 
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
			else if ($real == 'time')
			{
				return 'time';
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
		 * Creates a model that is used to mirror the filepro model passed in. Used for inserting, updating, and deleting with filepro models.
		 * @param object $model The filepro model to create the mirror for.
		 */
		function createMirrorModel(&$model)
		{
			return ClassRegistry::init(array(
				'class' => DboFilepro::mirrorPrefix . $model->name . DboFilepro::mirrorSuffix, 
				'alias' => $model->name . DboFilepro::mirrorSuffix, 
				'table' => DboFilepro::mirrorPrefix . $model->useTable . DboFilepro::mirrorSuffix,
				'ds' => 'fu05'
			));
		}
		
		/**
		 * This updates the record headers in the filepro file. This function does NOT lock 
		 * the record being updated before doing the work. The calling method should probably
		 * wrap its call with a _lockForWrite and _unlockAfterWrite. The reason we don't lock in here
		 * is because if other methods that use this already have a lock on the record, locking again
		 * in here would cause the lock to be released before acquiring again. That's the nature
		 * of MySQL locks.
		 * @param 
		 */
		function _updateRecordHeaders(&$model, $id, $created = null, $createdBy = null, $modified = null, $modifiedBy = null)
		{
			//grab the existing header
			$keyFile = $this->_openFile($this->keyPath($model));
			$keyLength = $this->keyLength($model);
			
			//make sure we have a good file handle
			if ($keyFile === false)
			{
				return false;
			}
			
			//if we can't seek to it give up
			if (dio_seek($keyFile, $id * $keyLength, SEEK_SET) == -1)
			{
				return false;
			}
			
			$data = dio_read($keyFile, $this->keyFileHeaderLength);
			
			//if we can't read it give up
			if ($data === null)
			{
				return false;
			}

			/*pr($data);		
			
			//Hexdump of the data
			echo '<pre>';
			for ($i = 0; $i < strlen($data); $i++)
			{
				echo ByteConverter::bin2Hex(substr($data, $i, 1)) . ' ';
				
				if ($i > 0 && ($i + 1) % 16 == 0)
				{
					echo '<br />';
				}
			}
			
			echo '<br /><br />';
			echo '</pre>';*/
				
			//see if the record is deleted (if so we don't update the headers)
			$isDeleted = !ByteConverter::bin2Number(substr($data, 0, 1));
			
			if (!$isDeleted)
			{
				if ($created !== null)
				{
					$value = $this->_formatValueForRecord($created, array('type' => 'time', 'length' => 8));
					$data = substr($data, 0, 2) . ByteConverter::number2Bin($value, true) . substr($data, 4);
				}
				
				if ($createdBy !== null)
				{
					$value = $this->_formatValueForRecord($createdBy, array('type' => 'int', 'length' => 8));
					$data = substr($data, 0, 4) . ByteConverter::number2Bin($value, true) . substr($data, 6);
				}
				
				if ($modified !== null)
				{
					$value = $this->_formatValueForRecord($modified, array('type' => 'time', 'length' => 8));
					$data = substr($data, 0, 6) . ByteConverter::number2Bin($value, true) . substr($data, 8);
				}
				
				if ($modifiedBy !== null)
				{
					$value = $this->_formatValueForRecord($modifiedBy, array('type' => 'int', 'length' => 8));
					$data = substr($data, 0, 8) . ByteConverter::number2Bin($value, true) . substr($data, 10);
				}
				
				/*pr($data);		
				
				//Hexdump of the data
				echo '<pre>';
				for ($i = 0; $i < strlen($data); $i++)
				{
					echo ByteConverter::bin2Hex(substr($data, $i, 1)) . ' ';
					
					if ($i > 0 && ($i + 1) % 16 == 0)
					{
						echo '<br />';
					}
				}
				
				echo '<br /><br />';
				echo '</pre>';
				
				return;*/
				
				//seek back to the beginning of the record and overwrite the header
				
				//if the seek fails give up
				if (dio_seek($keyFile, $id * $keyLength, SEEK_SET) == -1)
				{
					return false;
				}
					
				//if the write fails give up
				if (dio_write($keyFile, $data) <= 0)
				{
					return false;
				}
			}
			
			return true;
		}
		
		/**
		 * Creates a new record in the filePro file.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $fields An array of fields to save.
		 * @param array $values An array of values for the specified fields. 
		 * @return bool True if the creation was successful, false otherwise.
		 */
		function create(&$model, $fields = null, $values = null)
		{
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
				
				//verify that the mirror and filepro file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} filePro model is out-of-sync with mirror!");
					return false;
				}
				
				//set up our mirror fields
				$extraFields = array(
					'mirror_created' => date('Y-m-d'),
					'mirror_created_by' => $this->_resolveUsername(User::current()),
					'mirror_modified' => date('Y-m-d'),
					'mirror_modified_by' => $this->_resolveUsername(User::current()),
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
				
				//update the record headers
				$this->_updateRecordHeaders($model, $model->id, $extraFields['mirror_created'], $extraFields['mirror_created_by'], $extraFields['mirror_modified'], $extraFields['mirror_modified_by']);
			}
			catch (Exception $ex)
			{
				$model->onError();
				return false;
			}
			
			return true;
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
			return count($name) == 1 && !in_array($field, array(DboFilepro::countField, 'or', 'and')) ? ($model->alias . '.' . $field) : $field;
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
		 * Reads one or more records from the filePro model.
		 * @inherited
		 * @param object $model The model to operate on.
		 * @param array $queryData An array with the following keys: conditions, fields, order, limit, and page.
		 * @return array An array of matching records.
		 */
		function read(&$model, $queryData = array(), $recursive = null)
		{
			//see what fields we have for this model only
			$modelFields = $this->_unqualifiedNames($model, $queryData['fields']);
			
			//if no fields were specified, we grab them all (i.e. "select *")
			if (empty($queryData['fields']) || is_array($queryData['fields']) && empty($modelFields))
			{
				$queryData['fields'] = array_keys($this->describe($model));
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
			$index = isset($queryData['index']) ? ('index.' . $queryData['index']) : null;

			//short-circuit illogical cases
			if ($limit !== null && $limit == 0)
			{
				return array();
			}
			
			//grab our schema
			$schema = $this->describe($model, 'all');
			
			//if an index was specified, remove it from the query data now so it doesn't get applied to parent find() calls when joining data together
			if ($index != null)
			{
				unset($queryData['index']);
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
				if ($fields == DboFilepro::countField)
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
			//we know that an index contains the number of records in itself in the index header.
			//Therefore, if we're doing a count and have no conditions whatsoever, we can just take the
			//number of records in the index.
			if ($countOnly && empty($conditions) && count($schema['indexes']) > 0 && (empty($chains) || !$this->hasOtherConditions($model, $conditions, $chains)))
			{
				//turns out we can't do this for filepro because I've seen indexes not be accurate (as in, not even CLOSE - and I think the reason is that since filepro
				//doesn't truly "delete" records when they get deleted until the file is compacted, the index still has entries in it for the deleted records as well. I've never
				//verified it though)
				//return array('0' => array('0' => array('count' => $schema['indexes'][array_shift(array_keys($schema['indexes']))]->header['record_count'])));
			}

			if ($recursive > -1)
			{
				//automatically pull foreign keys on parent models that have a condition
				foreach ($model->belongsTo as $parent => $data) 
				{
					$parentModel = ClassRegistry::init($parent);
					
					if ($this->_hasConditionalClauses($parentModel, $this->_createConditionalClauses($parentModel, $this->describe($parentModel), $conditions)))
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
				$clauses = $this->_createConditionalClauses($model, $schema['fields'], $conditions);
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

			//use an index to find results if the user specified one
			if ($index != null)
			{
				$indexIDMatches = $this->_searchIndex($model, $schema, $index, $clauses, $order);
				
				if ($indexIDMatches !== false)
				{
					//go ahead and pull the records that match the condition against
					//that index as long as it's not worse than searching by ID (if we can)
					if ($idMatches === null || count($idMatches) > count($indexIDMatches))
					{
						//Note so I remember: unlike the U05 driver, we can't remove conditions that were resolved by the index
						//due to indexes being allowed on a partial length of a field. We still have to test those conditions
						//again because we may have records from the index that still don't match a whole condition (see
						//the filepro_index_controller's _searchSingle method for more info). 
						//
						//Furthermore, we also can't short circuit anything that we could in the U05 driver (like counts and limits)
						//for the same reasons. Bummer.
											
						$indexMatches = $indexIDMatches;
						
						//uncomment and comment out the unset above to test performance times without the index
						//$indexMatches = null;
					}
				}
				else
				{
					//if the index couldn't be utilized, we have to just resort to a table scan.
					//TODO - do I want a warning or anything?
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
			$keyFile = $this->_openFile($schema['key_path']);
			
			if ($keyFile === false)
			{
				throw new Exception("Unable to open key file {$schema['key_path']}");
			}
			
			$dataFile = false;
			
			if (dio_seek($keyFile, 0, SEEK_SET) == -1)
			{
				throw new Exception("Unable to seek to beginning of file {$schema['key_path']}.");
			}
			
			//we also have to open the data file if any of the fields are in the data segment
			if ($schema['data_length'] > 0)
			{
				$dataFile = $this->_openFile($schema['data_path']);
				
				if ($dataFile === false)
				{
					throw new Exception("Unable to open data file {$schema['data_path']}");
				}
				
				if (dio_seek($dataFile, 0, SEEK_SET) == -1)
				{
					throw new Exception("Unable to seek to beginning of file {$schema['data_path']}.");
				}
			}
			
			$recordsFound = 0;
			$currentIndexRecord = 0;
			
			//skip past records we know can't match if we have a lower bound
			if ($lowerBound != null)
			{	
				if (dio_seek($keyFile, (max(1, $lowerBound)) * $schema['key_length'], SEEK_SET) == -1)
				{
					throw new Exception("Unable to seek in file {$schema['key_path']}.");
				}
				
				if ($dataFile !== false)
				{
					if (dio_seek($dataFile, (max(1, $lowerBound)) * $schema['data_length'], SEEK_SET) == -1)
					{
						throw new Exception("Unable to seek in file {$schema['data_path']}.");
					}
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
					
					if (dio_seek($keyFile, ($indexMatches[$currentIndexRecord]) * $schema['key_length'], SEEK_SET) == -1)
					{
						throw new Exception("Unable to seek in file {$schema['key_path']}.");
					}
					
					if ($dataFile !== false)
					{
						if (dio_seek($dataFile, ($indexMatches[$currentIndexRecord]) * $schema['data_length'], SEEK_SET) == -1)
						{
							throw new Exception("Unable to seek in file {$schema['data_path']}.");
						}
					}
								
					$currentIndexRecord++;
				}
				
				$record = dio_read($keyFile, $schema['key_length']);
				
				//we've hit the end of file if the read returned null
				if ($record === null)
				{
					break;
				}
								
				//if we have a data segment, append it to the record so we have it all as one big record
				if ($dataFile !== false)
				{
					$dataSegment = dio_read($dataFile, $schema['data_length']);
					
					if ($dataSegment === null)
					{
						throw new Exception("Unable to read in file {$schema['data_path']}.");
					}
					
					$record .= $dataSegment;
				}
				
				//grab the record ID
				$id = floor(dio_seek($keyFile, 0, SEEK_CUR) / $schema['key_length']) - 1;
				
				if ($id < 0)
				{
					throw new Exception("Unable to seek to read ID in {$schema['key_path']}.");
				}
				
				//stop searching if we have an upper bound and we've gone beyond it
				if ($upperBound != null && $id > $upperBound)
				{
					break;
				}
				
				//rip out the header
				$header = array('is_deleted' => !ByteConverter::bin2Number(substr($record, 0, 1)));
					
				if ($header['is_deleted'])
				{
					$header['forward_freechain'] = ByteConverter::bin2Number(substr($record, 2, 4), true);
					$header['backward_freechain'] = ByteConverter::bin2Number(substr($record, 6, 4), true);
				}	
				else
				{
					$header['created'] = $this->_phpValue(ByteConverter::bin2Number(substr($record, 2, 2), true), 'time');
					$header['created_by'] = ByteConverter::bin2Number(substr($record, 4, 2), true);
					$header['modified'] = $this->_phpValue(ByteConverter::bin2Number(substr($record, 6, 2), true), 'time');
					$header['modified_by'] = ByteConverter::bin2Number(substr($record, 8, 2), true);
					$header['batch_modified'] = $this->_phpValue(ByteConverter::bin2Number(substr($record, 10, 2), true), 'time');
				}
				
				/*
				//Hexdump of the data
				echo '<pre>';
				for ($i = 0; $i < $schema['key_length']; $i++)
				{
					echo ByteConverter::bin2Hex(substr($record, $i, 1)) . ' ';
					
					if ($i > 0 && ($i + 1) % 16 == 0)
					{
						echo '<br />';
					}
				}
				
				echo '<br /><br />';
				echo '</pre>';
				*/
				
				//skip deleted rows (deleted rows have their first character as a zero)
				if ($header['is_deleted'])
				{
					continue;
				}
				
				//chop off the header
				$record = substr($record, $this->keyFileHeaderLength);
				
				//if we satisfied all of the conditions...
				if ($this->_recordMatches($header, $record, $id, $clauses))
				{
					//init the record with the "id" - the row number
					$data = array($model->alias => array('id' => $id));

					//pull the fields out that were specified
					foreach ($fields as $field)
					{
						//only pull a field if it's actually in the schema
						if (array_key_exists($field, $schema['fields']))
						{
							if ($schema['fields'][$field]['ordinal'] == -1)
							{
								//pull from the header if the ordinal is -1 (see this->headerFields)
								$data[$model->alias][$field] = $header[$field];
								
								//for created_by and modified_by, those are stored in the header as UIDs, so if the user wants to get either
								//of those fields, we'll do the auto conversion to usernames
								if (in_array($field, array('created_by', 'modified_by')))
								{
									$data[$model->alias][$field] = $this->_resolveUid($data[$model->alias][$field]);
								}
							}
							else
							{
								//otherwise pull straight from the record
								$data[$model->alias][$field] = $this->_phpValue(substr($record, $schema['fields'][$field]['position'], $schema['fields'][$field]['length']), $schema['fields'][$field]['type']);
							}
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
		 * Searches through an index to find records based on the conditions in the query.
		 * @param object $model The model to operate on.
		 * @param array $schema The model schema.
		 * @param string $indexName The name of the index to use.
		 * @param array $clauses A set of conditional clauses created by _createConditionalClauses.
		 * @param mixed $order The order being applied to the results, if any.
		 * @return muxed An array of record IDs that match the criteria, or false if the index couldn't 
		 * be utilized.
		 */
		function _searchIndex(&$model, $schema, $indexName, $clauses, $order)
		{
			//make sure the index exists and that it's supported
			if (!isset($schema['indexes'][$indexName]) || !$schema['indexes'][$indexName]->isSupported)
			{
				throw new Exception("Invalid attempt to use a non-existent or non-supported index ({$indexName}) on the {$model->name} model!");
			}
			
			$matchingConditions = array();
			
			//look at each field in the index and gather the conditions we can test against it
			foreach ($schema['indexes'][$indexName]->header['sort_info'] as $definition)
			{
				//make sure we have a condition that matches the field. If we don't, we can't go any deeper
				//into a combined index
				if (!array_key_exists($definition['field_name'], $clauses))
				{
					break;
				}
				
				//make sure to only use operators that the index search supports
				//TODO - modify this once we support multiple operators per field in the indexes. Instead of breaking,
				//just extract and use the operators that can be used.
				$operator = array_shift(array_keys($clauses[$definition['field_name']]));
				
				if (!preg_match($schema['indexes'][$indexName]->supportedIndexOperators, $operator))
				{
					break;
				}
				
				$matchingConditions[$definition['field_name']] = $clauses[$definition['field_name']];
				
				//TODO - remove this break once we support combined indexes
				break;
			}
			
			//if we don't have matching conditions, we can't use the index
			if (count($matchingConditions) == 0)
			{
				throw new Exception("Cannot use the specified index ({$indexName}) with the given conditions!");
			}
				
			//now that we have our conditions to search against, let's traverse the index to find matching records (but first refresh the index header in case of a rebalance of the B+ Tree)
			$schema['indexes'][$indexName]->loadHeader($schema['fields']);
			$tree = new BPlusTree($schema['indexes'][$indexName]);
			return $tree->search($matchingConditions);
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
			
			//we make sure to limit for finding parents because we're always looking one record
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
						$hasParentClauses = $this->_hasConditionalClauses($parentModel, $this->_createConditionalClauses($parentModel, $this->describe($parentModel), $queryData['conditions']));
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
				$hasChainClauses = $this->_hasConditionalClauses($chainModel, $this->_createConditionalClauses($chainModel, $this->describe($chainModel), isset($query['conditions']) ? $query['conditions'] : array()));
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
				if (preg_match(DboFilepro::ifNullPattern, $field, $matches))
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
		 * @param array $fields An array of the model's fields.
		 * @param array $conditions The conditions that would be passed to a find() call.
		 * @return array An array of clauses, indexed by unqualified field names.
		 * @access private
		 */
		function _createConditionalClauses(&$model, $fields, $conditions)
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
					$clauses['or'] = $this->_createConditionalClauses($model, $fields, $condition);
				}
				else if (strtolower($field) == 'and')
				{
					$clauses['and'] = $this->_createConditionalClauses($model, $fields, $condition);
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
					if (array_key_exists($field, $fields) || $field == 'id')
					{
						//save the info we need for the clause for later, indexed by operator since we may
						//have multiple conditions on the same field with different operators (i.e. >= x and <= y)
						$clauses[$field][strtoupper($operator)] = array(
							'field' => $field == 'id' ? null : $fields[$field], 
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
				return $this->_hasConditionalClauses($model, $this->_createConditionalClauses($model, $this->describe($model), $conditions));
			}
			
			return false;
		}
		
		/**
		 * Determines if a record matches the given clauses.
		 * @param array $header The record header.
		 * @param string $record The string that would be one entire record for the model.
		 * @param numeric $recordNumber The record number of the specified record.
		 * @param array $clauses An array of clauses created by a call to _createConditionalClauses.
		 * @param bool $matchAny Used internally during recursion. Dictates AND vs. OR logic
		 * @return bool True if the record matches all conditions, false otherwise.
		 * @access private
		 */
		function _recordMatches($header, $record, $recordNumber, $clauses, $matchAny = false)
		{
			$matches = true;

			//test each conditional clause against the row
			foreach ($clauses as $key => $clause)
			{
				if ((string)$key == 'or')
				{
					$matches = $this->_recordMatches($header, $record, $recordNumber, $clause, true);
				}
				else if ((string)$key == 'and')
				{
					$matches = $this->_recordMatches($header, $record, $recordNumber, $clause);
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
						
						if ($field['ordinal'] == -1)
						{
							//pull from the header if the ordinal is -1 (see this->headerFields). At this point the header
							//values have already been run through _phpValue so we don't want to do it again here
							$value = $header[$key];
							
							//if the field in the header is the created or modified by field, resolve the UID if the value is still numeric
							//because it may not have been resolved to a name yet
							if (is_numeric($value) && in_array($key, array('created_by', 'modified_by')))
							{
								$value = $this->_resolveUid($value);
							}
						}
						else
						{			
							//otherwise rip it out of the record and convert it to a php value		
							$value = $this->_phpValue(rtrim(substr($record, $field['position'], $field['length'])), $field['type']);
						}
					}
					
					//go through each clause on the field and make sure they all match
					foreach ($clause as $operator => $part)
					{
						//perform the proper operator against the actual value and the conditional value
						$partMatches = $this->_compareRecordValue($value, $part['condition'], $operator, $part['field']);						
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
		 * but if they coming from filepro, make sure to do a $this->_phpValue on them before passing
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
				return DboFilepro::countField;
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
				
				//verify that the mirror and filepro file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} filePro model is out-of-sync with mirror!");
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
						'mirror_modified' => date('Y-m-d'),
						'mirror_modified_by' => $this->_resolveUsername(User::current()),
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
					
					//if we updated successfully then update the headers 
					$success = $mirror->field('mirror_transaction_success', array('id' => $mirror->id));
					
					if ($success)
					{
						$this->_updateRecordHeaders($model, $model->id, null, null, $extraFields['mirror_modified'], $extraFields['mirror_modified_by']);
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
				//grab a mirrored model
				$mirror = $this->createMirrorModel($model);
				
				//verify that the mirror and filepro file match up
				if (!$this->_verifyMirror($model, $mirror))
				{
					$this->_syslog("{$model->name} filePro model is out-of-sync with mirror!");
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
						'mirror_modified' => date('Y-m-d'),
						'mirror_modified_by' => $this->_resolveUsername(User::current()),
						'mirror_transaction_success' => null,
						'mirror_action_type' => 'D',
						'mirror_filepro_record_id' => $match[$model->alias]['id']
					)));
					
					//kick off the process to do the delete for the record
					$this->_invokeFileproWriterScript($mirror->useTable, $mirror->id);
					
					//we don't update record headers in the filepro record here like we do in an insert and update because a deleted record has a different header
					
					//see if the operation was successful
					$success = $mirror->field('mirror_transaction_success', array('id' => $mirror->id));
					
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
		 * Formats a value for writing to a filepro file.
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
					case 'time':
						$value = trim($value);
						
						if ($value != '')
						{
							//Filepro stores times as the number of days since 1/1/1983 (but of course!), so we need to do the conversion here
							$value = round((strtotime($value) - strtotime('January 1 1983 00:00:00')) / 60.0 / 60.0 / 24.0);
						}

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
		 * Used to verify the schema of a filepro model against its mirror to make sure the two are identical.
		 * @param object $model The source filepro model.
		 * @param object $mirror The U05 mirror model.
		 * @return bool True if the mirror is identical, false otherwise.
		 */
		function _verifyMirror(&$model, &$mirror)
		{
			//grab the U05 driver
			$u05Driver = ConnectionManager::getDataSource('fu05');
			
			//grab schema from both models but be sure to get a fresh copy of both schemas and not from the cache
			$cached = array($this->cacheSources, $u05Driver->cacheSources);
			$this->cacheSources = false;
			$u05Driver->cacheSources = false;

			$schema = $this->describe($model, 'all');
			$mirrorSchema = $u05Driver->describe($mirror, 'all');
			
			//reset the cache sources values of both drivers
			$this->cacheSources = $cached[0];
			$u05Driver->cacheSources = $cached[1];
			
			//make sure the record length matches (we can't just compare against key + data length because of the possibility of unshrunken filepro files)
			$fileproLength = 0;
			
			//go through all the fields in the filepro model to sum up the record length
			foreach ($schema['fields'] as $field => $definition)
			{
				//this test ignores header fields, which don't count towards the length
				if ($definition['ordinal'] != -1)
				{
					$fileproLength += $definition['length'];
				}
			}
			
			if ($fileproLength != $mirrorSchema['record_length'] - array_sum(Set::extract($this->mirrorFields, '{s}.length')))
			{
				return false;
			}
			
			//grab the field names
			$fields = array_keys($schema['fields']);
			$mirrorFields = array_keys($mirrorSchema['fields']);
			
			//make sure we have the same number of fields (ignoring the filepro header fields, and the the special fields that we add to the mirror)
			if (count($fields) - count($this->headerFields) != count($mirrorFields) - count($this->mirrorFields))
			{
				return false;
			}
			
			foreach ($fields as $field)
			{
				//ignore the header fields
				if (array_key_exists($field, $this->headerFields))
				{
					continue;
				}
				
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
		 * Locks the model for writing.
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
					$this->_syslog("Failed to acquire lock on: {$model->useTable}");
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
		 * Unlocks the model after writing.
		 * @param object $model The model to operate on.
		 * @param int $id The ID of the record to unlock.
		 */
		function _unlockAfterWrite(&$model, $id)
		{
			$lock = Sanitize::escape($model->useTable . '.' . $id) . '.write_lock';
			$this->_lockModel()->query("do release_lock('{$lock}')");
		}
		
		/**
		 * Returns a value that came from filePro into its converted form as defined in the schema
		 * that can then be used in PHP (basically this is to convert values from filePro before returning
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
					if (trim($value) === '')
					{
						return null;
					}

					return substr($value, 4) . '-' . substr($value, 0, 2) . '-' . substr($value, 2, 2);
				case 'time':					
					//time values in the indexes are stored as a non-Y2K compliant date of m/d/y, so if we find one,
					//we'll let the comparer take care of making it compliant
					if (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $value))
					{
						return Comparer::makeDateY2kCompliant($value, 'Y-m-d');
					}
					
					//for filepro times, we want to return those as yyyy-mm-dd. Filepro stores times as the number of days 
					//since 1/1/1983 (but of course!), so we need to do the conversion here
					return trim($value) === '' ? null : date('Y-m-d', strtotime('January 1 1983 00:00:00') + ($value * 24 * 60 * 60));
				case 'int':
				case 'float':
					return trim($value) === '' ? null : $this->_comparableValue($value, $type);
				default:
					//just use comparable for now
					return $this->_comparableValue(rtrim($value), $type);
			}
		}
		
		/**
		 * Takes a value and returns a massaged version that can be used to compare two values of the same type.
		 * This should be used against PHP values - for values that come from filepro, they should pass through $this->_phpValue
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
				case 'time':
					$value = trim($value) === '' 
						? null 
						: (
							//Y-m-d -> Ymd
							preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
							? (substr($value, 0, 4) . substr($value, 5, 2) . substr($value, 8, 2))
							: (
								//mdY -> Ymd (the explicit test in the beginning is to make sure we don't
								//reverse dates that are already in Ymd)
								preg_match('/^(0[1-9]|1[012])\d{6}$/', $value)
								? (substr($value, 4) . substr($value, 0, 4))
								: (
									//Ymd -> Ymd
									preg_match('/^\d{8}$/', $value)
									? $value
									: null
								)
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
		 * Used to resolve UIDs stored in record headers to usernames.
		 * @param int $uid The UID to resolve.
		 * @return mixed The username if it was able to be resolved, otherwise the original UID is returned.
		 */
		function _resolveUid($uid)
		{		
			//short-circuit zero to root
			if ($uid == 0)
			{
				return 'root';
			}

			//create our model if necessary that pulls the mappings from MySQL	
			if ($this->_cacheModel === null)
			{
				$this->_cacheModel = ClassRegistry::init('UserCache');
			}
			
			//go grab the username from MySQL if we don't have it cached yet
			if (!array_key_exists($uid, $this->_userCache))
			{
				$username = $this->_cacheModel->resolveUid($uid);			
				$this->_userCache[$uid] = $username === false ? $uid : $username;
			}
			
			return $this->_userCache[$uid];
		}
		
		/**
		 * Used to resolve usernames to a UID.
		 * @param string $username The username to resolve.
		 * @return mixed The UID if it was able to be resolved, otherwise 0.
		 */
		function _resolveUsername($username)
		{
			//short circuit root to zero
			if ($username == 'root')
			{
				return 0;
			}

			//create our model if necessary that pulls the mappings from MySQL	
			if ($this->_cacheModel === null)
			{
				$this->_cacheModel = ClassRegistry::init('UserCache');
			}
			
			//try and find the uid (array key) in our cache by searching for the name
			$uid = array_search($username, $this->_userCache, true);

			//if we can't find it we have to try to resolve it
			if ($uid === false)
			{
				//if it's not in the cache, but it is in our list of invalid users, return zero (basically if the user doesn't exist, we say it's root)
				if (in_array($username, $this->_invalidUsers, true))
				{
					return 0;
				}
				
				//go grab the username from MySQL if we can
				$uid = $this->_cacheModel->resolveUsername($username);
					
				if ($uid === false)
				{
					//if we can't find resolve the user, it means it's invalid, so add it to our list and return zero
					$this->_invalidUsers[] = $username;
					return 0;
				}
				
				//cache the uid if we got a good one
				$this->_userCache[$uid] = $username;
			}
			
			return $uid;
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
		 * Executes the shell script used by the driver to trigger inserts, updates, and deletes in filepro after inserting records into a mirror table.
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
				escapeshellarg(DboFilepro::fileproWriteRetries),
				escapeshellarg(DboFilepro::fileproWriteInitialWaitPeriodMicroseconds),
				escapeshellarg(DboFilepro::fileproWritePeriodicWaitPeriodMicroseconds)
			));
		}
		
		/**
		 * Used to increment a "next free number" field in a filepro file where it is necessary for us to lock
		 * the record itself (vs. our MySQL locks), read the value, increment it, and write the incremented value back to the record.
		 * @param object $model The model to use.
		 * @param int $id The ID of the record to read and update.
		 * @param string $field The name of the field to increment.
		 * @param int $prefixLength The length of the prefix on the field. This is for fields where the number could be something like "A12345". In this
		 * case, the prefix length would be a 1. When the value is incremented the A will be ignored, the number part incremented, and then the A would be
		 * tacked back on.
		 * @param bool $returnIncremented States whether or not the value that is returned should be the already incremented value. If false (the default)
		 * the value returned is the value from the record BEFORE incrementing it. If true, the value returned is the value from the record AFTER incrementing
		 * it. Look at it from the perspective of if it's false, it's treating the field like a "next free number" field. If true, it's treating the field like
		 * a "last one used" field.
		 * @return string The pre-incremented value that was read.
		 */
		function increment(&$model, $id, $field, $prefixLength = 0, $returnIncremented = false)
		{
			$schema = $this->describe($model, 'all');
			
			//make sure the field exists
			if (!array_key_exists($field, $schema['fields']))
			{
				return false;
			}
			
			//we need to open the key file regardless of the segment the field is in because we need to lock the record
			$keyFile = $this->_openFile($schema['key_path']);
			
			if ($keyFile === false)
			{
				return false;
			}
			
			$file = null;
			$length = 0;
			$headerLength = 0;
			$fieldPosition = $schema['fields'][$field]['position'];
					
			//open the segment where the field is located			
			if ($schema['fields'][$field]['segment'] == 'key')
			{
				$file = $keyFile;
				$length = $schema['key_length'];
				$headerLength = $this->keyFileHeaderLength;
			}
			else if ($schema['fields'][$field]['segment'] == 'data')
			{
				$file = $this->_openFile($schema['data_path']);
				
				if ($file === false)
				{
					return false;
				}
				
				$length = $schema['data_length'];
				$headerLength = 0;
				
				//we need to offset the field position if we're in the data segment because in the schema, 
				//the key and data segments are treated as a single record, so the 'position' of fields in the 
				//data segment don't start at zero. They are basically offset by the length of the key - headers.
				$fieldPosition -= ($schema['key_length'] - $this->keyFileHeaderLength);
			}
			else
			{
				return false;
			}
			
			//lock the record in the key file
			if (!$this->_lockRecord($model, $keyFile, $id))
			{
				//if we can't acquire a lock, we have to give up
				return false;
			}
			
			//seek to the record and read it
			if (dio_seek($file, $id * $length, SEEK_SET) == -1)
			{
				return false;
			}
			
			$record = dio_read($file, $length);
			
			if ($record === null)
			{
				return false;
			}
			
			//extract the field to increment
			$prefix = '';
			$value = $this->_phpValue(substr($record, $headerLength + $fieldPosition, $schema['fields'][$field]['length']), $schema['fields'][$field]['type']);
			
			//account for the prefix if any
			if ($prefixLength > 0)
			{
				$prefix = substr($value, 0, $prefixLength);
				$value = substr($value, $prefixLength);
			}
			
			//make sure the value can be incremented
			if (!is_numeric($value))
			{
				return false;
			}

			//increment it
			$next = $value + 1;
			
			//write the incremented value back to the record
			if (dio_seek($file, $id * $length + $headerLength + $fieldPosition, SEEK_SET) == -1)
			{
				return false;
			}
			
			if (dio_write($file, $this->_formatValueForRecord($prefix . $next, $schema['fields'][$field]), $schema['fields'][$field]['length']) <= 0)
			{
				return false;
			}
			
			//unlock the record in the key file
			$this->_unlockRecord($model, $keyFile, $id);
			
			//return the proper value
			return $prefix . ($returnIncremented ? $next : $value);
		}
		
		/**
		 * Performs row-level locking on a the filepro file that filepro will also respect. 
		 * 
		 * IMPORTANT - Only to be used by the increment method because it directly writes to the file. If you use this 
		 * for anything else that ends up forwarding the write call to filepro, it will fail since it will be a separate process
		 * trying to write.
		 * 
		 * @param Model The model the lock is being applied to.
		 * @param numeric $keyFile The file handle of the key file to lock (created by dio_open).
		 * @param numeric $recordNumber The record to lock.
		 * @return boolean True if the lock was acquired, false otherwise.
		 */
		function _lockRecord(&$model, $keyFile, $recordNumber)
		{
			$result = -1;
			//pr(date('H:i:s') . " - locking {$recordNumber} (" . ($this->fileproLockOffset + ($recordNumber * $this->keyLength($model))) . ')');
			
			//lock the region filepro locks when updating a record. It starts at a base offset (16 at the time I wrote this) + (record number * key length including headers)
			//and locks 4 bytes
			for ($i = 0; $i < $this->lockRetries; $i++)
			{
				$result = dio_fcntl($keyFile, F_SETLK, array(
					'type' => F_WRLCK,
					'whence' => SEEK_SET, 
					'start' => (string)($this->fileproLockOffset + ($recordNumber * $this->keyLength($model))),  //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
					'length' => (int)4 //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)
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
			
			//if we couldn't acquire a lock we have to give up
			if ($result == -1)
			{
				return false;
			}
			
			//pr(date('H:i:s')  . " acquired lock");
			
			return true;
		}
		
		/**
		 * Unlocks a row-level lock on a filepro file.
		 * 
		 * IMPORTANT - Only to be used by the increment method because it directly writes to the file. If you use this 
		 * for anything else that ends up forwarding the write call to filepro, it will fail since it will be a separate process
		 * trying to write.
		 *
		 * @param Model The model the lock is being applied to.
		 * @param numeric $keyFile The file handle of the key file to unlock (created by dio_open).
		 * @param numeric $recordNumber The record to unlock.
		 */
		function _unlockRecord(&$model, $keyFile, $recordNumber)
		{
			//pr(date('H:i:s') . " - unlocking {$recordNumber} (" . ($this->fileproLockOffset + ($recordNumber * $this->keyLength($model))) . ')');
		
			dio_fcntl($keyFile, F_SETLK, array(
				'type' => F_UNLCK,
				'whence' => SEEK_SET, 
				'start' => (string)($this->fileproLockOffset + ($recordNumber * $this->keyLength($model))), //THIS MUST BE A STRING CAST OR IT WILL DIE - NECESSARY BECAUSE OF OUR DIO CHANGES FOR 2GIG FILES
				'length' => (int)4 //THE (int) CAST MUST BE THERE OR THE CALL WILL LOCK THE ENTIRE FILE(!)
			));
		}
	}
?>