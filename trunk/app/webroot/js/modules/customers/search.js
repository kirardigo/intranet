Modules.Customers.Search = {	

	initialize: function() {
		Modules.Customers.Search.addEventHandlers();
		
		//NOTE: if shown in a window, create the window with a height of 400 
		mrs.makeScrollable("ModulesCustomerSearchTable", { bSort: false, sScrollY: "300px", oLanguage: { sEmptyTable: "" } });
	},
	
	submit: function(event) {
		
		//let the user know we're working on the search (the setStyle is for FireFox to properly display the text 
		//due to the scrollable table stuff)
		$("ModulesCustomerSearchTable").down("tbody").update('<tr><td colspan="6">Loading. Please wait...</td></tr>').setStyle({ height: "auto" });
		
		var form = $("ModuleCustomerSearchForm");

		//make sure the user has at least one piece of criteria
		if (!form.select("input[type=text]").any(function(i) { return !$F(i).blank(); }))
		{
			alert("You must specify a value for at least one field.");
		}
		else
		{
			//go do the search
			new Ajax.Request("/modules/customers/search", {
				parameters: form.serialize(),
				onSuccess: function(transport) {
					//replace the table entirely and reinitialize it
					$("ModuleCustomerSearchContainer").update(transport.responseText);
					Modules.Customers.Search.initialize();
				}
			});
		}
		
		event.stop();
	},
	
	addEventHandlers: function() {
		var table = $("ModulesCustomerSearchTable");
		
		//wire up textboxes to issue a search when you press the enter key
		table.down("thead").select("input").each(function(input) {
			input.observe("keypress", function(event) {
				if (event.keyCode == Event.KEY_RETURN)
				{
					Modules.Customers.Search.submit(event);
				}
			})
		});
		
		//wire up the account links on the results to fire an event
		table.down("tbody").select("a").each(function(a) {
			a.observe("click", Modules.Customers.Search.onAccountSelected);
		});
	},
	
	onAccountSelected: function(event) {
		var a = event.element();
		var account = a.innerHTML;
		var name = a.up("td").next("td").innerHTML;
		
		Event.fire(a, "customer:accountSelected", { accountNumber: account, name: name });
		event.stop();
	}
}