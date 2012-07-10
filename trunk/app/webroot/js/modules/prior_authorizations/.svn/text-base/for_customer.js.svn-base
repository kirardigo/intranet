Modules.PriorAuthorizations.ForCustomer = {
	loadingWindow: null,
	
	addHandlers: function(table) {
		$("PriorAuthorizationsForCustomerTable").select("tbody a.Detail").invoke("observe", "click", Modules.PriorAuthorizations.ForCustomer.onRecordSelected);
		$("CreatePriorAuthLink").observe("click", Modules.PriorAuthorizations.ForCustomer.onRecordCreated);
		document.observe("priorAuthorization:select", Modules.PriorAuthorizations.ForCustomer.onRecordSelected);
		document.observe("priorAuthDenialMapping:refresh", function(event) {
			Modules.PriorAuthorizations.ForCustomer.reloadDenialTable(event.memo.id);
		});
	},
	
	selectRecord: function(id) {
		// loop through the IDs in the table and simulate a selection. this also ensures
		// that they don't pass in an arbitrary ID that does not belong to this account.
		$("PriorAuthorizationsForCustomerTable").select("input").each(function(recordInput) {
			if ($F(recordInput) == id)
			{
				recordInput.up("td").down("a").fire("priorAuthorization:select");
				$break; // used to stop the enumerable iteration once we find our match
			}
		});
	},
	
	onRecordSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var recordID = event.element().up("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		Modules.PriorAuthorizations.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		
		new Ajax.Updater("PriorAuthorizationsForCustomerDetailInfo", "/ajax/priorAuthorizations/detail/" + recordID, {
			evalScripts: true,
			onComplete: function() {
				Modules.PriorAuthorizations.ForCustomer.reloadDenialTable(recordID);
				Modules.PriorAuthorizations.ForCustomer.loadingWindow.destroy();
			}
		});
		
		event.stop();
	},
	
	onRecordCreated: function(event) {
		Modules.PriorAuthorizations.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		
		$("PriorAuthorizationsForCustomerTable").select(".Highlight").invoke("removeClassName", "Highlight");
		
		new Ajax.Updater("PriorAuthorizationsForCustomerDetailInfo", "/ajax/priorAuthorizations/detail/new:1", {
			evalScripts: true,
			onComplete: function() {
				Modules.PriorAuthorizations.ForCustomer.loadingWindow.destroy();
			}
		});
		
		event.stop();
	},
	
	deleteRecord: function(event) {
		event.stop();
		
		if (confirm("Are you sure you want to delete this record?"))
		{
			new Ajax.Request("/json/priorAuthorizations/delete/" + $F("PriorAuthorizationId"), {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						$("PriorAuthorizationsDetailForm").fire("client:reloadTab", { tab: "AuthsTab" });
					}
				}
			});
		}
	},
	
	addDetailHandlers: function() {
		mrs.bindDatePicker("PriorAuthorizationDateOfService");
		mrs.bindDatePicker("PriorAuthorizationDateActivated");
		mrs.bindDatePicker("PriorAuthorizationAuthorizationStartDate");
		mrs.bindDatePicker("PriorAuthorizationAuthorizationEndDate");
		mrs.bindDatePicker("PriorAuthorizationDateRequested", { onSelect: Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateRequested", "R") });
		mrs.bindDatePicker("PriorAuthorizationMitsRequestResponseDate", { onSelect: Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("MitsRequestResponseDate", "I") });
		mrs.bindDatePicker("PriorAuthorizationDateApproved", { onSelect: Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateApproved", "A") });
		mrs.bindDatePicker("PriorAuthorizationDateDenied", { onSelect: Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateDenied", "D") });
		mrs.bindDatePicker("PriorAuthorizationDateExpiration");
		mrs.bindDatePicker("PriorAuthorizationAppealsDate");
		
		$("PriorAuthSaveTop").observe("click", Modules.PriorAuthorizations.ForCustomer.submitForm);
		$("PriorAuthSaveBottom").observe("click", Modules.PriorAuthorizations.ForCustomer.submitForm);
		
		if ($("PriorAuthDelete") != undefined)
		{
			$("PriorAuthDelete").observe("click", Modules.PriorAuthorizations.ForCustomer.deleteRecord);
		}
		
		$("PriorAuthorizationDateRequested").observe("change", Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateRequested", "R"));
		$("PriorAuthorizationDateApproved").observe("change", Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateApproved", "A"));
		$("PriorAuthorizationDateDenied").observe("change", Modules.PriorAuthorizations.ForCustomer.updateStatus.curry("DateDenied", "D"));
		$("PriorAuthorizationCarrierNumber").observe("change", Modules.PriorAuthorizations.ForCustomer.updateCarrierDescription);
	},
	
	updateStatus: function(field, status) {
		// date fields are cached, only update the status when they are filled out for the first time
		if ($F("Virtual" + field + "Backup") == "")
		{
			$("PriorAuthorizationStatus").value = status
		}
	},
	
	updateCarrierDescription: function() {
		$("PriorAuthorizationCarrierNumber").removeClassName("FieldError");
		
		new Ajax.Request("/json/customerCarriers/checkCarrierReturnName", {
			parameters: {
				account_number: $F("CustomerAccountNumber"),
				carrier_number: $F("PriorAuthorizationCarrierNumber")
			},
			onSuccess: function(transport) {
				if (transport.headerJSON.notExist)
				{
					$("PriorAuthorizationCarrierNumber").addClassName("FieldError");
					$("PriorAuthorizationCarrierNumber").focus();
					alert("The specified carrier is not valid for this account.");
				}
				else
				{
					$("PriorAuthorizationCarrierDescription").value = transport.headerJSON.name;
				}
			}
		});
	},
	
	submitForm: function(event) {
		$("PriorAuthorizationAccountNumber").value = $F("CustomerAccountNumber");
		
		if (Modules.PriorAuthorizations.ForCustomer.onBeforePost(event))
		{
			new Ajax.Request("/json/priorAuthorizations/save", {
				parameters: $("PriorAuthorizationsDetailForm").serialize(true),
				onSuccess: Modules.PriorAuthorizations.ForCustomer.onPostCompleted
			});
		}
	},
	
	onBeforePost: function(event) {
		valid = true;
		
		if ($F("PriorAuthorizationTransactionControlNumberFile") == "")
		{
			$("PriorAuthorizationTransactionControlNumberFile").addClassName("FieldError");
			valid = false;
		}
		if ($F("PriorAuthorizationTransactionControlNumber") == "")
		{
			$("PriorAuthorizationTransactionControlNumber").addClassName("FieldError");
			valid = false;
		}
		if ($F("PriorAuthorizationDepartmentCode") == "")
		{
			$("PriorAuthorizationDepartmentCode").addClassName("FieldError");
			valid = false;
		}
		if ($F("PriorAuthorizationDescription") == "")
		{
			$("PriorAuthorizationDescription").addClassName("FieldError");
			valid = false;
		}
		if ($F("PriorAuthorizationDateRequested") == "")
		{
			$("PriorAuthorizationDateRequested").addClassName("FieldError");
			valid = false;
		}
		
		if (valid)
		{
			Modules.PriorAuthorizations.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		}
		else
		{
			alert("Please address the highlighted issues.");
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.PriorAuthorizations.ForCustomer.loadingWindow.destroy();
		
		if (!transport.headerJSON.success)
		{
			alert("Save failed: " + transport.headerJSON.message);
		}
		else
		{
			$("PriorAuthorizationsDetailForm").fire("client:reloadTab", { tab: "AuthsTab" });
		}
	},
	
	reloadDenialTable: function(id) {
		new Ajax.Updater("DenialCodeTable", "/ajax/priorAuthorizationDenialMapping/table/" + id, {
			evalScripts: true
		});
	},
	
	addDenialCodeHandlers: function() {
		$("PriorAuthDenialMappingAdd").observe("click", Modules.PriorAuthorizations.ForCustomer.addDenialCode);
		$$(".PriorAuthDenialDeleteLink").invoke("observe", "click", Modules.PriorAuthorizations.ForCustomer.deleteDenialCode);
	},
	
	addDenialCode: function(event) {
		event.stop();
		
		var url = "/json/priorAuthorizationDenialMapping/add/";
		
		if ($F("PriorAuthorizationAuthorizationIdNumber") == "")
		{
			alert("This prior authorization record does not have an ID.");
			return;
		}
		
		if ($F("PriorAuthDenialMappingCode") == "")
		{
			alert("Please specify a denial code.");
			$("PriorAuthDenialMappingCode").focus();
			return;
		}
		
		Modules.PriorAuthorizations.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		
		new Ajax.Request(url + $F("PriorAuthorizationAuthorizationIdNumber") + "/" + $F("PriorAuthDenialMappingCode"), {
			onComplete: function() {
				document.fire("priorAuthDenialMapping:refresh", {
					id: $F("PriorAuthDenialMappingID")
				});
				
				Modules.PriorAuthorizations.ForCustomer.loadingWindow.destroy();
			}
		});
	},
	
	deleteDenialCode: function(event) {
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			Modules.PriorAuthorizations.ForCustomer.loadingWindow = mrs.showLoadingDialog();
			
			new Ajax.Request("/json/priorAuthorizationDenialMapping/delete/" + recordID, {
				onComplete: function() {
					document.fire("priorAuthDenialMapping:refresh", {
						id: $F("PriorAuthDenialMappingID")
					});
					
					Modules.PriorAuthorizations.ForCustomer.loadingWindow.destroy();
				}
			});
		}
		
		row.removeClassName("Highlight");
		
		event.stop();
	}
};