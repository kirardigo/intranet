<?php if (!$isPostback): ?>
	<div id="TransactionQueueModuleContainer">
<?php endif; ?>

<table class="Styled" style="width: 300px; float: right; margin-top: 7px;">
	<tr>
		<?= '<th class="Right">Total ' . implode('s</th><th class="Right">Total ', Set::extract('/TransactionType[is_transfer=0]/description', $transactionTypes)) . 's</th>' ?>
		<th class="Right">Total Transfers</th>
	</tr>
	<tr>
		<?php
			$transfers = 0;
			
			//total up the amounts in the transactions for each transaction type,
			//but keep all types that are considered a transfer in their own grouped total
			foreach ($transactionTypes as $type)
			{
				$total = ifset($totals[$type['TransactionType']['code']], 0);
				
				if (!$type['TransactionType']['is_transfer'])
				{
					echo '<td class="Right">' . number_format($total, 2) . '</td>';
				}
				else
				{
					$transfers += $total;
				}
			}
		?>
		
		<td class="Right"><?= number_format($transfers, 2) ?></td>
	</tr>
</table>

<?php
	echo $form->create('TransactionQueue', array('id' => 'TransactionQueueModuleSearchForm', 'onsubmit' => 'Modules.TransactionQueue.View.search(false); return false;', 'style' => 'margin: 0px;'));
	
	//normally I wouldn't do a table here but IE was being totally stupid with the Horizontal div class and was
	//giving the textboxes a hanging indent no matter what I did
	echo '<table><tr><td>';
	echo $form->input('beginning_transaction_date_of_service', array(
		'id' => 'TransactionQueueModuleSearchBeginningTransactionDateOfService',
		'type' => 'text',
		'class' => 'DateField',
		'div' => false,
		'label' => 'Start Date'
	));
	echo '</td><td>';
	echo $form->input('ending_transaction_date_of_service', array(
		'id' => 'TransactionQueueModuleSearchEndingTransactionDateOfService',
		'type' => 'text',
		'class' => 'DateField',
		'div' => false,
		'label' => 'End Date'
	));
	echo '</td><td>';
	echo $form->input('cash_reference_number', array(
		'id' => 'TransactionQueueModuleSearchCashReferenceNumber',
		'class' => 'Text100',
		'div' => false,
		'label' => 'Cash Ref'
	));
	echo '</td><td>';
	echo $form->input('bank_number', array(
		'id' => 'TransactionQueueModuleSearchBankNumber',
		'options' => $banks,
		'empty' => true,
		'class' => 'Text125'
	));
	echo '</td><td>';
	echo $form->input('created_by', array(
		'id' => 'TransactionQueueModuleSearchCreatedBy',
		'class' => 'Text75',
		'div' => false,
		'label' => 'User'
	));
	echo '</td><td>';
	echo $form->button('Search', array(
		'id' => 'TransactionQueueModuleSearchButton', 
		'class' => 'StyledButton',
		'style' => 'margin-top: 8px',
		'onclick' => 'Modules.TransactionQueue.View.search(false)'
	));
	
	echo '</td></tr></table>';
	
	echo '<br class="ClearBoth"/>';
	
	echo $form->input('show_blank_cash_reference_numbers', array(
		'type' => 'checkbox', 
		'id' => 'TransactionQueueModuleSearchShowBlankCashReferenceNumbers', 
		'label' => array('class' => 'Checkbox')
	));
	
	echo $form->end();
?>

