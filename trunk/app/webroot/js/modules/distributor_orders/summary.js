Modules.DistributorOrders.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("DistributorOrdersSummaryForm");
		$("DistributorOrdersSummaryExportButton").observe("click", Modules.DistributorOrders.Summary.exportData);
		mrs.bindDatePicker("DistributorOrderOrderDateStart");
		mrs.bindDatePicker("DistributorOrderOrderDateEnd");
		mrs.bindDatePicker("DistributorOrderPrintDateStart");
		mrs.bindDatePicker("DistributorOrderPrintDateEnd");
	},
	
	exportData: function() {
		$("DistributorOrdersSummaryIsExport").value = 1;
		$("DistributorOrdersSummaryForm").submit();
		$("DistributorOrdersSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.DistributorOrders.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.DistributorOrders.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("DistributorOrdersSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, null, null, null, null, {bSortable: false}, null, null, null, null, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, {bSortable: false}] });
	}
}