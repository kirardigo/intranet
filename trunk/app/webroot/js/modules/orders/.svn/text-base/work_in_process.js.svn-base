Modules.Orders.WorkInProcess = {
	loadingWindow: null,
	
	addHandlers: function(table) {
		$("ExportButton").observe("click", Modules.Orders.WorkInProcess.exportData);
		mrs.bindDatePicker("OrderWorkCompletedDateStart");
		mrs.bindDatePicker("OrderWorkCompletedDateEnd");
	},
	
	exportData: function() {
		$("OrderIsExport").value = 1;
		$("OrderWorkInProcessForm").submit();
		$("OrderIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Orders.WorkInProcess.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Orders.WorkInProcess.loadingWindow.destroy();
		Modules.Orders.WorkInProcess.addTooltips();
	},
	
	addTooltips: function () {
		$$(".OrderWorkInProcessProgramTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/aaaReferrals/facility",
						options: {
							parameters: {
								aaa_number: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
		
		$$(".OrderWorkInProcessLTCFTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/aaaReferrals/facility",
						options: {
							parameters: {
								aaa_number: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
	},
	
	initializeTable: function() {
		var table = $("OrdersWorkInProcessTable");
		mrs.makeScrollable(table, { aoColumns: [null, {bSortable: false}, null, null, null, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, null, null, {bSortable: false}, null, null, {bSortable: false}, null, null, null, {bSortable: false}, {bSortable: false}, {bSortable: false}] });
	}
};