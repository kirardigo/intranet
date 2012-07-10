<?php
	class ElectronicMedicalClaimsController extends AppController
	{
		var $uses = array();
		var $pageTitle = 'EMC';
		
		/**
		 * Webpage wrapper around our EMC billing Cake shell.
		 */
		function billing()
		{
			if (!empty($this->data))
			{
				//prep the parameters to pass to the shell
				$parameters = array(
					'emc' => $this->data['Billing']['form_code'],
					'pc' => $this->data['Billing']['profit_center'],
					'date' => databaseDate($this->data['Billing']['billing_date']),
					'carrier' => implode(',', array_filter(array_map('trim', array(
						$this->data['Billing']['carrier_code_1'], 
						$this->data['Billing']['carrier_code_2'], 
						$this->data['Billing']['carrier_code_3'], 
						$this->data['Billing']['carrier_code_4'], 
						$this->data['Billing']['carrier_code_5']
					)))),
					'username' => $this->Session->read('user')
				);
				
				$args = '';
				
				//collapse the args for the command line
				foreach ($parameters as $key => $value)
				{
					$args .= "-{$key} " . escapeshellarg($value) . ' ';
				}
				
				//kick off the billing
				exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake electronic_medical_claims_billing %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						$args
					), 
					$output
				);
				
				$this->redirect('/processes/manager');
			}
			else
			{
				//default carriers and the billing date 
				$this->data['Billing']['carrier_code_1'] = 'MC20';
				$this->data['Billing']['carrier_code_2'] = 'MC21';
				$this->data['Billing']['carrier_code_3'] = 'MC22';
				$this->data['Billing']['billing_date'] = date('m/d/Y');
			}
		}
	}
?>