Modules.InventoryBundles.Products = {
	init: function()
	{
		$("InventoryBundleAdd").observe("click", Modules.InventoryBundles.Products.addRecord);
		$$(".InventoryBundleMoveUpLink").invoke("observe", "click", Modules.InventoryBundles.Products.moveUp);
		$$(".InventoryBundleMoveDownLink").invoke("observe", "click", Modules.InventoryBundles.Products.moveDown);
		$$(".InventoryBundleDeleteLink").invoke("observe", "click", Modules.InventoryBundles.Products.deleteRecord);
		
		new Ajax.Autocompleter("InventoryBundleNew", "InventoryBundleNew_autoComplete", "/ajax/inventory/autoComplete", {
			minChars: 3,
			indicator: "InventoryBundleIndicator",
			callback: Modules.InventoryBundles.Products.searchCallback
		});
		mrs.fixAutoCompleter("InventoryBundleNew");
	},
	
	reloadModule: function()
	{
		document.fire("inventoryBundle:reloadTab");
	},
	
	deleteRecord: function(event)
	{
		event.stop();
		row = this.up("tr");
		table = row.up("table");
		recordID = row.down("td").down("input").value;
		
		table.select("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to remove this item from the bundle?"))
		{
			new Ajax.Request("/json/inventoryBundles/delete/" + recordID, {
				onSuccess: function(transport)
				{
					if (transport.headerJSON.success)
					{
						Modules.InventoryBundles.Products.reloadModule();
					}
					else
					{
						alert("Item was not deleted successfully.");
					}
				}
			});
		}
		
		row.removeClassName("Highlight");
	},
	
	addRecord: function(event)
	{
		event.stop();
		
		new Ajax.Request("/json/inventoryBundles/add", {
			parameters: {
				"data[InventoryBundle][inventory_number_master]": $F("InventoryBundleMaster"),
				"data[InventoryBundle][inventory_number_item]": $F("InventoryBundleNew")
			},
			onSuccess: function(transport)
			{
				if (transport.headerJSON.success)
				{
					Modules.InventoryBundles.Products.reloadModule();
				}
				else
				{
					alert(transport.headerJSON.message);
				}
			}
		});
	},
	
	moveUp: function(event)
	{
		event.stop();
		recordID = this.up("tr").down("td").down("input").value;
		
		new Ajax.Request("/json/inventoryBundles/moveUp/" + recordID, {
			onSuccess: function(transport)
			{
				if (transport.headerJSON.success)
				{
					Modules.InventoryBundles.Products.reloadModule();
				}
				else
				{
					alert("Item was not moved successfully.");
				}
			}
		});
	},
	
	moveDown: function(event)
	{
		event.stop();
		recordID = this.up("tr").down("td").down("input").value;
		
		new Ajax.Request("/json/inventoryBundles/moveDown/" + recordID, {
			onSuccess: function(transport)
			{
				if (transport.headerJSON.success)
				{
					Modules.InventoryBundles.Products.reloadModule();
				}
				else
				{
					alert("Item was not moved successfully.");
				}
			}
		});
	},
	
	searchCallback: function(num) {
		return "data[Inventory][search]=" + $F("InventoryBundleNew");
	}
}