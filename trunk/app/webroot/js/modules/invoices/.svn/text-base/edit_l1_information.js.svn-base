Modules.Invoices.EditL1Information = {	

	onBeforePost: function(event) {
		event.memo.cancel = !$$D("InvoiceLine1Date");
	},
	
	onInformationUpdated: function(event) {
		
		if (!event.memo.success)
		{
			alert("There was a problem updating the L1 information. Please try again.");
			return;
		}
		
		Event.fire(event.element(), "invoice:l1InformationUpdated", { 
			status: $F("InvoiceLine1Status"), 
			initials: $F("InvoiceLine1Initials"), 
			date: $F("InvoiceLine1Date"), 
			carrier: $F("InvoiceLine1CarrierNumber") 
		});
	},
	
	//this should be invoked by the object creating the module when it's finished
	destroy: function() {
		document.stopObserving("fileNote:beforePost", Modules.Invoices.EditL1Information.onBeforePost);
		document.stopObserving("fileNote:postCompleted", Modules.Invoices.EditL1Information.onInformationUpdated);
	}
}