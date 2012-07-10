Modules.CustomerOwnedEquipment.ForCustomer = {
	loadingWindow: null,
	
	initializeSortableTable: function() {
		mrs.makeScrollable("CustomerOwnedEquipmentForCustomerTable", { sScrollY: "", aoColumns: [null, null, null, null, null, null, null, null, null, null, null, {bSortable: false}, {bSortable: false}] });
	},
	
	addHandlers: function(table) {
		$("CustomerOwnedEquipmentForCustomerTable").select("tbody a.Detail").invoke("observe", "click", Modules.CustomerOwnedEquipment.ForCustomer.onRecordSelected);
		$("CustomerOwnedEquipmentForCustomerTable").select("tbody a.Ledger").invoke("observe", "click", Modules.CustomerOwnedEquipment.ForCustomer.onInvoiceSelected);
		$("CreateCustomerOwnedEquipmentLink").observe("click", Modules.CustomerOwnedEquipment.ForCustomer.onRecordCreated);
		$$(".COEActiveLink").invoke("observe", "click", Modules.CustomerOwnedEquipment.ForCustomer.toggleStatus);
		document.observe("customerOwnedEquipment:select", Modules.CustomerOwnedEquipment.ForCustomer.onRecordSelected);
	},
	
	selectRecord: function(id) {
		// loop through the IDs in the table and simulate a selection. this also ensures
		// that they don't pass in an arbitrary ID that does not belong to this account.
		$("CustomerOwnedEquipmentForCustomerTable").select("input").each(function(recordInput) {
			if ($F(recordInput) == id)
			{
				recordInput.up("td").down("a").fire("customerOwnedEquipment:select");
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
		
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("CustomerOwnedEquipmentForCustomerDetailInfo", "/ajax/customerOwnedEquipment/detail/" + recordID, {
			onComplete: function() {
				win.destroy();
				Modules.CustomerOwnedEquipment.ForCustomer.onDetailLoaded(recordID);
			}
		});
		
		event.stop();
	},
	
	onRecordCreated: function(event) {
		Modules.CustomerOwnedEquipment.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		
		$("CustomerOwnedEquipmentForCustomerTable").select(".Highlight").invoke("removeClassName", "Highlight");
		
		new Ajax.Updater("CustomerOwnedEquipmentForCustomerDetailInfo", "/ajax/customerOwnedEquipment/detail/new:1", {
			evalScripts: true,
			onComplete: function() {
				Modules.CustomerOwnedEquipment.ForCustomer.loadingWindow.destroy();
			}
		});
		
		event.stop();
	},
	
	onInvoiceSelected: function(event) {
		var invoiceNumber = event.element().up("td").down("input").value;
		
		Event.fire(event.element(), "customerOwnedEquipment:ledgerRequested", {
			invoice: invoiceNumber
		});
		
		event.stop();
	},
	
	onDetailLoaded: function(recordID) {
		mrs.fixIEInputs("CustomerOwnedEquipmentDetailForm");
		mrs.bindDatePicker("CustomerOwnedEquipmentDateOfPurchase");
		mrs.bindDatePicker("CustomerOwnedEquipmentWarrantyExpirationDate");
		
		$("CustomerOwnedEquipmentSaveTop").observe("click", Modules.CustomerOwnedEquipment.ForCustomer.submitForm);
		$("CustomerOwnedEquipmentSaveBottom").observe("click", Modules.CustomerOwnedEquipment.ForCustomer.submitForm);
		$("CustomerOwnedEquipmentDelete").observe("click", Modules.CustomerOwnedEquipment.ForCustomer.deleteRecord);
		
		Event.fire($("CustomerOwnedEquipmentForCustomerDetailInfo"), "customerOwnedEquipment:detailLoaded", { recordID: recordID });
	},
	
	submitForm: function(event) {
		$("CustomerOwnedEquipmentAccountNumber").value = $F("CustomerAccountNumber");
		
		if (Modules.CustomerOwnedEquipment.ForCustomer.onBeforePost(event))
		{
			new Ajax.Request("/json/customerOwnedEquipment/save", {
				parameters: $("CustomerOwnedEquipmentDetailForm").serialize(true),
				onSuccess: Modules.CustomerOwnedEquipment.ForCustomer.onPostCompleted
			});
		}
	},
	
	onBeforePost: function(event) {
		valid = true;
		
		if ($F("CustomerOwnedEquipmentDescription") == "")
		{
			$("CustomerOwnedEquipmentDescription").addClassName("FieldError");
			valid = false;
		}
		if ($F("CustomerOwnedEquipmentModelNumber") == "")
		{
			$("CustomerOwnedEquipmentModelNumber").addClassName("FieldError");
			valid = false;
		}
		
		if (valid)
		{
			Modules.CustomerOwnedEquipment.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		}
		else
		{
			alert("Please address the highlighted issues.");
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.CustomerOwnedEquipment.ForCustomer.loadingWindow.destroy();
		
		if (!transport.headerJSON.success)
		{
			alert("Save failed: " + transport.headerJSON.message);
		}
		else
		{
			$("CustomerOwnedEquipmentDetailForm").fire("client:reloadTab", { tab: "COETab" });
		}
	},
	
	toggleStatus: function(event) {
		event.stop();
		
		row = this.up("tr");
		table = row.up("table");
		recordID = row.down("input").value;
		
		table.select("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you want to switch the status of this record?"))
		{
			linkElement = this;
			
			new Ajax.Request("/json/customerOwnedEquipment/toggleActive/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						if (linkElement.innerHTML == "Yes")
						{
							linkElement.innerHTML = "No";
						}
						else
						{
							linkElement.innerHTML = "Yes";
						}
					}
				}
			});
		}
		
		row.removeClassName("Highlight");
	},
	
	deleteRecord: function(event) {
		event.stop();
		
		if (confirm("Are you sure you want to delete this record?"))
		{
			new Ajax.Request("/json/customerOwnedEquipment/delete/" + $F("CustomerOwnedEquipmentId"), {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						$("CustomerOwnedEquipmentDetailForm").fire("client:reloadTab", { tab: "COETab" });
					}
				}
			});
		}
	}
};