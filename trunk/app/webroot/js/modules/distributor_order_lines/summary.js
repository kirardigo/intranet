Modules.DistributorOrderLines.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("DistributorOrderLinesSummaryForm");
		$("DistributorOrderLinesSummaryExportButton").observe("click", Modules.DistributorOrderLines.Summary.exportData);
		mrs.bindDatePicker("DistributorOrderLineOrderDateStart");
		mrs.bindDatePicker("DistributorOrderLineOrderDateEnd");
		mrs.bindDatePicker("DistributorOrderLinePrintDateStart");
		mrs.bindDatePicker("DistributorOrderLinePrintDateEnd");
	},
	
	exportData: function() {
		$("DistributorOrderLinesSummaryIsExport").value = 1;
		$("DistributorOrderLinesSummaryForm").submit();
		$("DistributorOrderLinesSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.DistributorOrderLines.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.DistributorOrderLines.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("DistributorOrderLinesSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, null, null, null, null, {bSortable: false}, null, null, null, null, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, {bSortable: false}, null] });
	}
}