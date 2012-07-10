<?php
	Configure::write('Cache.disable', true);
	
	class ArGlDistributionReportShell extends Shell 
	{
		var $uses = array();
		var $tasks = array('ReportParameters');
		var $data = array();
		
		var $parameters = array(
			array(
				'type' => 'string',
				'model' => 'Report',
				'field' => 'output_path',
				'flag' => 'o',
				'description' => 'The path to save the final report output',
				'required' => true
			),
			array(
				'type' => 'date',
				'model' => 'TransactionJournal',
				'field' => 'begin_date',
				'flag' => 's',
				'description' => 'The start of the date range to search'
			),
			array(
				'type' => 'date',
				'model' => 'TransactionJournal',
				'field' => 'end_date',
				'flag' => 'e',
				'description' => 'The end of the date range to search'
			),
			array(
				'type' => 'string',
				'model' => 'TransactionJournal',
				'field' => 'profit_center_number',
				'flag' => 'p',
				'description' => 'The profit center'
			),
			array(
				'type' => 'string',
				'model' => 'TransactionJournal',
				'field' => 'general_ledger_code',
				'flag' => 'c',
				'description' => 'The general ledger code'
			),
			array(
				'type' => 'string',
				'model' => 'TransactionJournal',
				'field' => 'inventory_group_code',
				'flag' => 'i',
				'description' => 'The inventory group code'
			),
			array(
				'type' => 'string',
				'model' => 'TransactionJournal',
				'field' => 'bank_number',
				'flag' => 'b',
				'description' => 'The bank number'
			),
			array(
				'type' => 'string',
				'model' => 'TransactionJournal',
				'field' => 'department_code',
				'flag' => 'd',
				'description' => 'The department code'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'grouping_order_code',
				'flag' => 'g',
				'description' => 'Grouping order code'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'totals_only',
				'flag' => 't',
				'description' => 'Totals only'
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{			
			$this->out("Generating A/R Distribution to G/L Report...");
			
			$data = $this->ReportParameters->parse($this->parameters);

			//grab the output path
			$path = $data['Report']['output_path'];
			unset($data['Report']);
			
			if (substr($path, -1) == '/')
			{
				$path = substr($path, 0, -1);
			}

			//run the report
			$report = $this->requestAction('/reports/accountsReceivable/glDistribution', array('data' => $data, 'return'));
			
			//save the contents
			file_put_contents($path . '/' . rand(100000, 999999) . '.pdf', $report);
			
			$this->out("Finished!");
			$this->out("");
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>