Modules.Invoices.Ledger = {	

	initialize: function() {
		Modules.Invoices.Ledger.addHandlers();
	},
	
	addHandlers: function() {
		//add handlers to show transaction detail
		$("InvoiceLedgerModuleLedgerTable").select("a").each(function(a) {
			a.observe("click", Modules.Invoices.Ledger.showTransactionDetail);
		});
		
		$("InvoicesLedgerFilterButton").observe("click", function(event) {
			Event.fire($("InvoiceLedgerModuleLedgerContainer"), "invoice:ledgerFilter", {
				accountNumber: $F("CustomerAccountNumber"),
				invoiceNumber: $F("TransactionInvoiceNumber"),
				carrierNumber: $F("TransactionCarrierNumber"),
				transactionType: $F("TransactionTransactionType")
			});
		});
		
		if ($("LedgerViewAllInvoices") != undefined)
		{
			$("LedgerViewAllInvoices").observe("click", function(event) {
				this.fire("client:reloadTab", { tab: "LedgerTab" });
				event.stop();
			});
		}
	},
	
	showTransactionDetail: function(event) {
		var id = this.id.split("_")[1];
		
		mrs.createWindow(700, 400).setAjaxContent(
			"/modules/transactions/detail/" + id,
			{ evalScripts: true }
		).show(true).activate();
		
		event.stop();
	}
}