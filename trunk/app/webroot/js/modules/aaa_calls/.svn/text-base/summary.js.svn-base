Modules.AaaCalls.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.AaaCalls.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.AaaCalls.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		$("AaaCallResetButton").observe("click", Modules.AaaCalls.Summary.resetFilters);
		mrs.bindDatePicker("AaaCallCallDateStart");
		mrs.bindDatePicker("AaaCallCallDateEnd");
		mrs.bindDatePicker("AaaCallNextCallDateStart");
		mrs.bindDatePicker("AaaCallNextCallDateEnd");
		mrs.fixIEInputs("AaaCallSummaryForm");
	},
	
	detailsLoaded: function()
	{
		$$(".AaaCallEditLink").invoke("observe", "click", Modules.AaaCalls.Summary.editRow);
		Modules.AaaCalls.Summary.addTooltips();
	},
	
	editRow: function(event)
	{
		recordID = event.element().up("td").down("input").value;
		
		window.open("/aaaCalls/edit/" + recordID, "_blank");
		event.stop();
	},
	
	resetFilters: function()
	{
		$("AaaCallAaaNumber").clear();
		$("AaaCallCallDateStart").clear();
		$("AaaCallCallDateEnd").clear();
		$("AaaCallNextCallDateStart").clear();
		$("AaaCallNextCallDateEnd").clear();
		$("AaaCallCompleted").clear();
		$("AaaReferralProfitCenterNumber").clear();
		$("HomecareCallSalesman").clear();
		$("HomecareCallMarketCode").clear();
		
		$("AaaCallsSummaryContainer").update();
	},
	
	addTooltips: function()
	{
		$$(".HomecareCallCallTypeTip").each(function(element) {
			new Tip(element, {
				ajax: {
					url: "/ajax/lookups/name",
					options: {
						parameters: {
							"data[Lookup][name]": "aaa_call_types",
							"data[LookupValue][code]": element.innerHTML
						}
					}
				},
				style: "darkgrey"
			});
		});
		
		$$(".HomecareCallMarketCodeTip").each(function(element) {
			new Tip(element, {
				ajax: {
					url: "/ajax/lookups/name",
					options: {
						parameters: {
							"data[Lookup][name]": "aaa_market_codes",
							"data[LookupValue][code]": element.innerHTML
						}
					}
				},
				style: "darkgrey"
			});
		});
	}
}