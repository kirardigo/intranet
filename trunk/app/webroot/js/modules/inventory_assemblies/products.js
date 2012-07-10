Modules.InventoryAssemblies.Products = {
	
	/**
	 * Used to initialize the module.
	 */
	init: function()
	{
		//wire up the new assembly item auto completer and add button
		$("InventoryAssemblyAdd").observe("click", Modules.InventoryAssemblies.Products.addRecord);
		
		new Ajax.Autocompleter("InventoryAssemblyNewItem", "InventoryAssemblyNewItem_autoComplete", "/ajax/inventory/autoComplete", {
			minChars: 3,
			indicator: "InventoryAssemblyIndicator",
			callback: Modules.InventoryAssemblies.Products.searchCallback
		});
		
		mrs.fixAutoCompleter("InventoryAssemblyNewItem");
		
		//wire up the edit & delete links in the table of existing assembly products
		$$(".InventoryAssemblyEditLink").invoke("observe", "click", Modules.InventoryAssemblies.Products.editRecord);
		$$(".InventoryAssemblyDeleteLink").invoke("observe", "click", Modules.InventoryAssemblies.Products.deleteRecord);
		
		mrs.fixIEInputs("InventoryAssemblyNewForm");
	},
	
	/**
	 * Fires an event to the host that requests a reload of the module. The host is responsible for doing the reloading.
	 */
	reloadModule: function()
	{
		document.fire("inventoryAssembly:reloadTab");
	},
	
	/**
	 * Creates a new assembly record for the master product.
	 */
	addRecord: function(event)
	{
		var valid = true;
		
		valid &= $$R("InventoryAssemblyNewItem");
		valid &= $$R("InventoryAssemblyNewAssemblyType");
		valid &= $$R("InventoryAssemblyNewQuantity") && $$N("InventoryAssemblyNewQuantity", true);
		
		if (valid)
		{				
			//kick off a json request to create the assembly record
			new Ajax.Request("/json/inventoryAssemblies/add", {
				parameters: {
					"data[InventoryAssembly][inventory_number_master]": $F("InventoryAssemblyNewMaster"),
					"data[InventoryAssembly][inventory_number_item]": $F("InventoryAssemblyNewItem"),
					"data[InventoryAssembly][assembly_type]": $F("InventoryAssemblyNewAssemblyType"),
					"data[InventoryAssembly][quantity]": $F("InventoryAssemblyNewQuantity")
				},
				onSuccess: function(transport)
				{
					//if we're good just reload the products, otherwise let the user know what went wrong
					if (transport.headerJSON.success)
					{
						Modules.InventoryAssemblies.Products.reloadModule();
					}
					else
					{
						alert(transport.headerJSON.message);
					}
				}
			});
		}
		
		event.stop();
	},
	
	editRecord: function(event)
	{
		//grab the ID of the record to edit
		row = this.up("tr");
		table = row.up("table");
		recordID = row.down("td").down("input").value;
		
		//highlight the selected row
		table.select("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		//load the detail
		new Ajax.Updater("InventoryAssemblyDetails", "/ajax/inventoryAssemblies/edit/" + recordID, {
			evalScripts: true
		});
		
		event.stop();
	},
	
	onBeforeEditPost: function(event) 
	{
		var valid = true;
		
		valid &= $$R("InventoryAssemblyAssemblyType");
		valid &= $$R("InventoryAssemblyQuantity") && $$N("InventoryAssemblyQuantity", true);
		
		if (valid)
		{
			new Ajax.Request("/json/inventoryAssemblies/edit/", {
				parameters: $("InventoryAssembliesEditForm").serialize(),
				onSuccess: function(transport)
				{
					//if we're good just reload the products, otherwise let the user know what went wrong
					if (transport.headerJSON.success)
					{
						Modules.InventoryAssemblies.Products.reloadModule();
					}
					else
					{
						alert("Item was not updated successfully.");
					}
				}
			});
		}
		
		event.stop();
	},
	
	onEditPostCompleted: function(request)
	{
		Modules.InventoryAssemblies.Products.reloadModule();
	},
	
	/**
	 * Makes an ajax request to delete a selected assembly record.
	 */
	deleteRecord: function(event)
	{		
		//grab the ID of the record to delete
		row = this.up("tr");
		table = row.up("table");
		recordID = row.down("td").down("input").value;
		
		//highlight the selected row
		table.select("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		//make sure they want to delete this record
		if (confirm("Are you sure you wish to remove this item from the assembly?"))
		{
			new Ajax.Request("/json/inventoryAssemblies/delete/" + recordID, {
				onSuccess: function(transport)
				{
					//if we're good just reload the products, otherwise let the user know what went wrong
					if (transport.headerJSON.success)
					{
						Modules.InventoryAssemblies.Products.reloadModule();
					}
					else
					{
						alert("Item was not deleted successfully.");
					}
				}
			});
		}
		
		row.removeClassName("Highlight");
		event.stop();
	},
	
	/**
	 * Callback for the autocompleter to prep the data before submitting to the ajax action.
	 */
	searchCallback: function(num) {
		return "data[Inventory][search]=" + $F("InventoryAssemblyNewItem");
	}
}