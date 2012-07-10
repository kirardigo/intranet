Modules.Invoices.Management = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("InvoicesManagementForm");
		mrs.bindDatePicker("InvoicesManagementDateOfServiceStart");
		mrs.bindDatePicker("InvoicesManagementDateOfServiceEnd");
	},
	
	addTableHandlers: function() {
		var table = $("InvoicesManagementTable");
		$(table).select("tbody a.EFN").invoke("observe", "click", Modules.Invoices.Management.onEFNSelected);
		$(table).select("tbody a.Invoice").invoke("observe", "click", Modules.Invoices.Management.onInvoiceSelected);
		$(table).select("tbody a.Auth").invoke("observe", "click", Modules.Invoices.Management.onAuthSelected);
	},
	
	onEFNSelected: function(event) {
		var account = event.element().up("tr").down(".Account");
		var invoice = event.element().up("tr").down(".Invoice");
		
		Event.fire(event.element(), "invoices:efnRequested", {
			accountNumber: account.innerHTML,
			invoiceNumber: invoice.innerHTML
		});
		
		event.stop();
	},
	
	onInvoiceSelected: function(event) {
		var account = event.element().up("tr").down(".Account");
		var invoice = event.element().up("tr").down(".Invoice");
		
		Event.fire(event.element(), "invoices:detailRequested", {
			accountNumber: account.innerHTML,
			invoiceNumber: invoice.innerHTML
		});
		
		event.stop();
	},
	
	onAuthSelected: function(event) {
		var account = event.element().up("tr").down(".Account");
		var invoice = event.element().up("tr").down(".Invoice");
		
		Event.fire(event.element(), "invoices:authRequested", {
			accountNumber: account.innerHTML,
			invoiceNumber: invoice.innerHTML
		});
		
		event.stop();
	},
	
	showLoadingDialog: function() {
		Modules.Invoices.Management.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Invoices.Management.loadingWindow.destroy();
	}
}