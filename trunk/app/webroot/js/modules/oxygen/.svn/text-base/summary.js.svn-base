Modules.Oxygen.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("OxygenSummaryForm");
		$("OxygenSummaryExportButton").observe("click", Modules.Oxygen.Summary.exportData);
		mrs.bindDatePicker("OxygenOsaSetupDateStart");
		mrs.bindDatePicker("OxygenOsaSetupDateEnd");
	},
	
	addTooltips: function() {
		$$(".AaaLabTip").each(function(element) {
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
		});
		
		$$(".AaaReferralTip").each(function(element) {
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
		});
	},
	
	exportData: function() {
		$("OxygenSummaryIsExport").value = 1;
		$("OxygenSummaryForm").submit();
		$("OxygenSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.Oxygen.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Oxygen.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("OxygenSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, null, null, {bSortable: false}, null, null, null, null, null, null, null, null, null] });
	}
}