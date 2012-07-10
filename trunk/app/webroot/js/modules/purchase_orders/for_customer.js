Modules.PurchaseOrders.ForCustomer = {
	
	addHandlers: function(table) {
		Modules.PurchaseOrders.ForCustomer.addPurchaseOrderHandlers();
	},
	
	addPurchaseOrderHandlers: function(table) {
		$("PurchaseOrdersForCustomerTable").select("tbody a").invoke("observe", "click", Modules.PurchaseOrders.ForCustomer.onPurchaseOrderSelected);
	},
	
	onPurchaseOrderSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var purchaseOrderNumber = event.element().up("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("PurchaseOrdersForCustomerDetailInfo", "/ajax/purchaseOrders/purchaseOrderDetail/" + purchaseOrderNumber + "/" + $F("PurchaseOrderDetailAccountNumber"), {
			onComplete: function() {
				win.destroy();
				
				mrs.makeScrollable("PurchaseOrdersForCustomerDetailTable", { sScrollY: "" });
			}
		});
		
		event.stop();
	}
};