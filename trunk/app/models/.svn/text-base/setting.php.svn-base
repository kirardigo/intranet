<?php
	class Setting extends AppModel
	{
		function get($name)
		{
			if (!is_array($name))
			{
				return $this->field('value', array('name' => $name));
			}
			else
			{
				$settings = $this->find('all', array(
					'conditions' => array('name' => $name),
					'contain' => array()
				));
				
				return Set::combine($settings, '{n}.Setting.name', '{n}.Setting.value');
			}
		}
		
		/**
		 * Load CSV files for a specified table from transfer directory.
		 * @param string $modelName The name of the model that the data belongs to.
		 */
		function loadFile($modelName)
		{
			// Instantiate the specified model
			$chosenModel = ClassRegistry::init($modelName);
			$tableName = $chosenModel->useTable;
			
			// Setup path for the import file
			$transferPath = $this->get('transfer_file_path');
			$importFilename = ($chosenModel->importFile != null) ? $chosenModel->importFile : "{$tableName}.txt";
			$importPath = "{$transferPath}/{$importFilename}";
			$tempPath = "{$transferPath}/{$importFilename}.tmp";
			
			// Don't do anything if the import file is not present
			if (!file_exists($importPath))
			{
				return;
			}
			
			// Remove id from database field list since the filePro export does not contain those
			$fields = array_keys($chosenModel->schema());
			array_shift($fields);
			$fieldList = implode(', ', $fields);
			
			// Truncate existing data
			$this->query("truncate table {$tableName}");
			
			// Make sure that consecutive delimiters or line-ending delimiters are treated as NULL.
			// The system command is cutting the doubled escape characters in half and then the
			// sed command does again which is why we need 8 backslashes in front of the N to
			// generate a \N for a NULL.
			system('cat ' . $importPath . ' | sed -e s/\|\|/\|\\\\\\\\N\|/g | sed -e s/\|$/\|\\\\\\\\N/g > ' . $tempPath);
			
			// Load data into table
			$sql = "load data infile '{$tempPath}' ignore into table {$tableName} "
				 . "fields terminated by '|' "
				 . "lines terminated by '\n' ignore 1 lines"
				 . "({$fieldList})";
			
			$this->query($sql);
			
			unlink($tempPath);
		}
	}
?>