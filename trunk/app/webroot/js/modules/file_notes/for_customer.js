Modules.FileNotes.ForCustomer = {
	loadingWindow: null,
	createWindow: null,
	
	showLoadingDialog: function() {
		Modules.FileNotes.ForCustomer.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.FileNotes.ForCustomer.loadingWindow.destroy();
	},
	
	init: function(accountNumber)
	{
		mrs.bindDatePicker("FileNoteFollowupDateStart");
		mrs.bindDatePicker("FileNoteFollowupDateEnd");
		mrs.fixIEInputs("FileNoteClientForm");
		
		$("FileNoteResetButton").observe("click", Modules.FileNotes.ForCustomer.resetForm);
		$("FileNoteCreateLink").observe("click", Modules.FileNotes.ForCustomer.addRecord.curry(accountNumber));
		
		document.observe("fileNote:postCompleted", function() {
			Modules.FileNotes.ForCustomer.createWindow.close();
		});
	},
	
	addHandlers: function()
	{
		$$(".FileNoteDetail").invoke("observe", "click", Modules.FileNotes.ForCustomer.loadDetails);
	},
	
	loadDetails: function(event)
	{
		row = this.up("tr");
		table = row.up("table");
		recordID = row.down("td").down("input").value;
		
		table.select("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		new Ajax.Updater("FileNoteDetailContainer", "/ajax/fileNotes/details/" + recordID, {
			onSuccess: Modules.FileNotes.ForCustomer.detailsLoaded
		});
		
		event.stop();
	},
	
	detailsLoaded: function(transport)
	{
		
	},
	
	addRecord: function(accountNumber, event) {
		event.stop();
		Modules.FileNotes.ForCustomer.createWindow = mrs.createWindow(600, 400).setAjaxContent(
			"/modules/fileNotes/create/" + accountNumber, {
				parameters: { showTcnFields: true },
				evalScripts: true
			}
		).show(true).activate();
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
		$("FileNoteClientForm").request({
			onSuccess: function(transport) {
				$("FileNoteContentContainer").update(transport.responseText);
			}
		});
	}
}