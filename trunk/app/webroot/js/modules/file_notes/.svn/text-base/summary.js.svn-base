Modules.FileNotes.Summary = {
	loadingWindow: null,
	createWindow: null,
	
	showLoadingDialog: function() {
		Modules.FileNotes.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.FileNotes.Summary.loadingWindow.destroy();
	},
	
	init: function()
	{
		mrs.bindDatePicker("FileNoteFollowupDateStart");
		mrs.bindDatePicker("FileNoteFollowupDateEnd");
		mrs.bindDatePicker("FileNoteCreatedDateStart");
		mrs.bindDatePicker("FileNoteCreatedDateEnd");
		mrs.fixIEInputs("FileNoteSummaryForm");
		
		$("FileNoteDepartmentCode").up("div").down("label").setStyle({ color: "#ff0000" });
		$("FileNoteFollowupDateStart").up("div").down("label").setStyle({ color: "#ff0000" });
		$("FileNoteFollowupDateEnd").up("div").down("label").setStyle({ color: "#ff0000" });
		$("FileNoteFollowupInitials").up("div").down("label").setStyle({ color: "#ff0000" });
		
		$("FileNoteResetButton").observe("click", Modules.FileNotes.Summary.resetForm);
	},
	
	addHandlers: function()
	{
		$$(".FileNoteDetail").invoke("observe", "click", Modules.FileNotes.Summary.showDetails);
	},
	
	showDetails: function(event)
	{
		recordID = this.up("tr").down("td").down("input").value;
		
		window.open("/fileNotes/details/" + recordID, "_blank");
		event.stop();
	},
	
	resetForm: function(event)
	{
		event.stop();
		$("FileNoteTransactionControlNumber").clear();
		$("FileNoteInvoiceNumber").clear();
		$("FileNoteFollowupDateStart").clear();
		$("FileNoteFollowupDateEnd").clear();
		$("FileNoteFollowupInitials").clear();
		
		// We cannot call a regular form submit here because it will not trigger the callbacks
		$("FileNoteSummaryForm").request({
			onSuccess: function(transport) {
				$("FileNoteContentContainer").update(transport.responseText);
			}
		});
	}
}