Modules.AaaProfiles.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.AaaProfiles.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.AaaProfiles.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{		
		$$(".aaaProfileEditLink").invoke("observe", "click", Modules.AaaProfiles.Summary.editRow);
		$("AaaProfileExportButton").observe("click", function() {
			$("AaaProfileIsExport").value = 1;
			$("AaaProfileSummaryForm").submit();
			$("AaaProfileIsExport").value = 0;
		});
		$("AaaProfileResetButton").observe("click", Modules.AaaProfiles.Summary.resetFilters);
		mrs.fixIEInputs("AaaProfileSummaryForm");
		
		var table = $("AaaProfileResultsTable");
		
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, null, null, {bSortable: false}] });
	},
	
	editRow: function(event)
	{
		recordID = event.element().up("td").down("input").value;
		
		window.open("/aaaProfiles/edit/" + recordID, "_blank");
		event.stop();
	},
	
	resetFilters: function(event)
	{
		$("AaaProfileAaaNumber").clear();
		$("AaaReferralProfitCenterNumber").clear();
		$("AaaReferralHomecareSalesman").clear();
		$("AaaReferralHomecareMarketCode").clear();
		
		$("AaaProfileResultsTable").down("tbody").update();
	}
}