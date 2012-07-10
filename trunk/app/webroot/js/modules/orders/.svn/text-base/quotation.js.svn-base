Modules.Orders.Quotation = {
	loadingWindow: null,
	
	addHandlers: function(table) {
		$("ExportButton").observe("click", Modules.Orders.Quotation.exportData);
		
		mrs.bindDatePicker("OrderStartDate");
		mrs.bindDatePicker("OrderEndDate");
	},
	
	exportData: function() {
		$("OrderIsExport").value = 1;
		$("OrderQuotationForm").submit();
		$("OrderIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Orders.Quotation.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Orders.Quotation.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("OrdersQuotationTable");
		mrs.makeScrollable(table);
	}
};