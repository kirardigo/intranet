<?php
	/**
	 * Behavior for FU05 models to allow them to have indexes located in MySQL.
	 */
	class IndexableBehavior extends ModelBehavior
	{
		/**
		 * Creates a table in MySQL that is used as an index for an FU05 model.
		 * @param (implied) The model the behavior is attached to.
		 * @param string $field The field to create the index for.
		 * @param bool $forBuilding False by default. Used internally to create build tables for indexes.
		 * @param bool $unchained False by default. Unchained indexes are those that cannot be automatically used by the FU05 driver. 
		 * @return bool True if successful, false otherwise.
		 */
		function createIndex(&$model, $field, $forBuilding = false, $unchained = false)
		{
			//determine the table and field length
			$table = $this->indexName($model, $field, $forBuilding, $unchained);
			$schema = $model->schema();

			if (!array_key_exists($field, $schema))
			{
				return false;
			}
			
			//grab the FU05 driver to determine what model to use to create indexes with
			//as well as the SQL type to use
			$db = ConnectionManager::getDataSource($model->useDbConfig);
			$indexModel = $db->_indexModel();
			$type = $db->_toSqlType($schema[$field]);
			
			//drop (if necessary) and create the index
			$indexModel->query("drop table if exists `{$table}`");
			
			$indexModel->query("
				create table if not exists `{$table}` (
					id int not null auto_increment primary key,
					value {$type} null,
					record_number int not null,
					index (value, record_number),
					unique index (record_number)
				)
			");
						
			return true;
		}
		
		/**
		 * Rebuilds either one or all indexes for the model.
		 * @param (implied) The model the behavior is attached to.
		 * @param string $field The field to build the index for. If omitted or null, all indexes are rebuilt.
		 * @param bool $swapIntoProduction Whether or not to swap the newly built index into production automatically. This
		 * is true by default.
		 * @return bool True if successful, false otherwise.
		 */
		function rebuildIndexes(&$model, $field = null, $swapIntoProduction = true)
		{
			//grab the available index tables for the model
			$db = ConnectionManager::getDataSource($model->useDbConfig);
			$schema = $db->describe($model, 'all');
			
			$fields = null;
			
			//if the user didn't pass a field, we're going to build them all
			if ($field == null)
			{
				$fields = array_keys($schema['indexes']);
			}
			else if (array_key_exists($field, $schema['indexes']))
			{
				//if they passed a field and we have the index for it, we'll build just that field
				$fields = array($field);
			}
			else
			{
				//otherwise we have nothing to do
				return true;
			}
			
			//we are going to build the indexes in a C app for speed, so grab the settings we're going to need to run it
			$settings = ClassRegistry::init('Setting')->get(array(
				'indexU05_path',
				'indexU05_temp_path',
			));
			
			return $this->_runIndexU05($settings, $db, $model, $schema, $fields, $swapIntoProduction, false);
		}
		
		/** 
		 * Used internally to run the indexU05 C program.
		 * @param array $settings The settings for the indexU05 application (indexU05_path and indexU05_temp_path from the Setting model).
		 * @param object $driver The FU05 driver.
		 * @param object $model The model the behavior is attached to
		 * @param array $schema The model's schema.
		 * @param array $fields The fields to run indexU05 against.
		 * @param bool $swapIntoProduction Whether or not to swap the newly built index into production automatically.
		 * @param bool $unchained False by default. Unchained indexes are those that cannot be automatically used by the FU05 driver. 
		 * @return bool True if successful, false otherwise.
		 */
		function _runIndexU05($settings, &$driver, &$model, $schema, $fields, $swapIntoProduction, $unchained)
		{
			//if we don't have any fields, there's nothing to do
			if (empty($fields))
			{
				return true;
			}		
			
			$indexModel = $driver->_indexModel();
			
			//craft the field specifications for the indexes we're building
			$fieldSpecifications = array();
			
			foreach ($fields as $fieldName)
			{
				$fieldSpecifications[] = $this->_createFieldSpecification($schema['fields'][$fieldName]);
			}
			
			//figure out the name of our output file
			$outputFile = $settings['indexU05_temp_path'] . DS . $model->alias . ($unchained ? '.uindexes' : '.indexes');
			
			//build the arguments needed by the indexing program
			$args = sprintf(
				'-mp %s -ml %s -f %s -o %s',
				escapeshellarg($schema['data_path']),
				$schema['record_length'],
				implode(',', $fieldSpecifications),
				escapeshellarg($outputFile)
			);
			
			//chainable models have extra arguments they need to pass to the indexing program for their chainable indexes
			if (!$unchained && $model->Behaviors->enabled('Chainable'))
			{
				//load the chain owner and its schema
				$owner = ClassRegistry::init($model->Behaviors->Chainable->settings[$model->alias]['ownerModel']);
				$ownerSchema = $driver->describe($owner, 'all');
				
				$args .= sprintf(
					' -op %s -ol %s -opf %s -nrf %s',
					escapeshellarg($ownerSchema['data_path']),
					$ownerSchema['record_length'],
					$this->_createFieldSpecification($ownerSchema['fields'][$model->Behaviors->Chainable->settings[$model->alias]['ownerField']]),
					$this->_createFieldSpecification($schema['fields'][$model->Behaviors->Chainable->settings[$model->alias]['chainField']])
				);
			}
			
			//run the indexing app
			//echo $settings['indexU05_path'] . ' ' . $args;
			exec($settings['indexU05_path'] . ' ' . $args, $output, $return);
			
			//make sure it ran ok
			if ($return != 0)
			{
				return false;
			}
			
			$i = 0;
			$success = true;
			
			//load the data created by the indexU05 program for each field
			foreach ($fields as $fieldName)
			{
				$columns = '';
				$j = 0;
				
				//we have to craft the columns so that we throw away fields that aren't in this index
				foreach ($fields as $name)
				{
					$columns .= ($i == $j ? 'value' : "@dummy{$j}") . ', ';
					$j++;
				}
				
				$columns .= 'record_number';

				$buildTable = $this->indexName($model, $fieldName, true, $unchained);
				$liveTable = $this->indexName($model, $fieldName, false, $unchained);
				
				//create/clear a build table for the index
				$this->createIndex($model, $fieldName, true, $unchained);

				//for unchained indexes, unlike regular indexes, the live table might not exist yet either since the driver doesn't require it, so we'll create it if need be
				if ($unchained && count($indexModel->query("show tables like '{$buildTable}'", false)) == 0)
				{
					$this->createIndex($model, $fieldName, false, true);
				}
			
				//suck the data in
				$loaded = $indexModel->query("
					load data infile '{$outputFile}' ignore into table `{$buildTable}`
					fields terminated by '|'
					lines terminated by '\\n'
					({$columns})
				");
				
				//swap the build into production if we're supposed to
				if ($loaded !== false && $swapIntoProduction)
				{
					$this->_swapBuildTablesInternal($model, $fieldName, $unchained);
				}
				
				$i++;
				$success &= $loaded !== false;
			}
			
			//remove the index file
			unlink($outputFile);
			
			//for chainable models that have unchained indexes to build, we need to build those now
			if (!$unchained && $model->Behaviors->enabled('Chainable') && !empty($model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes']))
			{
				$success &= $this->_runIndexU05($settings, $driver, $model, $schema, array_intersect($model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes'], $fields), $swapIntoProduction, true);
			}
			
			return $success;
		}
		
		/**
		 * Can be used to swap build and live index tables for a particular model.
		 * @param (implied) The model the behavior is attached to.
		 * @param string $field The field whose build tables should be swapped. Omit to swap all tables for the model.
		 */
		function swapBuildTables(&$model, $field = null)
		{
			//swap regular indexes first
			$this->_swapBuildTablesInternal($model, $field, false);
			
			//if this is a chainable model that has unchained indexes, swap those too
			if ($model->Behaviors->enabled('Chainable') && !empty($model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes']))
			{
				$this->_swapBuildTablesInternal($model, $field, true);
			}
		}
		
		/**
		 * Internally used to swap build and live index tables for a particular model.
		 * @param object The model the behavior is attached to.
		 * @param string $field The field whose build tables should be swapped. Omit to swap all tables for the model.
		 * @param bool $unchained False by default. Unchained indexes are those that cannot be automatically used by the FU05 driver. 
		 */
		function _swapBuildTablesInternal(&$model, $field = null, $unchained = false)
		{
			$db = ConnectionManager::getDataSource($model->useDbConfig);
			$indexModel = $db->_indexModel();
			$fields = array();
			
			//if no field was passed in, we'll swap all of them
			if ($field == null)
			{
				//if we're building unchained indexes, we need to pull the fields for those indexes from the chainable behavior
				if ($unchained)
				{
					$fields = $model->Behaviors->enabled('Chainable') ? $model->Behaviors->Chainable->settings[$model->alias]['unchainedIndexes'] : array();
				}
				else
				{
					//otherwise for standard indexes we can pull the field names from the schema
					$fields = array_keys($db->describe($model, 'indexes'));
				}
			}
			else
			{
				//otherwise if we got a field passed in, we only build that one
				$fields = array($field);
			}

			foreach ($fields as $name)
			{
				$buildTable = $this->indexName($model, $name, true, $unchained);
				$liveTable = $this->indexName($model, $name, false, $unchained);
						
				//skip swapping tables when there is no build table
				if (count($indexModel->query("show tables like '{$buildTable}'", false)) == 0)
				{
					continue;
				}
				
				//we have to do more involved swapping if a live table already exists
				if (count($indexModel->query("show tables like '{$liveTable}'", false)) != 0)
				{
					$indexModel->query("rename table `{$liveTable}` to `temp_{$liveTable}`, `{$buildTable}` to `{$liveTable}`, `temp_{$liveTable}` to `{$buildTable}`", false);
				}
				else
				{
					//otherwise we just push the build live
					$indexModel->query("rename table `{$buildTable}` to `{$liveTable}`", false);
				}				
				
				//analyze for performance
				$indexModel->query("analyze table `{$liveTable}`", false);
			}
		}
		
		/**
		 * Creates a field specification that is understood by the indexU05 program.
		 * @param array $schema The schema for the field to create the specification for.
		 * @return string The specification string.
		 */
		function _createFieldSpecification($schema)
		{
			return sprintf('%s:%s:%s', substr($schema['type'], 0, 1), $schema['position'], $schema['length']);
		}
		
		/**
		 * Calculates the name to use for an index table.
		 * @param Model $model The model the index is for.
		 * @param string $field The field the index is for.
		 * @param bool $forBuilding False by default. Used internally for build tables for indexes.
		 * @param bool $unchained False by default. Unchained indexes are those that cannot be automatically used by the FU05 driver. 
		 * @return string The name of the index table.
		 */
		function indexName(&$model, $field, $forBuilding = false, $unchained = false)
		{
			$prefix = ($unchained ? 'unchained_' : '') . ($forBuilding ? 'build' : 'index');
			$table = strtolower($model->useTable);	
			
			return "{$prefix}_{$table}_{$field}";
		}
	}
?>