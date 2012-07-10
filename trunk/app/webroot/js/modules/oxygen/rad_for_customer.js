Modules.Oxygen.RadForCustomer = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.bindDatePicker("OxygenOsaSetupDate");
		mrs.bindDatePicker("OxygenFirstNightSleepStudyDate");
		new Ajax.Autocompleter("OxygenOsaAaaLabCode", "OxygenOsaAaaLabCode_autoComplete", "/ajax/aaaReferrals/autoCompleteByFacility", {
			minChars: 3,
			callback: Modules.Oxygen.RadForCustomer.aaaLabCallback
	   });
		mrs.fixAutoCompleter("OxygenOsaAaaLabCode");
		new Ajax.Autocompleter("OxygenOsaAaaReferralCode", "OxygenOsaAaaReferralCode_autoComplete", "/ajax/aaaReferrals/autoCompleteByFacility", {
			minChars: 3,
			callback: Modules.Oxygen.RadForCustomer.aaaReferralCallback
	   });
		mrs.fixAutoCompleter("OxygenOsaAaaLabCode");
	},
	
	aaaLabCallback: function() {
		return "data[AaaReferral][search]=" + $F("OxygenOsaAaaLabCode");
	},
	
	aaaReferralCallback: function() {
		return "data[AaaReferral][search]=" + $F("OxygenOsaAaaReferralCode");
	},
	
	onBeforePost: function(event) {
		valid = true;
		
		$("OxygenOsaStatus").removeClassName("FieldError")
		
		if ($F("OxygenOsaStatus") == "")
		{
			$("OxygenOsaStatus").addClassName("FieldError").focus();
			valid = false;
		}
		
		if (valid)
		{
			Modules.Oxygen.RadForCustomer.loadingWindow = mrs.showLoadingDialog();
		}
		else
		{
			alert("Please address the highlighted issues.");
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.Oxygen.RadForCustomer.loadingWindow.destroy();
		
		if (!transport.headerJSON.success)
		{
			alert("Save failed!");
		}
		else
		{
			$("RadForCustomerForm").fire("client:reloadTab", { tab: "RadTab" });
		}
	},
	
	initializeTables: function() {
		mrs.makeScrollable([
			{table: "OxygenRadRentalTable", options: { sScrollY: "", aaSorting: [[3, "desc"]], oLanguage: { sEmptyTable: "No Sleep Rentals" }}}, 
			{table: "OxygenRadPurchaseTable", options: { sScrollY: "", aaSorting: [[3, "desc"]], oLanguage: { sEmptyTable: "No Sleep Equipment" }}}
		]);
	}
};