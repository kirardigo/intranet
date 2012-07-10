Modules.PurchaseOrders.Details = {
	init: function() 
	{	
		$$(".editLink").invoke("observe", "click", Modules.PurchaseOrders.Details.getDetails);
	},
	
	getDetails: function(event) 
	{
		recordId = this.up("td").down("input").value;
		purchaseOrderId = $("PurchaseOrderId").value;
		
		window.open("/purchaseOrderDetails/edit/" + purchaseOrderId  + "/" + recordId, "_blank");
		event.stop();	
	}
}