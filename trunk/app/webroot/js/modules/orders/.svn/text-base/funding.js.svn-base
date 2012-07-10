Modules.Orders.Funding = {
	loadingWindow: null,
	
	addHandlers: function() {
		$("OrderFundingExportButton").observe("click", Modules.Orders.Funding.exportData);
	},
	
	addTableHandlers: function() {
		var table = $("OrdersFundingTable");
		$(table).select("tbody a.EFN").invoke("observe", "click", Modules.Orders.Funding.onEFNSelected);
		$(table).select("tbody a.Auth").invoke("observe", "click", Modules.Orders.Funding.onAuthSelected);
	},
	
	onEFNSelected: function(event) {
		var cell = event.element().up("tr").down(".TCN");
		
		Event.fire(event.element(), "funding:efnRequested", {
			tcn: cell.innerHTML
		});
		
		event.stop();
	},
	
	onAuthSelected: function(event) {
		Event.fire(event.element(), "funding:priorAuthRequested", {
			auth_num: event.element().innerHTML
		});
		
		event.stop();
	},
	
	exportData: function() {
		$("OrderFundingIsExport").value = 1;
		$("OrderFundingForm").submit();
		$("OrderFundingIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Orders.Funding.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Orders.Funding.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("OrdersFundingTable");
		mrs.makeScrollable(table, { aoColumns: [{bSortable: false}, null, null, {bSortable: false}, null, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, null, null, null, {bSortable: false}, null, null, {bSortable: false}, null, {bSortable: false}, null, {bSortable: false}, null, null, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, null, null, null, null, null, null, null] });
	}
};