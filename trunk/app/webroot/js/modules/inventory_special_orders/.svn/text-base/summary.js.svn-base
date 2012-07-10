Modules.InventorySpecialOrders.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.InventorySpecialOrders.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.InventorySpecialOrders.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		mrs.bindDatePicker("InventorySpecialOrderPoDateStart");
		mrs.bindDatePicker("InventorySpecialOrderPoDateEnd");
		mrs.bindDatePicker("InventorySpecialOrderAssignedDateStart");
		mrs.bindDatePicker("InventorySpecialOrderAssignedDateEnd");
		mrs.fixIEInputs("InventorySpecialsForm");
		
		$("InventorySpecialExportButton").observe("click", Modules.InventorySpecialOrders.Summary.exportResults);
		$("InventorySpecialResetButton").observe("click", Modules.InventorySpecialOrders.Summary.resetFilters);
	},
	
	addHandlers: function()
	{
		$$(".InventorySpecialEditLink").invoke("observe", "click", Modules.InventorySpecialOrders.Summary.editRecord);
		$$(".InventorySpecialDeleteLink").invoke("observe", "click", Modules.InventorySpecialOrders.Summary.deleteRecord);
	},
	
	editRecord: function(event)
	{
		event.stop();
		recordID = this.up("tr").down("td").down("input").value;
		
		window.open("/inventorySpecialOrders/edit/" + recordID, "_blank");
	},
	
	deleteRecord: function(event)
	{
		event.stop();
		row = this.up("tr");
		recordID = row.down("td").down("input").value;
		
		row.up("table").select("tr.Highlight").invoke("removeClassName", "Highlight");
		
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/inventorySpecialOrders/delete/" + recordID;
		}
		else
		{
			row.removeClassName("Highlight");
		}
	},

	exportResults: function(event)
	{
		event.stop();
		$("VirtualIsExport").value = 1;
		$("InventorySpecialsForm").submit();
		$("VirtualIsExport").value = 0;
	},
	
	resetFilters: function(event)
	{
		event.stop();
		$("InventorySpecialOrderPoDateStart").clear();
		$("InventorySpecialOrderPoDateEnd").clear();
		$("InventorySpecialOrderOriginalPurchaseOrderNumber").clear();
		$("InventorySpecialOrderMrsInventoryNumber").clear();
		$("InventorySpecialOrderManufacturerInventoryNumber").clear();
		$("InventorySpecialOrderAssignedDateStart").clear();
		$("InventorySpecialOrderAssignedDateEnd").clear();
		
		// this is necessary because a regular form submit will not trigger the javascript callbacks
		$("InventorySpecialsForm").request({
			onSuccess: function(transport) {
				$("InventorySpecialsSummaryContainer").update(transport.responseText);
			}
		});
	}
}