<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Shell that implements the old U05 Rental Invoices (MU05FT) program.
	 */
	class BatchInvoicingShell extends Shell 
	{
		var $uses = array(
			'Customer',
			'CustomerCarrier',
			'TransactionQueue',
			'Rental',
			'BillingQueue',
			'Document',
			'Image',
			'Setting',
			'HealthcareProcedureCode',
			'ProfitCenter',
			'Invoice',
			'Inventory',
			'Process',
			'NextFreeNumber',
			'Lookup'
		);
		
		var $tasks = array('ReportParameters', 'Logging', 'Impersonate');
		
		/** This will store loaded settings from the database. */
		var $settings = array();
		
		/** This will hold our process ID that will be used to report our progress as we invoice. */
		var $processID;
		
		/** This is the absolute physical path to the root of where the shell will generate its invoices. */
		var $outputPath;
				
		/** These are running totals for the entire batch. */
		var $batchTotalGross = 0;
		var $batchTotalNet = 0;
		
		var $parameters = array(
			array(
				'type' 			=> 'date',
				'model' 		=> 'Virtual',
				'field' 		=> 'begin_date',
				'flag' 			=> 'begin',
				'description'	=> 'The begin date.',
				'required' 		=> true
			),
			array(
				'type' 			=> 'date',
				'model' 		=> 'Virtual',
				'field' 		=> 'end_date',
				'flag' 			=> 'end',
				'description'	=> 'The end date.',
				'required'		=> true
			),
			array(
				'type'			=> 'string',
				'model'			=> 'Virtual',
				'field'			=> 'username',
				'flag'			=> 'username',
				'description'	=> 'The user the process should run as.',
				'required'		=> true
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Customer',
				'field' 		=> 'account_number',
				'flag' 			=> 'account',
				'description'	=> 'The account number to create invoices for.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Customer',
				'field' 		=> 'profit_center_number',
				'flag' 			=> 'pc',
				'description'	=> 'The profit center to filter accounts by.'
			),
			array(
				'type' 			=> 'flag',
				'model' 		=> 'Virtual',
				'field'			=> 'maintenance_invoices_only',
				'flag'			=> 'maint',
				'description'	=> 'Specifies whether or not to only do maintenance invoices.'
			),
			array(
				'type'			=> 'string',
				'model'			=> 'Virtual',
				'field'			=> 'printer',
				'flag'			=> 'printer',
				'description'	=> 'The name of the printer to use. Omit to use the default printer.'
			),
			array(
				'type' 			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'should_suppress_printing',
				'flag'			=> 'noprint',
				'description'	=> 'Specifies whether or not to print the invoices.'
			),
			array(
				'type'			=> 'flag',
				'model'			=> 'Virtual',
				'field'			=> 'test_run_only',
				'flag'			=> 'test',
				'description'	=> 'Use this flag to specify that no records will actually be updated during the process. It is effectively a test run so you can keep running the same options over and over. It WILL however still use up TCN numbers, invoice numbers, print invoices, and create records in DocPop.'
			)
		);
		
		/**
		 * This shell is a port of the rental invoices CU05DG.TXT and MU05FT.TXT U05 programs.
		 * FU05 Logic:
		 *
		 * CU05DG.TXT - this is the menu screen from which the user could see a report of the previous invoicing or 
		 * run the invoicing again based on some parameters.
		 *
		 *
		 * MU05FT.TXT - this is the actual rental invoices program.
		 * 		* maxday$ = 31
		 * 		* maxdates% = 31
		 * 		* invln = 8 (i think this is the number of lines on a page of an invoice)
		 * 		* open and read work file that was generated from CU05DG.TXT (WU05ED.logname)
		 * 			* su63 contains the work file fields
		 * 				* 1 = default file printer condense on characters
		 * 				* 2 = default file printer condense off characters
		 * 				* 3 = B or F - generate in (B)ackground or (F)oreground
		 * 				* 4 = begin date
		 * 				* 5 = end date
		 * 				* 6 = begin invoice number
		 * 				* 7 = profit center
		 * 				* 8 = tax GL code
		 * 				* 9 = credit GL code
		 * 				* 10 = message 1
		 * 				* 11 = message 2
		 * 				* 12 = message 3
		 * 				* 13 = include serial number (Y/N)
		 * 				* 14 = sort by account (Y/N default name)
		 * 				* 15 = beginning sort range
		 * 				* 16 = ending sort range
		 * 				* 17 = include zero balance invoices (Y/N)
		 * 				* 18 = maintenance invoices only (Y/N)
		 * 				* 19 = account number (or ALL)
		 * 				* 20 = Generate for Laser (Y/N)
		 * 		* open profit center file (AX) and read them all into arrays of pc fields
		 * 			* NOTE - looks like the tax rate is a packed number. We're going to have to see Tom on how to unpack
		 * 		* open customer core (BL - 1, BM - 2, BN - 3, BP - 4 (not used))
		 * 		* open customer carrier (BR - 5)
		 * 		* open rental EQ (BY - 6)
		 * 		* open the transaction queue (BW - 7)
		 * 		* open PC inventory (BK - 8)
		 * 		* open customer billing (BQ - 9)
		 * 		* open transaction (BU - 10)
		 * 		* open 1500 queue (BX - 11)
		 * 		* open inventory (AP - 12, AQ - 13, AR - 14 (not used), AS - 16)
		 * 		* open print file (PU05DC.logname - 29)
		 * 		* load the day$ array with the number of days in each month of the ending date's year indexed by month.
		 * 		* calculate all possible dates:
		 * 			* bd$ = the starting date
		 * 			* ed$ = the ending date
		 * 			* c% = running date count (init to zero)
		 * 			* if the number of days in the ending date's month is less than maxday$ and the number of days in 
		 * 			  the ending date's month is less than or equal to the specified day in ending date, then adjust 
		 * 			  the ending date's day to be maxday$. (basically just ensure the day is valid for the specified month)
		 * 			* dd$ = bd$
		 * 			* while (dd$ < ed$ and c% < maxdates%)
		 * 				* increment the date count (c%)
		 * 				* store dd$ in the d$ array (it's a 31 element array) indexed by the current date count
		 * 				* increment dd$ by one day
		 * 		* for each account (ordered by name)
		 * 			* if it's not within the sort range, skip it
		 * 			* if only doing a single account number and this isn't it, skip it
		 * 			* if only doing a specific profit center and this account doesn't match it, skip it
		 * 			* if the billing pointer, carrier pointer, or rental equipment pointer is zero, skip it
		 * 			* look up the tax rate for the account (comes from the profit center). If it's not found, tax is zero.
		 * 			* NOTE: RERUN WOULD START HERE IN THE MIDDLE OF THE CHAIN, RESETTING COUNTS PER DAY
		 *			* for each record in the account's rental equipment pointer chain
		 * 				if the equipment hasn't been returned yet (blank returned_date field)
		 * 					if the last_invoiced_date is blank, set it to the setup_date
		 *					set a temp date (su45$) = last_invoiced_date + 1 month (NOTE: Always go to the next month ONLY (so from 1/31, adding one month would give you 2/28))
		 * 					if rental_day is blank, set rental day equal to setup_date's day
		 * 					if the temp date's day != rental_day then
		 * 						grab the temp date's month, and if the rental_day is <= the number of days in the temp date's month then 
		 * 							adjust the temp date's day to be the rental_day
		 * 						else
		 *							adjust the temp date's day to be the last day of the temp date's month
		 * 					if temp date < begin date or temp date > end date, then next chain record
		 * 					if returned_date is not blank and returned_date <= (temp date + 1 month) then next chain record
		 * 					for each date in our date count
		 * 						if date(i) = our temp date 
		 * 							if date(i)'s day = rental_day then
		 * 								processRental(i)
		 * 							else if rental_day > days in temp date's month
		 * 								WTF? - update date array for the current rental_day (d$(rental_day)) and set the date = our temp date
		 *								processRental(i)
		 * 			* once we hit the end of the chain...
		 * 			* for each day processed
		 * 				* if the number of entries for the day > 0 then
		 * 					* invoke addcrossref -a <account_number> -f 1096 -n <next_free_invoice_number> 
		 * 					* the return of addcrossref is the cross reference number that was generated for the document inserted into DocPop
		 * 					* generate a barcode from the cross ref
		 * 					* generate PDF invoice 
		 * 						* header - see lines 509-585
		 * 						* lines - see lines 589-732
		 * 							* notes:
		 * 								* if the inventory item can't be found or the quantity is < 1 then errors are written to a log and the item is skipped
		 * 								* when doing maintenance invoices, maintenance_date is updated by the maintenance_frequency (months).
		 * 								* depending on the 6_point_classification of the item, net amounts may need reduced by 25%. The net amounts in the rental itself are adjusted.
		 * 								* if we aren't doing maintenance invoices and the maintenance_date is filled out, use zero for gross amounts
		 * 						* su63 messages
		 * 						* active carrier balances
		 * 						* totals
		 * 						* barcode
		 * 						* footer
		 * 					* insert transaction queue records for the rental records on the invoice
		 * 					* update rental records that were invoiced
		 * 					* insert 1500 queue records for the rental records on the invoice
		 * 					* there is a comment that talks about how zero balance invoices may not be printed depending on the options ("include zero balance invoices")
		 * 					  and how docpop would already have the document record that should technically be deleted (but currently is not). We could probably do this going forward.
		 * 			* if we need to re-run more rentals for the customer, start back over at the rental record in the chain that we marked (see NOTE up above where the loop would start over)
		 *
		 * processRental(i) [lines 390 to 436 in MU05FT.TXT]
		 * {
		 * 		if ((doing maintenance invoices only and maintenance_date <> '' and maintenance_date between start and end date)
		 * 			or (not doing maintenance invoices only and maintenance_date == '' or maintenance_date not between start and end date))
		 * 		{
		 * 			if we're not re-running the invoice then
		 * 				store the current rental record number in rirerun variable
		 * 			if runningCount(i) + 1 > invln then (running count is an array indexed by day number between start and end date)
		 * 				mark that we're going to start re-running, and then go to the next chain record (we'd have to tell the calling function that we want to re-run)
		 * 				also note at this point, since we've been storing the record number in rirerun, we'll be able to come back to this record later)
		 * 			runningCount(i)++
		 * 			dt(i, runningCount(i)) = current rental record number (basically we're storing, for each day and then for each line within the day, what rental record we're using)
		 * 			rday(i) = rental_day (per day we store the rental_day? I don't get it...)
		 *			if rental quantity < 1 then next chain record
		 * 			rentDates(i, runningCount(i)) = our temp date (basically we're storing, for each day and then for each line within the day, the date being processed)
		 * 			monthsRented(i, runningCount(i)) = number_of_rental_months + 1 (basically we're storing, for each day and then for each line within the day, the number of months rented)
		 * 			rentDays(i, runningCount(i)) = rental_day (basically we're storing, for each day and then for each line within the day, the day of the rental)
		 * 		}
		 * }						
		 */
		function main()
		{
			$parameters = $this->ReportParameters->parse($this->parameters);
			$cancelled = false;
			
			//running totals for our processing
			$customersProcessed = 0;
			$percentComplete = 0;
			$customerCount = 0;
			
			//these are the percent of the total process that we use as guidelines for our percent of work finished
			$generatePercentage = 80;
			$combinePercentage = 10;
			$printPercentage = 10;
			
			//we need to maintain the log to store in the process at the end
			$this->Logging->maintainBuffer();
			
			//impersonate the user
			$this->Impersonate->impersonate($parameters['Virtual']['username']);
			
			//start a process
			$this->processID = $this->Process->createProcess('Batch Invoicing', true);
			
			//output all the parameters we're using for this run
			$this->Logging->write('Parameters:');
			
			foreach (Set::flatten($parameters) as $parameter => $value)
			{
				$this->Logging->write($parameter . ' => ' . $value);
			}
			
			$this->Logging->write('');
			$this->Logging->write('Starting process.');
			
			//load the default file
			$this->Logging->write('Loading default file.');

			App::import('Component', 'DefaultFile');
			$this->DefaultFile = new DefaultFileComponent();
			
			if (!$this->DefaultFile->load())
			{
				$this->Logging->write('Failed to load default file. Quitting.');
				$this->Process->updateProcess($this->processID, 0, 'Could not open default file.');
				$this->Process->interruptProcess($this->processID);	
				$this->Process->finishProcess($this->processID, $this->Logging->getBufferedOutput());
				return;
			}
			
			//load up the PDF component
			App::import('Component', 'Pdf');
			$this->Pdf = New PdfComponent();
			
			//load settings
			$this->Logging->write('Loading settings.');
			
			$this->settings = $this->Setting->get(array(
				'batch_invoicing_output_path', 
				'charge_transaction_type_id', 
				'billing_office_phone_number', 
				'billing_office_toll_free_phone_number',
				'company_name',
				'company_address_1',
				'company_city',
				'company_state',
				'company_zip',
				'docpop_batch_invoicing_queue_id',
				'batch_invoicing_advertisement_image'
			));
			
			//build our output path
			$this->outputPath = $this->settings['batch_invoicing_output_path'] . DS . $this->processID;
			
			//build our query
			$query = $this->buildQuery($parameters);
			
			//figure out how many customers we have to process
			$customerCount = isset($parameters['Customer']['account_number']) ? 1 : $this->getCustomerCount();
					
			//we're going to loop through each customer until we're done
			while (($customer = $this->Customer->find('first', $query)) !== false)
			{
				//check if we should interrupt
				if ($this->Process->isProcessInterrupted($this->processID))
				{
					$cancelled = true;
					$this->Process->updateProcess($this->processID, $percentComplete, 'Cancelling. Invoices generated so far will still be printed.');
					$this->Logging->write('Cancel initiated.');
					break;
				}
				
				//update the process
				$percentComplete = $generatePercentage * ($customersProcessed / $customerCount);
				$this->Process->updateProcess($this->processID, $percentComplete, "Processing {$customer['Customer']['account_number']}");
				$this->Logging->write("Processing {$customer['Customer']['account_number']}.");
				$this->Logging->increaseIndent();
				
				//look up the profit center
				$profitCenter = $this->ProfitCenter->find('first', array(
					'conditions' => array('profit_center_number' => $customer['Customer']['profit_center_number']), 
					'contain' => array()
				));
				
				//tax rate in the profit center file is not defined as a number so we hard cast here and also divide
				//by 100 to get it to the proper format (it's listed as 5.25 for example in AX instead of .0525)
				$profitCenter['ProfitCenter']['sales_tax_rate'] = (float)$profitCenter['ProfitCenter']['sales_tax_rate'] / 100;
				
				//look up and sort active carriers
				$activeCarriers = $this->Customer->activeCarriers(
					$customer['Customer']['account_number'], 
					array(
						'CustomerCarrier.carrier_number', 
						'CustomerCarrier.carrier_name', 
						'CustomerCarrier.carrier_group_code',
						'CustomerCarrier.is_active',
						'CustomerCarrier.carrier_type',
						'Carrier.name_to_print_on_statement'
					),
					true
				);
				
				usort($activeCarriers, array($this->CustomerCarrier, 'sortCarriers'));
				
				foreach ($activeCarriers as $i => $carrier)
				{
					$activeCarriers[$i]['Carrier']['statement_type'] = $this->CustomerCarrier->Carrier->field('statement_type', array('carrier_number' => $carrier['carrier_number']));
				}
				
				//this is going to hold all of the rentals we need to invoice, indexed by invoice date.
				$rentals = array();
				
				$this->Logging->write('Examining ' . count($customer['Rental']) . ' rental(s).');
						
				foreach ($customer['Rental'] as $rental)
				{
					//skip zero quantity rentals - we can't put this in the conditions of the query because
					//this field is defined as a string(!) in the map file
					if ((int)$rental['quantity'] <= 0)
					{
						continue;
					}
					
					//cast it to prevent any potential oddities later
					$rental['quantity'] = (int)$rental['quantity'];

					//figure out when (if) they were last invoiced
					$lastInvoiced = $rental['last_invoiced_date'] != null ? $rental['last_invoiced_date'] : $rental['setup_date'];
					
					//figure out when the next invoice would come
					$nextInvoiceDate = databaseDate(addMonth($lastInvoiced));
					$nextInvoiceDay = date('j', strtotime($nextInvoiceDate));
					
					//if the rental day isn't set yet we'll use the day on the setup date
					$rental['rental_day'] = $rental['rental_day'] != null ? $rental['rental_day'] : date('j', strtotime($rental['setup_date']));
					
					//if the rental day doesn't fall on the same day as the next invoice date, we need to adjust the
					//next invoice date to be on either the same day, or if it the day doesn't fit into this month,
					//we need to adjust the invoice date to be the last day of the month
					if ($rental['rental_day'] != $nextInvoiceDay)
					{
						$month = substr($nextInvoiceDate, 5, 2);
						$year = substr($nextInvoiceDate, 0, 4);
						$days = daysInMonth($month, $year);

						//if the rental day can fit within the next invoice's days in a month then adjust the next 
						//invoice date to be for the rental day
						if ($rental['rental_day'] <= $days)
						{
							$nextInvoiceDate = databaseDate("{$month}/{$rental['rental_day']}/{$year}");		
						}
						else
						{
							//otherwise adjust the next invoice's day to be the last day of the month it falls in
							$nextInvoiceDate = databaseDate("{$month}/{$days}/{$year}");
						}			
					}	 				
					
					//skip this rental if the next invoice date isn't within our range
					if ($nextInvoiceDate < $parameters['Virtual']['begin_date'] || $nextInvoiceDate > $parameters['Virtual']['end_date'])
					{
						continue;
					}
					
					//if this is the first rental for this invoice date, we need to initialize an array to
					//hold all rentals for that date
					if (!array_key_exists($nextInvoiceDate, $rentals))
					{
						$rentals[$nextInvoiceDate] = array();
					}
					
					//calculate the period for the rental (used when generating the invoice)
					$rental['period'] = $this->getRentalPeriod($rental['rental_day'], $nextInvoiceDate);
					
					//calculate a sort key for the rental (used when generating the invoice)
					$rental['sort_key'] = str_replace('-', '', databaseDate($rental['setup_date']) . databaseDate($rental['period']['start']) . databaseDate($rental['period']['end']));
					
					//store the rental for later
					$rentals[$nextInvoiceDate][] = $rental;
				}
				
				//now it's time to generate invoices for each day we have rentals
				foreach ($rentals as $invoiceDate => $lines)
				{
					$this->Logging->write('Generating invoice for ' . count($lines) . ' rental(s) on ' . databaseDate($invoiceDate) . '.');
					
					//check if we should interrupt
					if ($this->Process->isProcessInterrupted($this->processID))
					{
						$cancelled = true;
						$this->Process->updateProcess($this->processID, $percentComplete, 'Cancelling. Invoices generated so far will still be printed.');
						$this->Logging->write('Cancelling.');
						break;
					}
					
					$this->generateInvoice($customer, $activeCarriers, $profitCenter, $parameters, $invoiceDate, $lines);
				}
				
				//adjust to grab the next matching customer
				$query['conditions']['Customer.id >'] = $customer['Customer']['id'];
				
				//update our running count
				$customersProcessed = $customer['Customer']['id'];
				$this->Logging->decreaseIndent();
			}
			
			$this->Logging->clearIndent();
			
			//update the percent to the whole of our generate percentage as long as the user hasn't cancelled the process
			if (!$cancelled)
			{
				$percentComplete = $generatePercentage;
			}
			
			$this->Process->updateProcess($this->processID, $percentComplete, 'Merging Invoices');
			$this->Logging->write('Merging invoices.');
			
			//consolidate invoices
			$invoices = $this->combineInvoices();
			$percentComplete += $combinePercentage;
			$invoicesSaved = true;
			
			if ($invoices['printed'] === false && $invoices['zero_balance'] === false)
			{
				$this->Process->updateProcess($this->processID, $percentComplete, 'No invoices to merge');
				$this->Logging->write('No invoices to merge.');
			}
			else
			{			
				//save the zero balance invoices if we have any
				if ($invoices['zero_balance'] !== false)
				{
					$invoicesSaved &= $this->Process->addFile($this->processID, 'Zero Balance Invoices', basename($invoices['zero_balance']), 'application/pdf', file_get_contents($invoices['zero_balance']));
				}
				
				//save and print the invoices that should be printed
				if ($invoices['printed'] !== false)
				{
					//save it to the process
					$invoicesSaved &= $this->Process->addFile($this->processID, 'Printed Invoices', basename($invoices['printed']), 'application/pdf', file_get_contents($invoices['printed']));
					
					//print the invoices if we're supposed to
					if (!$parameters['Virtual']['should_suppress_printing'])
					{
						$this->Process->updateProcess($this->processID, $percentComplete, 'Printing Invoices');
						$this->Logging->write('Printing invoices.');
						system('gs -q -sDEVICE=ljet4 -dBATCH -dNOPAUSE -sOutputFile=- ' . escapeshellarg($invoices['printed']) . ' | lpr' . (isset($parameters['Virtual']['printer']) ? (' -P ' . escapeshellarg($parameters['Virtual']['printer'])) : ''));
					}
				}
			}
			
			//clean up files on disk as long as they were saved ok
			if ($invoicesSaved && is_dir($this->outputPath))
			{
				$folder = new Folder($this->outputPath);
				$folder->delete();
			}
			
			$percentComplete += $printPercentage;
			
			//write batch totals
			$this->Logging->write('');
			$this->Logging->write('Batch Totals:');
			$this->Logging->increaseIndent();
			$this->Logging->write('Gross: ' . str_pad(number_format($this->batchTotalGross, 2), 10, ' ', STR_PAD_LEFT));
			$this->Logging->write('Net:   ' . str_pad(number_format($this->batchTotalNet, 2), 10, ' ', STR_PAD_LEFT));
			$this->Logging->decreaseIndent();
			
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
		 * Builds the initial query used to get a customer.
		 * @param array $parameters The shell parameters.
		 * @return array A query suitable for a find() call.
		 */
		function buildQuery($parameters)
		{
			//craft a query to get rentals for each customer
			$query = array(
				'fields' => array(
					'Customer.account_number', 
					'Customer.name', 
					'Customer.profit_center_number', 
					'Customer.billing_pointer',
					'CustomerBilling.billing_name', 
					'CustomerBilling.address_1', 
					'CustomerBilling.address_2', 
					'CustomerBilling.city', 
					'CustomerBilling.zip_code',
					'CustomerBilling.salesman_number'
				),
				'conditions' => array(
					'Customer.id >' => 0
				),
				'contain' => array('CustomerBilling'),
				'chains' => array(
					'Rental' => array(
						'fields' => array(
							'Rental.inventory_number', 
							'Rental.inventory_description', 
							'Rental.serial_number', 
							'Rental.setup_date', 
							'Rental.quantity',
							'Rental.form_code', 
							'Rental.last_invoiced_date',
							'Rental.setup_date',
							'Rental.rental_day', 
							'Rental.returned_date', 
							'Rental.is_taxable', 
							'Rental.maintenance_date', 
							'Rental.maintenance_frequency', 
							'Rental.number_of_rental_months',
							'Rental.profit_center_number', 
							'Rental.modifier_1',
							'Rental.modifier_2',
							'Rental.modifier_3',
							'Rental.modifier_4',
							'Rental.6_point_classification',
							'Rental.healthcare_procedure_code',
							'Rental.carrier_1_code',
							'Rental.carrier_1_net_amount',
							'Rental.carrier_1_gross_amount',
							'Rental.carrier_2_code',
							'Rental.carrier_2_net_amount',
							'Rental.carrier_2_gross_amount',
							'Rental.carrier_3_code',
							'Rental.carrier_3_net_amount',
							'Rental.carrier_3_gross_amount',
							'Rental.department_code'
						),
						'conditions' => array(
							'returned_date' => null
						),
						'contain' => array()
					)
				)
			);
			
			//we have to filter out different rentals based on if we're doing maintenance invoices or not
			if ($parameters['Virtual']['maintenance_invoices_only'])
			{
				$query['chains']['Rental']['conditions']['maintenance_date <>'] = null;
				$query['chains']['Rental']['conditions']['maintenance_date between'] = array($parameters['Virtual']['begin_date'], $parameters['Virtual']['end_date']);
			}
			else
			{
				$query['chains']['Rental']['conditions']['or'] = array(
					'maintenance_date' => null,
					'maintenance_date <' => $parameters['Virtual']['begin_date'],
					'maintenance_date >' => $parameters['Virtual']['end_date'],
				);
			}
			
			//filter the account if we have one
			if (isset($parameters['Customer']['account_number']))
			{
				$query['conditions']['Customer.account_number'] = $parameters['Customer']['account_number'];
			}
			
			//filter the profit center if we have one
			if (isset($parameters['Customer']['profit_center_number']))
			{
				$query['conditions']['Customer.profit_center_number'] = $parameters['Customer']['profit_center_number'];
			}
			
			return $query;
		}
		
		/**
		 * Creates a PDF invoice.
		 * @param array $customer The customer record of the customer the invoice is for.
		 * @param array $activeCarriers An array of active carriers for the customer.
		 * @param array $profitCenter The profit center for the customer.
		 * @param array $parameters The shell parameters.
		 * @param string $invoiceDate The date to generate the invoice for.
		 * @param array $lines An array of rentals to put on the invoice.
		 * @return mixed The full path to the generated PDF, or false if one was not generated.
		 */
		function generateInvoice(&$customer, &$activeCarriers, &$profitCenter, &$parameters, $invoiceDate, &$lines)
		{
			//generate the invoice & TCN number
			$invoiceNumber = $this->Invoice->nextInvoiceNumber();
			$tcnNumber = $this->NextFreeNumber->next('transaction_control_number');
			
			//we can't generate the invoice without a number
			if ($invoiceNumber === false)
			{
				$this->Logging->write('Cannot generate invoice - could not get next invoice number.');
				return false;
			}

			//generate a document in DocPop for the invoice 					
			$barcode = $this->Document->generate($customer['Customer']['account_number'], $invoiceNumber, $tcnNumber);
			
			//we can't generate the invoice without a barcode
			if ($barcode === false)
			{
				$this->Logging->write('Cannot generate invoice - could not get next barcode number.');
				return false;
			}
			
			//create a transaction template for transaction queue entries that will be generated from the invoice
			$transactionTemplate = $this->createTransactionTemplate($customer, $parameters, $invoiceNumber, $tcnNumber);
			
			//start the PDF document (note - a page is 180 units wide)
			$document = $this->Pdf->create('', '', 'LETTER');
			$document->SetAutoPageBreak(false);
			$document->AddPage();
			
			//barcode
			$document->write1DBarcode($barcode, 'C39', 15, 15, 30, 10, 0.4, array('text' => true));
			
			//logo
			$document->Image(IMAGES . 'millersBatchInvoiceLogo.jpg', '', 15, 50, '', '', '', '', false, 300, 'C');
			
			$document->SetY(15);
			$document->SetFont('freesans', '', 10);

			//right header
			$this->Pdf->row(
				$document,
				array(' ', 'Invoice #', $invoiceNumber),
				array(135, 25, 25)
			);
			
			$this->Pdf->row(
				$document,
				array(' ', 'TCN #', $tcnNumber),
				array(135, 25, 25)
			);			
			
			$this->Pdf->row(
				$document,
				array(' ', 'Account #', $customer['Customer']['account_number']),
				array(135, 25, 25)
			);
			
			$this->Pdf->row(
				$document,
				array(' ', 'Invoice Date', formatDate($invoiceDate)),
				array(135, 25, 25)
			);
			
			//"Mail Payment to:" line
			$document->Ln(12);
			
			$this->Pdf->row(
				$document,
				array('', array('Mail Payment to:', array('style' => 'I'))),
				array(126.7, 53.3)
			);
			
			$document->Ln(2);
			
			//bill to and payment address
			
			//swap "last name, first name" to be "first name last name"
			$billingName = array_map('trim', explode(',', $customer['CustomerBilling']['billing_name'], 2));
			$billingName = count($billingName) == 2 ? ($billingName[1] . ' ' . $billingName[0]) : $billingName[0];
			
			//this +19 is the exact indent we need to get the addresses at the proper location to fit an envelope window
			$document->SetLeftMargin(PDF_MARGIN_LEFT + 19);
			
			$this->Pdf->columns(
				$document,
				$customer['CustomerBilling']['address_2'] != '' ? 
					array(
						array($billingName),
						array($customer['CustomerBilling']['address_1']),
						array($customer['CustomerBilling']['address_2']),
						array("{$customer['CustomerBilling']['city']} {$customer['CustomerBilling']['zip_code']}")
					)
					:
					array(
						array($billingName),
						array($customer['CustomerBilling']['address_1']),
						array("{$customer['CustomerBilling']['city']} {$customer['CustomerBilling']['zip_code']}")
					),
				array(					
					array($this->settings['company_name']),
					array($this->settings['company_address_1']),
					array("{$this->settings['company_city']}, {$this->settings['company_state']} {$this->settings['company_zip']}")
				),
				//again, these odd looking widths are for envelope window placement
				array(40, 45, 22.7, 51.3, 21)
			);
			
			$document->SetLeftMargin(PDF_MARGIN_LEFT);
			
			$document->Ln(3);
	
			//detail page 1 header
			$this->Pdf->columns(
				$document,
				array(
					array("Equipment for {$customer['Customer']['name']}")
				),
				array(
					array(array("Call {$this->settings['billing_office_toll_free_phone_number']} with questions about your account.\nWe can safely process your payment by phone Free of charge.", array('border' => 1, 'align' => 'C')))
				),
				array(40, 30, 10, 50, 50)
			);
			
			//line item background
			$boxTop = $document->GetY();
			$boxHeight = 125;
			$this->renderLineItemBackground($document, $activeCarriers, $boxTop, $boxHeight);
			
			$lineWidths = array(97, 20, 20, 20, 20);
			$subLineWidths = array(97);
			$lineHeight = $document->getFontSize() * K_CELL_HEIGHT_RATIO;
			
			//init our totals
			$tax = 0;
			$totalGross = 0;
			$totalNet = 0;
			$totalInsurance = 0;
			$totalDue = 0;
			
			//this is going to hold all of the rental records whose data we need to update
			//if the invoice is actually printed
			$updatedRentals = array();
			
			//this is going to hold all the information we need to insert entries in the transaction queue
			//for lines that are invoiced.
			$transactions = array();
			
			//this is going to hold all of the billing queue entries we need to insert
			$billingQueueEntries = array();
			
			//sort the rentals so we can group them by rental period and setup date
			$lines = Set::sort($lines, '{n}.sort_key', 'asc');
			$previousGroup = null;
			
			//indent the margin a bit for our lines
			$document->SetLeftMargin(PDF_MARGIN_LEFT + 3);

//This is junk data that you can use for testing group changes and multi-page invoices.
/*		
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[] = $lines[count($lines) - 1];
$lines[2]['sort_key'] = $lines[2]['sort_key'] . 'A';
$lines[3]['sort_key'] = $lines[3]['sort_key'] . 'A';
*/
			//individual lines
			foreach ($lines as $lineNumber => $rental)
			{
				$document->Ln($lineHeight * 2);
				
				//see if we're changing groups
				$changeOfGroup = $previousGroup == null || $previousGroup != $rental['sort_key'];
				$previousGroup = $rental['sort_key'];
				
				//see if we need to start a new page (we start a new page if there are less that 3 line height's worth
				//of space left in the box - 8 if we're changing groups)
				if ($document->GetY() > $boxTop + $boxHeight - ($lineHeight * ($changeOfGroup ? 8 : 3)))
				{
					//subsequent pages have their box start up higher
					if ($document->getNumPages() == 1)
					{
						$boxHeight += $boxTop - 20;
						$boxTop = 20;
					}
					
					//new page
					$document->AddPage();					
					$this->renderLineItemBackground($document, $activeCarriers, $boxTop, $boxHeight);
					$document->Ln($lineHeight * 2);
				}
				
				//this is going to hold all of the fields in the rental that we're going to update after generating the line
				$updatedData = array(
					'id' => $rental['id']
				);
				
				//update the maintenance date if we're doing maintenance invoices
				if ($parameters['Virtual']['maintenance_invoices_only'])
				{
					$updatedData['maintenance_date'] = databaseDate(addMonths($rental['maintenance_date'], $rental['maintenance_frequency']));
				}
								
				//if an item is capped and this is the 4th month, we need to reduce net amounts for
				//all carriers by 25% if we have MC20 as a carrier with a balance remaining
				if (strtoupper($rental['6_point_classification']) == 'CAP' && $rental['number_of_rental_months'] == 3)
				{
					foreach (array('1', '2', '3') as $i)
					{
						if ($rental["carrier_{$i}_code"] == 'MC20' && $rental["carrier_{$i}_net_amount"] > 0)
						{
							//if we found the MC20 carrier, we need to go through all amounts now and reduce them by 25%
							foreach (array('1', '2', '3') as $j)
							{
								if ($rental["carrier_{$j}_net_amount"] > 0)
								{
									$updatedData["carrier_{$j}_net_amount"] = round($rental["carrier_{$j}_net_amount"] * .75, 2);
								}
							}

							break;
						}
					}
				}
				
				$net = array();
				$gross = array();
				$lineNet = 0;
				$lineGross = 0;
				$lineInsurance = 0;
				$lineDue = 0;
				
				//if we're not doing maintenance invoices but there is a maintenance date, use zeros for all amounts
				if (!$parameters['Virtual']['maintenance_invoices_only'] && $rental['maintenance_date'] != null)
				{
					$net = array(0, 0, 0);
					$gross = array(0, 0, 0);
				}
				else
				{
					$net[] = isset($updatedData['carrier_1_net_amount']) ? $updatedData['carrier_1_net_amount'] : $rental['carrier_1_net_amount'];
					$net[] = isset($updatedData['carrier_2_net_amount']) ? $updatedData['carrier_2_net_amount'] : $rental['carrier_2_net_amount'];
					$net[] = isset($updatedData['carrier_3_net_amount']) ? $updatedData['carrier_3_net_amount'] : $rental['carrier_3_net_amount'];
					$gross[] = $rental['carrier_1_gross_amount'];
					$gross[] = $rental['carrier_2_gross_amount'];
					$gross[] = $rental['carrier_3_gross_amount'];
				}
				
				//keeps track of gross/net amounts per carrier on the line
				$carrierGross = array();
				$carrierNet = array();
				
				//go through the active carriers...
				foreach ($activeCarriers as $carrier)
				{
					//and try to find them on the rental
					foreach (array('1', '2', '3') as $i)
					{
						//if we found them, add their gross and net to running totals
						if ($carrier['carrier_number'] == $rental["carrier_{$i}_code"])
						{
							//store the gross for the carrier for later
							if (!isset($carrierGross[$carrier['carrier_number']]))
							{
								$carrierGross[$carrier['carrier_number']] = 0;
							}
							
							$carrierGross[$carrier['carrier_number']] += $gross[(int)$i - 1];
							
							//store the net for the carrier for later
							if (!isset($carrierNet[$carrier['carrier_number']]))
							{
								$carrierNet[$carrier['carrier_number']] = 0;
							}
							
							$carrierNet[$carrier['carrier_number']] += $net[(int)$i - 1];
							
							//increment the line net/gross
							$lineNet += $net[(int)$i - 1];
							$lineGross += $gross[(int)$i - 1];
							
							//figure out whether the insurance company or patient pays the amount
							if (in_array($carrier['Carrier']['statement_type'], array('1', '4', '5')))
							{
								$lineDue += $net[(int)$i - 1];
							}
							else
							{
								$lineInsurance += $net[(int)$i - 1];
							}
						}
					}
				}
				
				//update our tax total if this item is taxable
				if ($rental['is_taxable'])
				{
					$tax += $lineGross * $profitCenter['ProfitCenter']['sales_tax_rate'];
				}
				
				//update our grand totals
				$totalNet += $lineNet;
				$totalGross += $lineGross;
				$totalInsurance += $lineInsurance;
				$totalDue += $lineDue;
				
				//render the group header if we're on a new group
				if ($changeOfGroup)
				{
					//outdent so our background color fills the whole line
					$margins = $document->getMargins();
					$document->SetLeftMargin(PDF_MARGIN_LEFT);
					$document->SetX(PDF_MARGIN_LEFT);
					
					$this->Pdf->row(
						$document,
						array(
							'',
							'Rental From:',
							formatDate($rental['period']['start']) . ' to ' . formatDate($rental['period']['end']),
							''
						),
						array(3, 22, 75, 80),
						array('fillColor' => array(240, 240, 240))
					);
					
					$this->Pdf->row(
						$document,
						array(
							'',
							'Setup Date:',
							formatDate($rental['setup_date']),
							''
						),
						array(3, 22, 75, 80),
						array('fillColor' => array(240, 240, 240))
					);
					
					//revert the margin
					$document->SetLeftMargin($margins['left']);
					$document->SetX($margins['left']);
					
					$document->Ln($lineHeight * 1);
					
					$this->Pdf->row(
						$document,
						array(
							array("Reoccurring Rental Invoice for:", array('style' => 'U'))
						),
						$subLineWidths
					);
					
					$document->Ln($lineHeight * 1);
				}
				
				//render the line
				$description = $rental['healthcare_procedure_code'] !== '' 
					? $this->HealthcareProcedureCode->field('description', array('code' => $rental['healthcare_procedure_code']))
					: $rental['inventory_description'];
					
				$this->Pdf->row(
					$document,
					array(
						$description,
						array(number_format($lineGross, 2), array('align' => 'R')),
						array(number_format($lineNet, 2), array('align' => 'R')),
						array(number_format($lineInsurance, 2), array('align' => 'R')),
						array(number_format($lineDue, 2), array('align' => 'R'))
					),
					$lineWidths
				);
				
				$this->Pdf->row(
					$document,
					array('Inventory #: ' . $rental['inventory_number'] . ', MRS #: ' . $rental['serial_number']),
					$subLineWidths
				);
				
				///adjust any other extra info we need to update the rental
				$updatedData['number_of_rental_months'] = $rental['number_of_rental_months'] + 1;
				
				//update the rental record with updated data
				if (!$parameters['Virtual']['test_run_only'])
				{
					//pr(Set::flatten($updatedData));
					$this->Rental->create();
					$this->Rental->save(array('Rental' => $updatedData));
				}
				
				//store data about the rental we need to update if the invoice is actually printed
				$data = array(
					'id' => $rental['id'],
					'last_invoiced_date' => databaseDate($invoiceDate),
					'rental_day' => $rental['rental_day'], //the rental passed to this method may have had the rental day modified
					'restart_date' => databaseDate($invoiceDate)
				);
				
				//if the rental is capped...
				if (strtoupper($rental['6_point_classification']) == 'CAP')
				{
					//update the form code on the rental going into the 14th month
					if (trim($rental['modifier_3']) == '' && $updatedData['number_of_rental_months'] == 14)
					{
						$data['form_code'] = 'NN';
					}
					
					//update modifier 2 if necessary
					if ($updatedData['number_of_rental_months'] == 1)
					{
						$data['modifier_2'] = 'KH';
					}
					else if ($updatedData['number_of_rental_months'] >= 2 && $updatedData['number_of_rental_months'] <= 3)
					{
						$data['modifier_2'] = 'KI';
					}
					else if ($updatedData['number_of_rental_months'] >= 4 && $updatedData['number_of_rental_months'] <= 15)
					{
						$data['modifier_2'] = 'KJ';
					}
				}
				
				$updatedRentals[] = $data;
				
				//grab the inventory item for the rental
				$inventoryItem = $this->Inventory->find('first', array(
					'fields' => array('group_field', 'general_ledger_rental_code'),
					'conditions' => array('inventory_number' => $rental['inventory_number']),
					'contain' => array()
				));
				
				//create transaction data for this line				
				$lineTransactionTemplate = array_merge(
					$transactionTemplate,
					array(
						'transaction_date_of_service' => databaseDate($rental['period']['start']), 
						'data_entry_date' => databaseDate($rental['period']['start']),
						'equipment_description' => $rental['inventory_description'],
						'quantity' => $rental['quantity'],
						'inventory_number' => $rental['inventory_number'],
						'inventory_description' => $rental['inventory_description'],
						'healthcare_procedure_code' => $rental['healthcare_procedure_code'],
						'inventory_group_code' => $inventoryItem !== false ? $inventoryItem['Inventory']['group_field'] : '',
						'department_code' => $rental['department_code'],
						'unique_identification_number' => $invoiceNumber . '.' . ($lineNumber + 1)
					)
				);
				
				//we create transactions for each carrier
				foreach ($carrierGross as $carrierNumber => $amount)
				{ 
					//first a transaction for the gross amount
					$transactions[] = array_merge(
						$lineTransactionTemplate,
						array(
							'amount' => number_format($amount, 2, '.', ''),
							'general_ledger_code' => $parameters['Virtual']['maintenance_invoices_only'] ? '885S' : ($inventoryItem !== false ? $inventoryItem['Inventory']['general_ledger_rental_code'] : ''),
							'carrier_number' => $carrierNumber
						)
					);
					
					//then a transaction for the tax if there is any
					if ($rental['is_taxable'])
					{
						$transactions[] = array_merge(
							$lineTransactionTemplate,
							array(
								'amount' => number_format($amount * $profitCenter['ProfitCenter']['sales_tax_rate'], 2, '.', ''),
								'general_ledger_code' => $this->DefaultFile->data['tax_gl_code'],
								'carrier_number' => $carrierNumber
							)
						);
					}
					
					//now a credit for the difference between gross and net 
					//(per Peggy, we make a negative charge instead of a credit)
					//(now we will use same gl code as gross transaction rather than gl code from default file)
					$transactions[] = array_merge(
						$lineTransactionTemplate,
						array(
							'amount' => number_format(($amount - $carrierNet[$carrierNumber]) * -1, 2, '.', ''),
							'general_ledger_code' => $parameters['Virtual']['maintenance_invoices_only'] ? '885S' : ($inventoryItem !== false ? $inventoryItem['Inventory']['general_ledger_rental_code'] : ''),
							'carrier_number' => $carrierNumber
						)
					);
					
					//and finally a charge for the tax for the difference between gross and net if there was tax
					if ($rental['is_taxable'])
					{
						$transactions[] = array_merge(
							$lineTransactionTemplate,
							array(
								'amount' => number_format(($amount - $carrierNet[$carrierNumber]) * $profitCenter['ProfitCenter']['sales_tax_rate'], 2, '.', ''),
								'general_ledger_code' => $this->DefaultFile->data['rental_credit_gl_code'],
								'carrier_number' => $carrierNumber
							)
						);
					}
				}
				
				//now set up the billing queue entry if necessary
				if ($rental['quantity'] >= 1)
				{
					//fill out the standard details
					$entry = array(
						'account_number' => $customer['Customer']['account_number'],
						'rental_or_purchase' => 'R',
						'record_number' => $rental['id'],
						'invoice_number' => $invoiceNumber,
						'form_code' => $parameters['Virtual']['maintenance_invoices_only'] ? 'MS' : (isset($data['form_code']) ? $data['form_code'] : $rental['form_code']),
						'client_name' => $customer['Customer']['name'],
						'date_of_service' => databaseDate($rental['period']['start']),
						'service_to_date' => databaseDate($rental['period']['start']),
						'carrier_1_code' => $rental['carrier_1_code'],
						'carrier_1_net_amount' => 0,
						'carrier_1_gross_amount' => 0,
						'carrier_2_code' => $rental['carrier_2_code'],
						'carrier_2_net_amount' => 0,
						'carrier_2_gross_amount' => 0,
						'carrier_3_code' => $rental['carrier_3_code'],
						'carrier_3_net_amount' => 0,
						'carrier_3_gross_amount' => 0,
						'modifier_1' => $parameters['Virtual']['maintenance_invoices_only'] ? '' : $rental['modifier_1'],
						'modifier_2' => $parameters['Virtual']['maintenance_invoices_only'] ? 'MS' : (isset($data['modifier_2']) ? $data['modifier_2'] : $rental['modifier_2']),
						'modifier_3' => $parameters['Virtual']['maintenance_invoices_only'] ? '' : $rental['modifier_3'],
						'should_send_cmn' => 0,
						'unique_identification_number' => $invoiceNumber . '.' . ($lineNumber + 1),
						'modifier_4' => $rental['modifier_4']
					);
					
					//if we're doing maintenance invoices or there is no maintenance date we need to set up the amounts for the carriers
					if ($parameters['Virtual']['maintenance_invoices_only'] || $rental['maintenance_date'] == null)
					{
						if (trim($entry['carrier_1_code']) !== '')
						{
							$entry['carrier_1_net_amount'] = isset($updatedData['carrier_1_net_amount']) ? $updatedData['carrier_1_net_amount'] : $rental['carrier_1_net_amount'];
							$entry['carrier_1_gross_amount'] = $rental['carrier_1_gross_amount'];
						}
						
						if (trim($entry['carrier_2_code']) !== '')
						{
							$entry['carrier_2_net_amount'] = isset($updatedData['carrier_2_net_amount']) ? $updatedData['carrier_2_net_amount'] : $rental['carrier_2_net_amount'];
							$entry['carrier_2_gross_amount'] = $rental['carrier_2_gross_amount'];
						}
						
						if (trim($entry['carrier_3_code']) !== '')
						{
							$entry['carrier_3_net_amount'] = isset($updatedData['carrier_3_net_amount']) ? $updatedData['carrier_3_net_amount'] : $rental['carrier_3_net_amount'];
							$entry['carrier_3_gross_amount'] = $rental['carrier_3_gross_amount'];
						}
					}
					
					$billingQueueEntries[] = $entry;
				}
			}
			
			//if we're doing maintenance invoices, zero balance (gross + tax) invoices are not processed
			if ($parameters['Virtual']['maintenance_invoices_only'] && $totalGross + $tax == 0)
			{
				$this->Logging->write('Zero balance invoice skipped.');
				return false;
			}
			
			//update batch totals
			$this->batchTotalGross += $totalGross;
			$this->batchTotalNet += $totalNet;
			
			//create the transaction queue entries
			if (!$parameters['Virtual']['test_run_only'])
			{
				$this->Logging->write('Creating transactions.');
				//pr(Set::flatten($transactions));
			
				foreach ($transactions as $transaction)
				{
					$this->TransactionQueue->create();
					$this->TransactionQueue->save(array('TransactionQueue' => $transaction));
				}
			}
			
			//update the rentals
			if (!$parameters['Virtual']['test_run_only'])
			{
				$this->Logging->write('Updating rentals.');
				//pr(Set::flatten($updatedRentals));
			
				foreach ($updatedRentals as $rental)
				{
					$this->Rental->create();
					$this->Rental->save(array('Rental' => $rental));
				}
			}
			
			//create the billing queue entries
			if (!$parameters['Virtual']['test_run_only'])
			{
				$this->Logging->write('Creating billing queue entries.');
				//pr(Set::flatten($billingQueueEntries));
			
				foreach ($billingQueueEntries as $entry)
				{
					$this->BillingQueue->create();
					$this->BillingQueue->save(array('BillingQueue' => $entry));
				}
			}
			
			//totals
			$document->SetY($boxTop + $boxHeight);
			$indent = 115;
			$totalWidths = array(60, 20);
			
			$document->SetX($indent);
			
			$this->Pdf->row(
				$document,
				array('Invoice Total:', number_format($totalGross, 2)),
				$totalWidths,
				array('align' => 'R', 'border' => 1)
			);
			
			$document->SetX($indent);
			
			$this->Pdf->row(
				$document,
				array('Tax:', number_format($tax, 2)),
				$totalWidths,
				array('align' => 'R', 'border' => 1)
			);
			
			$document->SetX($indent);
			
			$this->Pdf->row(
				$document,
				array('Total Due from You:', number_format($totalDue + $tax, 2)),
				$totalWidths,
				array('align' => 'R', 'border' => 1)
			);
			
			//reset the margin
			$document->SetLeftMargin(PDF_MARGIN_LEFT);
			$document->SetX(PDF_MARGIN_LEFT);
			
			//box off the left side of the detail box (i'm drawing a full box and then "erasing" the top line 
			//of the box with a white line. Yes it's ghetto).
			$document->Rect(PDF_MARGIN_LEFT, $boxTop + $boxHeight, $indent - PDF_MARGIN_LEFT, $document->GetY() - ($boxTop + $boxHeight));
			$document->Line(PDF_MARGIN_LEFT + .1, $boxTop + $boxHeight, $indent - .2, $boxTop + $boxHeight, array('color' => array(255, 255, 255)));
			
			//advertisement
			$top = $document->GetY();
			$document->Ln(.2);
			$document->Image(WWW_ROOT . 'files' . DS . $this->settings['batch_invoicing_advertisement_image'], 15, $document->GetY(), 100, 40);
			$document->Rect(15, $document->GetY(), 100, 40);
			
			//footer text
			$document->SetY($top);
			$document->Ln(1);
			$document->SetFont('freesans', '', 9);
			
			$this->Pdf->row(
				$document,
				array(
					'', 
					array("To arrange for more equipment or if you have questions about this equipment, please call {$this->settings['company_name']}:", array('style' => 'I', 'align' => 'C'))),
				array(100, 80)
			);
			
			$document->SetFont('freesans', '', 10);
			$document->Ln(2);
			
			//profit center number
			$this->Pdf->row(
				$document,
				array(
					'', 
					array("{$profitCenter['ProfitCenter']['abbreviation']} at {$profitCenter['ProfitCenter']['toll_free_number']}", array('align' => 'C'))
				),
				array(100, 80)
			);
			
			//make sure the output path exists
			$path = $this->outputPath . DS . Inflector::slug($customer['Customer']['name']) . '.' . $customer['Customer']['account_number'] . DS . ($totalDue != 0 ? 'printed' : 'zero_balance');
			new Folder($path, true, 0777);
			
			$this->Logging->write('Generating PDF.');
			
			//generate the PDF
			$filename = $path . DS . date('Ymd', strtotime($invoiceDate)) . '.pdf';
			$document->Output($filename, 'F');
			
			$this->Logging->write('Sending invoice to DocPop.');
			
			//send it to DocPop move it to the invoicing queue so it shows up in the application
			$documentID = $this->Document->resolveIDFromBarcode($barcode);
			$this->Image->appendBinaryFileToDocument($documentID, $filename);
			$this->Document->assignToQueue($documentID, $this->settings['docpop_batch_invoicing_queue_id']);
			
			return $filename;
		}
		
		/**
		 * This draws the background on the invoice PDF where the line items are printed.
		 * @param object $document The PDF document being worked on.
		 * @param int $top The Y position to start rendering.
		 * @param int $height The height of the background to draw.
		 */
		function renderLineItemBackground($document, &$activeCarriers, $top, $height)
		{
			$boxBottom = $top + $height;
			$lineHeight = $document->getFontSize() * K_CELL_HEIGHT_RATIO;
			$left = 115;
			$fillColor = array(240, 240, 240);
			
			//first the large outer rectangle
			$document->Rect(15, $top, 180, $height);
			
			//now the four columns on the right hand side
			$document->Rect($left, $top, 20, $height);
			$document->Rect($left + 20, $top, 20, $height);
			$document->Rect($left + 40, $top, 20, $height);
			$document->Rect($left + 60, $top, 20, $height);
			
			//filled in small rectangles at the top of the columns (i.e. our "headers")
			$document->Rect($left, $top, 20, 15, 'DF', array(), $fillColor);
			$document->Rect($left + 20, $top, 20, 15, 'DF', array(), $fillColor);
			$document->Rect($left + 40, $top, 20, 15, 'DF', array(), $fillColor);
			$document->Rect($left + 60, $top, 20, 15, 'DF', array(), $fillColor);
			
			$document->SetY($top);
			
			//store the margins and indent for our text
			$margins = $document->getMargins();
			$document->SetLeftMargin(PDF_MARGIN_LEFT + 3);
			
			//only on the first page do we print the client funding
			if ($document->getNumPages() == 1)
			{
				$output = 'Our records indicate your insurance is: ';
				
				foreach ($activeCarriers as $i => $carrier)
				{
					
					$output .= ($i > 0 ? ', ' : '') . ($i + 1) . ") {$carrier['Carrier']['name_to_print_on_statement']}";
				}
				
				$this->Pdf->row($document, array(array($output, array('align' => 'C'))), array(98));
			}
			
			$document->SetY($top);
			
			//render the column headers
			$this->Pdf->row(
				$document,
				array(
					'', 
					array("\nBilled\nAmount", array('align' => 'C')), 
					array("Insurance\nAllowed\nAmount", array('align' => 'C')), 
					array("Insurance\nExpected\nPayment", array('align' => 'C')), 
					array("\nAmount\nYou Owe", array('align' => 'C'))
				),
				array(98, 20, 20, 20, 20)
			);
			
			//reset the margin
			$document->SetLeftMargin($margins['left']);
			$document->SetX($margins['left']);
		}
		
		/**
		 * Determines the start and end dates for a rental period. This is based on lines 553 to 569 from MU05FT.TXT
		 * @param int $rentalDay The rental day on the rental.
		 * @param string $invoiceDate The date the invoice is for.
		 * @return array An array with two keys, start and end, that have the dates for the rental period.
		 */
		function getRentalPeriod($rentalDay, $invoiceDate)
		{
			$period = array('start' => databaseDate($invoiceDate), 'end' => '');
			$day = (int)$rentalDay;
			
			//if the rental day is the same as the invoice, the end of the period is just one month out
			if ($day == (int)date('j', strtotime($invoiceDate)))
			{
				$period['end'] = databaseDate(addMonth($invoiceDate));
			}
			else
			{
				//otherwise, add a month to the invoice date, and then as long as the rental day is less
				//than or equal to the number of days in the next month, use that day
				$period['end'] = databaseDate(addMonth($invoiceDate));
				
				$time = strtotime($period['end']);
				$month = date('n', $time);
				$year = date('Y', $time);
				
				if ($day <= daysInMonth($month, $year))
				{
					$period['end'] = databaseDate("{$month}/{$day}/{$year}");
				}
			}
			
			return $period;
		}
		
		/**
		 * Generates a template for a transaction queue record that can be used by an invoice.
		 * @param array $customer The customer record of the customer the invoice is for.
		 * @param array $parameters The shell parameters.
		 * @param string $invoiceNumber The invoice number of the invoice.
		 * @param string $tcnNumber The TCN number for the transaction.
		 * @return array An array containing key/value pairs of transaction queue record fields filled
		 * out with defaults for the invoice.
		 */
		function createTransactionTemplate(&$customer, &$parameters, $invoiceNumber, $tcnNumber)
		{
			return array(
				'account_number' => $customer['Customer']['account_number'],
				'invoice_number' => $invoiceNumber,
				'transaction_type' => $this->settings['charge_transaction_type_id'],
				'salesman_number' => $customer['CustomerBilling']['salesman_number'],
				'user_id' => User::current(),
				'post_status' => 'R',
				'profit_center_number' => $customer['Customer']['profit_center_number'],
				'transaction_control_number_file' => 'H',
				'transaction_control_number' => $tcnNumber,
				'rental_or_purchase' => 'R'
			);
		}
		
		/**
		 * Approximates how many customers there are to process.
		 * @return int An approximate number of customers to process.
		 */
		function getCustomerCount()
		{			
			//I'm TOTALLY cheating here and using an index to get the highest customer ID that we know about 
			//because for performance reasons it would take too long to go through the normal find methods. We're
			//using the highest ID as our count instead of a regular count since our main query starts at ID 1 and just
			//keeps going until it can't find any more. So, even if certain IDs are deleted or skipped because they don't
			//match the criteria, we can still count them as "processed".
			
			$db = ConnectionManager::getDataSource($this->Customer->useDbConfig);
			$indexModel = $db->_indexModel();
			
			$result = $indexModel->query('
				select max(record_number) as the_count
				from index_fu05bl_name
			');
			
			return $result[0][0]['the_count'];
		}
		
		/**
		 * This method takes all of the generated invoices and combines them into two documents - one containing
		 * invoices that should be printed, and one that contains zero-balance invoices. Neither, one, or both documents
		 * may be generated.
		 * @return array An array with the following keys - 'printed' and 'zero_balance'. The values are the full path to
		 * each document, or false if no document for that key exists.
		 */
		function combineInvoices()
		{
			//go grab all the customer names from our output path
			$folder = new Folder($this->outputPath, true, 0777);
			$customers = array_shift($folder->read());
			
			$documents = array('printed' => false, 'zero_balance' => false);
			
			if (empty($customers))
			{
				return $documents;
			}
			
			//merge each customer's invoices together into printed and zero balance documents
			foreach (array('printed', 'zero_balance') as $type)
			{
				//this will hold the paths to the combined PDFs that we make per customer
				$combined = array();
				
				foreach ($customers as $name)
				{
					$subfolder = new Folder($this->outputPath . DS . $name . DS . $type);
					
					if (is_dir($subfolder->pwd()))
					{
						$invoices = array_pop($subfolder->read());
						$invoices = array_map('escapeshellarg', $invoices);
					
						exec('cd ' . escapeshellarg($subfolder->pwd()) . '; pdftk ' . implode(' ', $invoices) . " cat output combined.pdf", $output, $return);
						
						//hold on to the path to the PDF
						$combined[] = $subfolder->pwd() . DS . "combined.pdf";
					}
				}
				
				if (!empty($combined))
				{					
					//now merge all of the customers together
					$combined = array_map('escapeshellarg', $combined);
					exec('cd ' . escapeshellarg($this->outputPath) . '; pdftk ' . implode(' ', $combined) . " cat output {$type}.pdf", $output, $return);
				
					//hang on to the doc path
					$documents[$type] = $this->outputPath . DS . "{$type}.pdf";
				}
			}

			//return the paths to the final PDFs
			return $documents;
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup() {}
	}
?>