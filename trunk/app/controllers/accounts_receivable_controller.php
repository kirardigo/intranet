<?php

	class AccountsReceivableController extends AppController
	{
		var $uses = array('Customer', 'TransactionJournal', 'GeneralLedger');
		var $components = array('Pdf');
		
		/**
		 * Pseudocode of CU05BB.TXT
		 * ========================
		 * a(1) - gen in background
		 * a(2) - condense print
		 * a(3) - grouping order code
		 * a(4) - profit center
		 * a(5) - begin date
		 * a(6) - end date
		 * a(7) - totals only
		 * a(8) - gl code
		 * a(9) - grouping code
		 * a(10) - bank number
		 * a(11) - department
		 *
		 * open work file WU05AR.<initials>
		 * 		* if not condense print, write two blank lines, otherwise write out printer_condense_on (3 lines in default file) 
		 * 		  to the first line, and printer_condense_off (3 lines in default file) to the second line.
		 * 		* write a B if gen in background, or an F if in foreground.
		 * 		* write a(3) through a(11) each on a separate line
		 * close work file
		 * 
		 * if running in foreground:
		 * 		if default file is_running_compiled = N then 
		 *  		run MU05BZ.BAS 
		 * 			else
		 *  			shell out and run "MU05BZ.BAS -e > /dev/null"
		 * 		else
		 *  			shell out and run MU50BZ.BAS in background (unix & at end of commmand)
		 *
		 * Pseudocode of MU05BZ.BAS
		 * ========================
		 * flags:
		 * 		flag1% - signals EOF of WU05AT.<initials> (7) work file if = 1
		 * 		flag2% - signals to print subtotal line instead of normal line if = 1
		 * 		flag3% - signals change of a series if = 1
		 *
		 * read CU0BB.TXT work file WU05AR.<initials> (uses su63(x) variable)
		 * 		type = grouping order code
		 * 		pc = profit center
		 * 		bd = begin date
		 * 		ed = end date
		 * 		gc = G/L code
		 * 		gp = grouping code
		 * 		dp = department
		 *
		 * if type = 'Y' then
		 * 		fl1 = 4 (field length 1)
		 * 		fl2 = 8 (field length 2)
		 * else
		 * 		fl1 = 4 (field length 1)
		 * 		fl2 = 3 (field length 2)
		 *
		 * open default file (sdefault(x) variable) - FU05AN.DAT
		 * open transaction journal (uses fBV variables) - FU05BV.DAT (1)
		 * open GL core file (uses fAA, fAB, fAC, fAD variables) - FU05AA.DAT (2), FU05AB.DAT (3), FU05AC.DAT (4), FU05AD.DAT (5)
		 * 
		 * open report output file PU05AR.<initials> (6)
		 * write first line from WU05AR work file (printer_condense_on) to output file
		 * 
		 * open a new work file for sorting: WU05AT.<initials> (7)
		 *
		 * process transaction journal
		 * 		read each record...
		 * 			if journal.transaction_date_of_service not between start and end date then
		 * 				skip record
		 * 			if chosen profit center isn't ALL and journal.profit_center_number <> chosen profit center then
		 * 				skip record
		 * 			if chosen G/L code isn't ALL and journal.general_ledger_code <> chosen G/L code then
		 * 				skip record
		 * 			if chosen grouping code isn't ALL and journal.inventory_group_code <> chosen grouping code then
		 * 				skip record
		 * 			if chosen department isn't A and journal.department_code <> chosen department then
		 * 				skip record
		 * 			if chosen bank number isn't A and journal.bank_number <> chosen bank number then
		 * 				skip record
		 * 			if type = 'Y' then
		 *				buffer .= journal.inventory_group_code . journal.general_ledger_code . journal.profit_center_number
		 * 			else
		 * 				buffer .= journal.general_ledger_code . journal.profit_center_number . journal.inventory_group_code
		 *
		 * 			write to WU05AT work file: buffer . journal.department_code . journal.bank_number . journal.transaction_date_of_service (YYYYMMDD) . journal.invoice_number . journal.account_number . money_format('%^!#7.2', journal.amount) . journal.transaction_type
		 * 		next
		 *
		 * sort WU05AT work file
		 * reopen sorted WU05AT work file
		 *
		 * print report header:
		 * 		if type = 'Y' then
		 * 			column_1 = "GRP\nCODE"
		 * 			column_2 = "G/L CODE"
		 * 		else
		 * 			column_1 = "G/L CODE"
		 * 			column_2 = "GRP CODE"
		 * 		column_3 = "DEPT"
		 * 		column_4 = "DESCRIPTION"
		 * 		column_5 = "DATE"
		 * 		column_6 = "INVOICE"
		 * 		column_7 = "ACCT NO."
		 * 		column_8 = "AMOUNT"
		 * 		column_9 = "BK#"
		 * 		if type = 'Y' then
		 * 			column_10 = "SUBTOTAL FOR G/L CODE"
		 * 			column_11 = "TOTAL FOR GROUP CODE"
		 * 		else
		 * 			column_10 = "SUBTOTAL FOR PROFIT CENTER"
		 * 			column_11 = "TOTAL FOR G/L CODE"
		 * 
		 * 		if type = 'Y' then
		 * 			report_header_line_1 = 'BY GROUPING CODE'
		 * 		else
		 * 			report_header_line_1 = 'BY G/L CODE'
		 *
		 * 		report_header_line_1 .= " DATES {$bd} to {$ed} FOR P/C {$pc}"
		 * 		report_header_line_2 = 'A/R DISTRIBUTION TO G/L"
		 * 		report_header_line_3 = "G/L CODE: {$gc} / GROUP CODE: {$gp} / DEPARTMENT: {$dp}"
		 *
		 * for each row in WU05AT work file 
		 * 		set previous row into a$ variable
		 * 		read row into na$ variable
		 * 		add the row's amount to a subtotal hashtable (subt) indexed by journal.transaction_type
		 * 		add the row's amount to the running total (tot)
		 * 		add the row's amount to the running subtotal (subtot)
		 * 		add the row's amount to the running grand total (gtot)
		 * 		add the row's amount to the running amount (amt)
		 * 		if column_1's value changed between rows, print totals section
		 * 			if type = 'Y':
		 * 				if column_1, gl code, profit center, or department changed, print subtotals
		 * 			else
		 * 				if column_1, gl code, or profit center changed, print subtotals
		 * 		if journal.bank_number value changed between rows, print subtotals
		 * 		if any of the following differ between rows:
		 * 			journal.general_ledger_code
		 * 			journal.profit_center_number
		 * 			journal.inventory_group_code
		 * 			journal.department_code
		 * 			journal.bank_number 
		 * 			journal.transaction_date_of_service
		 * 			journal.invoice_number
		 * 			journal.account_number
		 * 			and the CU0BB.TXT work file says to NOT do totals only then
		 * 				print line
		 * 					special formatting:
		 * 						- G/L code is "{$journal.general_ledger_code}-{$journal.profit_center_number}"
		 * 						- description is FU05AA.DAT (G/L) description and is only printed on a line above a subtotal or total. If G/L code is not found, "*** NOT ON FILE ***" is printed
		 * 						- if doing totals only:
		 * 							omit column 5 through column 8
		 * 				reset amt variable
		 * next
		 *
		 * printing subtotals:
		 * 		prints in column 10 as:  money_format('%^!#7.2', subtot) . '(' . <item/row count> . ')'
		 * 		resets subtot variable
		 * 
		 * printing totals:
		 * 		prints in column 10 as: money_format('%^!#7.2', subtot) . '(' . <item/row count> . ')'
		 * 		prints in column 11 as: money_format('%^!#7.2', tot) . '(' . <item/row count> . ')'
		 * 		prints rows for each subtotal from the indexed hashtable (subt) for each type:
		 * 			CHG: subt(1)
		 * 			PMT: subt(2)
		 * 			CRD: subt(3)
		 * 			TR CHG: subt(4)
		 * 			TR CRD: subt(5)
		 * 			SUBTOTAL: subt(1) + subt(2) ... + subt(5)
		 * 		resets subtot variable
		 * 		resets tot variable
		 * 		adds subt amounts to a running total tot() hashtable variable 
		 * 		resets subt variable
		 *
		 * printing grand totals:
		 * 		prints rows for each total from the indexed hashtable (tot) for each type:
		 * 			TOTAL CHG: tot(1)
		 * 			TOTAL PMT: tot(2)
		 * 			TOTAL CRD: tot(3)
		 * 			TOTAL TR CHG: tot(4)
		 * 			TOTAL TR CRD: tot(5)
		 * 		prints "GRAND TOTAL" in column 6
		 * 		prints in column 8 as: money_format('%^!#7.2', gtot) . '(' . <item/row count> . ')'
		 *
		 * end of report:
		 * 		write second line from WU05AR work file (printer_condense_off) to output file 
		 * 		prints "END OF REPORT: <total rows> entries printed."
		 * 		depending on if running in background or not, kills off the basic script
		 * 		
		 */
		function report_glDistribution()
		{
			$this->pageTitle = 'A/R Distribution to G/L';
			
			if (!empty($this->data))
			{
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['TransactionJournal.begin_date']) && isset($conditions['TransactionJournal.end_date']))
				{
					$conditions["TransactionJournal.transaction_date_of_service between"] = array(
						databaseDate($conditions['TransactionJournal.begin_date']), 
						databaseDate($conditions['TransactionJournal.end_date'])
					);
					
					unset($conditions['TransactionJournal.begin_date']);
					unset($conditions['TransactionJournal.end_date']);
				}
				else if (isset($conditions['TransactionJournal.begin_date']))
				{
					$conditions['TransactionJournal.transaction_date_of_service >='] = databaseDate($conditions['TransactionJournal.begin_date']);
					unset($conditions['TransactionJournal.begin_date']);
				}
				else if (isset($conditions['TransactionJournal.end_date']))
				{
					$conditions['and'] = array('TransactionJournal.transaction_date_of_service <=' => databaseDate($conditions['TransactionJournal.end_date']));
					unset($conditions['TransactionJournal.end_date']);
				}
				
				$groupByGroupCode = $conditions['Option.grouping_order_code'];
				unset($conditions['Option.grouping_order_code']);
				
				$totalsOnly = $conditions['Option.totals_only'];
				unset($conditions['Option.totals_only']);

				if (empty($conditions))
				{
					$this->set('noCriteria', true);
					return;
				}

				$order = array(
					'TransactionJournal.department_code', 
					'TransactionJournal.bank_number', 
					'TransactionJournal.transaction_date_of_service', 
					'TransactionJournal.invoice_number', 
					'TransactionJournal.account_number'
				);
				
				set_time_limit(0);
				
				$matches = $this->TransactionJournal->find('all', array(
					'conditions' => $conditions,
					'fields' => array(
						'TransactionJournal.inventory_group_code',
						'TransactionJournal.general_ledger_code',
						'TransactionJournal.profit_center_number',
						'TransactionJournal.department_code',
						'TransactionJournal.transaction_date_of_service',
						'TransactionJournal.transaction_type',
						'TransactionJournal.invoice_number',
						'TransactionJournal.account_number',
						'TransactionJournal.amount',
						'TransactionJournal.bank_number',
					),
					'contain' => array(),
					'order' => array_merge(
						$groupByGroupCode ? 
							array('TransactionJournal.inventory_group_code', 'TransactionJournal.general_ledger_code', 'TransactionJournal.profit_center_number') : 
							array('TransactionJournal.general_ledger_code', 'TransactionJournal.profit_center_number', 'TransactionJournal.inventory_group_code'),
						$order
					)
				));
				
				$this->layout = 'blank';
				
				//we pull GL codes separately instead of in the contain for performance
				$glCodes = Set::combine(
					$this->GeneralLedger->find('all', array(
						'contain' => array()
					)), 
					'/GeneralLedger/general_ledger_code', 
					'/GeneralLedger/description'
				);
				
				foreach($matches as $match)
				{
					$match['GeneralLedger']['description'] = ifset($glCodes[$match['TransactionJournal']['general_ledger_code']], '');
				}

				$this->set('matches', $matches);
				
				$this->set('groupByGroupCode', $groupByGroupCode);
				$this->set('totalsOnly', $totalsOnly);
				$this->set('manager', $this->Pdf);
				$this->set('pdf', $this->Pdf->create());
			}
		}
		
		/**
		 * Front-end for the A/R Aging shell.
		 */
		function report_aging()
		{	
			$this->pageTitle = 'A/R Aging';
			
			if (!empty($this->data))
			{
				//aging date is required
				if (!isset($this->data['Invoice']['aging_date']))
				{
					$this->set('noAgingDate');
					return;
				}
				
				//prep the parameters to pass to the shell
				$parameters = array(
					'date' => databaseDate($this->data['Invoice']['aging_date']),
					'username' => $this->Session->read('user')
				);

				//first the field -> value parameters
				$fields = array(
					'Customer.start_range' => 'start',
					'Customer.end_range' => 'end',
					'Customer.profit_center_number' => 'profitCenter',
					'CustomerCarrier.carrier_number' => 'carrierNumber',
					'Carrier.statement_type' => 'statementType',
					'Carrier.group_code' => 'groupCode',
					'Transaction.account_balance' => 'accountBalance',
					'Invoice.minimum_days_old' => 'minimumDays',
					'Carrier.network' => 'network'
				);
				
				foreach ($fields as $key => $flag)
				{
					list($model, $field) = explode('.', $key);
					
					if (trim($this->data[$model][$field]) != '')
					{
						$parameters[$flag] = $this->data[$model][$field];
					}
				}
				
				//now all the option flags
				$options = array(
					'Option.sort_by_account_number' => 'sortNumber',
					'Option.break_down_by_invoice' => 'invoices',
					'Option.print_customer_memo' => 'showMemos',
					'Option.age_from_service_date' => 'ageServiceDate',
					'Option.print_summary_page_only' => 'summaryOnly',
					'Option.print_with_carrier_number' => 'showCarrierNumbers',
					'Option.print_with_billing_date_blank' => 'showBlankBillingDates',
					'Option.print_with_credit_invoices_displayed' => 'showCreditInvoices'
				);
					
				foreach ($options as $key => $flag)
				{
					list($model, $field) = explode('.', $key);
					
					if ($this->data[$model][$field])
					{
						$parameters[$flag] = '';
					}
				}
				
				$args = '';
				
				//collapse the args for the command line
				foreach ($parameters as $key => $value)
				{
					$args .= "-{$key} " . escapeshellarg($value) . ' ';
				}
				
				//kick off the report
				exec(
					sprintf(
						"cd %s; nohup ./cake/console/cake ar_aging_report %s > /dev/null 2>&1 &",
						escapeshellarg(ROOT),
						$args
					), 
					$output
				);
				
				$this->redirect('/processes/manager');
			}
			else
			{
				//default to sort by name
				$this->data['Option']['sort_by_account_number'] = 0;
				
				//default to today's date
				$this->data['Invoice']['aging_date'] = date('m/d/Y');
			}
		}
	}
?>