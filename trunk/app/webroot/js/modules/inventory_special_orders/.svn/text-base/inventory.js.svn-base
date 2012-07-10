Modules.InventorySpecialOrders.Inventory = {
	init: function()
	{
		$$(".InventorySpecialEditLink").invoke("observe", "click", Modules.InventorySpecialOrders.Inventory.editRecord);
		$$(".InventorySpecialDeleteLink").invoke("observe", "click", Modules.InventorySpecialOrders.Inventory.deleteRecord);
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
			new Ajax.Request("/json/inventorySpecialOrders/delete/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
		else
		{
			row.removeClassName("Highlight");
		}
	}
}