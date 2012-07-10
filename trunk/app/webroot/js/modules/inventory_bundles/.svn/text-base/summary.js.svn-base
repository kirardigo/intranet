Modules.InventoryBundles.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.InventoryBundles.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.InventoryBundles.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		mrs.fixIEInputs("InventoryBundlesForm");
		
		$("InventoryBundleExportButton").observe("click", Modules.InventoryBundles.Summary.exportResults);
		$("InventoryBundleResetButton").observe("click", Modules.InventoryBundles.Summary.resetFilters);
	},
	
	addHandlers: function()
	{
		$$(".InventoryBundleEditLink").invoke("observe", "click", Modules.InventoryBundles.Summary.editRecord);
		$$(".InventoryBundleDeleteLink").invoke("observe", "click", Modules.InventoryBundles.Summary.deleteRecord);
	},
	
	editRecord: function(event)
	{
		event.stop();
		recordID = this.up("tr").down("td").down("input").value;
		
		window.open("/inventoryBundles/edit/" + recordID, "_blank");
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
			new Ajax.Request("/json/inventoryBundles/delete/" + recordID, {
				onSuccess: function(transport)
				{
					if (transport.headerJSON.success)
					{
						row.remove();
					}
					else
					{
						alert("Item was not deleted successfully.");
					}
				}
			});
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
		$("InventoryBundlesForm").submit();
		$("VirtualIsExport").value = 0;
	},
	
	resetFilters: function(event)
	{
		event.stop();
		$("InventoryBundleInventoryNumberMaster").clear();
		$("InventoryBundleInventoryNumberItem").clear();
		
		// this is necessary because a regular form submit will not trigger the javascript callbacks
		$("InventoryBundlesForm").request({
			onSuccess: function(transport) {
				$("InventoryBundlesSummaryContainer").update(transport.responseText);
			}
		});
	}
}