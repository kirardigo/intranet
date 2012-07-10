<?php
	Configure::write('Cache.disable', true);
	App::import('ConnectionManager');
	App::import('Folder');
	
	/**
	 * This shell is responsible for building mirror files for all filepro and fu05 models. The drivers utilize mirrors of the actual filepro/fu05
	 * files when it does updates and deletes (i.e. any writing activity - but in the case of fu05, only when explicitly asking to write 
	 * via filepro). See the drivers for more details.
	 */
	class FileproMirrorGeneratorShell extends Shell
	{
		var $tasks = array('Logging');
		var $driverProgramName = 'prc.iud_driver';
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->startTimer();
			
			//grab all the models
			$u05Driver = ConnectionManager::getDataSource('fu05');
			$fileproDriver = ConnectionManager::getDataSource('filepro');
			$models = Configure::listObjects('model');
			
			//process all of the ones that use the filepro or fu05 driver
			foreach ($models as $name)
			{
				$model = ClassRegistry::init($name);
				
				if ($model->useDbConfig != 'filepro' && $model->useDbConfig != 'fu05')
				{
					continue;
				}
				
				$mirrorPrefix = $model->useDbConfig == 'filepro' ? DboFilepro::mirrorPrefix : DboFu05::mirrorPrefix;
				$mirrorSuffix = $model->useDbConfig == 'filepro' ? DboFilepro::mirrorSuffix : DboFu05::mirrorSuffix;
				$mirrorFields = $model->useDbConfig == 'filepro' ? $fileproDriver->mirrorFields : $u05Driver->mirrorFields;
				
				//we're going to create an FU05 model that is the mirror of a model. We're going to put
				//both the map and dat files underneath a subdirectory of where the FU05 driver normally places map files.
				$tableName = $mirrorPrefix . $model->useTable . $mirrorSuffix;
				$mirrorPath = $u05Driver->config['map_file_path'] . DS . $tableName;
				
				$this->Logging->write("Generating {$tableName}");
				
				//create the mirror path if it doesn't exist yet
				new Folder($mirrorPath, true, 0777);
				
				$this->Logging->write("\tCreating map");
				$mapFile = $mirrorPath . DS . 'map';
				
				//see if the map file for the mirror exists. If it doesn't, we're going to create it via filepro so that it ends up with the proper encoded password in the
				//first line of the map file. If that encoded string is wrong, we wouldn't be able to invoke the filepro processing we're going to be creating after the map file.
				if (!file_exists($mapFile))
				{
					exec(sprintf('%s %s', escapeshellarg(APP . 'vendors' . DS . 'shells' . DS . 'filepro_mirror_generator.sh'), escapeshellarg($tableName)));
				}
				
				//open the mirror's map file to grab the first line that we need to keep, and extract the encoded password
				$mirrorHeader = array_slice(array_filter(explode("\n", file_get_contents($mapFile))), 0, 2);
				$firstLine = explode(':', $mirrorHeader[0]);
				
				//we need to grab everything including and after the 5th element, because the encoded password can actually contain colons too, which is normally the field separator
				$encodedPassword = implode(':', array_slice($firstLine, 5));
				
				//figure out the field count and record length to use for our mirror. We can't just use the filepro driver's keyLength & dataLength, or the fu05 driver's recordLength methods 
				//because those come from the map header, which shows he physical length of the key and data segments, but that may not be equal to the sum of the lengths of the fields 
				//in the file. If you remove a field from the middle of the key or data segment, filepro will ask you if you want to shrink the file to the smaller length. If you choose
				//no, it takes the remaining fields beyond the field you removed, and moves them forward so that all of the empty unused space is at the end of each record. Because
				//of this, when we create our mirror, we need to sum the fields to get the correct length we should place in our mirror's map file.
				$schema = $model->schema();
				
				//the field count is the total of all the fields plus our added mirror fields
				$fieldCount = count($schema) + count($mirrorFields);
				
				if ($model->useDbConfig == 'filepro')
				{
					//for filepro models, we need to subtract the header fields which aren't part of the "real" record length
					$fieldCount -= count($fileproDriver->headerFields);
				}
				
				$length = 0;
				
				//go through all the fields in the model to sum up the record length
				foreach ($schema as $field => $definition)
				{
					//this test ignores header fields in filepro models, which don't count towards the size of our mirror
					if ($definition['ordinal'] != -1)
					{
						$length += $definition['length'];
					}
				}
				
				//tack on the length of our mirror fields
				$length += array_sum(Set::extract($mirrorFields, '{s}.length'));
				
				//grab the map file of the original model
				$map = array_filter(explode("\n", file_get_contents($model->useDbConfig == 'filepro' ? $fileproDriver->_mapPath($model) : $u05Driver->_mapPath($model))));
				
				//ditch the header
				array_shift($map);
				
				//FU05 map files have two header lines
				if ($model->useDbConfig == 'fu05')
				{
					array_shift($map);
				}

				//start our new map file and append the original model's map fields to it. We place the encoded password from the real map file of the mirror in the header
				//so that filepro will correctly recognize it.
				$output = array("Alien:{$length}:0:{$fieldCount}:{$firstLine[4]}:{$encodedPassword}");
				$output[] = $mirrorPath . DS . $tableName . '.DAT:2:0:00:00:';
				$output = array_merge($output, $map);
				
				//now write out the extra fields we maintain in the mirror
				foreach ($mirrorFields as $name => $info)
				{
					$output[] = $name . ':' . str_pad($info['length'], 3, ' ', STR_PAD_LEFT) . ':' . $info['type'] . ':';
				}
				
				//create/update the map file
				file_put_contents($mapFile, implode("\n", $output));
				chmod($mapFile, 0777);
				
				$this->Logging->write("\tCreating DAT");
				
				//create/zero the mirror DAT file
				$datFile = $mirrorPath . DS . $tableName . '.DAT';
				file_put_contents($datFile, '');
				chmod($datFile, 0777);
				
				//create the mirror model now that we can since the map file is out there
				$mirror = $model->useDbConfig == 'filepro' ? $fileproDriver->createMirrorModel($model) : $u05Driver->createMirrorModel($model);
				
				$this->Logging->write("\tGenerating filePro processing");
				
				//generate the iud_driver filepro processing that the driver will use to write data to the actual (i.e. not the mirror) table
				$programFile = $mirrorPath . DS . $this->driverProgramName;
				file_put_contents($programFile, $this->generateFileproProcessing($model, $mirror));
				chmod($mapFile, 0777);
				
				$this->Logging->write("\tDone");
			}
			
			$this->Logging->writeElapsedTime();
			$this->Logging->write('Done');
		}
		
		/**
		 * This method generates the content of the filepro processing program that will be generated for a mirror model to insert, update, and delete
		 * records in filepro.
		 * @param object $model The model to work on.
		 * @param object $mirror The mirror model for the $model argument that the processing will be generated for.
		 * @return string The generated filepro script content.
		 */
		function generateFileproProcessing($model, $mirror)
		{
			$modelFields = $model->schema();
			$mirrorFields = $mirror->schema();
			$actionTypeOrdinal = $mirrorFields['mirror_action_type']['ordinal'];
			$processStatusOrdinal = $mirrorFields['mirror_transaction_success']['ordinal'];
			$fileproRecordIDOrdinal= $mirrorFields['mirror_filepro_record_id']['ordinal'];
			
			$output = array();
			
			$output[] = ":'** This processing is AUTOMATICALLY GENERATED - do NOT modify **:'****************************************************************:";
			$output[] = ":'Determine and execute action::";
			$output[] = ":{$actionTypeOrdinal} = \"I\":gosub ins:";
			$output[] = ":{$actionTypeOrdinal} = \"U\":gosub upd:";
			$output[] = ":{$actionTypeOrdinal} = \"D\":gosub del:";
			$output[] = ":'Flush and close files:write;close:";
			$output[] = ":'Exit processing back to shell:EXIT:";
			$output[] = "ins:'** Insert Record::";
			$output[] = "::lookup XMTARGET={$model->useTable} r=free  -n:";
			$output[] = ":NOT XMTARGET:{$processStatusOrdinal} = \"N\";goto ins99:";
			$output[] = ":'Assign values::";
			
			foreach ($modelFields as $name => $field)
			{
				//check to make sure we don't write out the header fields
				if ($field['ordinal'] != -1)
				{
					$output[] = "::XMTARGET({$field['ordinal']}) = {$mirrorFields[$name]['ordinal']}:";
				}
			}
			
			$output[] = ":'Log new record number and flag success:{$fileproRecordIDOrdinal} = XMTARGET(@RN) ; {$processStatusOrdinal} = \"Y\":";
			$output[] = 'ins99::RETURN:';
			$output[] = "upd:'** Update Record::";
			$output[] = "::lookup XMTARGET={$model->useTable}  r={$fileproRecordIDOrdinal}  -n:";
			$output[] = ":NOT XMTARGET:{$processStatusOrdinal} = \"N\";goto upd99:";
			$output[] = ":'Update values::";
			
			foreach ($modelFields as $name => $field)
			{
				//check to make sure we don't write out the header fields
				if ($field['ordinal'] != -1)
				{
					$output[] = "::XMTARGET({$field['ordinal']}) = {$mirrorFields[$name]['ordinal']}:";
				}
			}
			
			$output[] = ":'Flag success:{$processStatusOrdinal} = \"Y\":";
			$output[] = 'upd99::RETURN:';
			$output[] = "del:'** Delete Record::";
			$output[] = "::lookup XMTARGET={$model->useTable}  r={$fileproRecordIDOrdinal}  -n:";
			$output[] = ":NOT XMTARGET:{$processStatusOrdinal} = \"N\";goto del99:";
			$output[] = "::delete XMTARGET:";
			$output[] = ":'Flag success:{$processStatusOrdinal} = \"Y\":";
			$output[] = 'del99::RETURN:';
			
			return implode("\n", $output);
		}
	}
?>