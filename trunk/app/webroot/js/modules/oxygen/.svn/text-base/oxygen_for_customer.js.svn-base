Modules.Oxygen.OxygenForCustomer = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.bindDatePicker("OxygenDateTestPerformed");
		mrs.bindDatePicker("OxygenLabInitialDateOrderedOrRenewal");
	},
	
	initializeTable: function(table) {
		table = $(table);
		mrs.makeScrollable(table, { sScrollY: "", oLanguage: { sEmptyTable: "No Active Oxygen Rentals" } });
	},
	
	onBeforePost: function(event) {
		valid = true;
		
		if (valid)
		{
			Modules.Oxygen.OxygenForCustomer.loadingWindow = mrs.showLoadingDialog();
		}
		else
		{
			alert("Please address the highlighted issues.");
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.Oxygen.OxygenForCustomer.loadingWindow.destroy();
		
		if (!transport.headerJSON.success)
		{
			alert("Save failed!");
		}
		else
		{
			$("OxygenForCustomerForm").fire("client:reloadTab", { tab: "OxygenTab" });
		}
	}
};