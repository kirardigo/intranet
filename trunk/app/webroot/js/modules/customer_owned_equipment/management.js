Modules.CustomerOwnedEquipment.Management = {
	loadingWindow: null,
	
	showLoadingDialog: function()
	{
		Modules.CustomerOwnedEquipment.Management.loadingWindow = mrs.showLoadingDialog();
	},
	
	closeLoadingDialog: function()
	{
		Modules.CustomerOwnedEquipment.Management.loadingWindow.destroy();
	},
	
	addTooltips: function()
	{
		$$(".COEManagementAAATip").each(function(element) {
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
	
	addHandlers: function()
	{
		mrs.bindDatePicker("CustomerOwnedEquipmentDateOfPurchaseStart");
		mrs.bindDatePicker("CustomerOwnedEquipmentDateOfPurchaseEnd");
		$("ResetButton").observe("click", Modules.CustomerOwnedEquipment.Management.resetFilters);
	},
	
	detailsLoaded: function()
	{
		$$(".editLink").invoke("observe", "click", Modules.CustomerOwnedEquipment.Management.editRow);
		Modules.CustomerOwnedEquipment.Management.addTooltips();
	},
	
	deleteRow: function(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/customerOwnedEquipment/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	},
	
	editRow: function(event)
	{
		event.stop();
		row = event.element().up("tr");
		accountNumber = row.down("td").down("input.accountNumber").value;
		recordID = row.down("td").down("input.recordID").value;
		
		window.open("/customers/inquiry/accountNumber:" + accountNumber + "/tab:COETab/load:" + recordID, "_blank");
	},
	
	resetFilters: function()
	{
		$("CustomerOwnedEquipmentAccountNumber").clear();
		$("CustomerOwnedEquipmentIsActive").clear();
		$("CustomerOwnedEquipmentDateOfPurchaseStart").clear();
		$("CustomerOwnedEquipmentDateOfPurchaseEnd").clear();
		$("CustomerOwnedEquipmentManufacturerFrameCode").clear();
		$("CustomerOwnedEquipmentModelNumber").clear();
		$("CustomerOwnedEquipmentPurchaseHealthcareProcedureCode").clear();
		$("CustomerOwnedEquipmentInitialCarrierNumber").clear();
		$("CustomerOwnedEquipmentTiltManufacturerCode").clear();
		$("CustomerProfitCenterNumber").clear();
		$("CustomerBillingProgramOptions").clear();
		$("CustomerBillingSchoolOrProgramNumberFromAaaFile").clear();
		$("CustomerBillingLtcfOptions").clear();
		$("CustomerBillingLongTermCareFacilityNumber").clear();
		$("CustomerBillingSalesmanNumber").clear();
		
		$("COEManagementContainer").update();
	}
}