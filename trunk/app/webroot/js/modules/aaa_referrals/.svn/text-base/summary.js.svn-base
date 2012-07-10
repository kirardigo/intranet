Modules.AaaReferrals.Summary = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.fixIEInputs("AaaReferralsSummaryForm");
		$("AaaReferralsSummaryExportButton").observe("click", Modules.AaaReferrals.Summary.exportData);
	},
	
	exportData: function() {
		$("AaaReferralsSummaryIsExport").value = 1;
		$("AaaReferralsSummaryForm").submit();
		$("AaaReferralsSummaryIsExport").value = 0;
	},
	
	showLoadingDialog: function() {
		Modules.AaaReferrals.Summary.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.AaaReferrals.Summary.loadingWindow.destroy();
	},
	
	initializeTable: function() {
		var table = $("AaaReferralsSummaryTable");
		mrs.makeScrollable(table, { aoColumns: [null, null, {bSortable: false}, null, {bSortable: false}, null, {bSortable: false}, null, null, null, {bSortable: false}, {bSortable: false}, {bSortable: false}, null, null, {bSortable: false}, null, null, {bSortable: false}, null] });
		Modules.AaaReferrals.Summary.addTooltips();
	},
	
	addTooltips: function()
	{
		$$(".AaaReferralSummaryTypeTip").each(function(element) {
			new Tip(element, {
				ajax: {
					url: "/ajax/facilityTypes/name",
					options: {
						parameters: {
							"data[FacilityType][code]": element.innerHTML
						}
					}
				},
				style: "darkgrey"
			});
		});
	}
}