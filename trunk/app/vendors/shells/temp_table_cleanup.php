<?php
	Configure::write('Cache.disable', true);
	App::import('ConnectionManager');
	
	/**
	 * This shell is responsible for getting rid of all of the temporary tables that are
	 * created by the website.
	 */
	class TempTableCleanupShell extends Shell 
	{
		var $tasks = array('Logging');
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->startTimer();
			
			//grab the temp tables
			$db = ConnectionManager::getDataSource('default');
			$tables = Set::flatten($db->query("show tables like 'temp\_%'"));
			
			//go through each and drop them
			foreach ($tables as $table)
			{
				$this->Logging->write("Dropping table {$table}");
				$db->query("drop table `{$table}`");
			}
			
			$this->Logging->writeElapsedTime();
			$this->Logging->write('Done');
		}
	}
?>