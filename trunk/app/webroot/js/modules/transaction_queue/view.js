Modules.TransactionQueue.View = {
	chargeTransactionType: 0,
	paymentTransactionTypes: $A(),
	tableID: null,
	
	initialize: function(table) {
		
		table = $(table);
		
		//add handlers 
		Modules.TransactionQueue.View.addHandlers(table);
		
		//look up the charge transaction type
		new Ajax.Request("/json/transactionTypes/chargeTransactionType", {
			onSuccess: function(transport) {
				Modules.TransactionQueue.View.chargeTransactionType = transport.headerJSON.code;
			}
		});
		
		//look up payment transaction types
		new Ajax.Request("/json/transactionTypes/paymentTransactionTypes", {
			onSuccess: function(transport) {
				Modules.TransactionQueue.View.paymentTransactionTypes = transport.headerJSON.codes;
			}
		});
		
		//wire up the table to be scrollable, hanging on to the ID for later so we can reference the scrollable instance
		mrs.makeScrollable(table, { bSort: false });
		Modules.TransactionQueue.View.tableID = table.id;
	},
	
	addHandlers: function(table) {
		Modules.TransactionQueue.View.addEditHandlers(table);
		Modules.TransactionQueue.View.addDeleteHandlers(table);

		$("TransactionQueueModuleBatchPostButton").observe("click", Modules.TransactionQueue.View.batchPost);
	},
	
	addEditHandlers: function(table) {
		$(table).select("tbody a.Edit").invoke("observe", "click", Modules.TransactionQueue.View.onEdit);
	},
	
	addDeleteHandlers: function(table) {
		$(table).select("tbody a.Delete").invoke("observe", "click", Modules.TransactionQueue.View.onDelete);
	},
	
	addBlankCashReferenceNumbersHandler: function() {
		$("TransactionQueueModuleSearchShowBlankCashReferenceNumbers").observe("click", function(event) {

			//let the host know the setting changed
		 	Event.fire(event.element(), "transactionQueue:showBlankCashReferenceNumbersChanged", { 
				showBlankCashReferenceNumbers: event.element().checked
			});
			
			//perform a search again now that the setting changed
			Modules.TransactionQueue.View.search(true);
		});
	},
	
	addDateFormatting: function() {
		mrs.bindDateFormatting(
			"TransactionQueueModuleSearchBeginningTransactionDateOfService", 
			"TransactionQueueModuleSearchEndingTransactionDateOfService"
		);
	},
	
	updateOriginalPagerVariables: function(source) {
		
		//this callback is used when hitting paginator links or sort column headers. This keeps our hidden
		//fields that keep track of our pager variables in sync
		var pattern = /([a-zA-Z_]+):([a-zA-Z0-9_]+)/g;
		
		while (arg = pattern.exec(source.href))
		{
			if (arg[1] == "page") 
			{
				$("TransactionQueueModuleSearchOriginalPage").value = arg[2];
			}
			else if (arg[1] == "sort")
			{
				$("TransactionQueueModuleSearchOriginalSortField").value = arg[2];
			}
			else if (arg[2] == "direction")
			{
				$("TransactionQueueModuleSearchOriginalSortDirection").value = arg[2];
			}
		}
		
		$("TransactionQueueModuleContainer").update("Loading. Please wait...");
	},
	
	search: function(useOriginal) {
		
		//grab the fields we need to search with
		var beginDate = useOriginal ? $F("TransactionQueueModuleSearchOriginalBeginningTransactionDateOfService") : $F("TransactionQueueModuleSearchBeginningTransactionDateOfService");
		var endDate = useOriginal ? $F("TransactionQueueModuleSearchOriginalEndingTransactionDateOfService") : $F("TransactionQueueModuleSearchEndingTransactionDateOfService");
		var reference = useOriginal ? $F("TransactionQueueModuleSearchOriginalCashReferenceNumber") : $F("TransactionQueueModuleSearchCashReferenceNumber");
		var user = useOriginal ? $F("TransactionQueueModuleSearchOriginalCreatedBy") : $F("TransactionQueueModuleSearchCreatedBy");
		var bank = useOriginal ? $F("TransactionQueueModuleSearchOriginalBankNumber") : $F("TransactionQueueModuleSearchBankNumber");
		var showBlank = $("TransactionQueueModuleSearchShowBlankCashReferenceNumbers").checked;
		var page = useOriginal ? $F("TransactionQueueModuleSearchOriginalPage") : "1";
		var sortField = useOriginal ? $F("TransactionQueueModuleSearchOriginalSortField") : "";
		var sortDirection = useOriginal ? $F("TransactionQueueModuleSearchOriginalSortDirection") : "asc";
		
		var container = $("TransactionQueueModuleContainer");
		
		container.update("Loading. Please wait...");
		
		//refresh the form
		new Ajax.Updater(container, "/modules/transactionQueue/view" +
				(!beginDate.blank() ? ("/beginDate:" + encodeURIComponent(beginDate.toDatabaseDateString())) : "") +
				(!endDate.blank() ? ("/endDate:" + encodeURIComponent(endDate.toDatabaseDateString())) : "") +
				(!reference.blank() ? ("/cashref:" + encodeURIComponent(reference)) : "") +
				(!bank.blank() ? ("/bankNumber:" + encodeURIComponent(bank)) : "") +
				"/showblank:" + (showBlank ? "1" : "0") +
				(!user.blank() ? ("/user:" + encodeURIComponent(user)) : "") +
				"/isPostback:1" +
				"/page:" + encodeURIComponent(page) + 
				"/sort:" + encodeURIComponent(sortField) + 
				"/direction:" + encodeURIComponent(sortDirection), {
			evalScripts: true
		});
		
		//if we didn't use the original data to search, fire an event and update the original filters
		if (!useOriginal)
		{
			Event.fire(container, "transactionQueue:searchPerformed", { 
				beginDate: beginDate,
				endDate: endDate,
				cashReferenceNumber: reference,
				bankNumber: bank,
				user: user
			});
			
			$("TransactionQueueModuleSearchOriginalBeginningTransactionDateOfService").value = beginDate;
			$("TransactionQueueModuleSearchOriginalEndingTransactionDateOfService").value = endDate;
			$("TransactionQueueModuleSearchOriginalCashReferenceNumber").value = reference;
			$("TransactionQueueModuleSearchOriginalBankNumber").value = bank;
			$("TransactionQueueModuleSearchOriginalCreatedBy").value = user;
			$("TransactionQueueModuleSearchOriginalPage").value = page;
			$("TransactionQueueModuleSearchOriginalSortField").value = sortField;
			$("TransactionQueueModuleSearchOriginalSortDirection").value = sortDirection;
		}
	},
	
	onEdit: function(event) {
		
		var row = event.element().up("tr");
		var cells = row.select("td");
		
		//add editable controls for all the fields
		cells[0].insert(new Element("input", { type: "text", name: "data[TransactionQueue][general_ledger_code]", value: cells[0].down("span").hide().innerHTML }).addClassName("Text35"));
		
		var dateOfService = new Element("input", { type: "text", name: "data[TransactionQueue][transaction_date_of_service]", value: cells[1].down("span").hide().innerHTML }).addClassName("Text65");
		mrs.bindDateFormatting(dateOfService);
		cells[1].insert(dateOfService);
		
		cells[3].insert(new Element("input", { type: "text", name: "data[TransactionQueue][general_ledger_description]", value: cells[3].down("span").hide().innerHTML }).addClassName("Text100"));
		cells[4].insert(new Element("input", { type: "text", name: "data[TransactionQueue][amount]", value: cells[4].down("span").hide().innerHTML.gsub(/,/, "") }).addClassName("Text50"));
		
		var selectedType = cells[5].down("span").hide().innerHTML;
		cells[5].insert($("TransactionQueueModuleTransactionTypesContainer").innerHTML).select("option").any(function(op, i) { 
			if (op.innerHTML.split(" - ")[1] == selectedType) 
			{ 
				op.up("select").selectedIndex = i;
				return true; 
			} 
			return false; 
		});
		cells[5].down("select").name = "data[TransactionQueue][transaction_type]";
		
		cells[6].insert(new Element("input", { type: "text", name: "data[TransactionQueue][carrier_number]", value: cells[6].down("span").hide().innerHTML }).addClassName("Text50"));
		cells[7].insert(new Element("input", { type: "text", name: "data[TransactionQueue][invoice_number]", value: cells[7].down("span").hide().innerHTML }).addClassName("Text50"));
		
		var billingDate = new Element("input", { type: "text", name: "data[TransactionQueue][billing_date]", value: cells[8].down("span").hide().innerHTML }).addClassName("Text65");
		mrs.bindDateFormatting(billingDate);
		cells[8].insert(billingDate);
		
		var selectedBank = cells[11].down("span").hide().innerHTML;
		cells[11].insert($("TransactionQueueModuleBanksContainer").innerHTML).select("option").any(function(op, i) { 
			if (op.innerHTML == selectedBank) 
			{ 
				op.up("select").selectedIndex = i;
				return true; 
			} 
			return false; 
		});
		cells[11].down("select").name = "data[TransactionQueue][bank_number]";
		
		//hide the edit and delete buttons
		cells[12].down("a").hide();
		cells[13].down("a").hide();
		
		//create save and cancel buttons
		cells[12].insert(new Element("a", { href: "#" }).addClassName("Save").insert(new Element("img", { src: "/img/iconSave.png" })).observe("click", Modules.TransactionQueue.View.onSave));
		cells[13].insert(new Element("a", { href: "#" }).addClassName("Cancel").insert(new Element("img", { src: "/img/iconCancel.png" })).observe("click", Modules.TransactionQueue.View.onCancel));
		
		//resize the scrollable table to fit the extra buttons and scroll to the row being edited
		var scrollable = mrs.tables[Modules.TransactionQueue.View.tableID];
		scrollable.fnAdjustColumnSizing();
		jQuery(scrollable.fnSettings().nTable.parentNode).scrollTo(row);
		
		event.stop();
	},
	
	onSave: function(event) {
		var row = event.element().up("tr");
		var cells = row.select("td");
		
		var glCode = cells[0].down("input");
		var dateOfService = cells[1].down("input");
		var glDescription = cells[3].down("input");
		var amount = cells[4].down("input");
		var type = cells[5].down("select");
		var carrier = cells[6].down("input");
		var invoice = cells[7].down("input");
		var billingDate = cells[8].down("input");
		var bank = cells[11].down("select");
		var id = cells[13].down("input");
		
		//reset validation fields that may only conditionally be tested
		Validation.clearError(bank);
		Validation.clearError(type);
		
		//validate the fields
		var valid = $$R(dateOfService, "Date of Service") && $$D(dateOfService, "Date of Service") && $$C(dateOfService, Date.parse($F(dateOfService)) <= new Date().getTime(), "Date of Service", "cannot be in the future.");
		valid &= $$R(glCode, "G/L Code");
		valid &= $$R(amount, "Amount") && $$N(amount, true, "Amount");
		valid &= $$R(carrier, "Carrier");
		valid &= $$R(invoice, "Invoice");
		valid &= $$R(billingDate, "Billing Date") && $$D(billingDate, "Billing Date");
		
		//verify GL code
		new Ajax.Request("/json/generalLedger/verify", {
			parameters: { code: $F(glCode) },
			asynchronous: false,
			onSuccess: function(transport) {
				if (!transport.headerJSON.exists)
				{
					//fake validation to force an error
					$$P(glCode, /^foo$/, "G/L Code", "does not contain a valid G/L code.");
					valid = false;
				}
			}
		});
		
		//bank is required on payments
		if (Modules.TransactionQueue.View.paymentTransactionTypes.any(function(t) { return t == $F(type); }))
		{
			valid &= $$R(bank, "Bank", "is required on a payment");
		}

		//verify carrier/invoice combination
		if (!$F(carrier).blank() && !$F(invoice).blank())
		{
			new Ajax.Request("/json/invoices/verify", {
				parameters: { accountNumber: cells[2].down("span").innerHTML, invoiceNumber: $F(invoice), carrierNumber: $F(carrier) },
				asynchronous: false,
				onSuccess: function(transport) {
					verified = transport.headerJSON.verified;
					carriers = $A(transport.headerJSON.carriers);
					
					//if the carrier isn't good for this account, tell the user what carriers they can use
					if (!verified)
					{
						//fake validation to force an error
						$$P(carrier, /^foo$/, "Carrier", "is not valid for the specified invoice. Valid carriers are: " + carriers.invoke("escapeHTML").join(", "));
						valid = false;
					}
					
					//if the invoice doesn't exist, we don't flag it as an error because the user is allowed to "create"
					//new invoices here (they won't truly be created until the batch post process runs)
				}
			});
		}
		
		if (!valid)
		{
			event.stop();
			return;
		}
		
		//charges can only be made on active carriers, so we need to check the status
		if ($F(type) == Modules.TransactionQueue.View.chargeTransactionType)
		{
			var isActive = false;
			
			new Ajax.Request("/json/customerCarriers/checkStatus", {
				parameters: { accountNumber: cells[2].down("span").innerHTML, carrierNumber: $F(carrier) },
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
			event.stop();
			return;
		}
		
		var win = mrs.showLoadingDialog();
		
		//post the change
		new Ajax.Request("/json/transactionQueue/edit", {
			parameters: Form.serializeElements([
				id, 
				glCode, 
				dateOfService, 
				glDescription, 
				amount, 
				type, 
				carrier, 
				invoice, 
				billingDate, 
				bank
			]),
			onSuccess: function(transport) {
				
				win.destroy();
				
				if (!transport.headerJSON.success)
				{
					alert("There was a problem saving the transaction. Please try again.");
					return;
				}
				
				//fire the updated event
				Event.fire(row, "transactionQueue:transactionUpdated", { id: $F(id) });
				
				//reload the table
				Modules.TransactionQueue.View.search(true);
			}
		});
		
		event.stop();
	},
	
	onCancel: function(event) {
		var row = event.element().up("tr");
		var cells = row.select("td");
		
		cells.each(function(cell) { 
			cell.select("input[type=text]").invoke("remove"); 
			cell.select("span").invoke("show"); 
			
			//remove any validation error controls if we have any
			cell.select(".ErrorIcon").invoke("remove");
		});
		
		//have to use Element.remove for select elements or else it bombs
		//(see: http://www.ruby-forum.com/topic/140745)
		Element.remove(cells[5].down("select"));
		Element.remove(cells[11].down("select"));
		
		cells[12].select("a.Save")[0].remove();
		cells[13].select("a.Cancel")[0].remove();
		
		cells[12].down("a").show();
		cells[13].down("a").show();
		
		event.stop();
	},
	
	onDelete: function(event) {
		
		if (confirm("Are you sure you want to delete this transaction from the queue?"))
		{
			var cell = event.element().up("td");
			var row = cell.up("tr");
			var id = $F(cell.down("input"));
			
			new Ajax.Request("/json/TransactionQueue/delete/" + id, {
				onSuccess: function(transport) {
					Event.fire(event.element(), "transactionQueue:transactionDeleted");

					//reload the table
					Modules.TransactionQueue.View.search(true);
				}
			});
		}
		
		event.stop();
	},
	
	batchPost: function(event) {
		var win = mrs.showLoadingDialog();
		
		new Ajax.Request("/json/transactionQueue/batchPost", {
			parameters: $("TransactionQueueModuleBatchPostForm").serialize(),
			onSuccess: function(transport) {
				win.destroy();
				location.href = "/processes/manager/reset:1";
			}
		});
		
		event.stop();
	}
}