<?php if (count($transactions) > 0): ?>

	<?php
		//render the form to allow the user to kick off a batch post of the transactions
		echo $form->create('TransactionQueue', array('id' => 'TransactionQueueModuleBatchPostForm', 'style' => 'margin: 0px;'));
		
		//hidden fields we need
		echo $form->hidden('beginning_transaction_date_of_service', array('id' => 'TransactionQueueModuleBeginningTransactionDateOfService'));
		echo $form->hidden('ending_transaction_date_of_service', array('id' => 'TransactionQueueModuleEndingTransactionDateOfService'));
		echo $form->hidden('cash_reference_number', array('id' => 'TransactionQueueModuleCashReferenceNumber'));
		echo $form->hidden('bank_number', array('id' => 'TransactionQueueModuleBankNumber'));
		echo $form->hidden('created_by', array('id' => 'TransactionQueueModuleCreatedBy'));
		
		//options for the batch post
		echo $form->input('create_suggested_credits', array('id' => 'TransactionQueueModuleCreateSuggestedCredits', 'type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
		echo $form->hidden('show_blank_cash_reference_numbers', array('id' => 'TransactionQueueModuleShowBlankCashReferenceNumbers'));
		
		echo $form->button('Batch Post', array('id' => 'TransactionQueueModuleBatchPostButton'));
		echo $form->end();
		echo '<br />';
	?>
	
	<!-- This is hidden and used to inject the banks into a edited queue row -->
	<div id="TransactionQueueModuleBanksContainer" style="display: none;">
		<select>
			<option value=""></option>
			<?php
				foreach ($banksCompact as $bank)
				{
					echo '<option value="' . h($bank) . '">' . h($bank) . '</option>';
				}
			?>
		</select>
	</div>

	<!-- This is hidden and used to inject the transaction types into a edited queue row -->
	<div id="TransactionQueueModuleTransactionTypesContainer" style="display: none;">
		<select>
			<?php
				foreach ($transactionTypes as $type)
				{
					echo '<option value="' . h($type['TransactionType']['code']) . '">' . h($type['TransactionType']['code']) . ' - ' . h($type['TransactionType']['description']) . '</option>';
				}
			?>
		</select>
	</div>
	
	<?php
		//set up the options for the paginator so that it retains all of the passed criteria
		$paginator->options(array(
			'url' => array_merge(
				array(
					'controller' => 'modules/transactionQueue',
					'action' => 'view',
					'isPostback' => 1
				),
				$this->passedArgs
			),
			'update' => 'TransactionQueueModuleContainer',
			'onclick' => 'Modules.TransactionQueue.View.updateOriginalPagerVariables(this);'
		));
		
		//render pager links
		echo $this->element('page_links');
	?>

	<table class="Styled NoBorder" id="TransactionQueueTable">
		<thead>
			<tr>
				<?php
					echo $paginator->sortableHeader('G/L Code', 'general_ledger_code');
					echo $paginator->sortableHeader('DOS', 'transaction_date_of_service');
					echo $paginator->sortableHeader('Account', 'account_number');
					echo $paginator->sortableHeader('G/L Description', 'general_ledger_description');
					echo $paginator->sortableHeader('Amount', 'amount');
					echo $paginator->sortableHeader('Type', 'transaction_type');
					echo $paginator->sortableHeader('Carrier', 'carrier_number');
					echo $paginator->sortableHeader('Invoice', 'invoice_number');
					echo $paginator->sortableHeader('Bill Date', 'billing_date');
					echo $paginator->sortableHeader('User', 'user_id');
					echo $paginator->sortableHeader('Status', 'post_status');
					echo $paginator->sortableHeader('Bank', 'bank_number');
				?>
				<th>&nbsp;</th> 
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>	
			<?php	
				//massage types for O(1) lookup
				$transactionTypes = Set::combine($transactionTypes, '{n}.TransactionType.code', '{n}.TransactionType.description');
				
				foreach ($transactions as $transaction)
				{
					echo $html->tableCells(
						array(
							$html->tag('span', h($transaction['TransactionQueue']['general_ledger_code'])),
							$html->tag('span', formatDate($transaction['TransactionQueue']['transaction_date_of_service'])),
							$html->tag('span', h($transaction['TransactionQueue']['account_number'])),
							$html->tag('span', h($transaction['TransactionQueue']['general_ledger_description'])),
							array($html->tag('span', h(number_format($transaction['TransactionQueue']['amount'], 2))), array('class' => 'Right')),
							$html->tag('span', h(ifset($transactionTypes[$transaction['TransactionQueue']['transaction_type']], $transaction['TransactionQueue']['transaction_type']))),
							$html->tag('span', h($transaction['TransactionQueue']['carrier_number'])),
							$html->tag('span', h($transaction['TransactionQueue']['invoice_number'])),
							$html->tag('span', formatDate($transaction['TransactionQueue']['billing_date'])),
							$html->tag('span', h($transaction['TransactionQueue']['created_by'] !== '' ? $transaction['TransactionQueue']['created_by'] : $transaction['TransactionQueue']['user_id'])),
							$html->tag('span', h($transaction['TransactionQueue']['post_status'])),
							$html->tag('span', h($transaction['TransactionQueue']['bank_number'])),
							$html->link($html->image('iconEdit.png'), '#', array('class' => 'Edit', 'title' => 'Edit', 'escape' => false)),
							$html->link($html->image('iconDelete.png'), '#', array('class' => 'Delete', 'title' => 'Delete', 'escape' => false)) . "<input type=\"hidden\" name=\"data[TransactionQueue][id]\" value=\"{$transaction['TransactionQueue']['id']}\" />"
						),
						array(),
						array('class' => 'Alt')
					);
				}
			?>
		</tbody>
	</table>
	
	<?= $this->element('page_links') ?>
	
	<script type="text/javascript">
		Modules.TransactionQueue.View.initialize("TransactionQueueTable");
	</script>
<?php else: ?>
	There are no transactions in the queue for the specified transaction date, cash reference number, and user.
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	<?php
		echo $form->hidden('TransactionQueue.original_beginning_transaction_date_of_service', array('id' => 'TransactionQueueModuleSearchOriginalBeginningTransactionDateOfService'));
		echo $form->hidden('TransactionQueue.original_ending_transaction_date_of_service', array('id' => 'TransactionQueueModuleSearchOriginalEndingTransactionDateOfService'));
		echo $form->hidden('TransactionQueue.original_cash_reference_number', array('id' => 'TransactionQueueModuleSearchOriginalCashReferenceNumber'));
		echo $form->hidden('TransactionQueue.original_bank_number', array('id' => 'TransactionQueueModuleSearchOriginalBankNumber'));
		echo $form->hidden('TransactionQueue.original_created_by', array('id' => 'TransactionQueueModuleSearchOriginalCreatedBy'));
		echo $form->hidden('TransactionQueue.original_page', array('id' => 'TransactionQueueModuleSearchOriginalPage'));
		echo $form->hidden('TransactionQueue.original_sort_field', array('id' => 'TransactionQueueModuleSearchOriginalSortField'));
		echo $form->hidden('TransactionQueue.original_sort_direction', array('id' => 'TransactionQueueModuleSearchOriginalSortDirection'));
	?>
<?php endif; ?>

<script type="text/javascript">
	Modules.TransactionQueue.View.addBlankCashReferenceNumbersHandler();
	Modules.TransactionQueue.View.addDateFormatting();
</script>
