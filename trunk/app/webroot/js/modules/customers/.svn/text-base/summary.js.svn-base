Modules.Customers.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("CustomersSummaryForm");
		$("CustomersSummaryExportButton").observe("click", Modules.Customers.Summary.exportData);
		mrs.bindDatePicker("CustomerSetupDate");
		mrs.bindDatePicker("CustomerSetupDateEnd");
	},
	
	exportData: function() {
		$("CustomersSummaryIsExport").value = 1;
		$("CustomersSummaryForm").submit();
		$("CustomersSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Customers.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Customers.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("CustomersSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, {bSortable: false}, {bSortable: false}, null, null, null, {bSortable: false}, null, null, null, null, null] });
	}
}