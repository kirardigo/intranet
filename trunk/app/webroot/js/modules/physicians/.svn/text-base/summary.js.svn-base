Modules.Physicians.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("PhysiciansSummaryForm");
		$("PhysiciansSummaryExportButton").observe("click", Modules.Physicians.Summary.exportData);
	},
	
	exportData: function() {
		$("PhysiciansSummaryIsExport").value = 1;
		$("PhysiciansSummaryForm").submit();
		$("PhysiciansSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Physicians.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Physicians.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("PhysiciansSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, {bSortable: false}, null, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, null, null, null, null, {bSortable: false}, {bSortable: false}] });
	}
}