<?php
	class NsfPaymentQueueController extends AppController
	{
		/**
		 * Purge the NSF payment queue.
		 */
		function purge()
		{
			$this->pageTitle = 'Purge NSF Payment Queue';
			
			// Only launch the purge if the user submits the form
			if (isset($this->data))
			{
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake purge_nsf_payment_queue -impersonate %s %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						ifset($this->data['NsfPaymentQueue']['purge_date']) == '' ? '' : '-date ' . escapeshellarg($this->data['NsfPaymentQueue']['purge_date'])
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			
			$this->data['NsfPaymentQueue']['purge_date'] = formatDate('-3 months');
		}
	}
?>