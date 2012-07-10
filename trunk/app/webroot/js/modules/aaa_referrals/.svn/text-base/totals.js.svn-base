Modules.AaaReferrals.Totals = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("AaaMonthlySummaryForm");
		$("AaaMonthlySummaryExportButton").observe("click", Modules.AaaReferrals.Totals.exportData);
		$("AaaMonthlySummaryResetButton").observe("click", Modules.AaaReferrals.Totals.clearInputs);
		mrs.bindDatePicker("AaaMonthlySummaryDateMonthStart");
		mrs.bindDatePicker("AaaMonthlySummaryDateMonthEnd");
	},
	
	exportData: function() {
		$("AaaMonthlySummaryIsExport").value = 1;
		$("AaaMonthlySummaryForm").submit();
		$("AaaMonthlySummaryIsExport").value = 0;
	},
	
	clearInputs: function() {
		unloadModule($("TotalsTab"));
		changeTab($("TotalsTab"));
	},
	
	showLoadingDialog: function() {
		Modules.AaaReferrals.Totals.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.AaaReferrals.Totals.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("AaaMonthlySummaryTable");
		mrs.makeScrollable(table);
	}
}