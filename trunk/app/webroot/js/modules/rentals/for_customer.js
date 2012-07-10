Modules.Rentals.ForCustomer = {
	addHandlers: function(table) {
		$("RentalsForCustomerTable").select("tbody a.Detail").invoke("observe", "click", Modules.Rentals.ForCustomer.onRentalSelected);
		$("RentalsForCustomerTable").select("tbody a.Invoice").invoke("observe", "click", Modules.Rentals.ForCustomer.onInvoicesSelected);
	},
	
	onRentalSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var id = event.element().up("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("RentalsForCustomerDetailInfo", "/ajax/rentals/rentalDetail/" + id, {
			onComplete: function() {
				win.destroy();
				Modules.Rentals.ForCustomer.onDetailLoaded(id);
			}
		});
		
		event.stop();
	},
	
	onDetailLoaded: function(id) {
		Event.fire($("RentalsForCustomerDetailInfo"), "rental:detailLoaded", { recordID: id });
	},
	
	onInvoicesSelected: function(event) {
		var id = event.element().up("td").down("input").value;
		
		Event.fire(event.element(), "rental:invoicesRequested", {
			recordID: id
		});
		
		event.stop();
	}
};