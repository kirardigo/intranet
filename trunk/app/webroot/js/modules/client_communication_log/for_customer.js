Modules.ClientCommunicationLog.ForCustomer = {
	addHandlers: function(table) {
		$("CCLForCustomerTable").select("tbody a").invoke("observe", "click", Modules.ClientCommunicationLog.ForCustomer.onRowSelected);
		$("CCLNewRecord").observe("click", Modules.ClientCommunicationLog.ForCustomer.createNew);
	},
	
	onRowSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var cclID = event.element().up("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("CCLForCustomerDetailInfo", "/ajax/clientCommunicationLog/editLog/" + cclID, {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
			}
		});
		
		event.stop();
	},
	
	createNew: function(event) {
		// Remove existing highlighting
		$("CCLForCustomerTable").select("tr").invoke("removeClassName", "Highlight");
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("CCLForCustomerDetailInfo", "/ajax/clientCommunicationLog/editLog", {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
			}
		});
		
		event.stop();
	},
	
	postLogRecord: function() {
		var win = mrs.showLoadingDialog();
		
		new Ajax.Request("/json/clientCommunicationLog/postLog", {
			evalScripts: true,
			parameters: Form.serialize($("CCLDetailForm")),
			onSuccess: function(transport) {
				var success = transport.headerJSON.success;
				alert(success);
				win.destroy();
			}
		});
	}
};