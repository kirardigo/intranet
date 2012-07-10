<?php
	Configure::write('Cache.disable', true);
	
	class PurgeNsfPaymentQueueShell extends Shell 
	{
		var $uses = array('NsfPaymentQueue', 'Process');
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'impersonate',
				'flag' 			=> 'impersonate',
				'required'		=> true,
				'description'	=> 'The user to mark as the creator for generated records.'
			),
			array(
				'type'			=> 'string',
				'model' 		=> 'NsfPaymentQueue',
				'field'			=> 'posted_date',
				'flag'			=> 'date',
				'default'		=> '-3 months',
				'description' 	=> 'Program will purge all records prior to this date'
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
			
			$processID = $this->Process->createProcess('Purge NSF Payment Queue', false);
			
			$this->Process->updateProcess($processID, 0, 'Purging...');
			$this->Logging->write('Purging the NSF Payment Queue...');
			$this->Logging->write('Purge Date: ' . formatDate($parameters['NsfPaymentQueue']['posted_date']));
			
			$db = ConnectionManager::getDataSource($this->NsfPaymentQueue->useDbConfig);
			$fileInfo = stat($db->dataPath($this->NsfPaymentQueue));
			$recordLength = $db->recordLength($this->NsfPaymentQueue);
			$totalRecords = $fileInfo['size'] / $recordLength;
			$processedRecords = 0;
			$purgeDate = databaseDate($parameters['NsfPaymentQueue']['posted_date']);
			$id = 0;
			
			$this->Logging->write("Original Record Count: {$totalRecords}");
			
			// Prepare the temp file for writing
			$tempFile = TMP . $this->NsfPaymentQueue->useTable . '.TMP';
			$f = fopen($tempFile, 'w');
			
			// Go through all the data that's not deleted and write each qualifying record to a temp file
			while (($data = $this->NsfPaymentQueue->find('first', array('contain' => array(), 'conditions' => array('id >' => $id)))) !== false)
			{
				$processedRecords++;
				$this->Process->updateProcess($processID, min(($processedRecords / $totalRecords) * 100, 100));
				
				// Do not purge records that are more recent than the cut-off date or do not yet have a posted date
				$recordDate = databaseDate($data['NsfPaymentQueue']['posted_date']);
				
				if ($recordDate == '' || $recordDate >= $purgeDate)
				{
					// We're going to cheat and use a private method of the FU05 driver that can create the
					// buffer that gets written for the record
					$buffer = $db->_createRecordBuffer($this->NsfPaymentQueue, array_keys($data['NsfPaymentQueue']), array_values($data['NsfPaymentQueue']));
					
					// Write the buffer
					fwrite($f, $buffer);
				}
				
				// Prep to find the next available record
				$id = $data['NsfPaymentQueue']['id'];
			}
			
			fclose($f);
			
			$fileInfo = stat($tempFile);
			$purgedRecords = $totalRecords - ($fileInfo['size'] / $recordLength);
			
			// Copy the file over the original (we copy and unlink instead of move to retain file attributes)
			if (copy($tempFile, $db->dataPath($this->NsfPaymentQueue)))
			{
				$this->Logging->write("Purged Record Count: {$purgedRecords}");
				$this->Process->updateProcess($processID, 100, "Finished purging {$purgedRecords} records");
			}
			else
			{
				$this->Logging->write("Unable to copy the purged file live. Please check that the file is not locked.");
				$this->Process->updateProcess($processID, 100, "Unable to copy the purged file live");
			}
			
			unlink($tempFile);
			$this->Process->finishProcess($processID, $this->Logging->getBufferedOutput());			
			$this->out('');
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>