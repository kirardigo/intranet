<?php
	Configure::write('Cache.disable', true);
	
	class PurgeTransactionQueueShell extends Shell 
	{
		var $uses = array('TransactionQueue', 'Process');
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to mark as the creator for generated records.'
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->maintainBuffer();
			
			// Parse the report parameters
			$parameters = $this->ReportParameters->parse($this->parameters);
			$this->Impersonate->impersonate($parameters['Virtual']['impersonate']);
			
			$processID = $this->Process->createProcess('Purge Transaction Queue', false);
			
			$this->Process->updateProcess($processID, 25, 'Purging...');
			$this->Logging->write("Purging the Transaction Queue...");
			
			// Get datasource file information
			$db = ConnectionManager::getDataSource($this->TransactionQueue->useDbConfig);
			$fileInfo = stat($db->dataPath($this->TransactionQueue));
			$recordLength = $db->recordLength($this->TransactionQueue);
			
			// Get the number of records in the file before the defrag
			$processedRecords = $fileInfo['size'] / $recordLength;
			$this->Logging->write("Original Record Count: {$processedRecords}");
			
			// Delete any record that has an empty account number, transaction date, and amount
			$this->TransactionQueue->deleteAll(array(
				'TransactionQueue.account_number' => '',
				'TransactionQueue.transaction_date_of_service' => null,
				'TransactionQueue.amount' => '' //blank instead of null because amount is defined as a string in BW
			));
			
			// Get the number of records purged from the file during the defrag
			$fileInfo = stat($db->dataPath($this->TransactionQueue));
			$purgedRecords = $processedRecords - ($fileInfo['size'] / $recordLength);
			$this->Logging->write("Purged Record Count: {$purgedRecords}");
			
			$this->Logging->write('Finished');
			$this->Process->updateProcess($processID, 100, "Finished purging {$purgedRecords} records");
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());
			
			$this->out('');
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>