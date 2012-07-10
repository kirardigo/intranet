Modules.Purchases.ForCustomer = {
	addHandlers: function(table) {
		$("PurchasesForCustomerTable").select("tbody a").invoke("observe", "click", Modules.Purchases.ForCustomer.onPurchaseSelected);
	},
	
	onPurchaseSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var purchaseNumber = event.element().up("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("PurchasesForCustomerDetailInfo", "/ajax/purchases/purchaseDetail/" + purchaseNumber, {
			onComplete: function() {
				win.destroy();
				Modules.Purchases.ForCustomer.onDetailLoaded(purchaseNumber);
			}
		});
		
		event.stop();
	},
	
	onDetailLoaded: function(purchaseNumber) {
		Event.fire($("PurchasesForCustomerDetailInfo"), "purchase:detailLoaded", { purchaseNumber: purchaseNumber });
	}
};