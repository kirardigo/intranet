<?php
	/**
	 * Utility function that checks ahead to see if we should print a group total row following the current row.
	 * @param bool $groupByGroupCode States whether the report is being grouped by group code or not.
	 * @param array $current The current record being processed by the report.
	 * @param array $next The next record being to be processed by the report, or null if the current record is the last record.
	 * @return bool True if the totals row should be printed, false otherwise.
	 */
	function shouldPrintGroupTotals($groupByGroupCode, $current, $next)
	{
		if ($next === null)
		{
			return true;
		}
		
		$column = $groupByGroupCode ? 'inventory_group_code' : 'general_ledger_code';
		
		return $current['TransactionJournal'][$column] != $next['TransactionJournal'][$column];
	}
	
	/**
	 * Utility function that checks ahead to see if we should print a subtotal row following the current row.
	 * @param bool $groupByGroupCode States whether the report is being grouped by group code or not.
	 * @param array $current The current record being processed by the report.
	 * @param array $next The next record being to be processed by the report, or null if the current record is the last record.
	 * @return bool True if the subtotals row should be printed, false otherwise.
	 */
	function shouldPrintSubtotals($groupByGroupCode, $current, $next)
	{
		if ($next === null)
		{
			return true;
		}
		
		if ($current['TransactionJournal']['bank_number'] != $next['TransactionJournal']['bank_number'])
		{
			return true;
		}
		
		if ($groupByGroupCode)
		{
			return shouldPrintGroupTotals($groupByGroupCode, $current, $next) 
				|| $current['TransactionJournal']['general_ledger_code'] != $next['TransactionJournal']['general_ledger_code']
				|| $current['TransactionJournal']['profit_center_number'] != $next['TransactionJournal']['profit_center_number']
				|| $current['TransactionJournal']['department_code'] != $next['TransactionJournal']['department_code'];
		}
		else
		{
			return shouldPrintGroupTotals($groupByGroupCode, $current, $next) 
				|| $current['TransactionJournal']['general_ledger_code'] != $next['TransactionJournal']['general_ledger_code']
				|| $current['TransactionJournal']['profit_center_number'] != $next['TransactionJournal']['profit_center_number'];
		}
	}
	
	/**
	 * Utility function that checks ahead to see if the current row is the end of a grouped series.
	 * @param array $current The current record being processed by the report.
	 * @param array $next The next record being to be processed by the report, or null if the current record is the last record.
	 * @return bool True if the current row is the end of a series, false otherwise.
	 */
	function isEndOfSeries($current, $next)
	{
		if ($next === null)
		{
			return true;
		}
		
		return $current['TransactionJournal']['general_ledger_code'] != $next['TransactionJournal']['general_ledger_code'] || 
			$current['TransactionJournal']['profit_center_number'] != $next['TransactionJournal']['profit_center_number'] ||
			$current['TransactionJournal']['inventory_group_code'] != $next['TransactionJournal']['inventory_group_code'] ||
			$current['TransactionJournal']['department_code'] != $next['TransactionJournal']['department_code'] ||
			$current['TransactionJournal']['bank_number'] != $next['TransactionJournal']['bank_number'] ||
			$current['TransactionJournal']['transaction_date_of_service'] != $next['TransactionJournal']['transaction_date_of_service'] ||
			$current['TransactionJournal']['invoice_number'] != $next['TransactionJournal']['invoice_number'] ||
			$current['TransactionJournal']['account_number'] != $next['TransactionJournal']['account_number'];
	}
	
	/**
	 * Utility function that prints the headers of the table.
	 * @param PdfComponent $manager The pdf component.
	 * @param object $pdf The tcpdf object.
	 * @param array $headers An array of header cells.
	 * @param array $widths An array of cell widths.
	 */
	function printTableHeader($manager, $pdf, $headers, $widths)
	{
		$manager->row($pdf, $headers, $widths, array('style' => 'B', 'border' => 'B'));
		$pdf->Ln(1);
	}
	
	/**
	 * Utility function that prints a row in the report.
	 * @param PdfComponent $manager The pdf component.
	 * @param object $pdf The tcpdf object.
	 * @param array $headers An array of header cells.
	 * @param array $widths An array of cell widths.
	 * @param HtmlHelper $html The HTML helper used to render output.
	 * @param bool $groupByGroupCode States whether the report is being grouped by group code or not.
	 * @param bool $totalsOnly States whether or not only totals are being rendered.
	 * @param array $record The record to render.
	 * @param numeric $amount The amount to render for the row.
	 * @param numeric $subtotal The subtotal amount, if any, to render for the row.
	 * @param int $subtotalCount The number of series that make up the subtotal.
	 * @param numeric $groupTotal The total group amount, if any, to render for the row.
	 * @param int $groupTotalCount The number of series that make up the group total.
	 */
	function printRow($manager, $pdf, $headers, $widths, $html, $groupByGroupCode, $totalsOnly, $record, $amount, $subtotal = null, $subtotalCount = null, $groupTotal = null, $groupTotalCount = null)
	{
		//static $isAlt = false;
		
		$glCode = $record['TransactionJournal']['general_ledger_code'] . '-' . $record['TransactionJournal']['profit_center_number'];			
		$value1 = $groupByGroupCode ? $record['TransactionJournal']['inventory_group_code'] : $glCode;
		$value2 = $groupByGroupCode ? $glCode : $record['TransactionJournal']['inventory_group_code'];

		$manager->row(
			$pdf, 
			array(
				$value1,
				$value2,
				$record['TransactionJournal']['department_code'],
				($subtotal !== null || $groupTotal !== null) ? (isset($record['GeneralLedger']['description']) ? $record['GeneralLedger']['description'] : '*** NOT ON FILE ***') : '',
				!$totalsOnly ? formatDate($record['TransactionJournal']['transaction_date_of_service']) : '',
				!$totalsOnly ? $record['TransactionJournal']['invoice_number'] : '',
				!$totalsOnly ? $record['TransactionJournal']['account_number'] : '',
				array(!$totalsOnly ? money_format('%.2n', $amount) : '', array('align' => 'R')),
				'',
				array($record['TransactionJournal']['bank_number'], array('align' => 'R')),
				array($subtotal !== null ? money_format('%.2n', $subtotal) : '', array('align' => 'R')),
				array($subtotal !== null ? ('(' . number_format($subtotalCount) . ')') : '', array('align' => 'R')),
				array($groupTotal !== null ? money_format('%.2n', $groupTotal) : '', array('align' => 'R')),
				array($groupTotal !== null ? ('(' . number_format($groupTotalCount) . ')') : '', array('align' => 'R'))
			), 
			$widths,
			array(), //$isAlt ? array('fillColor' => array(225, 223, 211)) : array(),
			array('printTableHeader', $headers, $widths)
		);
		
		//$isAlt ^= true;
	}
	
	/**
	 * Utility function that prints a group total row in the report.
	 * @param PdfComponent $manager The pdf component.
	 * @param object $pdf The tcpdf object.
	 * @param array $headers An array of header cells.
	 * @param array $widths An array of cell widths.
	 * @param HtmlHelper $html The HTML helper used to render output.
	 * @param string $label The label to render for the row.
	 * @param numeric $groupTotal The group total amount to render for the row.
	 */
	function printGroupTotalRow($manager, $pdf, $headers, $widths, $html, $label, $groupTotal)
	{
		$manager->row(
			$pdf, 
			array(
				'', '', '', '', '', 
				$label, 
				array(money_format('%.2n', $groupTotal), array('align' => 'R')), 
				'', '', '', '', '', ''
			),
			array_merge(array_slice($widths, 0, 5), array(array_sum(array_slice($widths, 5, 2))), array_slice($widths, 7)),
			array('fillColor' => array(214, 236, 254), 'style' => 'B'),
			array('printTableHeader', $headers, $widths)
		);
	}
	
	/**
	 * Utility function that prints a grand total row in the report.
	 * @param PdfComponent $manager The pdf component.
	 * @param object $pdf The tcpdf object.
	 * @param array $headers An array of header cells.
	 * @param array $widths An array of cell widths.
	 * @param HtmlHelper $html The HTML helper used to render output.
	 * @param string $label The label to render for the row.
	 * @param numeric $total The total amount to render for the row.
	 * @param int $totalCount The number of series, if any, that make up the grand total.
	 */
	function printGrandTotalRow($manager, $pdf, $headers, $widths, $html, $label, $total, $totalCount = null)
	{
		$manager->row(
			$pdf, 
			array(
				'', '', '', '', '', 
				$label, 
				array(money_format('%.2n', $total), array('align' => 'R')), 
				array($totalCount !== null ? (' (' . number_format($totalCount) . ')') : '', array('align' => 'R')),
				'', '', '', '', ''
			),
			array_merge(array_slice($widths, 0, 5), array(array_sum(array_slice($widths, 5, 2))), array_slice($widths, 7)),
			array('fillColor' => array(51, 51, 51), 'color' => array(255, 255, 255), 'style' => 'B'),
			array('printTableHeader', $headers, $widths)
		);
	}

	//render the form to let the user choose their criteria
	if (!isset($matches))
	{
		if (isset($noCriteria))
		{
			echo '<p class="Warning">You must specify at least one filter.</p>';
		}
	
		echo '<fieldset>';
		echo '<legend>Filters</legend>';
		
		echo $form->create('TransactionJournal', array('url' => '/reports/accountsReceivable/glDistribution'));
		
		echo $form->label('Date Range');
		echo $form->input('begin_date', array('label' => false, 'div' => false));
		echo ' to ';
		echo $form->input('end_date', array('label' => false, 'div' => false));
		
		echo '<br /><br />';
	
		echo $form->input('profit_center_number', array('label' => 'Profit Center', 'div' => array('class' => 'Horizontal')));
		echo $form->input('general_ledger_code', array('label' => 'G/L Code', 'div' => array('class' => 'Horizontal'))) . ' ';
		echo $form->input('inventory_group_code', array('label' => 'Group Code', 'div' => array('class' => 'Horizontal'))) . ' ';
		echo $form->input('bank_number', array('div' => array('class' => 'Horizontal'))) . ' ';
		echo $form->input('department_code', array('div' => array('class' => 'Horizontal')));
	
		echo '<br style="clear: left;" /><br />';
		
		echo $form->input('Option.grouping_order_code', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'div' => false));
		echo $form->input('Option.totals_only', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox'), 'div' => false));
		
		echo $form->end('Generate');
		echo '</fieldset>';
	}
	else
	{
		$widths = array(25, 20, 15, 56, 20, 20, 15, 15, 10, 15, 17, 10, 17, 10); //265 total
		$headers = array(
			$groupByGroupCode ? "Group\nCode" : "G/L\nCode",
			$groupByGroupCode ? "G/L\nCode" : "Group\nCode",
			'Dept',
			'Description',
			'Date',
			'Invoice',
			"Account\nNumber",
			array('Amount', array('align' => 'R')),
			' ',
			array("Bank\nNumber", array('align' => 'R')),
			array($groupByGroupCode ? "G/L Code\nSubtotal" : "Profit Center\nSubtotal", array('align' => 'R')),
			' ',
			array($groupByGroupCode ? "Group Code\nTotal" : "G/L Code\nTotal", array('align' => 'R')),
			' '
		);
				
		$pdf->setPageOrientation('landscape');

		//build the page headers
		$pdf->setTopLeftHeaderString('A/R Distribution to G/L');
		$pdf->setTopRightHeaderString(date('m/d/Y - h:i:s A'));
		$pdf->AddPage();
		
		//build the report header (basically just list the chosen parameters)
		$pdf->SetY(10);
		$pdf->SetFontSize(14);
		
		$manager->row(
			$pdf,
			array('Parameters'),
			array(array_sum($widths))
		);

		$pdf->SetFontSize(9);
		$parameterWidths = array(35, 25, 35, 25);
		
		$manager->row(
			$pdf, 
			array(
				'Start Date:', formatDate(ifset($this->data['TransactionJournal']['begin_date'])), 
				'Bank Number:', ifset($this->data['TransactionJournal']['bank_number'])
			), 
			$parameterWidths
		);
		
		$manager->row(
			$pdf, 
			array(
				'End Date:', formatDate(ifset($this->data['TransactionJournal']['end_date'])),
				'Department Code:', ifset($this->data['TransactionJournal']['department_code'])
			), 
			$parameterWidths
		);
		
		$manager->row(
			$pdf, 
			array(
				'Profit Center:', ifset($this->data['TransactionJournal']['profit_center_number']),
				'Grouping Order Code:', $this->data['Option']['grouping_order_code'] == 0 ? 'N' : 'Y'
			), 
			$parameterWidths
		);
		
		$manager->row(
			$pdf, 
			array(
				'G/L Code:', ifset($this->data['TransactionJournal']['general_ledger_code']),
				'Totals Only:', $this->data['Option']['totals_only'] == 0 ? 'N' : 'Y'
			), 
			$parameterWidths
		);
		
		$manager->row(
			$pdf, 
			array(
				'Group Code:', ifset($this->data['TransactionJournal']['inventory_group_code'])
			), 
			$parameterWidths
		);
		
		$manager->row($pdf, array(array('', array('border' => 'B'))), array_sum($parameterWidths));
		$pdf->Ln(5);
		
		//render the table header
		printTableHeader($manager, $pdf, $headers, $widths);

		//initialize our running totals
		$transactionTypeGroupTotals = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
		$transactionTypeGrandTotals = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
		
		$seriesTotal = 0;
		$subtotal = 0;
		$groupTotal = 0;
		$grandTotal = 0;
		
		$subtotalCount = 0;
		$groupCount = 0;
		$grandTotalCount = 0;
		
		$totalCount = count($matches);
		
		//go through each row...
		foreach ($matches as $i => $match)
		{
			$lastRecord = $i == $totalCount - 1;
			
			//update our running totals			
			$transactionTypeGroupTotals[$match['TransactionJournal']['transaction_type']] += $match['TransactionJournal']['amount'];
			$transactionTypeGrandTotals[$match['TransactionJournal']['transaction_type']] += $match['TransactionJournal']['amount'];
			$seriesTotal += $match['TransactionJournal']['amount'];
			$subtotal += $match['TransactionJournal']['amount'];
			$groupTotal += $match['TransactionJournal']['amount'];
			$grandTotal += $match['TransactionJournal']['amount'];
			
			$isEndOfSeries = isEndOfSeries($matches[$i], $lastRecord ? null : $matches[$i + 1]);
			$printed = false;
		
			if ($isEndOfSeries)
			{
				$subtotalCount++;
				$groupCount++;
				$grandTotalCount++;
			}
			
			//look ahead to see if we need to sum up the current series
			$shouldPrintSubtotals = shouldPrintSubtotals($groupByGroupCode, $matches[$i], $lastRecord ? null : $matches[$i + 1]);
			$shouldPrintGroupTotals = shouldPrintGroupTotals($groupByGroupCode, $matches[$i], $lastRecord ? null : $matches[$i + 1]);
		
			if ($shouldPrintSubtotals)
			{
				//print the current row
				printRow($manager, $pdf, $headers, $widths, $html, $groupByGroupCode, $totalsOnly, $match, $seriesTotal, $subtotal, $subtotalCount, $shouldPrintGroupTotals ? $groupTotal : null, $groupCount);
				$printed = true;
				
				//reset running totals
				$seriesTotal = 0;
				$subtotal = 0;
				$subtotalCount = 0;
			}
			
			if ($shouldPrintGroupTotals)
			{
				//print the current row if it hasn't already been printed
				if (!$printed)
				{
					printRow($manager, $pdf, $headers, $widths, $html, $groupByGroupCode, $totalsOnly, $match, $seriesTotal, $subtotal, $subtotalCount, $groupTotal, $groupCount);
					$printed = true;
				}
				
				//print the group totals
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'CHG', $transactionTypeGroupTotals[1]);
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'PMT', $transactionTypeGroupTotals[2]);
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'CRD', $transactionTypeGroupTotals[3]);
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'TR CHG', $transactionTypeGroupTotals[4]);
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'TR CRD', $transactionTypeGroupTotals[5]);
				printGroupTotalRow($manager, $pdf, $headers, $widths, $html, 'SUBTOTAL', array_sum($transactionTypeGroupTotals));
				
				//reset running totals
				$transactionTypeGroupTotals = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
				$seriesTotal = 0;
				$subtotal = 0;
				$subtotalCount = 0;
				$groupTotal = 0;
				$groupCount = 0;
			}
			
			//if we're not just showing totals and this is the end of series, we'll render the row
			if (!$totalsOnly && $isEndOfSeries)
			{
				//print the current row if it hasn't already been printed
				if (!$printed)
				{
					printRow($manager, $pdf, $headers, $widths, $html, $groupByGroupCode, $totalsOnly, $match, $seriesTotal);
				}
				
				//reset series totals
				$seriesTotal = 0;
			}
		}
		
		//print the grand totals
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'TOTAL CHG', $transactionTypeGrandTotals[1]);
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'TOTAL PMT', $transactionTypeGrandTotals[2]);
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'TOTAL CRD', $transactionTypeGrandTotals[3]);
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'TOTAL TR CHG', $transactionTypeGrandTotals[4]);
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'TOTAL TR CRD', $transactionTypeGrandTotals[5]);
		printGrandTotalRow($manager, $pdf, $headers, $widths, $html, 'GRAND TOTAL', $grandTotal, $grandTotalCount);
		
		Configure::write('debug', 0);
		$pdf->Output('AR Distribution to GL.pdf', 'I');
	}	
?>