Modules.Invoices.Selection = {	

	initialize: function() {
		Modules.Invoices.Selection.addHandlers();
	},
	
	addHandlers: function() {
		//add the handler for the select/clear all checkbox
		$("InvoiceSelectionModuleSelectAllCheckbox").observe("click", function(event) {
			$("InvoiceSelectionModuleInvoicesTable").down("tbody").select("input[type=checkbox]").each(function(checkbox) {
				checkbox.checked = event.element().checked;
			});
		});
		
		//add the handler to fire the selectionFinished event with a memo of all of the selected invoice numbers
		$("InvoiceSelectionModuleCopyButton").observe("click", function() {
			var table = $("InvoiceSelectionModuleInvoicesTable");
			
			var selected = table.down("tbody").select("input[type=checkbox]").findAll(function(checkbox) { 
				return checkbox.checked; 
			});
			
			Event.fire(table, "invoice:selectionFinished", { invoices: selected.pluck("value") });
		});
	}
}