<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs', 
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=customers.search,invoices.for_customer,invoices.edit_l1_information,invoices.selection,invoices.ledger,file_notes.create,customers.change_status,transaction_queue.view'
	), false);
?>

<style type="text/css">		
	#PendingTransactions td {
		vertical-align: top;
	}
	
	#PendingTransactions input, #PendingTransactions select {
		margin: 0;
	}
</style>

<script type="text/javascript">

	var nextFreeTransaction = 1;
	var searchActivated = true;
	var transactionTypes = $H();
	var invoicesAreDirty = false;
	var invoicesAreDisplayed = false;
	
	<?php foreach ($transactionTypes as $type): ?>
		transactionTypes.set("<?= $type['TransactionType']['code'] ?>", { isPayment: <?= $type['TransactionType']['is_payment'] ? 'true' : 'false' ?>, isSubtracted: <?= $type['TransactionType']['is_amount_subtracted'] ? 'true' : 'false' ?> });
	<?php endforeach; ?>
	
	function onCustomerSelected(accountNumber, name)
	{
		//set the customer name field
		$("CustomerAccountNumber").value = accountNumber.toUpperCase();
		$("CustomerName").value = name;
		
		//clear the pending balance text
	//	$("PendingBalances").update("");
		
		//get the customer status
		getCustomerStatus(accountNumber);
		
		//as soon as we load invoices, we disable searching to "lock-in" the customer
		deactivateSearchControls();
	}
	
	function onCustomerNameSelected(input, li)
	{
		var account = li.select("span.AccountNumber")[0].innerHTML.strip();
		var name = $F(input);
		
		//fire the customer selected event
		onCustomerSelected(account, name);
	}
	
	function onInvoiceSelected(input, li)
	{
		var account = li.select("span.AccountNumber")[0].innerHTML.strip();
		var name = li.select("span.CustomerName")[0].innerHTML.strip();
		
		//fire the customer selected event
		onCustomerSelected(account, name);
		
		//grab the invoice number and fill out the invoice for the user
		$("PendingInvoice").value = li.innerHTML.split("<")[0];
	}
	
	function onTcnSelected(li)
	{
		var tcn = li.select("span.TcnNumber")[0].innerHTML.strip();
		var account = li.select("span.AccountNumber")[0].innerHTML.strip();
		var name = li.select("span.CustomerName")[0].innerHTML.strip();

		$("InvoiceTransactionControlNumber").value = tcn;
		
		//fire the customer selected event
		onCustomerSelected(account, name);
		
		//grab the invoice number and fill out the invoice for the user
		$("PendingInvoice").value = li.innerHTML.split("<")[0];
	}
	
	function getCustomerStatus(accountNumber)
	{
		//go find the customer's status and update the text on screen, along with a link
		//to let the user change it
		new Ajax.Request("/json/customers/status", {
			parameters: { accountNumber: accountNumber },
			onSuccess: function(transport) {
				
				var status = $("AccountStatus");
				status.update((transport.headerJSON.status == "" ? "Unknown" : (transport.headerJSON.status + (transport.headerJSON.description == "" ? "" : (" - " + transport.headerJSON.description)))) + " ");

				if (transport.headerJSON.status != "" && parseInt(transport.headerJSON.status, 10) >= 30)
				{
					status.addClassName("Exception");
				}
				else
				{
					status.removeClassName("Exception");
				}
				
				status.insert(
					new Element("a", { href: "#" })
						.update("Change")
						.observe("click", updateCustomerStatus.curry(accountNumber))
				);
			}			
		});
	}
	
	function clearCustomerStatus()
	{
		$("AccountStatus").update();
	}
	
	function updateCustomerStatus(accountNumber)
	{
		var win = mrs.createWindow(500, 400).setAjaxContent(
			"/modules/customers/changeStatus/" + encodeURIComponent(accountNumber),
			{ evalScripts: true }
		).show(true).activate();
		
		//make sure we clean up the module when it's done
		win.observe("destroyed", Modules.Customers.ChangeStatus.destroy);
	}
	
	function updateBalanceToPost()
	{
		var batchAmount = $F("TransactionQueuePaymentBatchAmount");
		var balanceToPost = $("TransactionQueueBalanceToPost");
		var referenceNumber = $F("TransactionQueueCashReferenceNumber");
		
		updatePendingTotal();
		
		//make sure we have good numbers
		if (!batchAmount.isNumeric() || batchAmount.blank() || referenceNumber.blank())
		{
			balanceToPost.value = "N/A";
			return;
		}
		
		balanceToPost.value = "Calculating...";

		//go figure out what the balance would be and update the textbox with the results
		new Ajax.Request("/json/transactionQueue/balanceToPost", {
			parameters: { 
				batchAmount: batchAmount, 
				pendingTotal: getPendingTotal(), 
				cashReferenceNumber: $F("TransactionQueueCashReferenceNumber") 
			},
			onSuccess: function(transport) {
				balanceToPost.value = "$" + parseFloat(transport.headerJSON.balance).toFixed(2);
			}			
		});
	}
	
	function updatePendingTotal()
	{
		$("PendingTotal").update(getPendingTotal().format(2));
	}
	
	function getPendingTotal()
	{
		//make sure we have rows in the table (we have to do it this way since we have an "empty" row when there are no records)
		if (mrs.tables["PendingTransactions"].fnSettings().fnRecordsTotal() == 0)
		{
			return 0;
		}
		
		//go through all rows looking for payments, and sum up the total of all of those rows		
		return $("PendingTransactions").down("tbody").select("tr").inject(0, function(total, row) { 
	
			var amount = 0;
			var type = $F(row.select("td")[2].down("select"));
			
			if (transactionTypes.any(function(t) { return t.key == type && t.value.isPayment }))
			{
				var value = $F(row.select("td")[3].down("input")).strip();
				amount = (!value.blank() && value.isNumeric()) ? parseFloat(value) : 0; 
			}
			
			return total + amount;
		});
	}
	
	function getInvoices(accountNumber)
	{
		//clear the dirty bit and mark that the invoices are displayed now
		invoicesAreDirty = false;
		invoicesAreDisplayed = true;
		
		//load the invoices module for the specified account
		Tabs.select(0);
		new Ajax.Updater($("InvoicesTab").update("Loading. Please wait..."), "/modules/invoices/forCustomer/" + accountNumber + "/clickableCarriers:1/showEditL1InformationLink:1", { evalScripts: true });
	}
	
	function clearInvoices()
	{
		//clear the dirty bit and mark that invoices aren't displayed
		invoicesAreDirty = false;
		invoicesAreDisplayed = false;
		
		//clear the tab
		$("InvoicesTab").update("Press <b>Ctrl+I</b> to load invoices for the customer.");
	}
	
	function deletePendingTransaction(event)
	{		
		//grab the specified row and delete it
		var row = event.element().up("tr");
		mrs.tables["PendingTransactions"].fnDeleteRow(row);
		
		//update our balances 
		updateBalanceToPost();
		
		event.stop();
	}
	
	function savePendingTransactions()
	{
		var table = $("PendingTransactions");
		var invoices = $H();
		var delimiter = "__";
		var autoTransactions = $("AutoTransactions");
		var hasUnfiledRows = false;
		var hasFiledRows = false;
		
		autoTransactions.update();
		
		//validate header info
		var transactionDate = $("TransactionQueueTransactionDateOfService");
		var validHeader = $$R(transactionDate) && $$D(transactionDate) && $$C(transactionDate, Date.parse($F(transactionDate)) <= new Date().getTime(), null, "cannot be in the future.");
		validHeader &= $$R($("TransactionQueueCashReferenceNumber"));
		validHeader &= $$R($("TransactionQueueBankNumber"));
		
		if (!validHeader)
		{
			return;
		}
		
		//go through the pending transactions if we have any
		if (mrs.tables["PendingTransactions"].fnSettings().fnRecordsTotal() > 0)
		{
			table.down("tbody").select("tr").each(function(row, i) {
				var cells = row.select("td");
				
				var filed = row.cells[6].down("input").checked;
				
				//stop if we have rows that aren't filed
				if (!filed)
				{
					hasUnfiledRows = true;
					throw $break;
				}
				
				hasFiledRows = true;
				
				//as long as this row isn't for a newly created invoice...
				if (!row.hasClassName("Created"))
				{
					var invoice = $F(row.cells[0].down("input"));
					var carrier = $F(row.cells[1].down("input"));
					var type = $F(row.cells[2].down("select"));
					var amount = $F(row.cells[3].down("input"));
		
					var key = invoice + delimiter + carrier;
					
					//go grab the pending balance for the invoice/carrier if we don't have it yet
					if (!invoices.containsKey(key))
					{			
						new Ajax.Request("/json/invoices/currentPendingBalance", {
							parameters: { invoiceNumber: invoice, carrierNumber: carrier },
							asynchronous: false,
							onComplete: function(transport) {
								invoices.set(key, { balance: transport.headerJSON.balance });
							}
						});
					}
				
					//apply this row's transaction amount to the pending balance
					invoices.get(key).balance -= parseFloat(transactionTypes.get(type).isSubtracted ? amount : (amount * -1));
				}
			});
		}
		
		if (hasUnfiledRows)
		{
			mrs.showDialog("There are pending rows that have not been marked as filed. Please file or remove the rows and try again.", $("SaveButton"));
			return;
		}
		else if (!hasFiledRows)
		{
			mrs.showDialog("You must have at least one pending transaction to save.", $("SaveButton"));
			return;
		}
		
		//figure out what invoices still have a balance
		var nonZeroBalances = invoices.findAll(function(record) {
			return record.value.balance.toFixed(2) != "0.00";
		});
		
		//if we have any non-zero balances, we'll show a window to let the user
		//choose which ones they want to automatically adjust to zero
		if (nonZeroBalances.length > 0)
		{
			var windowContent = new Element("div").setStyle({ padding: "5px" }).insert("<p>The following invoice/carrier combinations have non-zero balances remaining. Please select which combinations, if any, you would like to automatically create an adjustment for to zero out the balance.</p>");
			
			var balancesTable = new Element("table")
				.insert(
					new Element("tr")
						.insert(new Element("th").update(""))
						.insert(new Element("th").update("Invoice"))
						.insert(new Element("th").update("Carrier"))
						.insert(new Element("th").addClassName("Right").update("Balance"))
				);
			
			nonZeroBalances.each(function(record, i) {
				var parts = record.key.split(delimiter);
				
				balancesTable.insert(
					new Element("tr").addClassName(i % 2 == 1 ? "Alt" : "")
						.insert(new Element("td").setStyle({ verticalAlign: "middle" }).insert(new Element("input", { type: "checkbox", id: "DataAutoTransactionsInvoiceAndCarrierNumber" + i, name: "data[AutoTransactions][invoice_and_carrier_number][" + i + "]", value: record.key })))
						.insert(new Element("td").update(parts[0]))
						.insert(new Element("td").update(parts[1]))
						.insert(new Element("td").addClassName("Right").update("$" + record.value.balance.format(2)))
				);
			});
			
			var win = mrs.createWindow(400, 300);

			//this looks rediculous the way I'm inserting the table, but IE is idiotic and doesn't show the table
			//if I just insert the #$&*@#% element itself 
			windowContent.insert("<table class=\"Styled\">" + balancesTable.innerHTML + "</table>");
			windowContent.insert("<br /><br />");
			windowContent.insert(
				new Element("button", { 'id': 'PostSuggestedCreditsSubmit' }).update("Submit").addClassName("StyledButton").observe("click", function(w) { 
					w.content.select("input").each(function(c) { autoTransactions.insert(c.hide()); });
					w.close();
					postPendingTransactions(); 
				}.curry(win)) 
			);
					
			win.setContent(windowContent).show(true).activate();
			balancesTable.show();
			$('PostSuggestedCreditsSubmit').focus();
		}
		else
		{
			postPendingTransactions();
		}
	}
	
	function postPendingTransactions()
	{
		var win = mrs.showLoadingDialog();
		
		new Ajax.Request("/json/transactionQueue/postTransactions", {
			evalScripts: true, 
			parameters: Form.serialize($("PendingTransactionsForm")),
			onSuccess: function(transport) {
			
				var success = $A(transport.headerJSON.success);
				var rows = $("PendingTransactions").down("tbody").select("tr");
				var deleted = 0;
				
				//for rows that successfully saved, remove them from the table
				success.each(function(saved, i) {
					if (saved)
					{
						mrs.tables["PendingTransactions"].fnDeleteRow(rows[i]);
						deleted++;
					}
				});
				
				win.destroy();
				
				if (deleted != rows.length)
				{
					//alert user that at least one pending transaction failed
					mrs.showDialog("The remaining transactions in the table failed to save. Automatic transactions have not been created.");
				}
				else
				{
					//all pending transactions saved successfully, so reset the table
					resetPendingTransactionsTable();
				}
				
				updateBalanceToPost();
				refreshTransactionQueue();
				
			} /*, onComplete: function(t) { alert(t.responseText); }*/
		});
	}
	
	function cancelPendingTransactions()
	{
		mrs.confirmDialog("Are you sure you want to remove all pending transactions?", function() {
			resetPendingTransactionsTable();
		});
	}
	
	function resetPendingTransactionsTable()
	{		
		//clear the whole table
		mrs.tables["PendingTransactions"].fnClearTable();
		
		//reset the next free transaction
		nextFreeTransaction = 1;
		
		//re-enable the search controls
		activateSearchControls();
	}
	
	function activateSearchControls()
	{
		if (!searchActivated)
		{
			//enable and clear the controls
			$A(["CustomerAccountNumber", "CustomerName", "InvoiceInvoiceNumber", "InvoiceTransactionControlNumber"]).each(function(element) {
				element = $(element);
				element.removeClassName("ReadOnly");
				element.removeAttribute("readOnly");
				element.removeAttribute("tabIndex");
				element.value = "";
			});
			
			//clear the customer status
			clearCustomerStatus();
			
			//clear the tabs and select the invoice tab
			clearInvoices();
			$("TransactionQueueTab").update();
			$("LedgerTab").update();
			Tabs.select(0);
			
			//show the advanced search
			$("AdvancedSearchButton").show();
			
			//clear the pending fields
			$("PendingInvoice").hide().value = "";
			$("PendingCarrier").hide().value = "";
			$("PendingBalances").update();
			
			$("CustomerAccountNumber").focus();
			searchActivated = true;
		}
	}
	
	function deactivateSearchControls()
	{
		if (searchActivated)
		{
			$A(["CustomerAccountNumber", "CustomerName", "InvoiceInvoiceNumber", "InvoiceTransactionControlNumber"]).each(function(element) {
				element = $(element);
				
				element.addClassName("ReadOnly");
				element.setAttribute("readOnly", "readOnly");
				element.setAttribute("tabIndex", -1);
			});
			
			//hide the advanced search
			$("AdvancedSearchButton").hide();
			
			$("PendingInvoice").show().focus();
			$("PendingCarrier").show();
			
			searchActivated = false;
		}
	}
	
	function determineGLCode(event)
	{
		var type = $F(event.element());
		var cells = event.element().up("tr").select("td");
		
		if (type != "")
		{
			//go see if we can determine the code based on the invoice, carrier, and transaction type
			new Ajax.Request("/json/generalLedger/determineCode", {
				parameters: { 
					invoiceNumber: $F(cells[0].down("input")),
					carrierNumber: $F(cells[1].down("input")),
					transactionType: type
				},
				onSuccess: function(transport) {
					if (transport.headerJSON.code != null)
					{
						//if we were able to determine the code, update the code and 
						//description fields
						cells[4].down("input").value = transport.headerJSON.code;
						cells[5].down("input").value = transport.headerJSON.description;
					}
				}			
			});
		}
		
		event.stop();
	}
	
	function filePendingTransaction(event)
	{
		var checkbox = event.element();
		var row = checkbox.up("tr");
		
		var invoice = $F(row.cells[0].down("input"));
		var carrier = $F(row.cells[1].down("input"));
		var type = row.cells[2].down("select");
		var amount = row.cells[3].down("input");
		var glCode = row.cells[4].down("input");
		var copyLink = row.cells[8].down("a");
		var rowErrors = $A();
		var hasErrors = false;
		
		//validate the row's fields 
		if (checkbox.checked)
		{
			var valid = $$R(type, "Transaction Type")
			valid &= $$R(amount, "Amount") && $$N(amount, true, "Amount");
			valid &= $$R(glCode, "G/L Code");
			
			//verify GL code
			new Ajax.Request("/json/generalLedger/verify", {
				parameters: { code: $F(glCode) },
				asynchronous: false,
				onComplete: function(transport) {
					if (!transport.headerJSON.exists)
					{
						//fake validation to force an error
						$$P(glCode, /^foo$/, "G/L Code", "does not contain a valid G/L code.");
						valid = false;
					}
				}
			});
			
			//charges can only be made on active carriers, so we need to check the status
			if ($F(type) == "<?= $chargeTransactionType ?>")
			{
				var isActive = false;
				
				new Ajax.Request("/json/customerCarriers/checkStatus", {
					parameters: { accountNumber: $F("CustomerAccountNumber"), carrierNumber: carrier },
					asynchronous: false,
					onComplete: function(transport) {
						isActive = transport.headerJSON.isActive;
					}
				});
				
				if (!isActive)
				{
					//fake validation to force an error
					$$P(type, /^foo$/, "Transaction Type", "cannot have a charge made on an inactive carrier.");
					valid = false;
				}
			}
				
			if (!valid)
			{
				checkbox.checked = false;
				return;	
			}
		}
		
		//toggle textboxes to readonly depending on the checked status
		row.select("input[type=text]").each(function(input) {
			if (checkbox.checked)
			{
				input.addClassName("ReadOnly");
				input.setAttribute("readOnly", "readOnly")
				input.setAttribute("tabIndex", -1);
			}
			else
			{
				input.removeClassName("ReadOnly");
				input.removeAttribute("readOnly");
				input.removeAttribute("tabIndex");
			}
		});
		
		//the select field is special because the readonly attribute doesn't work for selects.
		//So we actually have to create a readonly textbox containing the value and hide the select.
		row.select("select").each(function(combo) {
			if (checkbox.checked)
			{
				var input = new Element("input", { type: "text"}).addClassName("ReadOnly");
				input.setAttribute("readOnly", "readOnly");
				input.setAttribute("tabIndex", -1);
				
				input.value = combo.options[combo.selectedIndex].innerHTML;
				combo.insert({ after: input }).hide();
			}
			else
			{
				combo.show().next().remove();
			}
		});

		//if the user filed the row...
		if (checkbox.checked)
		{			
			//activate the copy link 
			copyLink.setStyle({ visibility: "visible" });
			
			//format the amount
			amount.value = parseFloat(amount.value).toFixed(2);
			
			//take the user to the save button
			var saveButton = $("SaveButton");
			saveButton.focus();
		}
		else
		{
			//if they un-filed the row, remove the copy link
			copyLink.setStyle({ visibility: "hidden" });
		}
	}
	
	function createPendingTransaction(invoiceNumber, carrierNumber)
	{
		if (invoiceNumber.blank() || carrierNumber.blank())
		{
			mrs.showDialog("You must specify both an invoice number and carrier number.", $("PendingCarrier"));
			return;
		}
					
		var exists;
		var verified;
		var carriers;
		
		new Ajax.Request("/json/invoices/verify", {
			parameters: { accountNumber: $F("CustomerAccountNumber"), invoiceNumber: invoiceNumber, carrierNumber: carrierNumber },
			asynchronous: false,
			onSuccess: function(transport) {
				exists = transport.headerJSON.exists;
				verified = transport.headerJSON.verified;
				carriers = $A(transport.headerJSON.carriers);
			}
		});
		
		if (!verified)
		{
			mrs.showDialog("The specified carrier is not valid for this invoice. Valid carriers are: " + carriers.invoke("escapeHTML").join(", "), $("PendingCarrier"));
			return;
		}
		else if (!exists)
		{
			mrs.confirmDialog("The specified invoice does not exist. Would you like to create it?", function() {
				createPendingTransactionRow(invoiceNumber, carrierNumber, true);
			});
			
			return;
		}
		
		createPendingTransactionRow(invoiceNumber, carrierNumber, false);
	}
	
	function createPendingTransactionRow(invoiceNumber, carrierNumber, isNewInvoice)
	{		
		//create the content for each cell and add the row to the table
		var indexes = mrs.tables["PendingTransactions"].fnAddData([
			invoiceNumber + "<input type=\"hidden\" name=\"data[TransactionQueue][invoice_number][" + nextFreeTransaction + "]\" value=\"" + invoiceNumber + "\" />",
			carrierNumber + "<input type=\"hidden\" name=\"data[TransactionQueue][carrier_number][" + nextFreeTransaction + "]\" value=\"" + carrierNumber + "\" />",
			$("TransactionTypesContainer").innerHTML,
			"<input type=\"text\" name=\"data[TransactionQueue][amount][" + nextFreeTransaction + "]\" class=\"Text75\" />",
			"<input type=\"text\" name=\"data[TransactionQueue][general_ledger_code][" + nextFreeTransaction + "]\" class=\"Text35\" />",
			"<input type=\"text\" name=\"data[TransactionQueue][general_ledger_description][" + nextFreeTransaction + "]\" class=\"Text250\" />",
			"<input type=\"checkbox\" />",
			"<a href=\"#\"><img src=\"/img/iconDelete.png\" title=\"Delete\" /></a>",
			"<a href=\"#\" style=\"visibility: hidden;\"><img src=\"/img/iconCopy.png\" title=\"Copy\" /></a>"
		]);

		//grab the newly inserted row		
		var row = $(mrs.tables["PendingTransactions"].fnSettings().aoData[indexes[0]].nTr);
		var cells = row.select("td");
		
		//wire up event handlers to the controls in the row
		cells[2].select("select")[0].observe("change", function(e) { updateBalanceToPost(); determineGLCode(e); }).name = "data[TransactionQueue][transaction_type][" + nextFreeTransaction + "]";
		cells[3].select("input")[0].observe("change", updateBalanceToPost);
		cells[4].select("input")[0].observe("change", updateGlCodeDescription);
		cells[6].select("input")[0].observe("click", filePendingTransaction);
		cells[7].select("a")[0].observe("click", deletePendingTransaction);
		cells[8].select("a")[0].observe("click", copyPendingTransaction);
		
		//for new invoices make sure we mark them for later
		if (isNewInvoice)
		{
			row.addClassName("Created");
		}
		
		//adjust the table size for scrolling
		mrs.tables["PendingTransactions"].fnAdjustColumnSizing();
		mrs.tables["PendingTransactions"].fnDraw();
		
		cells[2].select("select")[0].focus();
		nextFreeTransaction++;
		
		return row;
	}
	
	function updateGlCodeDescription(event)
	{
		var code = $F(event.element());
		
		if (!code.blank())
		{
			new Ajax.Request("/json/generalLedger/description", {
				parameters: { code: code },
				onSuccess: function(transport) {
					event.element().up("td").next().down("input").value = transport.headerJSON.description;
				}
			});
		}
	}
	
	var copiedPendingTransactionRow = null;
	
	function copyPendingTransaction(event) {
		
		//keep track of the row the user is wanting to copy 
		copiedPendingTransactionRow = event.element().up("tr");
		var carrier = copiedPendingTransactionRow.down("td").next("td").down("input").value;
		
		//load the module to allow the user to select a series of invoices
		mrs.createWindow(500, 400).setAjaxContent(
			"/modules/invoices/selection/" + encodeURIComponent($F("CustomerAccountNumber")) + "/" + encodeURIComponent(carrier),
			{ evalScripts: true }
		).show(true).activate();
		
		event.stop();
	}
	
	var currentTransactionQueueParameters = { beginDate: null, endDate: null, cashReferenceNumber: null, bankNumber: null, user: "<?= $session->read('user') ?>" };
	var showBlankCashReferenceNumbers = false;
	
	function refreshTransactionQueue()
	{
		var tab = $("TransactionQueueTab");
		
		//don't do anything if the tab isn't visible
		if (!tab.visible())
		{
			return;
		}
		
		//if we never had a search on the queue yet, let's default the start and end dates and cash ref
		//to what is specified on the current form, if any
		if (currentTransactionQueueParameters.beginDate == null)
		{
			var date = $F("TransactionQueueTransactionDateOfService");
			currentTransactionQueueParameters.beginDate = !date.blank() && date.isDate() ? date : "";
			currentTransactionQueueParameters.endDate = currentTransactionQueueParameters.beginDate;
			currentTransactionQueueParameters.cashReferenceNumber = $F("TransactionQueueCashReferenceNumber");
			currentTransactionQueueParameters.bankNumber = $F("TransactionQueueBankNumber");
		}
		
		tab.update("Loading. Please wait...");
		
		new Ajax.Updater(tab, "/modules/transactionQueue/view" +
				(!currentTransactionQueueParameters.beginDate.blank() ? ("/beginDate:" + encodeURIComponent(currentTransactionQueueParameters.beginDate.toDatabaseDateString())) : "") +
				(!currentTransactionQueueParameters.endDate.blank() ? ("/endDate:" + encodeURIComponent(currentTransactionQueueParameters.endDate.toDatabaseDateString())) : "") +
				(!currentTransactionQueueParameters.cashReferenceNumber.blank() ? ("/cashref:" + encodeURIComponent(currentTransactionQueueParameters.cashReferenceNumber)) : "") +
				(!currentTransactionQueueParameters.bankNumber.blank() ? ("/bankNumber:" + encodeURIComponent(currentTransactionQueueParameters.bankNumber)) : "") +
				"/showblank:" + (showBlankCashReferenceNumbers ? "1" : "0") +
				(!currentTransactionQueueParameters.user.blank() ? ("/user:" + encodeURIComponent(currentTransactionQueueParameters.user)) : ""), {
			evalScripts: true
		});
	}
	
	function createEfn(event)
	{
		if (searchActivated)
		{
			mrs.showDialog("You must select a customer.");
		}
		else
		{
			var win = mrs.createWindow(500, 400).setAjaxContent(
				"/modules/fileNotes/create/" + encodeURIComponent($F("CustomerAccountNumber")),
				{ 
					parameters: { showTcnFields: true },
					evalScripts: true
				}
			).show(true).activate();
			
			//wire up the window to close when the user saves the note, and then unregister the event so it
			//doesn't stick around (otherwise we could register it multiple times since it's a global event, which
			//would be bad because it would fire once for each time it was registered)
			var f = function(event) { win.destroy(); };
			document.observe("fileNote:postCompleted", f);
			win.observe("destroyed", function() { document.stopObserving("fileNote:postCompleted", f); });
		}
				
		event.stop();
	}
	
	var ctrlPressed = false;
	
	document.observe("dom:loaded", function() {
		// Wire up:
		//		Ctrl+Home - will take you to enter a new invoice for the current user
		//		Ctrl+I - will load invoices for a customer if one is chosen
		document.observe("keydown", function(event) {
			if (event.keyCode == Tabs.CTRL_KEY) {
				ctrlPressed = true;
				event.stop();
			}
			
			if (ctrlPressed && event.keyCode == Event.KEY_HOME)
			{
				//take the user back up to the pending invoice field 
				var pending = $("PendingInvoice");
				pending.activate();
				event.stop();
			}
			else if (ctrlPressed && String.fromCharCode(event.keyCode).toLowerCase() == "i")
			{
				if (!searchActivated)
				{
					getInvoices($F("CustomerAccountNumber"));
				}
				else
				{
					mrs.showDialog("You must select an account before you can load invoices.", $("CustomerAccountNumber"));
				}
				
				event.stop();
			}
		});
		
		document.observe("keyup", function(event) {
			if (event.keyCode == Tabs.CTRL_KEY) {
				ctrlPressed = false;
				event.stop();
			}
		});
		
		mrs.makeScrollable("PendingTransactions", { 
			bAutoWidth: false,
			bSort: false,
			sScrollY: "150px", 
			oLanguage: { sEmptyTable: "Specify an invoice and carrier to create a transaction." }, 
			aoColumns: [{sWidth: "100px"}, {sWidth: "100px"}, {sWidth: "150px"}, {sWidth: "100px"}, {sWidth: "50px"}, {sWidth: "272px"}, {sWidth: "25px"}, {sWidth: "25px"}, {sWidth: "25px"}] });
		
		//wire up fields to response to changes for post balance and the transaction queue
		$("TransactionQueuePaymentBatchAmount").observe("change", updateBalanceToPost);
		$("TransactionQueueCashReferenceNumber").observe("change", updateBalanceToPost);
		
		//be nice with dates
		mrs.bindDatePicker("TransactionQueueTransactionDateOfService");
		
		//wire up the account number field to find the customer when they press enter
		$("CustomerAccountNumber").observe("keypress", function(event) {
			if (event.keyCode == Event.KEY_RETURN)
			{
				var accountNumber = $F(event.element());
				
				new Ajax.Request("/json/customers/name", {
					parameters: { accountNumber: accountNumber },
					onComplete: function(transport) {
						if (transport.headerJSON.name != false)
						{
							onCustomerSelected(accountNumber, transport.headerJSON.name);
						}
						else
						{
							mrs.showDialog("Customer not found.", event.element());
						}
					}
				});
			
				event.stop();
			}
		});
		
		//wire up the advanced search module
		$("AdvancedSearchButton").observe("click", function() {
			mrs.createWindow(900, 400).setAjaxContent(
				"/modules/customers/search/",
				{ evalScripts: true }
			).show(true).activate();
		})
		
		//wire up buttons
		$("SaveButton").observe("click", savePendingTransactions);
		$("CancelButton").observe("click", cancelPendingTransactions);
		$("CreateEfnButton").observe("click", createEfn);
		
		var pendingInvoice = $("PendingInvoice");
		
		//wire up the pending invoice textbox to grab carrier balances on blur
		pendingInvoice.observe("blur", function(event) {
		
			var pendingBalances = $("PendingBalances").update("Please wait. Loading balances...");
			
			new Ajax.Request("/json/invoices/balances", {
				parameters: { 
					accountNumber: $F("CustomerAccountNumber"),
					invoiceNumber: $F(pendingInvoice)
				},
				onSuccess: function(transport) {
					var balances = transport.headerJSON;
					var template = new Template("<span class=\"Carrier#{i}\"><b>#{carrier}:</b> $#{balance}</span>")
					var output = "Invoice not found on account.";
					
					if (balances.id != false)
					{
						output = "<b>Date:</b> " + Date.normalize(balances.date_of_service);
						output += " <b>Invoice Amount:</b> $" + balances.amount.format(2);
						
						var carriers = $R(1, 3).collect(function(i) {
							return balances["carrier_" + i + "_code"].blank() ? "" : template.evaluate({ i: i, carrier: balances["carrier_" + i + "_code"], balance: balances["carrier_" + i + "_balance"].format(2)});
						});
						
						output += " " + carriers.join(" ");
					}
						
					pendingBalances.update(output);
				}
			});
		});
		
		var pendingCarrier = $("PendingCarrier");
		
		//wire up the pending carrier textbox to create a new record when the enter key is pressed
		pendingCarrier.observe("keypress", function(event) { 
			if (event.keyCode == Event.KEY_RETURN)
			{
				pendingInvoice.value = pendingInvoice.value.toUpperCase();
				pendingCarrier.value = pendingCarrier.value.toUpperCase();
				
				createPendingTransaction($F(pendingInvoice), $F(pendingCarrier)); 
				event.stop();
			}
		});
		
		//initialize the invoices tab
		clearInvoices();
		
		mrs.fixAutoCompleter("CustomerAccountNumber");
		mrs.fixAutoCompleter("CustomerName");
		mrs.fixAutoCompleter("InvoiceInvoiceNumber");
		mrs.fixAutoCompleter("InvoiceTransactionControlNumber");
		
		//wire up the transaction queue to refresh whenever its tab page is clicked on, and wire up
		//the invoices tab to refresh if it is marked dirty. We have to do things this way because the
		//scrollable tables in the tabs don't create themselves correctly if their container isn't visible
		Tabs.changeCallback = function(page) { 
			if (page.id == "TransactionQueueTab") 
			{ 
				refreshTransactionQueue(); 
			} 
			else if (page.id == "InvoicesTab" && !searchActivated && invoicesAreDirty && invoicesAreDisplayed)
			{
				getInvoices($F("CustomerAccountNumber"));
			}
		};
	});
	
	document.observe("customer:accountSelected", function(event) {
		//close the search dialog
		UI.defaultWM.windows()[0].close();
		
		//choose the selected customer
		onCustomerSelected(event.memo.accountNumber, event.memo.name);
	});
	
	document.observe("invoice:carrierSelected", function(event) {
		createPendingTransactionRow(event.memo.invoice, event.memo.carrier, false);
	});
	
	document.observe("invoice:ledgerRequested", function(event) {
		//when there is a request to show a ledger for an invoice, load the ledger
		//module for it in the 3rd tab
		Tabs.select(2);
		new Ajax.Updater($("LedgerTab").update("Loading. Please wait..."), "/modules/invoices/ledger/" + encodeURIComponent($F("CustomerAccountNumber")) + "/0/1/invoiceNumber:" + encodeURIComponent(event.memo.invoice), { evalScripts: true });
	});
	
	document.observe("invoice:ledgerFilter", function(event) {
		url = "/modules/invoices/ledger/" + event.memo.accountNumber + "/0/0";
		
		if (event.memo.invoiceNumber != "")
		{
			url += "/invoiceNumber:" + event.memo.invoiceNumber;
		}
		
		if (event.memo.carrierNumber != "")
		{
			url += "/carrierNumber:" + event.memo.carrierNumber;
		}
		
		if (event.memo.transactionType != "")
		{
			url += "/transactionType:" + event.memo.transactionType;
		}
		
		new Ajax.Updater($("LedgerTab").update("Loading. Please wait..."), url, { evalScripts: true });
	});
	
	var editedL1Row = null;
	
	document.observe("invoice:editL1Requested", function(event) {
	
		//keep track of the row being edited
		editedL1Row = event.memo.row;
		
		//when there is a request to edit L1 info for an invoice, load the L1 module in a window
		var win = mrs.createWindow(500, 400).setAjaxContent(
			"/modules/invoices/editL1Information/" + encodeURIComponent(event.memo.invoice),
			{ evalScripts: true }
		).show(true).activate();
		
		//make sure we clean up the module when it's done
		win.observe("destroyed", Modules.Invoices.EditL1Information.destroy);
	});
	
	document.observe("invoice:l1InformationUpdated", function(event) {
		//close the window
		UI.defaultWM.windows()[0].close();
		
		//update the L1 info of the row whose invoice was updated
		Modules.Invoices.ForCustomer.updateL1Information(editedL1Row, event.memo.status, event.memo.date, null);
		editedL1Row = null;
	});
	
	document.observe("invoice:selectionFinished", function(event) {
		//close the window
		UI.defaultWM.windows()[0].close();
		
		//grab the data we need from the copied row
		var cells = copiedPendingTransactionRow.select("td");
		var carrier = $F(cells[1].down("input"));
		var type = cells[2].down("select").selectedIndex;
		var amount = $F(cells[3].down("input"));
		var glCode = $F(cells[4].down("input"));
		var description = $F(cells[5].down("input"));
		
		//create copies of the row
		event.memo.invoices.each(function(invoice) {
			var row = createPendingTransactionRow(invoice, carrier, copiedPendingTransactionRow.hasClassName("Created"));
			
			var cells = row.select("td");
			cells[2].down("select").selectedIndex = type;
			cells[3].down("input").value = amount;
			cells[4].down("input").value = glCode;
			cells[5].down("input").value = description;
		});
		
		//clear the reference to the copied row
		copiedPendingTransactionRow = null;
		
		updateBalanceToPost();
	});
	
	document.observe("customer:statusChanged", function(event) {
		UI.defaultWM.windows()[0].close();
		getCustomerStatus($F("CustomerAccountNumber"));
	});
	
	document.observe("transactionQueue:transactionDeleted", function(event) {
		updateBalanceToPost();
	});
	
	document.observe("transactionQueue:transactionUpdated", function(event) {
		updateBalanceToPost();
	});
	
	document.observe("transactionQueue:searchPerformed", function(event) {
		//capture the search that was performed so when we refresh the transaction queue we can
		//remember what they were on
		currentTransactionQueueParameters = event.memo;
	});
	
	document.observe("transactionQueue:showBlankCashReferenceNumbersChanged", function(event) {
		//capture the current settingso when we refresh the transaction queue we can
		//remember what their option was
		showBlankCashReferenceNumbers = event.memo.showBlankCashReferenceNumbers;
	});
