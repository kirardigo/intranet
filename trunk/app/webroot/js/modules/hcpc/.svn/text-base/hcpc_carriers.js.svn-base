Modules.Hcpc.Carriers = {
	loadingWindow: null,
	readonly: false,
	
	initializeSortableTable: function() {
		mrs.makeScrollable("HcpcCarriersTable", { sScrollY: "" });	
	},
	
	addHandlers: function(readonly) {
		Modules.Hcpc.Carriers.readonly = readonly;
		Modules.Hcpc.Carriers.addCarrierHandlers();
		Modules.Hcpc.Carriers.addCarrierAutocomplete();
	},
	
	addCarrierHandlers: function(table) {
		$("HcpcCarriersTable").select("a.HcpcCarrierDetail").invoke("observe", "click", Modules.Hcpc.Carriers.onCarrierSelected);
		$("HcpcCarriersTable").select("tbody a.HcpcCarrierDelete").invoke("observe", "click", Modules.Hcpc.Carriers.onCarrierDeleted);
	},
	
	addCarrierAutocomplete: function() {
		/*new Ajax.Autocompleter("CarrierSearch", "Carrier_autoComplete", "/ajax/carriers/autoComplete", {
			minChars: 3,
			afterUpdateElement: Modules.CustomerCarriers.ForCustomer.carrierSearchUpdate
		});*/
	},
	
	onCarrierSelected: function(event) {
		var selectedRow = event.element().up("tr");

		var id = event.element().up("tr").down("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		Modules.Hcpc.Carriers.launchEdit(id);

		event.stop();
	},
	
	onCarrierDeleted: function(event) {
		/*var selectedRow = event.element().up("tr");
		var carrierID = event.element().up("tr").down("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		$("CustomerCarriersForCustomer_DetailInfo").update("");
		
		if (confirm("Are you sure you wish to remove the selected carrier?"))
		{
			// Indicate loading is in process
			var win = mrs.showLoadingDialog();
			
			new Ajax.Request("/json/customerCarriers/carrierDelete/" + carrierID, {
				onComplete: function(transport) {
					win.destroy();
					
					if (!transport.headerJSON.success)
					{
						alert(transport.headerJSON.message);
						event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
					}
					else
					{
						$("CarrierSearchForm").fire("client:reloadTab", { tab: "CustomerCarriersTab" });
					}
				}
			});
		}
		else
		{
			event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		}
		
		event.stop();*/
	},

	launchEdit: function(id) {
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("HcpcCarrier_DetailInfo", "/ajax/hcpc/carrierDetail/" + id + (Modules.Hcpc.Carriers.readonly ? "/readonly:1" : ""), {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
				Modules.Hcpc.Carriers.onDetailLoaded();	
			}
		});
	},
	
	onDetailLoaded: function() {
		if (!Modules.Hcpc.Carriers.readonly)
		{
			mrs.bindDatePicker("HcpcCarrierInitialDate");
			mrs.bindDatePicker("HcpcCarrierDiscontinuedDate");
			mrs.bindDatePicker("HcpcCarrierUpdatedDate");
			
			$("ModifierAddLink").observe("click", Modules.Hcpc.Carriers.addModifier);
			$$(".HcpcModifierDeleteLink").invoke("observe", "click", Modules.Hcpc.Carriers.deleteModifier);
		}
		else
		{
			mrs.disableControls("HcpcCarrierDetail");
			mrs.disableControls("HcpcCarrierModifierDetail");
		}
	},
	
	onBeforePost: function(event) {
		event.stop();
		
		valid = true;
		
		valid &= $$N("HcpcCarrierAllowableSale", true);
		valid &= $$N("HcpcCarrierAllowableRent", true);
		valid &= $$N("HcpcCarrierPreviousAllowableSale", true);
		valid &= $$N("HcpcCarrierPreviousAllowableRent", true);
		valid &= $$D("HcpcCarrierInitialDate");
		valid &= $$D("HcpcCarrierDiscontinuedDate");
		valid &= $$D("HcpcCarrierUpdatedDate");
		valid &= $$N("HcpcCarrierHcpcMessageReferenceNumber");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			Modules.Hcpc.Carriers.loadingWindow = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.Hcpc.Carriers.loadingWindow.destroy();
		
		var json = transport.responseText.evalJSON();
		
		if (!json.success)
		{				
			alert("There was a problem saving the HCPC Carrier record. Please try again.");
		}
		else
		{
			$("HcpcCarrierForm").fire("hcpc:reloadTab", { tab: "HcpcCarriersTab" });
		}
	},
	
	addModifier: function(event) {
		event.stop();
		
		table = $("ModifiersTable");
		modifier = $F("HcpcModifierHcpcModifier");
		droplist = $("HcpcModifierHcpcModifier");
		
		if (modifier != "")
		{
			selectedText = droplist.options[droplist.selectedIndex].innerHTML;
			
			new Ajax.Request("/json/hcpcModifiers/associateModifier/"  + $F("HcpcCarrierHcpcCode") + "/" + $F("HcpcCarrierCarrierNumber") + "/" + modifier, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						table.show();
						$$(".HcpcModifierDeleteLink").invoke("stopObserving", "click",  Modules.Hcpc.Carriers.deleteModifier);
						
						table.down("tbody").insert('<tr class="Auto">'
							+ '<td><input type="hidden" value="' + transport.headerJSON.id + '" />'	 + selectedText + '</td>'
							+ '<td><a class="HcpcModifierDeleteLink" href="#"><img src="/img/iconDelete.png"></a></td></tr>');
						
						$$(".HcpcModifierDeleteLink").invoke("observe", "click",  Modules.Hcpc.Carriers.deleteModifier);
					}
				}
			});
		}
	}, 
	
	deleteModifier: function(event) {
		event.stop();
		
		row = event.element().up("tr");
		row.addClassName("Highlight");
		recordID = row.down("td").down("input").value;
		
		if (confirm("Are you sure you wish to delete this row?"))
		{
			new Ajax.Request("/json/hcpcModifiers/removeAssociation/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
		
		row.up("table").select(".Highlight").invoke("removeClassName", "Highlight");
	}
};