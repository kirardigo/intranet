Modules.Invoices.ManagementForClaims = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("InvoicesManagementForClaimsForm");
		mrs.bindDatePicker("InvoicesManagementForClaimsDateOfServiceStart");
		mrs.bindDatePicker("InvoicesManagementForClaimsDateOfServiceEnd");
		mrs.bindDatePicker("InvoiceManagementForClaimsLine1Date");
	},
	
	addTableHandlers: function() {
		var table = $("InvoicesManagementForClaimsTable");
		$(table).select("tbody a.EFN").invoke("observe", "click", Modules.Invoices.ManagementForClaims.onEFNSelected);
		$(table).select("tbody a.Invoice").invoke("observe", "click", Modules.Invoices.ManagementForClaims.onInvoiceSelected);
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
		
	showLoadingDialog: function() {
		Modules.Invoices.ManagementForClaims.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Invoices.ManagementForClaims.loadingWindow.destroy();
	}
}