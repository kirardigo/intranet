Modules.InventoryProfitCenter.Core = {
	loadingWindow: null,
	
	init: function() {
		$$(".editLink").invoke("observe", "click", Modules.InventoryProfitCenter.Core.getInventoryItemProfitCenterDetail);
		$$(".addLink").invoke("observe", "click", Modules.InventoryProfitCenter.Core.showProfitCenterDetailDiv);
		$$(".deleteLink").invoke("observe", "click", Modules.InventoryProfitCenter.Core.deleteInventoryItemForProfitCenter);
		
		//$("CancelButton").observe("click", Modules.Inventory.Core.closeWindow);
	},
	
	deleteInventoryItemForProfitCenter: function(event) {
		event.stop();
		
		row = event.element().up("tr");
		row.addClassName("Highlight");
		recordID = row.down("td").down("input").value;
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			new Ajax.Request("/json/inventoryProfitCenter/deleteInventoryItemForProfitCenter/" + recordID, {
				onComplete: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
		
		row.up("table").select(".Highlight").invoke("removeClassName", "Highlight");
	},
	
	hideProfitCenterDetailDiv: function(event) {

		 //Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		$("InventoryProfitCenterDetail").hide();
		
		win.destroy();
		
		event.stop();	
	},
	
	showProfitCenterDetailDiv: function(event) {
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		$("InventoryProfitCenterDetail").show();
		
		new Ajax.Updater("InventoryProfitCenterDetail", "/ajax/inventory_profit_center/profit_center_detail/" , {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
				
				$("InventoryProfitCenterInventoryNumber").value = $("InventoryInventoryNumber").value
			}
		});

		event.stop();
	},
	
	getInventoryItemProfitCenterDetail: function(event) {
		//Indicate loading is in process
		var win = mrs.showLoadingDialog();
	
		//get the selected row
		var selectedRow = event.element().up("tr");
		
		//get the id of the record from the hidden field in the view
		var id = event.element().up("tr").down("td").down("input").value;
		 
		//highlight the row
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		//show the div
		$("InventoryProfitCenterDetail").show();
		
		new Ajax.Updater("InventoryProfitCenterDetail", "/ajax/inventory_profit_center/profit_center_detail/" + id , {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
			}
		});
		
		event.stop();
	},
	
	onBeforePost: function(event) {
		event.stop();
	
		valid = true;
		valid &= $$R("InventoryProfitCenterProfitCenterNumber");
		valid &= $$R("InventoryProfitCenterStockLevel") && $$N("InventoryProfitCenterStockLevel");
		valid &= $$R("InventoryProfitCenterReorderLevel") && $$N("InventoryProfitCenterReorderLevel");
		valid &= $$R("InventoryProfitCenterLocator");
		valid &= $$R("InventoryProfitCenterShipTo");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			Modules.InventoryProfitCenter.Core.loadingWindow = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.InventoryProfitCenter.Core.loadingWindow.destroy();
		
		var json = transport.responseText.evalJSON();
		
		if(json.success == "duplicate")
		{
			alert("This inventory item already exists for this profit center. It could not be saved. Please check your values a try again.");
		}
		
		if (!json.success)
		{				
			alert("There was a problem saving the Inventory record. Please try again.");
		}
	},
	
	closeWindow: function() {
		window.open("","_self");
		window.close();
	}
}