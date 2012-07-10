Modules.Hcpc.Hcpc = {
	loadingWindow: null,
	
	init: function() {
		mrs.bindDatePicker("HcpcInitialDate");
		mrs.bindDatePicker("HcpcDiscontinuedDate");
		
		$("CancelButton").observe("click", Modules.Hcpc.Hcpc.closeWindow);
	},
	
	onBeforePost: function(event) {
		event.stop();
		
		valid = true;
		valid = $$R("HcpcCode");
		valid &= $$R("HcpcDescription");
		valid &= $$D("HcpcInitialDate");
		valid &= $$D("HcpcDiscontinuedDate");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			Modules.Hcpc.Hcpc.loadingWindow = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.Hcpc.Hcpc.loadingWindow.destroy();
		
		var json = transport.responseText.evalJSON();
		
		if (!json.success)
		{				
			alert("There was a problem saving the HCPC record. Please try again.");
		}
		else
		{
			$("HcpcFormSave").fire("hcpc:reloadTab", { tab: "HcpcTab" });
		}
	},
	
	closeWindow: function() {
		window.open("","_self");
		window.close();
	}
};