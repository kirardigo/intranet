Modules.Carriers.Claims = {
	init: function() {
		mrs.bindPhoneFormatting(
			"CarrierClaimsPhoneNumber",
			"CarrierClaimsTollFreePhoneNumber",
			"CarrierClaimsFaxNumber",
			"CarrierProviderRelationsPhone",
			"CarrierProviderRelationsFax"
		);
		mrs.bindDatePicker("CarrierEftStartDate");
		mrs.bindDatePicker("CarrierContractDate");
		mrs.bindDatePicker("CarrierRecredentialedDate");
		mrs.bindMailto("CarrierContactEmail");
		
		$("ProviderAddLink").observe("click", Modules.Carriers.Claims.addProvider);
		$$(".ProviderDeleteLink").invoke("observe", "click", Modules.Carriers.Claims.deleteProvider);
		$("CarrierStatementType").observe("change", Modules.Carriers.Claims.changeStatementType);
	},
	
	addProvider: function(event) {
		event.stop();
		
		table = $("ProvidersTable");
		carrier = $F("CarrierCarrierNumber");
		pctr = $F("CarrierProviderNumberProfitCenter");
		number = $F("CarrierProviderNumberNumber");
		
		if (pctr != "" && number != "")
		{
			new Ajax.Request("/json/carrierProviderNumbers/addProvider/" + carrier + "/" + pctr + "/" + number, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						table.show();
						$$(".ProviderDeleteLink").invoke("stopObserving", "click", Modules.Carriers.Claims.deleteProvider);
						
						table.down("tbody").insert('<tr class="Auto">'
							+ '<td><input type="hidden" value="' + transport.headerJSON.id + '" />'	+ pctr + '</td><td>' + number + '</td>'
							+ '<td><a class="ProviderDeleteLink" href="#"><img src="/img/iconDelete.png"></a></td></tr>');
						
						$$(".ProviderDeleteLink").invoke("observe", "click", Modules.Carriers.Claims.deleteProvider);
					}
				}
			});
		}
	},
	
	deleteProvider: function(event) {
		event.stop();
		
		row = event.element().up("tr");
		row.addClassName("Highlight");
		recordID = row.down("td").down("input").value;
		
		if (confirm("Are you sure you wish to delete this row?"))
		{
			new Ajax.Request("/json/carrierProviderNumbers/removeProvider/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
		
		row.up("table").select(".Highlight").invoke("removeClassName", "Highlight");
	},
	
	changeStatementType: function(event) {
		if ($F("CarrierStatementType") == "")
		{
			$("CarrierGroupCode").clear();
		}
		else
		{
			new Ajax.Request("/json/carriers/getStatementTypeGroupCode/" + $F("CarrierStatementType"), {
				onSuccess: function(transport) {
					$("CarrierGroupCode").value = transport.headerJSON.groupCode;
				}
			});
		}
	}
}