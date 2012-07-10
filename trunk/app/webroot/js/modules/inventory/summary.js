Modules.Inventory.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.Inventory.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Inventory.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		mrs.fixIEInputs("InventorySummaryForm");
		$("ResetButton").observe("click", Modules.Inventory.Summary.resetFilters);
		
		$("ExportButton").observe("click", Modules.Inventory.Summary.regularExport);
		$("PicklistExport").observe("click", Modules.Inventory.Summary.picklistExport);
	},
	
	regularExport: function()
	{	
		$("VirtualIsExport").value = 1;
		$("InventorySummaryForm").submit();
		$("VirtualIsExport").value = 0;
	},
	
	picklistExport: function()
	{
		$("VirtualIsPicklistExport").value = 1;
		$("InventorySummaryForm").submit();
		$("VirtualIsPicklistExport").value = 0;
	},
	
	addHandlers: function()
	{
		$$(".editLink").invoke("observe", "click", Modules.Inventory.Summary.editRow);
		$$(".viewLink").invoke("observe", "click", Modules.Inventory.Summary.viewRow);
		$$(".deleteLink").invoke("observe", "click", Modules.Inventory.Summary.deleteRow);		
	},
	
	editRow: function(event)
	{
		id = this.up("td").down("input").value;
		
		window.open("/inventory/edit/" + id, "_blank");
		event.stop();
	},
	
	viewRow: function(event)
	{
		event.stop();
		recordID = this.up("tr").down("td").down("input").value;
		
		window.open("/inventory/view/" + recordID, "_blank");
	},
	
	deleteRow: function(event)
	{
		event.stop();
		
		row = this.up("tr");
		id = row.down("td").down("input").value;
		
		row.up("table").select(".Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			new Ajax.Request("/json/inventory/delete/" + id, {
				onComplete: function(transport) {
					if (transport.headerJSON.success)
					{
						Modules.Inventory.Summary.submitForm();
					}
					else
					{
						alert("There was a problem deleting the record. Please try again.");
					}
				}
			});
		}
		
		row.removeClassName("Highlight");
	},
	
	submitForm: function() {
		$("InventorySummaryForm").request({
			onSuccess: function(transport) {
				$("InventorySummaryContainer").update(transport.responseText);
				Modules.Inventory.Summary.closeLoadingDialog();
			}
		});
	},
	
	resetFilters: function()
	{
		$("InventoryInventoryNumber").clear();
		$("InventoryDescription").clear();
		$("InventoryMedicareHealthcareProcedureCode").clear();
		$("InventoryProfitCenterNumber").clear();
		$("InventoryVendorCode").clear();
		$("InventoryShowDiscontinued").value = 0;
		
		// this is necessary because a regular form submit will not trigger the javascript callbacks
		Modules.Inventory.Summary.showLoadingDialog();
		Modules.Inventory.Summary.submitForm();
	}
};