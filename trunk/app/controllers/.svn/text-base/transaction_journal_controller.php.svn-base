<?php
	class TransactionJournalController extends AppController
	{
		var $uses = array('TransactionJournal', 'Lookup');
		
		/**
		 * Purge the transaction journal.
		 */
		function purge()
		{
			$this->pageTitle = 'Purge Transaction Journal';
			
			// Only launch the purge if the user submits the form
			if (isset($this->data))
			{
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake purge_transaction_journal -impersonate %s %s -profitCenter %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						$this->data['TransactionJournal']['purge_date'] == '' ? '' : '-date ' . escapeshellarg($this->data['TransactionJournal']['purge_date']),
						escapeshellarg($this->data['TransactionJournal']['profit_center_number'])
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			
			$this->set('profitCenters', $this->Lookup->get('profit_centers', true, true));
			$this->data['TransactionJournal']['purge_date'] = formatDate('-3 months');
		}
	}
?>