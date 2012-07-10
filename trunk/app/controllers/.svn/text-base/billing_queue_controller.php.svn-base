<?php
	class BillingQueueController extends AppController
	{
		/**
		 * Purge the billing queue.
		 */
		function purge()
		{
			$this->pageTitle = 'Purge Billing Queue';
			
			// Only launch the purge if the user submits the form
			if (isset($this->data))
			{
				shell_exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake purge_billing_queue -impersonate %s %s %s %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						escapeshellarg($this->Session->read('user')),
						ifset($this->data['BillingQueue']['purge_date']) == '' ? '' : '-date ' . escapeshellarg($this->data['BillingQueue']['purge_date']),
						ifset($this->data['BillingQueue']['form_code']) == '' ? '' : '-formCode ' . escapeshellarg($this->data['BillingQueue']['form_code']),
						$this->data['BillingQueue']['is_invoice_zero_balance_required'] ? '-zeroBalanceRequired' : ''
					)
				);
				
				$this->redirect('/processes/manager/reset:1');
			}
			
			$this->data['BillingQueue']['purge_date'] = formatDate('-3 months');
		}
	}
?>