</script>

<?= $form->create('TransactionQueue', array('url' => '/transactionQueue/postTransactions', 'id' => 'PendingTransactionsForm', 'class' => 'Horizontal')) ?>

<div class="GroupBox">
	<h2>Batch Header</h2>
	
	<div class="Content">
		<div class="FormColumn">
			<?= $form->input('bank_number', array('options' => $banks, 'empty' => '', 'class' => 'Text125')) ?>
		</div>
		
		<div class="FormColumn">
			<?= $form->input('transaction_date_of_service', array('type' => 'text', 'label' => 'Transaction Date')) ?>
		</div>
		
		<div class="FormColumn">
			<?= $form->input('payment_batch_amount') ?>
		</div>
		
		<div class="FormColumn">
			<?= $form->input('cash_reference_number') ?>
		</div>
		
		<div class="FormColumn">
			<?= $form->input('balance_to_post', array('readonly' => 'readonly', 'tabindex' => -1, 'class' => 'ReadOnly')) ?>
		</div>	
		
		<br class="ClearBoth" />
	</div>
</div>

<div class="GroupBox">
	<h2>Transactions</h2>
	
	<div class="Content">
	
		<div class="FormColumn">
			<?= $form->input('Customer.account_number') ?>
		</div>
		
		<div class="FormColumn">
			<label>Account Name</label>
			<?= $ajax->autoComplete('Customer.name', '/ajax/customers/autoCompleteByName', array('style' => 'width: 150px;', 'afterUpdateElement' => 'onCustomerNameSelected', 'minChars' => 4)) ?>
			<?= $html->link($html->image('iconSearch.png'), '#', array('id' => 'AdvancedSearchButton', 'title' => 'Advanced Search', 'escape' => false, 'style' => 'vertical-align: top')) ?>
		</div>
		
		<div class="FormColumn">
			<label>Invoice</label>
			<?= $ajax->autoComplete('Invoice.invoice_number', '/ajax/invoices/autoComplete', array('afterUpdateElement' => 'onInvoiceSelected', 'minChars' => 4)) ?>
		</div>
		
		<div class="FormColumn">
			<label>TCN</label>
			<?= $ajax->autoComplete('Invoice.transaction_control_number', '/ajax/invoices/autoCompleteByTcn', array('updateElement' => 'onTcnSelected', 'minChars' => 4)) ?>
		</div>
		
		<div class="FormColumn">
			<label>Account Status</label>
			<span id="AccountStatus"></span>
		</div>
		
		<br style="clear: left" />
		
		<table>
			<tr>
				<td style="width: 100px;"><input type="text" id="PendingInvoice" class="Text75" style="margin: 0; display: none;" /></td>
				<td style="width: 100px;"><input type="text" id="PendingCarrier" class="Text75" style="margin: 0; display: none;" /></td>
				<td id="PendingBalances"></td>
			</tr>
		</table>
		
		<table id="PendingTransactions" class="Styled">
			<thead>
				<tr>
					<th>Invoice</th>
					<th>Carrier</th>
					<th>Trans Type</th>
					<th>Amount</th>
					<th>G/L Code</th>
					<th>Description</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody tabindex="-1">
			</tbody>
		</table>
	</div>
