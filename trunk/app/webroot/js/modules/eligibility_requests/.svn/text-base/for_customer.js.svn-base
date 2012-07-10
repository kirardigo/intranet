Modules.EligibilityRequests.ForCustomer = {
	initialize: function(account, table, zirmedUrl) {
		
		//this is the dialog template to show some basic details of the Zirmed response
		var template = new Template(" \
			<div class=\"GroupBox\"> \
				<h2>eVOB Reponse</h2> \
				<div class=\"Content DisplayForm\"> \
					<label>Status:</label><p>#{status}&nbsp;</p> \
					<label>Rejection Reason:</label><p>#{rejected_reason}&nbsp;</p> \
					<label>Followup Action:</label><p>#{followup_action}&nbsp;</p> \
					<label>Details:</label><p><a href=\"" + zirmedUrl + "/account:#{account}/carrier:#{carrier}" + "\" target=\"_blank\">Open eVOB website in new window</a></p> \
				</div> \
			</div> \
		");
		
		//this is the dialog template when the Zirmed request fails for some reason
		var errorTemplate = " \
			<div class=\"GroupBox\"> \
				<h2>eVOB Reponse</h2> \
				<div class=\"Content DisplayForm\"> \
					<label>Error:</label><p>There was a problem submitting the request. Please try again later.</p> \
				</div> \
			</div> \
		";
		
		//go through all the "New Request" links and wire up event handlers to do the AJAX request
		$(table).select("a.NewRequest").each(function(a) {
			a.observe("click", function(event) {
										
				//grab the carrier number
				var carrier = a.up("tr").down("td:first").innerHTML;
				
				//show a loading dialog while we wait on Zirmed
				var win = mrs.showLoadingDialog();
				
				//kick off the request
				new Ajax.Request("/json/eligibilityRequests/submitRequest/" + account + "/" + carrier, {
					onComplete: function(transport, json) {
						//close the loading dialog
						win.destroy();
						
						//show the results of the zirmed request
						mrs.createWindow(600, 400, null, json.EligibilityResponse.id == false ? errorTemplate : template.evaluate(Object.extend(json.EligibilityResponse, { account: account, carrier: carrier }))).show(true).activate();
					}
				});
				
				event.stop();
			});
		});
	}
}