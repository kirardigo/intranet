<?php
	class Document extends AppModel
	{
		var $useDbConfig = 'docpop';
		var $useTable = 'Document';
		var $primaryKey = 'DocID'; 
		
		var $belongsTo = array('Queue' => array('foreignKey' => 'QueueID'));
		
		/** Used to cache settings for generate() so it doesn't have to hit the database on every call. */
		var $_settings = null;
		
		/**
		 * Creates a new document in DocPop. Internally this uses the addcrossref C application to generate a
		 * cross reference number to store in the Document record that gets created in the DocPop database.
		 * @param string $accountNumber The account number to create the document for.
		 * @param string $invoiceNumber The invoice number to create the document for.
		 * @param string $tcnNumber The TCN number to create the document for.
		 * @return mixed The cross reference number of the document that was inserted if successful, false otherwise.
		 */
		function generate($accountNumber, $invoiceNumber, $tcnNumber)
		{
			//load and cache settings if we don't have them yet
			if ($this->_settings == null)
			{
				$this->_settings = ClassRegistry::init('Setting')->get(array('addcrossref_path'));
			}
						
			$command = $this->_settings['addcrossref_path'] . ' -a ' . escapeshellarg($accountNumber) . ' -f 1096 -n ' . escapeshellarg($invoiceNumber) . ' -i ' . escapeshellarg($tcnNumber);
			
			$result = exec($command, $output, $returned);
			return $returned !== 0 ? false : trim($result);
		}
		
		/**
		 * Determines the Document ID from the given barcode.
		 * @param string $barcode The barcode to search with.
		 * @return mixed The ID of the document if found, false otherwise.
		 */
		function resolveIDFromBarcode($barcode)
		{
			return $this->field('DocID', array(
				'CrossRefID' => $barcode, 
				'or' => array(
					'DeletedStatus' => null,
					'DeletedStatus <>' => 1
				)
			));
		}
		
		/**
		 * Assigns a document to a queue. Should only be used on documents that don't have a queue already assigned.
		 * @param int $documentID The ID of the document to assign.
		 * @param int $queueID The ID of the queue to assign the document to.
		 */
		function assignToQueue($documentID, $queueID)
		{
			$this->save(array('Document' => array(
				'DocID' => $documentID,
				'QueueID' => $queueID,
				'SortID' => $this->field('((ifnull(max(SortID), 0) + 1))', array('QueueID' => $queueID)),
				'WorkStation' => 'eMRS',
				'CreatedBy' => 'MILLERS\\' . User::current(),
				'CreatedAt' => date('Y-m-d'),
				'LastSavedBy' => 'MILLERS\\' . User::current(),
				'LastSavedAt' => date('Y-m-d'),
				'LastSavedMilli' => 0,
				'LastSavedSession' => mt_rand(1000000, 9999999) . mt_rand(1000000, 9999999)
			)));
		}
		
		/**
		 * Changes all document records in DocPop for the given account number to be associated with a different account.
		 * @param string $currentAccountNumber The account number to migrate.
		 * @param string $newAccountNumber The new account number to apply to all matching records.
		 * @return bool True if successful, false otherwise.
		 */
		function migrateAccount($currentAccountNumber, $newAccountNumber)
		{
			//escape the new account number since updateAll doesn't do any quoting or escaping on the fields
			$db = ConnectionManager::getDataSource($this->useDbConfig);
			$newAccountNumber = $db->value($newAccountNumber);
			
			//grab the name of the index field that contains account numbers in docpop
			$field = ClassRegistry::init('Setting')->get('docpop_account_number_field');
			
			//do the update
			return $this->updateAll(array($field => $newAccountNumber), array($field => $currentAccountNumber));
		}
	}
?>