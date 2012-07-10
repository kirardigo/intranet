Modules.Transactions.Related = {
	loadingWindow: null,
	
	addTooltips: function() {
		$$(".TransactionRelatedAccountNumberTip").each(function(element) {
			new Tip(element, {
				ajax: {
					url: "/ajax/customers/name",
					options: {
						parameters: {
							accountNumber: element.innerHTML
						}
					}
				},
				style: "darkgrey"
			});
		});
		
		$$(".TransactionRelatedHCPCTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/healthcareProcedureCodes/description",
						options: {
							parameters: {
								code: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
				
		$$(".TransactionRelatedCarrier1Tip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/customerCarriers/name",
						options: {
							parameters: {
								carrier_number: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
	},
	
	addHandlers: function() {
		mrs.fixIEInputs("TransactionsRelatedForm");
		mrs.bindDatePicker("TransactionsRelatedPeriodPostingDateStart");
		mrs.bindDatePicker("TransactionsRelatedPeriodPostingDateEnd");
	},
	
	showLoadingDialog: function() {
		Modules.Transactions.Related.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Transactions.Related.loadingWindow.destroy();
	}
}