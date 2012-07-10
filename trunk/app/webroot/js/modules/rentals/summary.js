Modules.Rentals.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("RentalsSummaryForm");
		$("RentalsSummaryExportButton").observe("click", Modules.Rentals.Summary.exportData);
		$("RentalsSummaryMrsExportButton").observe("click", Modules.Rentals.Summary.exportMrsData);
		$("RentalsSummaryResetButton").observe("click", Modules.Rentals.Summary.resetData);
		mrs.bindDatePicker("RentalSetupDate");
		mrs.bindDatePicker("RentalSetupDateEnd");
		mrs.bindDatePicker("RentalReturnedDate");
		mrs.bindDatePicker("RentalReturnedDateEnd");
	},
	
	exportData: function() {
		$("RentalsSummaryIsExport").value = 1;
		$("RentalsSummaryForm").submit();
		$("RentalsSummaryIsExport").value = 0;
	},
	
	exportMrsData: function() {
		$("RentalsSummaryIsMrsExport").value = 1;
		$("RentalsSummaryForm").submit();
		$("RentalsSummaryIsMrsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Rentals.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Rentals.Summary.loadingWindow.destroy();
	},
	
	resetData: function(event) {
		$("RentalProfitCenterNumber").clear();
		$("RentalSetupDate").clear();
		$("RentalSetupDateEnd").clear();
		$("RentalReturnedDate").clear();
		$("RentalReturnedDateEnd").clear();
		$("RentalDepartmentCode").clear();
		$("RentalPlaceOfService").clear();
		$("RentalHealthcareProcedureCode").clear();
		$("RentalInventoryNumber").clear();
		$("Rental6PointClassification").clear();
		$("RentalCarrierCode").clear();
		$("RentalDiagnosisPointer").clear();
		$("RentalGeneralLedgerCode").clear();
		
		$("RentalsSummaryTable").remove();
	},
	
	initializeTable: function() {
		var table = $("RentalsSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, null, {bSortable: false}, null, null, {bSortable: false}, null, {bSortable: false}, null, null, null, {bSortable: false}, null, null, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, null, {bSortable: false}, {bSortable: false}, null, null, null, null, null, null] });
	}
}