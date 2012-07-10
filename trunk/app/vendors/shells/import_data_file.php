<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Import a specific model from the filePro CSV export into MySQL.
	 */
	class ImportDataFileShell extends Shell 
	{
		var $uses = array('Setting');
		
		var $tasks = array('ReportParameters', 'Logging');
		
		var $parameters = array(
			array(
				'type' => 'string',
				'model' => 'Setting',
				'field' => 'model_name',
				'flag' => 'm',
				'description' => 'The name of the model to load',
				'required' => true
			)
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$data = $this->ReportParameters->parse($this->parameters);
			
			$modelName = $data['Setting']['model_name'];
			
			$this->Logging->write("Starting filePro import of {$modelName}");
			
			try
			{
				$chosenModel = ClassRegistry::init($modelName);
				$reflectionClass = new ReflectionClass($modelName);
				
				// If model does not have an explicit import, use the generic loader
				if ($reflectionClass->hasMethod('fileProImport'))
				{
					$chosenModel->fileProImport();
				}
				else
				{
					$this->Logging->write("No filePro import method defined for {$modelName}");
					//$this->Setting->loadFile($modelName);
				}
			}
			catch (Exception $ex)
			{
				$this->Logging->write('ERROR: ' . $ex->getMessage());
			}
			
			$this->Logging->write("Finished filePro import of {$modelName}");
			$this->Logging->writeElapsedTime();
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup()
		{
			$this->Logging->startTimer();
		}
	}
?>