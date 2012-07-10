<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Imports data for pre-defined Models from the filePro CSV exports into MySQL.
	 */
	class ImportFileProShell extends Shell 
	{
		var $uses = array('Setting');
		
		var $tasks = array('Logging');
		
		var	$importModels = array(
			'Budget',
			'DistributorOrder',
			'DistributorOrderLine',
			'ElectronicFileNote',
			'Order'
		);
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			foreach ($this->importModels as $modelName)
			{
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
					exit;
				}
				
				$this->Logging->write("Finished filePro import of {$modelName}");
				$this->Logging->writeElapsedTime();
			}
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