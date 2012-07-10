Modules.Transactions.Management = {
	loadingWindow: null,
	
	addTooltips: function() {
		$$(".TransactionManagementAccountNumberTip").each(function(element) {
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
		
		$$(".TransactionManagementHCPCTip").each(function(element) {
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
		
		$$(".TransactionManagementAAATip").each(function(element) {
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
		
		$$(".TransactionManagementLTCFTip").each(function(element) {
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
		
		$$(".TransactionManagementCarrier1Tip").each(function(element) {
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
		
		$$(".TransactionManagementCarrier2Tip").each(function(element) {
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
		
		$$(".TransactionManagementCarrier3Tip").each(function(element) {
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
		
		$$(".TransactionManagementPhysicianTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/physicians/name",
						options: {
							parameters: {
								physician_number: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
		
		$$(".TransactionManagementGeneralLedgerTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/generalLedger/description",
						options: {
							parameters: {
								general_ledger_code: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
		
		$$(".TransactionManagementInventoryTip").each(function(element) {
			if (element.innerHTML == "")
			{
				element.removeClassName("TooltipContainer");
			}
			else
			{
				new Tip(element, {
					ajax: {
						url: "/ajax/inventory/description",
						options: {
							parameters: {
								inventory_number: element.innerHTML
							}
						}
					},
					style: "darkgrey"
				});
			}
		});
	},
	
	addHandlers: function() {
		mrs.fixIEInputs("TransactionsManagementForm");
		mrs.bindDatePicker("TransactionsManagementPeriodPostingDateStart");
		mrs.bindDatePicker("TransactionsManagementPeriodPostingDateEnd");
		mrs.bindDatePicker("TransactionsManagementDateOfServiceStart");
		mrs.bindDatePicker("TransactionsManagementDateOfServiceEnd");
	},
	
	showLoadingDialog: function() {
		Modules.Transactions.Management.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function() {
		Modules.Transactions.Management.loadingWindow.destroy();
	}
}