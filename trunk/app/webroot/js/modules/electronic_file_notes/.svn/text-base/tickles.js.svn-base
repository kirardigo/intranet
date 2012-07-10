Modules.ElectronicFileNotes.Tickles= {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.bindDatePicker("ElectronicFileNoteFollowupDate");
		$("ExportButton").observe("click", Modules.ElectronicFileNotes.Tickles.exportData);
	},
	
	exportData: function() {
		$("ElectronicFileNoteIsExport").value = 1;
		$("ElectronicFileNotesTicklesForm").submit();
		$("ElectronicFileNoteIsExport").value = 0;
	},
	
	initializeTable: function() {
		var table = $("ElectronicFileNotesTicklesTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, null, null, {bSortable: false}, {bSortable: false}, null, null, null, null, null] });
	},
	
	showLoadingDialog: function() {
		Modules.ElectronicFileNotes.Tickles.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.ElectronicFileNotes.Tickles.loadingWindow.destroy();
	}
}