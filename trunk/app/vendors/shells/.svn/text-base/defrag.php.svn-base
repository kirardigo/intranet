<?php
	Configure::write('Cache.disable', true);
	
	class DefragShell extends Shell
	{
		var $uses = array('Process');
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' => 'string',
				'model' => 'Option',
				'field' => 'model',
				'flag' => 'm',
				'description' => 'The model to defrag.'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'do_not_index',
				'flag' => 'noindex',
				'description' => 'Normally, a defrag will rebuild all indexes for the model after it is done defragging. Passing this parameter will prevent the indexes from being rebuilt.'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'all_models',
				'flag' => 'a',
				'description' => 'States that all applicable models should be defragged.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to run the process as.'
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			
			//impersonate the user
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			//grab the models to defrag
			Cache::clear();
			$models = array();
			
			//defrag all models if that's what they want
			if ($parameters['Option']['all_models'])
			{
				$models = Configure::listObjects('model');
			}
			else if (isset($parameters['Option']['model']))
			{
				//otherwise, defrag a specific model
				$models[] = $parameters['Option']['model'];
			}
			else
			{
				//if the user didn't specify any flags, we need to show them the usage
				$this->ReportParameters->usage($this->parameters);
				$this->_stop();
			}
			
			//kick off a process
			$processID = $this->Process->createProcess('Defragging U05 Files', true);
			$buildIndexes = !$parameters['Option']['do_not_index'];
			$percentComplete = 0;
			$cancelled = false;
			
			foreach ($models as $i => $name)
			{
				//check if we should interrupt
				if ($this->Process->isProcessInterrupted($processID))
				{
					$cancelled = true;
					$this->Process->updateProcess($processID, $percentComplete, 'Cancelling');
					$this->Logging->write('Cancelling');
					break;
				}
				
				//grab the model
				$model = ClassRegistry::init($name);
				
				//make sure the model can be defragged
				if (!$model->Behaviors->enabled('Defraggable'))
				{
					continue;
				}
				
				//update the process
				$this->Process->updateProcess($processID, $percentComplete, "Defragging {$name}");
				$this->Logging->write("Defragging {$name}");
				
				//grab information about it's DAT file
				$db = ConnectionManager::getDataSource($model->useDbConfig);
				$fileInfo = stat($db->dataPath($model));
				$recordLength = $db->recordLength($model);
			
				//get the number of records in the file before the defrag
				$originalCount = $fileInfo['size'] / $recordLength;
				$this->Logging->write("Original Record Count: {$originalCount}");
				
				//defrag it (and optionally build indexes)
				$model->defrag($buildIndexes);
				
				//get the number of records purged from the file during the defrag
				$fileInfo = stat($db->dataPath($model));
				$recordCount = $fileInfo['size'] / $recordLength;
				$purgedRecords = $originalCount - $recordCount;
				
				//update stats
				$percentComplete = $i / count($models) * 100;
				$this->Logging->write("New Record Count: {$recordCount} ({$purgedRecords} purged)");
			}
			
			//update the percent on the process that we were able to get to
			if ($cancelled)
			{				
				$this->Process->updateProcess($processID, $percentComplete, 'Cancelled');
				$this->Logging->write('Cancelled');
			}
			else
			{
				$this->Process->updateProcess($processID, 100, 'Finished');
				$this->Logging->write('Finished');
			}

			//close up shop
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>