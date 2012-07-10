Modules.ManufacturerFormCodes.Summary = {
	loadingWindow: null,
	
	showLoadingDialog: function() {
		Modules.ManufacturerFormCodes.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.ManufacturerFormCodes.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		mrs.fixIEInputs("InventorySpecialsForm");
		$("FormCodeExportButton").observe("click", Modules.ManufacturerFormCodes.Summary.exportData);
		$("FormCodeResetButton").observe("click", Modules.ManufacturerFormCodes.Summary.resetFilters);
	},
	
	addHandlers: function()
	{
		$$(".editLink").invoke("observe", "click", Modules.ManufacturerFormCodes.Summary.editRow);
	},
	
	editRow: function(event)
	{
		id = this.up("td").down("input").value;

		window.open("/manufacturerFormCodes/edit/" + id, "_blank");
		event.stop();
	},
	
	exportData: function()
	{
		$("VirtualIsExport").value = 1;
		$("ManufacturerFormCodeSummaryForm").submit();
		$("VirtualIsExport").value = 0;
	},
	
	resetFilters: function()
	{
		$("ManufacturerFormCodeFormCode").clear();
		$("ManufacturerFormCodeSequenceNumber").clear();
		$("ManufacturerFormCodeSequenceDescription").clear();
		
		// this is necessary because a regular form submit will not trigger the javascript callbacks
		$("ManufacturerFormCodeSummaryForm").request({
			onSuccess: function(transport) {
				$("ManufacturerFormCodeSummaryContainer").update(transport.responseText);
			}
		});
	}
}