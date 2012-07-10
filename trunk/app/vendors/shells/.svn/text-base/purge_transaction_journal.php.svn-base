<?php
	Configure::write('Cache.disable', true);
	
	class PurgeTransactionJournalShell extends Shell 
	{
		var $uses = array('TransactionJournal', 'Process');
		
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
				'model' 		=> 'TransactionJournal',
				'field'			=> 'transaction_date_of_service',
				'flag'			=> 'date',
				'default'		=> '-3 months',
				'description' 	=> 'Program will purge all records prior to this date.'
			),
			array(
				'type' 			=> 'string',
				'model'			=> 'TransactionJournal',
				'field'			=> 'profit_center_number',
				'flag'			=> 'profitCenter',
				'description'	=> 'Limit purge to a specified profit center.'
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
			
			$cachedProfitCenterNumber = $parameters['TransactionJournal']['profit_center_number'];
			
			if (ifset($parameters['TransactionJournal']['profit_center_number']) == 'ALL')
			{
				unset($parameters['TransactionJournal']['profit_center_number']);
			}
			
			$processID = $this->Process->createProcess('Purge Transaction Journal', false);
			
			$this->Process->updateProcess($processID, 0, 'Purging...');
			$this->Logging->write('Purging the Transaction Journal...');
			$this->Logging->write('Purge Date: ' . formatDate($parameters['TransactionJournal']['transaction_date_of_service']));
			$this->Logging->write('Profit Center: ' . $cachedProfitCenterNumber);
			
			$db = ConnectionManager::getDataSource($this->TransactionJournal->useDbConfig);
			$fileInfo = stat($db->dataPath($this->TransactionJournal));
			$recordLength = $db->recordLength($this->TransactionJournal);
			$totalRecords = $fileInfo['size'] / $recordLength;
			$processedRecords = 0;
			$purgeDate = databaseDate($parameters['TransactionJournal']['transaction_date_of_service']);
			$id = 0;
			
			$this->Logging->write("Original Record Count: {$totalRecords}");
			
			// Prepare the temp file for writing
			$tempFile = TMP . $this->TransactionJournal->useTable . '.TMP';
			$f = fopen($tempFile, 'w');
			
			// Go through all the data that's not deleted and write each record out to a temp file
			while (($data = $this->TransactionJournal->find('first', array('contain' => array(), 'conditions' => array('id >' => $id)))) !== false)
			{
				$processedRecords++;
				$this->Process->updateProcess($processID, min(($processedRecords / $totalRecords) * 90, 90));
				
				if (databaseDate($data['TransactionJournal']['transaction_date_of_service']) < $purgeDate &&
					(!isset($parameters['TransactionJournal']['profit_center_number']) ||
					$parameters['TransactionJournal']['profit_center_number'] == $data['TransactionJournal']['profit_center_number']))
				{
					$purgedRecords++;
				}
				else
				{
					// We're going to cheat and use a private method of the FU05 driver that can create the
					// buffer that gets written for the record
					$buffer = $db->_createRecordBuffer($this->TransactionJournal, array_keys($data['TransactionJournal']), array_values($data['TransactionJournal']));
					fwrite($f, $buffer);
				}
				
				$id = $data['TransactionJournal']['id'];
			}

			fclose($f);
			
			$fileInfo = stat($tempFile);
			$purgedRecords = $totalRecords - ($fileInfo['size'] / $recordLength);
			
			// Copy the file over the original (we copy and unlink instead of move to retain file attributes)
			if (copy($tempFile, $db->dataPath($this->TransactionJournal)))
			{
				$this->Logging->write("Purged Record Count: {$purgedRecords}");
			
				$this->Logging->write("Rebuilding indexes.");
				$this->Process->updateProcess($processID, 90, "Rebuilding indexes");
				$this->TransactionJournal->rebuildIndexes();
				
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