</div>
<div style="margin-top: 5px; font-size: 12px;">
	Pending Total: <span id="PendingTotal">0.00</span>
</div>

<br /><br />

<!-- will hold form fields for auto-created transactions -->
<div id="AutoTransactions"></div>

<?php
	echo $form->button('Save', array('id' => 'SaveButton')) . ' ';
	echo $form->button('Cancel', array('id' => 'CancelButton')) . ' ';
	echo $form->button('Create eFN', array('id' => 'CreateEfnButton', 'class' => 'StyledButton'));
	
	echo $form->end();
?>

<br /><br />

<ul class="TabStrip">
	<li class="Selected"><a href="#">Invoices</a></li>
	<li><a href="#">Transaction Queue</a></li>
	<li><a href="#">Ledger</a></li>
</ul>

<div class="TabContainer">
	<!-- Invoices-->
	<div id="InvoicesTab" class="TabPage">
	</div>
	
	<!-- Transaction Queue -->
	<div id="TransactionQueueTab" class="TabPage" style="display: none;">
	</div>
	
	<!-- Ledger -->
	<div id="LedgerTab" class="TabPage" style="display: none;">
	</div>
</div>

<!-- This is hidden and used to inject the transaction types into a pending transaction row -->
<div id="TransactionTypesContainer" style="display: none;">
	<select>
		<option value=""></option>
		<?php
			foreach ($transactionTypes as $type)
			{
				echo '<option value="' . h($type['TransactionType']['code']) . '">' . h($type['TransactionType']['code']) . ' - ' . h($type['TransactionType']['description']) . '</option>';
			}
		?>
	</select>
</div>