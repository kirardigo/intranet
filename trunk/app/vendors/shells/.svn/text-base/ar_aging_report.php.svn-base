<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Shell that implements the old U05 A/R Aging Report (MU05BS) program.
	 */
	class ArAgingReportShell extends Shell 
	{
		var $uses = array('Customer', 'Invoice', 'CustomerCarrier', 'Process');
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');

		/** This will be used as a PdfComponent - casing is on purpose to simulate components in a controller. */
		var $Pdf = null;
		
		/** This will contain all of the settings needed to create the report. Things like column widths, running totals, etc. */
		var $pdfSettings = array();
				
		/** This will hold our process ID that will be used to report our progress as we go through each customer. */
		var $processID;
		
		/** The model used by the U05 driver for indexes, which we're going to abuse :) */
		var $indexModel = null;
		
		/** This holds the ID of the current record to process from our sort table. */
		var $current = 0;
		
		/** This is the MySQL table name we use to fetch customers in a sorted order. */
		var $sortTable = 'aging_sort';
		
		/** This is the field in the invoice model that we'll be aging from. The shell parameter dictates what that is. */
		var $invoiceDateField = null;
		
		var $parameters = array(
			array(
				'type' => 'date',
				'model' => 'Invoice',
				'field' => 'aging_date',
				'flag' => 'date',
				'description' => 'The aging date',
				'required' => true
			),
			array(
				'type' => 'string',
				'model' => 'Customer',
				'field' => 'start_range',
				'flag' => 'start',
				'description' => 'The start of the range to search'
			),
			array(
				'type' => 'string',
				'model' => 'Customer',
				'field' => 'end_range',
				'flag' => 'end',
				'description' => 'The end of the range to search'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'sort_by_account_number',
				'flag' => 'sortNumber',
				'description' => 'Search by account number instead of name'
			),
			array(
				'type' => 'string',
				'model' => 'Customer',
				'field' => 'profit_center_number',
				'flag' => 'profitCenter',
				'description' => 'The profit center to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'CustomerCarrier',
				'field' => 'carrier_number',
				'flag' => 'carrierNumber',
				'description' => 'The carrier to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Carrier',
				'field' => 'statement_type',
				'flag' => 'statementType',
				'description' => 'The statement type to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Carrier',
				'field' => 'group_code',
				'flag' => 'groupCode',
				'description' => 'The carrier grouping code to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Transaction',
				'field' => 'account_balance',
				'flag' => 'accountBalance',
				'description' => 'The minimum (non-inclusive) account balance to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Invoice',
				'field' => 'minimum_days_old',
				'flag' => 'minimumDays',
				'description' => 'The minimum days old on the invoice to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Carrier',
				'field' => 'network',
				'flag' => 'network',
				'description' => 'The network to filter by'
			),
			array(
				'type' => 'string',
				'model' => 'Virtual',
				'field' => 'username',
				'flag' => 'username',
				'description' => 'The user to run the report as',
				'required' => true
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'break_down_by_invoice',
				'flag' => 'invoices',
				'description' => 'Break down by invoice'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'print_customer_memo',
				'flag' => 'showMemos',
				'description' => 'Print customer memo'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'age_from_service_date',
				'flag' => 'ageServiceDate',
				'description' => 'Age from service date'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'print_summary_page_only',
				'flag' => 'summaryOnly',
				'description' => 'Print summary page only'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'print_with_carrier_number',
				'flag' => 'showCarrierNumbers',
				'description' => 'Print with carrier number'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'print_with_billing_date_blank',
				'flag' => 'showBlankBillingDates',
				'description' => 'Print with billing date blank'
			),
			array(
				'type' => 'flag',
				'model' => 'Option',
				'field' => 'print_with_credit_invoices_displayed',
				'flag' => 'showCreditInvoices',
				'description' => 'Print with credit invoices displayed'
			)
		);
		
		/**
		 * Main entry point of the shell.
		 * 
		 * Pseudocode of CU05AR.TXT
		 * ========================
		 * 
		 * checks overflow file (FU05BP.DAT) and allows the user to choose not to continue
		 * 
		 * a(1) - gen in background
		 * a(2) - condense print
		 * a(3) - aging date
		 * a(4) - profit center
		 * a(5) - break down by invoice
		 * a(6) - beginning sort range (account number or name - based on a(14))
		 * a(7) - ending sort range (account number or name - based on a(14))
		 * a(8) - print customer memo
		 * a(9) - car# or ALL or NET
		 * a(10) - age from service date (default bill date)
		 * a(11) - statement type
		 * a(12) - carrier grouping code
		 * a(13) - status
		 * a(14) - sort by account number (default name)
		 * a(15) - account balances greater than
		 * a(16) - minimum days old
		 * a(17) - print summary pages only?
		 * a(18) - print with carrier number (default name)
		 * a(19) - print with billing date blank?
		 * a(20) - print with credit invoices displayed?
		 * a(21) - network
		 *
		 * open work file WU05AY.<initials>
		 * 		* if not condense print, write two blank lines, otherwise write out printer_condense_on (3 lines in default file) 
		 * 		  to the first line, and printer_condense_off (3 lines in default file) to the second line.
		 * 		* write a B if gen in background, or an F if in foreground.
		 * 		* write a(3) through a(21) each on a separate line
		 * close work file
		 * 
		 * if running in foreground:
		 * 		if default file is_running_compiled = N then 
		 *  		run MU05BS.BAS 
		 * 			else
		 *  			shell out and run "MU05BS.BAS -e > /dev/null"
		 * 		else
		 *  			shell out and run MU50BS.BAS in background (unix & at end of commmand)
		 *
		 * Pseudocode of MU05BS.TXT
		 * ========================
		 * open default file (sdefault(x) variable) - FU05AN.DAT
		 * open customer core files (uses fbl, fbm, fbn, fbp variables) - FU05BL.DAT (1), FU05BM.DAT - index file on account number (2), FU05BN.DAT - index file on customer name, account number (3), FU05BP.DAT (4)
		 * open customer billing file (uses fbq variable) - FU05BQ.DAT (5)
		 * open customer carrier file (uses fbr variable) - FU05BR.DAT (6)
		 * open invoice file (uses fbt variable) - FU05BT.DAT (7)
		 * open carrier files (uses fbf, fbg, fbh, fbj variables) - FU05BF.DAT (9), FU05BG.DAT (10), FU05BH.DAT (11), FU05BJ.DAT (12)
		 * open transaction file (uses fbu variable) - FU05BU.DAT (13)
		 *
		 * read CU05AR.TXT work file W05AY.<initials> (uses su63(x) variable)
		 *
		 * variables:
		 * sum - if print summary pages only, sum = 1 else sum = 0
		 * car - if print with carrier number, car = 1 else car = 0
		 * cred - if print with credit invoices displayed, cred = 1 else cred = 0
		 * z - if car = 1, z=4 else z=6 (this basically toggles the printing of columns 12 and 13, which 
		 *     only appear when printing carrier numbers)
		 * type - specifies the type applied to a given invoice (credit, current, over 30, over 60, etc). The type
		 *        differs depending on if credit invoices are being shown and if carrier number is being printed. Different
		 *        columns are shown depending the state of both, and what is considered to be "current" changes. Possible values:
		 * 			1 (credit) - showing credit invoices AND invoice balance < 0
		 * 			2 (current) - shoing credit invoices AND difference between aging date and invoice date <= 60
		 * 			1 (current) - invoice balance < 0 AND difference between aging date and invoice date <= 30
		 * 			2 (over 30) - difference between aging date and invoice date > 30 and <= 60
		 * 			3 (over 60) - difference between aging date and invoice date > 60 and <= 90		 
		 * 			4 (over 90) - printing with carrier number AND difference > 90 and <= 120
		 * 			5 (over 120) - printing with carrier number AND difference > 120 and <= 150
		 * 			6 (over 150) - printing with carrier number and nothing above matched
		 * 			4 (over 90) - no cases above matched
		 * tlc - number of customers printed
		 * f1 - used to state whether or not the customer header was printed
		 * f2 - used to state whether or not a carrier header was printed
		 * f3 - set when "break down by invoice" is chosen. Set to 1 when the customer and carrier lines are printed to
		 *      cause the comment memo to be printed
		 * f4 - i think this is used to count how many lines have been printed per customer if "break down by invoice" is chosen
		 * f5 - used as a flag whether or not the customer.profit_center_number and customer.phone_number have been printed
		 * f6 - used as a flag to print or not the customer memo has been printed
		 * f7 - unsure, always set to zero
		 * v8 - customer_billing.old_collection_status
		 * v9 - the number of days between the aging date and an invoice date
		 * v13 - carrier.statement_type
		 * tt1() - carrier totals indexed by type (see type variable above)
		 * tt2() - I think these are grand totals by type
		 * tt3() - totals indexed by type for v8 = 1
		 * tt4() - totals indexed by type for v8 = 2

		 * tt5() - totals indexed by type for v8 = 3
		 * rctot(,) - "recap" totals; two dimension array. Contains all of the running totals indexed 
		 *            by carrier.statement_type (v13) and then by type (see type variable above)
		 * 
		 * 
		 * open report output file PU05AW.<initials> (8)
		 * write first line from WU05AY work file (printer_condense_on) to output file
		 *
		 * print report header:
		 * 		column_1 = "ACCT # STATUS"
		 * 		column_2 = "NAME"
		 * 		if print with carrier number then
		 * 			column_3 = "CARR NO."
		 * 		else
		 * 			column_3 = "CARRIER"
		 *
		 * 		column_4 = "INVOICE(AGE)"
		 * 		column_5 = "BILL DATE"
		 * 		column_6 = "SERV"
		 * 		if print with credit invoices displayed then
		 * 			column_7 = "CREDIT"
		 * 			column_8 = "CURRENT"
		 * 		else
		 * 			column_7 = "CURRENT"
		 * 			column_8 = "OVER 30"
		 *
		 * 		column_9 = "OVER 60"
		 * 		column_10 = "OVER 90"
		 * 		if print with carrier number then
		 * 			column_11 = "OVER 120"
		 * 			column_12 = "OVER 150"
		 * 			column_13 = "TOTAL"
		 * 		else
		 * 			column_11 = "TOTAL"
		 * 
		 * 		report_header_line_1 = 'A/R AGING REPORT'
		 * 		report_header_line_2 = '<company name>' (comes from default file company_name)
		 * 		report_header_line_3 = "AGING DATE: <date>" (date is the selected aging date from work file - a(3))
		 *
		 * prints a whole page of what parameters were chosen from work file
		 *
		 * now to process the data...
		 * 		if sort by account number, read from index file FU05BM, else read from index file FU05BN
		 * 		in order to determine the next record to read from FU05BL. This logic is used as the loop
		 * 		to read each customer in order and process them. So...
		 *
		 * 		with each customer...
		 * 			if sort field (customer.account_number or customer.name) is not within sort range then next customer
		 * 			if customer.profit_center_number <> chosen profit center then next customer
		 * 			if customer.invoice_pointer = 0 then next customer
		 * 			if customer.carrier_pointer = 0 then next customer
		 * 			if customer.transaction_pointer = 0 then next customer
		 * 			read record from transaction file where id = customer.transaction_pointer
		 * 			if "account balances greater than" criteria chosen and transaction.account_balance < criteria, next customer
		 * 			if "status" criteria is set:
		 * 				if customer.billing_pointer = 0 then next customer
		 * 				read record from customer_billing where id = customer.billing_pointer
		 * 				if customer_billing.old_collection_status <> "status" criteria then next customer
		 * 			set v8$ variable to customer_billing.old_collection_status, or empty if customer.billing_pointer = 0
		 * 			if "car#" criteria is specified and <> "NET" then
		 * 				look through all carriers for the customer (customer.carrier_pointer chain)
		 * 				if no customer_carrier.carrier_number fields in the chain match then next customer
		 * 			if "statement type" or "carrier grouping code" was specified then
		 * 				look up the carrier record for the customer carriers that matched
		 * 					and compare the carrier.statement_type and carrier.group_code fields. 
		 * 				If either doesn't match then next customer
		 * 			if "car#" criteria was "NET" then
		 * 				compare the carrier records for matching customer carriers and compare
		 * 					against carrier.network.
		 * 				If it doesn't match then next customer
		 * 			set v4$(row number) hash variable to customer_carrier.carrier_number
		 * 			set v5$(row number) hash variable to customer_carrier.carrier_name
		 * 			if the carrier record was found then
		 * 				set v13$(row number) hash variable to carrier.statement_type
		 * 			else
		 * 				set v13$(row number) = ''
		 * 
		 * VIEW		As long as we got at least one good customer carrier that matched then
		 * |			if not print summary pages only then (do this on first line of first carrier)
		 * |				print "{customer.account_number}-{customer_billing.old_collection_status} {customer.name}"
		 * |				print "{customer.profit_center_number} {customer.phone_number}"
		 * |				if break down by invoice then 
		 * |					print memo (customer.bad_debt_note) if non-blank
		 *
		 * 				for each customer carrier
		 * 					reset carrier totals indexed by type
		 * 					for each invoice in customer.invoice_pointer chain
		 * 						if "age from service date" then
		 * 							set v10$ variable to invoice.date_of_service
		 * 						else
		 * 							set v10$ variable to invoice.billing_date
		 * 						if not "print with billing date blank" and v10$ is blank then next invoice
		 * 						if v10$ >= "aging date" then next invoice
		 * 						if "minimum days old" is set, and v10$ is not that many days old then next invoice
		 *
		 * VIEW					determine what v4(row number) (carrier_number) matches in the invoice - either
		 * |						invoice.carrier_1_code, invoice.carrier_2_code, or invoice.carrier_3_code. Whichever
		 * |						one it matches, set v3 equal to the corresponding carrier_X_balance field. If
		 * |						the invoice didn't match any of those codes or balance = 0 then next invoice
		 * |					update running and recap totals grouped by the type of invoice (see type and rctot variables above)
		 * | 					if not "print summary pages only" and "break down by invoice"
		 * |						print ([header 1] | [header 2] | [header 3]), [carrier name/number], invoice.invoice_number, aging days (v9), invoice.billing_date, invoice.date_of_service, invoice.carrier_X_balance (whichever was chosen) in correct type column
		 * |							A note about header1, 2, and 3:
		 * | 								header1 = "{customer.account_number}-{customer_billing.old_collection_status} {customer.name}"
		 * | 								header2 = "{customer.profit_center_number} {customer.phone_number}"
		 * | 								header3 = customer.bad_debt_note
		 * |								Headers are printed on a line if they haven't been printed yet. They are only
		 * | 								printed once per customer.
		 * | 							A note about carrier name/number - this is only printed on the first line of each carrier		 
		 * 					next invoice
		 *
		 * VIEW				if not "print summary pages only" then
		 * | 					if carrier had invoices then
		 * | 						print ([header1] | [header2] | [header3]), [carrier name/number], carrier totals by type in multiple columns
		 * 				next carrier
		 *
		 * VIEW			if customer had invoices increment tlc by 1
		 * | 			if not "print summary pages only" then
		 * |				render header2 and/or memo if they haven't been printed yet for the customer
		 * 		next customer
		 *
		 * VIEW	print grand totals:
		 * | 		re-print report header
		 * |		write "GROSS TOTALS" line:
		 * | 			totals in ttl2
		 * | 		write "UNCOLLECTABLE (status 1)" line:
		 * |			totals in ttl3
		 * |		write "SUBTOTAL" line:
		 * |			totals in ttl2 - totals in ttl3
		 * |		write "PENDING (status 2)" line:
		 * |			totals in ttl4
		 * |		write "POC (status 3)" line:
		 * |			totals in ttl5
		 * |		write "NET TOTALS" line:
		 * |			totals in ttl2 - (ttl3 + ttl4 + ttl5)
		 * |	print recap
		 */
		function main()
		{
			$cancelled = false;
			$percentComplete = 0;
			$lastCustomerProcessed = null;
			
			//initialize our parameters
			$parameters = array_merge(
				array(
					'Carrier' => array(), 
					'CustomerBilling' => array(), 
					'CustomerCarrier' => array(), 
					'Billing' => array(),
					'Invoice' => array(),
					'Customer' => array(),
					'Transaction' => array()
				),
				$this->ReportParameters->parse($this->parameters)
			);
			
			//we need to maintain the log to store in the process at the end
			$this->Logging->maintainBuffer();
			
			//impersonate the user
			$this->Impersonate->impersonate($parameters['Virtual']['username']);
			
			//start a process
			$this->processID = $this->Process->createProcess('A/R Aging Report', true);
			
			//output all the parameters we're using for this run
			$this->Logging->write('Parameters:');
			
			foreach (Set::flatten($parameters) as $parameter => $value)
			{
				$this->Logging->write($parameter . ' => ' . $value);
			}
			
			$this->Logging->write('');
			$this->Logging->write('Starting process.');
			
			//grab the index model from the driver
			$db = ConnectionManager::getDataSource('fu05');
			$this->indexModel = $db->_indexModel();

			//create our sorted customer table
			$this->Logging->write('Calculating sort order.');
			$customerCount = $this->createSortTable($parameters);
			$this->Logging->write("{$customerCount} customers to process.");
			
			//figure out what invoice field we're aging from
			$this->invoiceDateField = $parameters['Option']['age_from_service_date'] ? 'date_of_service' : 'billing_date';
			
			//load up the PDF component
			App::import('Component', 'Pdf');
			$this->Pdf = New PdfComponent();
			$document = $this->Pdf->create();
			
			//set up our PDF settings and start the document
			$this->pdfSettings = $this->createPdfSettings($parameters);
			$this->initializePdf($document, $parameters);

			while (($customer = $this->next($parameters)) !== false)
			{
				//check if we should interrupt
				if ($this->Process->isProcessInterrupted($this->processID))
				{
					$cancelled = true;
					$this->Process->updateProcess($this->processID, $percentComplete, 'Cancelling. Report will be generated with partial data.');
					$this->Logging->write("Cancel initiated. The last customer processed was: {$lastCustomerProcessed}");
					break;
				}
				
				//update the process
				$percentComplete = 100 * ($this->current / $customerCount);
				
				if ($this->current % 10 == 0)
				{
					$this->Process->updateProcess($this->processID, $percentComplete, "Processing ({$this->current} of {$customerCount})");
				}
				
				//process the customer
				$this->processCustomer($document, $customer, $parameters);
				
				//keep track of the last processed customer in case the user cancels the process
				$lastCustomerProcessed = $customer['Customer']['account_number'];
			}
			
			//render totals
			$this->renderTotals($document);
			
			//save the PDF
			$this->Process->addFile($this->processID, 'A/R Aging Report', 'report.pdf', 'application/pdf', $document->Output('', 'S'));
			
			//update the percent on the process that we were able to get to
			if ($cancelled)
			{				
				$this->Process->updateProcess($this->processID, $percentComplete, 'Cancelled');
			}
			else
			{
				$this->Process->updateProcess($this->processID, 100, 'Finished');
			}
			
			//finish the process
			$this->Logging->write('Done.');
			$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
		}
		
		/**
		 * Creates a temporary MySQL table that contains the sorted order of customers to process.
		 * @param array $parameters The parameters the shell was invoked with.
		 * @return int The number of records in the sorted table.
		 */
		function createSortTable($parameters)
		{
			//sorting through the U05 driver is expensive, especially when grabbing a large amount of customers.
			//so I'm going to cheat like crazy and create a sort order via the indexes
			$db = ConnectionManager::getDataSource('fu05');
			$indexes = $db->describe($this->Customer, 'indexes');
			
			//figure out what we're filtering/sorting by so we can figure out the right index to use
			$filter = $parameters['Option']['sort_by_account_number'] == 0 ? 'name' : 'account_number';
			$indexTable = $indexes[$filter];
			
			//create our sort table
			$this->indexModel->query("create temporary table {$this->sortTable} like `{$indexTable}`");
			
			//figure out the conditions to filter the results by before populating the sort
			$whereClause = '';
			
			//find the length of the field we're searching/ordering on
			$length = $this->Customer->schema($filter);
			$length = $length['length'];
			
			//apply the range if we have one
			if (isset($parameters['Customer']['start_range']) && isset($parameters['Customer']['end_range']))
			{
				$start = Sanitize::escape($parameters['Customer']['start_range']);
				$end = Sanitize::escape(str_pad($parameters['Customer']['end_range'], $length, 'z'));
				$whereClause = "where value between '{$start}' and '{$end}'";
			}
			else if (isset($conditions['Customer.start_range']))
			{
				$start = Sanitize::escape($conditions['Customer']['start_range']);
				$whereClause = "where value >= '{$start}'";
			}
			else if (isset($conditions['Customer.end_range']))
			{
				$end = Sanitize::escape(str_pad($parameters['Customer']['end_range'], $length, 'z'));
				$whereClause = "where value <= '{$end}'";
			}
			
			//populate the table in sorted order
			$this->indexModel->query("
				insert into `{$this->sortTable}` (value, record_number)
				select value, record_number from `{$indexTable}` 
				{$whereClause}
				order by value
			");
			
			//return the count to process
			return array_pop(Set::flatten($this->indexModel->query("select count(1) from `{$this->sortTable}`")));
		}
		
		/**
		 * Grabs the next customer record to process.
		 * @param array $parameters The parameters the shell was invoked with. This is pass by reference
		 * for a reason - namely because of the possible recursion that occurs here, I don't want to be making
		 * copies of copies of copies of the parameters array.
		 * @return array The next customer.
		 */
		function next(&$parameters)
		{
			$found = false;
			
			while (!$found)
			{
				$next = $this->indexModel->query("select record_number from {$this->sortTable} where id > {$this->current} order by id limit 1");
				
				if (empty($next))
				{
					return false;
				}
				
				$this->current++;
				
				//grab the customer
				$customer = $this->Customer->find('first', array(
					'fields' => array(
						'Customer.account_number',
						'Customer.name',
						'Customer.profit_center_number',
						'Customer.phone_number',
						'Customer.bad_debt_note',
						'Customer.invoice_pointer',
						'Customer.carrier_pointer',
						'Customer.transaction_pointer',
						'Customer.billing_pointer'
					),
					'conditions' => array('Customer.id' => $next[0][$this->sortTable]['record_number']),
					'contain' => array('CustomerBilling')
				));
	
				//as long as we have one, test all the other requirements to see if we should process them,
				//as well as gather any additional data from other models
				if ($customer !== false)
				{
					//skip customers with no transactions, invoices or carriers
					if ($customer['Customer']['invoice_pointer'] == 0 || $customer['Customer']['carrier_pointer'] == 0 || $customer['Customer']['transaction_pointer'] == 0)
					{
						continue;
					}
					
					//make sure the profit center matches if we have one
					if (isset($parameters['Customer']['profit_center_number']) && $parameters['Customer']['profit_center_number'] != $customer['Customer']['profit_center_number'])
					{
						continue;
					}
						
					//make sure the account balance meets the minimum if we have one
					if (isset($parameters['Transaction']['account_balance']))
					{
						//otherwise grab the balance from the head of the transaction chain and compare it
						$balance = $this->Transaction->field('account_balance', array('id' => $customer['Customer']['transaction_pointer']));
						
						//if the balance doesn't hit the (non-inclusive) minimum, skip this customer
						if ($balance <= $parameters['Transaction']['account_balance'])
						{
							continue;
						}
					}
					
					//make sure our carrier conditions match if we have any
					$conditions = array('CustomerCarrier.account_number' => $customer['Customer']['account_number']);
					
					foreach (array('CustomerCarrier.carrier_number', 'Carrier.statement_type', 'Carrier.group_code', 'Carrier.network') as $field)
					{
						list($model, $name) = explode('.', $field);
						
						if (isset($parameters[$model][$name]))
						{
							$conditions[$field] = $parameters[$model][$name];
						}
					}
					
					$customer['CustomerCarrier'] = $this->CustomerCarrier->find('all', array(
						'fields' => array(
							'CustomerCarrier.carrier_number', 
							'CustomerCarrier.carrier_name', 
							'Carrier.statement_type'
						),
						'conditions' => $conditions,
						'contain' => array('Carrier')
					));
					
					//if we didn't get any carriers back because of specific conditions (other than our account number filter)
					//we have to skip this customer
					if (empty($customer['CustomerCarrier']) && count($conditions) > 1)
					{
						continue;
					}
					
					//find matching invoices
					
					//determine how to filter the aging date (we have to figure out what field to use, as well
					//as apply a requirement that the invoice must be so many days old
					$date = strtotime($parameters['Invoice']['aging_date']);
					$days = 0;
					
					if (isset($parameters['Invoice']['minimum_days_old']))
					{
						$days = $parameters['Invoice']['minimum_days_old'];
					}
					
					$conditions = array(
						'account_number' => $customer['Customer']['account_number'],
						"{$this->invoiceDateField} <=" => databaseDate("-{$days} days", $date)
					);
					
					if (!$parameters['Option']['print_with_billing_date_blank'])
					{
						$conditions['and'] = array("{$this->invoiceDateField} <>" => '');
					}
	
					$customer['Invoice'] = $this->Invoice->find('all', array(
						'fields' => array(
							'invoice_number', 
							'date_of_service', 
							'billing_date', 
							'carrier_1_code', 
							'carrier_1_balance', 
							'carrier_2_code', 
							'carrier_2_balance', 
							'carrier_3_code', 
							'carrier_3_balance'
						),
						'conditions' => $conditions,
						'contain' => array()
					));
					
					$found = true;
				}
			}
			
			return $customer;
		}
		
		/**
		 * Determines the type of invoice (i.e. whether it's current or how old it is).
		 * @param numeric $balance The balance on the invoice.
		 * @param string $agingDate The aging date.
		 * @param string $invoiceDate The invoice date.
		 * @param bool $showCreditInvoices States whether or not we're showing credit invoices.
		 * @param bool $printWithCarrierNumber States whether or not we're printing with carrier numbers.
		 * @return string The type of invoice.
		 */
		function determineInvoiceType($balance, $agingDate, $invoiceDate, $showCreditInvoices, $printWithCarrierNumber)
		{
			$agingDate = strtotime($agingDate);
			$invoiceDate = strtotime($invoiceDate);
			$difference = $invoiceDate == null ? 0 : (($agingDate - $invoiceDate) / 60 / 60 / 24);
		
			if ($balance < 0 && $showCreditInvoices)
			{
				return 'Credit';
			}
			else if ($showCreditInvoices && $difference <= 60)
			{
				return 'Current';
			}
			else if ($balance < 0 || $difference <= 30)
			{
				return 'Current';
			}
			else if ($difference > 30 && $difference <= 60)
			{
				return '30 days';
			}
			else if ($difference > 60 && $difference <= 90)
			{
				return '60 days';
			}
			else if ($printWithCarrierNumber && $difference > 90 && $difference <= 120)
			{
				return '90 days';
			}
			else if ($printWithCarrierNumber && $difference > 120 && $difference <= 150)
			{
				return '120 days';
			}
			else if ($printWithCarrierNumber)
			{
				return '150 days';
			}
			else
			{
				return '90 days';
			}
		}
		
		/**
		 * Starts the PDF of the report - sets the orientation, renders the report header, parameters, etc.
		 * @param object $document The PDF document being rendered.
		 * @param array $parameters The parameters the shell was invoked with.
		 */
		function initializePdf($document, $parameters)
		{
			$document->setPageOrientation('landscape');

			//build the page headers
			$document->setTopLeftHeaderString('A/R Aging Report');
			$document->setTopRightHeaderString(date('m/d/Y - h:i:s A'));
			$document->AddPage();
			
			//build the report header (basically just list the chosen parameters)
			$document->SetY(10);
			$document->SetFontSize(14);
			
			$this->Pdf->row(
				$document,
				array('Parameters'),
				array(array_sum($this->pdfSettings['widths']))
			);
	
			$document->SetFontSize(9);
			$parameterWidths = array(50, 25, 50, 25, 50, 25);
			
			$this->Pdf->row(
				$document, 
				array(
					'Aging Date:', formatDate(isset($parameters['Invoice']['aging_date']) ? $parameters['Invoice']['aging_date'] : ''), 
					'Profit Center:', isset($parameters['Customer']['profit_center_number']) ? $parameters['Customer']['profit_center_number'] : ''
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'Sort By:', $parameters['Option']['sort_by_account_number'] == 0 ? 'Name' : 'Account Number', 
					'Carrier Number:', isset($parameters['CustomerCarrier']['carrier_number']) ? $parameters['CustomerCarrier']['carrier_number'] : '',
					'Account Balances Greater Than:', isset($parameters['Transaction']['account_balance']) ? $parameters['Transaction']['account_balance'] : ''
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'From:', isset($parameters['Customer']['start_range']) ? $parameters['Customer']['start_range'] : '', 
					'Statement Type:', isset($parameters['Carrier']['statement_type']) ? $parameters['Carrier']['statement_type'] : '',
					'Minimum Days Old:', isset($parameters['Invoice']['minimum_days_old']) ? $parameters['Invoice']['minimum_days_old'] : ''
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'To:', isset($parameters['Customer']['end_range']) ? $parameters['Customer']['end_range'] : '',
					'Carrier Grouping Code:', isset($parameters['Carrier']['group_code']) ? $parameters['Carrier']['group_code'] : '',
					'Network:', isset($parameters['Carrier']['network']) ? $parameters['Carrier']['network'] : ''
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'Break Down By Invoice:', $parameters['Option']['break_down_by_invoice'] == 0 ? 'N' : 'Y',
					'Print With Carrier Number:', $parameters['Option']['print_with_carrier_number'] == 0 ? 'N' : 'Y',
					'Print Customer Memo:', $parameters['Option']['print_customer_memo'] == 0 ? 'N' : 'Y',
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'Print With Billing Date Blank:', $parameters['Option']['print_with_billing_date_blank'] == 0 ? 'N' : 'Y',
					'Age From Service Date:', $parameters['Option']['age_from_service_date'] == 0 ? 'N' : 'Y',
					'Print With Credit Invoices Displayed:', $parameters['Option']['print_with_credit_invoices_displayed'] == 0 ? 'N' : 'Y'
				), 
				$parameterWidths
			);
			
			$this->Pdf->row(
				$document, 
				array(
					'Print Summary Page Only:', $parameters['Option']['print_summary_page_only'] == 0 ? 'N' : 'Y'
				), 
				$parameterWidths
			);
			
			$this->Pdf->row($document, array(array('', array('border' => 'B'))), array_sum($parameterWidths));
			$document->Ln(5);
			
			//render the table header
			$this->renderTableHeader($this->Pdf, $document);
		}
		
		/**
		 * Renders the table headers used in the report. This is typically printed at the top of each page.
		 * @param object $manager The PdfComponent doing the rendering. This is required because we also use this
		 * as a callback mechanism for the PdfComponent->row method (see that for details).
		 * @param object $document The PDF document being rendered.
		 */
		function renderTableHeader($manager, $document)
		{
			$this->Pdf->row($document, $this->pdfSettings['headers'], $this->pdfSettings['widths'], array('style' => 'B', 'border' => 'B'));
		}
		
		/**
		 * Processes and prints information about the customer to the report.
		 * @param object $document The PDF document being rendered.
		 * @param array $customer The customer to process.
		 * @param array $parameters The parameters the shell was invoked with.
		 */
		function processCustomer($document, $customer, $parameters)
		{
			$printedHeader1 = false;
			$printedHeader2 = false;
			$printedMemo = $parameters['Option']['print_customer_memo'] ? false : true;
			$customerHadInvoices = false;
			
			//go through each carrier for the customer
			foreach ($customer['CustomerCarrier'] as $i => $carrier)
			{
				$carrierHadInvoices = false;
				$printedCarrierNumber = false;
				
				//reset carrier type totals
				$carrierTypeTotals = $this->pdfSettings['types'];
				
				//go through each invoice for the customer trying to pair them up with the current carrier
				foreach ($customer['Invoice'] as $j => $invoice)
				{
					$balance = null;
					$rowStyle = array();

					//figure out if this carrier played a part in this invoice
					switch ($carrier['CustomerCarrier']['carrier_number'])
					{
						case $invoice['Invoice']['carrier_1_code']:
							$balance = $invoice['Invoice']['carrier_1_balance'];
							break;
						case $invoice['Invoice']['carrier_2_code']:
							$balance = $invoice['Invoice']['carrier_2_balance'];
							break;
						case $invoice['Invoice']['carrier_3_code']:
							$balance = $invoice['Invoice']['carrier_3_balance'];
							break;
					}
					
					//if they didn't or if the balance is zero, we don't care about it
					if ($balance === null || (int)$balance === 0)
					{
						continue;
					}
					
					$customerHadInvoices = true;
					$carrierHadInvoices = true;
					
					//figure out what type the invoice falls under (current, 30 days, etc.)
					$type = $this->determineInvoiceType(
						$balance, 
						$parameters['Invoice']['aging_date'],
						$invoice['Invoice'][$this->invoiceDateField],
						$parameters['Option']['print_with_credit_invoices_displayed'], 
						$parameters['Option']['print_with_carrier_number']
					);

					//update our subtotal and totals by type						
					$carrierTypeTotals[$type] += $balance;
					$this->pdfSettings['typeTotals'][$type] += $balance;
										
					//update our recap totals
					$statementType = isset($carrier['Carrier']['statement_type']) ? $carrier['Carrier']['statement_type'] : '';
					
					if (!array_key_exists($statementType, $this->pdfSettings['recapTotals']))
					{
						$this->pdfSettings['recapTotals'][$statementType] = $this->pdfSettings['types'];
					}
					
					$this->pdfSettings['recapTotals'][$statementType][$type] += $balance;
					
					//print the invoice if we are supposed to
					if (!$parameters['Option']['print_summary_page_only'] && $parameters['Option']['break_down_by_invoice'])
					{
						$columns = array('', '');
						$rowWidths = $this->pdfSettings['widths'];
						
						//print headers if we need to
						if (!$printedHeader1)
						{
							//header 1 is the customer number and name
							$columns = array(
								"{$customer['Customer']['account_number']}",
								$customer['Customer']['name']
							);
							
							$rowStyle = array('border' => 'T');
							$printedHeader1 = true;
						}
						else if (!$printedHeader2)
						{
							//header 2 always follows header 1 and consists of the profit number and phone number
							$columns = array(
								$customer['Customer']['profit_center_number'],
								$customer['Customer']['phone_number']
							);

							$printedHeader2 = true;
						}
						else if (!$printedMemo)
						{
							//the memo is always printed on the 3rd line 
							$columns = array(
								array($customer['Customer']['bad_debt_note'], array('colspan' => 2))
							);
							
							$rowWidths = array_merge(array($rowWidths[0] + $rowWidths[1]), array_slice($rowWidths, 2));
							$printedMemo = true;
						}
						
						//print the carrier number only once per carrier
						if (!$printedCarrierNumber)
						{
							$columns[] = h($parameters['Option']['print_with_carrier_number'] ? $carrier['CustomerCarrier']['carrier_number'] : $carrier['CustomerCarrier']['carrier_name']);
							$printedCarrierNumber = true;
						}
						else
						{
							$columns[] = '';
						}

						//now the easy columns
						$difference = $invoice['Invoice'][$this->invoiceDateField] == null ? 0 : ((strtotime($parameters['Invoice']['aging_date']) - strtotime($invoice['Invoice'][$this->invoiceDateField])) / 60 / 60 / 24);
						$columns[] = "{$invoice['Invoice']['invoice_number']} (" . number_format($difference) . ")";
						$columns[] = formatDate($invoice['Invoice']['billing_date']);
						$columns[] = formatDate($invoice['Invoice']['date_of_service']);
						
						//build the columns that will contain the balance
						foreach (array_keys($this->pdfSettings['types']) as $key)
						{
							$columns[] = array($key == $type ? money_format('%.2n', $balance) : '', array('align' => 'R'));
						}
						
						//empty totals column
						$columns[] = '';
						
						//render the row
						$this->Pdf->row(
							$document, 
							$columns, 
							$rowWidths, 
							array_merge(
								$rowStyle,
								$this->pdfSettings['isAlt'] ? array('fillColor' => $this->pdfSettings['altColor']) : array()
							),
							array($this, 'renderTableHeader')
						);
						
						$this->pdfSettings['isAlt'] ^= true;
					}
				}
				
				//after printing all the invoices...
				if (!$parameters['Option']['print_summary_page_only'])
				{		
					//if we never had any non-zero invoices for the carrier, we aren't going to print a total
					if (!$carrierHadInvoices)
					{
						continue;
					}
					
					$rowWidths = $this->pdfSettings['widths'];
					$rowStyle = array();
					$columns = array('', '');
					
					//after we've went through the invoices, we need to make sure to print any headers 
					//that weren't printed
					if (!$printedHeader1)
					{
						$columns = array(
							"{$customer['Customer']['account_number']}",
							$customer['Customer']['name']
						);

						$rowStyle = array('border' => 'T');
						$printedHeader1 = true;
					}
					else if (!$printedHeader2)
					{
						$columns = array(
							$customer['Customer']['profit_center_number'],
							$customer['Customer']['phone_number']
						);
						
						$printedHeader2 = true;
					}
					else if (!$printedMemo)
					{
						$columns = array(
							$customer['Customer']['bad_debt_note']
						);
						
						$rowWidths = array_merge(array($rowWidths[0] + $rowWidths[1]), array_slice($rowWidths, 2));
						
						$printedMemo = true;
					}
					
					//print the carrier number if we haven't yet
					if (!$printedCarrierNumber)
					{
						$columns[] = $parameters['Option']['print_with_carrier_number'] ? $carrier['CustomerCarrier']['carrier_number'] : $carrier['CustomerCarrier']['carrier_name'];
						$printedCarrierNumber = true;
					}
					else
					{
						$columns[] = '';
					}
					
					//blanks for invoice
					$columns[] = '';
					$columns[] = $parameters['Option']['break_down_by_invoice'] ? array('Total', $this->pdfSettings['invoiceTotalStyle']) : '';
					$columns[] = $parameters['Option']['break_down_by_invoice'] ? array('', $this->pdfSettings['invoiceTotalStyle']) : '';
					
					//carrier totals
					foreach ($carrierTypeTotals as $total)
					{
						$columns[] = array($total != 0 ? money_format('%.2n', $total) : '', array_merge(array('align' => 'R'), $parameters['Option']['break_down_by_invoice'] ? $this->pdfSettings['invoiceTotalStyle'] : array()));
					}
					
					//summed total
					$columns[] = array(money_format('%.2n', array_sum($carrierTypeTotals)), array_merge(array('align' => 'R'), $parameters['Option']['break_down_by_invoice'] ? $this->pdfSettings['invoiceTotalStyle'] : array()));

					$this->Pdf->row(
						$document, 
						$columns, 
						$rowWidths, 
						array_merge(
							$rowStyle,
							$this->pdfSettings['isAlt'] ? array('fillColor' => $this->pdfSettings['altColor']) : array()
						),
						array($this, 'renderTableHeader')
					);
					
					$this->pdfSettings['isAlt'] ^= true;
				}
			}
			
			if ($customerHadInvoices)
			{
				$this->pdfSettings['customersPrinted']++;
				
				//finish up any headers if we're not just doing a summary page
				if (!$parameters['Option']['print_summary_page_only'])
				{
					//print the second line if we still have to
					if (!$printedHeader2)
					{
						$this->Pdf->row(
							$document, 
							array(
								$customer['Customer']['profit_center_number'],
								$customer['Customer']['phone_number']
							), 
							array($this->pdfSettings['widths'][0], array_sum(array_slice($this->pdfSettings['widths'], 1))),
							$this->pdfSettings['isAlt'] ? array('fillColor' => $this->pdfSettings['altColor']) : array(),
							array($this, 'renderTableHeader')
						);
						
						$this->pdfSettings['isAlt'] ^= true;
						$printedHeader2 = true;
					}
					
					//print the memo if we still have to
					if (!$printedMemo && $customer['Customer']['bad_debt_note'] != '')
					{							
						$this->Pdf->row(
							$document, 
							array(
								$customer['Customer']['bad_debt_note']
							), 
							array(array_sum($this->pdfSettings['widths'])),
							$this->pdfSettings['isAlt'] ? array('fillColor' => $this->pdfSettings['altColor']) : array(),
							array($this, 'renderTableHeader')
						);
						
						$this->pdfSettings['isAlt'] ^= true;
						$printedMemo = true;
					}
				}
			}
		}
		
		/**
		 * Creates all of the settings used by the PDF as it is being generated. Things like column widths,
		 * row styles, running totals, etc.
		 * @param array $parameters The parameters the shell was invoked with.
		 * @return array The PDF settings.
		 */
		function createPdfSettings($parameters)
		{
			$settings = array(
				'widths' => $parameters['Option']['print_with_carrier_number']
					? array(30, 45, 20, 25, 20, 20, 15, 15, 15, 15, 15, 15, 15)
					: array(30, 45, 45, 25, 20, 20, 16, 16, 16, 16, 16),
				'headers' => array_merge(
					array('Account #', 'Name', 'Carrier', 'Invoice (age)', 'Bill Date', 'Service Date'), 
					$parameters['Option']['print_with_credit_invoices_displayed'] 
						? array(array('Credit', array('align' => 'R')), array('Current', array('align' => 'R')))
						: array(array('Current', array('align' => 'R')), array('Over 30', array('align' => 'R'))),
					array(array('Over 60', array('align' => 'R')), array('Over 90', array('align' => 'R'))),
					$parameters['Option']['print_with_carrier_number'] 
						? array(array('Over 120', array('align' => 'R')), array('Over 150', array('align' => 'R')))
						: array(),
					array(array('Total', array('align' => 'R')))
				),
				'types' => array_merge(
					$parameters['Option']['print_with_credit_invoices_displayed'] 
						? array('Credit' => 0, 'Current' => 0, '60 days' => 0, '90 days' => 0) 
						: array('Current' => 0, '30 days' => 0, '60 days' => 0, '90 days' => 0),
					$parameters['Option']['print_with_carrier_number']
						? array('120 days' => 0, '150 days' => 0)
						: array()
				),
				'recapTotals' => array(),
				'customersPrinted' => 0,
				'isAlt' => false,
				'altColor' => array(225, 223, 211),
				'grandTotalStyle' => array('fillColor' => array(51, 51, 51), 'color' => $this->Pdf->colors['white']),
				'invoiceTotalStyle' => array('style' => 'B', 'fillColor' => array(119, 119, 119), 'color' => $this->Pdf->colors['white'])
			);
	
			//these need initialized after the fact because they are copies of the same array structure as "types"
			$settings['typeTotals'] = $settings['types'];
			
			return $settings;
		}
		
		/**
		 * Renders all of the totals to the PDF.
		 * @param object $document The PDF document being rendered.
		 */
		function renderTotals($document)
		{
			//gross totals
			$columns = array('', '', '', '', 'Gross Totals');
			$totalWidths = array_merge(array_slice($this->pdfSettings['widths'], 0, 4), array(array_sum(array_slice($this->pdfSettings['widths'], 4, 2))), array_slice($this->pdfSettings['widths'], 6));
	
			foreach ($this->pdfSettings['typeTotals'] as $total)
			{
				$columns[] = array(money_format('%.2n', $total), array('align' => 'R'));
			}
			
			$columns[] = array(money_format('%.2n', array_sum($this->pdfSettings['typeTotals'])), array('align' => 'R'));
	
			$this->Pdf->row($document, $columns, $totalWidths, $this->pdfSettings['grandTotalStyle'], array($this, 'renderTableHeader'));
			
			$document->AddPage();
			$document->SetFontSize(14);
			$this->Pdf->row($document, array('Recap'), array(array_sum($this->pdfSettings['widths'])));
			$document->SetFontSize(9);
			$document->Ln(5);
			
			//recap table		
			$recapHeaders = array_merge(array('Statement Type'), array_slice($this->pdfSettings['headers'], 6));
			$recapWidths = array_merge(array(array_sum(array_slice($this->pdfSettings['widths'], 0, 6))), array_slice($this->pdfSettings['widths'], 6));
			
			//render the recap headers
			$this->Pdf->row($document, $recapHeaders, $recapWidths, array('style' => 'B', 'border' => 'B'));
			$this->pdfSettings['isAlt'] = false;
				
			foreach ($this->pdfSettings['recapTotals'] as $statementType => $totals)
			{
				$columns = array();
				$columns[] = $statementType;
				
				foreach ($totals as $total)
				{
					$columns[] = array(money_format('%.2n', $total), array('align' => 'R'));
				}
				
				$columns[] = array(money_format('%.2n', array_sum($totals)), array('align' => 'R'));
				
				$this->Pdf->row($document, $columns, $recapWidths, $this->pdfSettings['isAlt'] ? array('fillColor' => $this->pdfSettings['altColor']) : array());
				$this->pdfSettings['isAlt'] ^= true;
				
			}
			
			$this->Pdf->row(
				$document, 
				array('End of Report: ' . number_format($this->pdfSettings['customersPrinted']) . ' entries printed.'), 
				array(array_sum($recapWidths)),
				$this->pdfSettings['grandTotalStyle']
			);
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>