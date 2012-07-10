Modules.CustomerCarriers.CustomerSummary = {
	addHandlers: function(table) {
		$("CustomerCarrierCustomerSummary").select("tbody a").invoke("observe", "click", Modules.CustomerCarriers.CustomerSummary.onCarrierSelected);
	},
	
	onCarrierSelected: function(event) {
		var carrier = event.element().up("td").down("input").value;
		
		Event.fire(event.element(), "customerCarriers:invoicesRequested", {
			carrierNumber: carrier
		});
		
		event.stop();
	}
};