Modules.Invoices.ForCustomer = {	
	addHandlers: function(table) {
		Modules.Invoices.ForCustomer.addCarrierHandlers(table);
		Modules.Invoices.ForCustomer.addLedgerHandlers(table);
		Modules.Invoices.ForCustomer.addL1Handlers(table);
		Modules.Invoices.ForCustomer.addPurchasesHandlers(table);
	},
	
	addCarrierHandlers: function(table) {
		$(table).select("tbody a.Carrier").invoke("observe", "click", Modules.Invoices.ForCustomer.onCarrierSelected);
	},
	
	addLedgerHandlers: function(table) {
		$(table).select("tbody a.Ledger").invoke("observe", "click", Modules.Invoices.ForCustomer.onLedgerRequested);
	},
	
	addL1Handlers: function(table) {
		$(table).select("tbody a.L1").invoke("observe", "click", Modules.Invoices.ForCustomer.onEditL1Requested);
	},
	
	addPurchasesHandlers: function(table) {
		$(table).select("tbody a.Purchases").invoke("observe", "click", Modules.Invoices.ForCustomer.onPurchasesRequested);
	},
	
	onCarrierSelected: function(event) {
		
		var cells = event.element().up("tr").select("td");
		
		Event.fire(event.element(), "invoice:carrierSelected", {
			invoice: cells[0].innerHTML,
			tcn: cells[1].innerHTML,
			date: cells[2].innerHTML,
			amount: cells[3].innerHTML,
			carrier: event.element().innerHTML,
			balance: event.element().up("td").next("td").innerHTML
		});
		
		event.stop();
	},
	
	onLedgerRequested: function(event) {

		var cells = event.element().up("tr").select("td");
		
		Event.fire(event.element(), "invoice:ledgerRequested", {
			invoice: cells[0].innerHTML
		});
		
		event.stop();
	},
	
	onEditL1Requested: function(event) {

		var row = event.element().up("tr");
		var cells = row.select("td");
		
		Event.fire(event.element(), "invoice:editL1Requested", {
			row: row,
			invoice: cells[0].innerHTML
		});
		
		event.stop();
	},
	
	onPurchasesRequested: function(event) {

		var cells = event.element().up("tr").select("td");
		
		Event.fire(event.element(), "invoice:purchasesRequested", {
			invoice: cells[0].innerHTML
		});
		
		event.stop();
	},
	
	updateL1Information: function(row, status, date, amount) {
		var cells = row.select("td");
		
		if (status != null)
		{
			cells[11].update(status);
		}
		
		if (date != null)
		{
			cells[12].update(date);
		}
		
		if (amount != null)
		{
			cells[13].update(amount);
		}
	},
	
	toggleClosedInvoices: function(accountNumber, showClosed, clickableCarriers, showPurchasesLink, showEditL1InformationLink, rentalID, carrierNumber) {
		var container = $("ClosedInvoicesContainer");
		
		if (showClosed)
		{
			container.update("Loading. Please wait...");
			
			new Ajax.Updater(container, "/modules/invoices/forCustomer/" + accountNumber + "/closedInvoices:1/clickableCarriers:" + (clickableCarriers ? "1" : "0") + "/showPurchasesLink:" + (showPurchasesLink ? "1" : "0") + "/showEditL1InformationLink:" + (showEditL1InformationLink ? "1" : "0") + (rentalID != null ? ("/rentalID:" + rentalID) : "") + (carrierNumber != null ? ("/carrierNumber:" + carrierNumber) : ""), {
				onComplete: function() {
					var table = $("ClosedInvoicesTable");
					mrs.makeScrollable(table, { aaSorting: [[3, "desc"]], aoColumns: [null, null, null, null, null, null, null, null, null, null, null, null, null, null, {bSortable: false}, {bSortable: false}] });
				},
				evalScripts: true
			});
		}
		else
		{
			container.update();
		}
	},
	
	toggleAgedOpenBalances: function(accountNumber, checked, rentalID, carrierNumber) {
		var container = $("AgedOpenBalanceContainer");
		
		if (checked)
		{
			container.update("Loading. Please wait...");
			
			new Ajax.Updater(container, "/ajax/invoices/agedOpenInvoices/" + accountNumber + (rentalID != null ? ("/rentalID:" + rentalID) : "") + (carrierNumber != null ? ("/carrierNumber:" + carrierNumber) : ""), {
				evalScripts: true
			});
		}
		else
		{
			$("AgedOpenBalanceContainer").update();
		}
	},
	
	initializeOpenInvoices: function() {
		var table = $("OpenInvoicesTable");
		mrs.makeScrollable(table, { aaSorting: [[3, "asc"]], aoColumns: [null, null, null, null, null, null, null, null, null, null, null, null, null, null, {bSortable: false}, {bSortable: false}] });
	}
}