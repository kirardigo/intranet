Modules.CustomerCarriers.ForCustomer = {
	loadingWindow: null,
	
	initializeSortableTable: function() {
		mrs.makeScrollable("CustomerCarriersForCustomer_CarrierTable", { sScrollY: "", aoColumns: [{bSortable: false}, null, null, null, null, null, null, null, null, null, null, null, null, {bSortable: false}] });
	},
	
	addHandlers: function(table) {
		Modules.CustomerCarriers.ForCustomer.addCarrierHandlers();
		Modules.CustomerCarriers.ForCustomer.addCarrierAutocomplete();
	},
	
	addCarrierHandlers: function(table) {
		$("CustomerCarriersForCustomer_CarrierTable").select("tbody a.Detail").invoke("observe", "click", Modules.CustomerCarriers.ForCustomer.onCarrierSelected);
		$("CustomerCarriersForCustomer_CarrierTable").select("tbody a.Delete").invoke("observe", "click", Modules.CustomerCarriers.ForCustomer.onCarrierDeleted);
	},
	
	addCarrierAutocomplete: function() {
		new Ajax.Autocompleter("CarrierSearch", "Carrier_autoComplete", "/ajax/carriers/autoComplete", {
			minChars: 3,
			afterUpdateElement: Modules.CustomerCarriers.ForCustomer.carrierSearchUpdate
		});
	},
	
	launchEdit: function(carrierID) {
		// Indicate loading is in process
		var win = mrs.showLoadingDialog();
		
		new Ajax.Updater("CustomerCarriersForCustomer_DetailInfo", "/ajax/customerCarriers/carrierDetail/" + carrierID, {
			evalScripts: true,
			onComplete: function() {
				win.destroy();
				Modules.CustomerCarriers.ForCustomer.onDetailLoaded(carrierID);
			}
		});
	},
	
	carrierSearchUpdate: function(element, listItem) {
		location.href = "/customerCarriers/create/" + $F("CustomerAccountNumber") + "/" + listItem.id;
		/*
		new Ajax.Request("/json/customerCarriers/carrierInsert/" + $F("CustomerAccountNumber") + "/" + listItem.id, {
			onComplete: function(transport) {
				if (!transport.headerJSON.success)
				{
					alert(transport.headerJSON.message);
					$("CarrierSearch").clear();
				}
				else
				{
					$("CarrierSearchForm").fire("client:reloadTab", { tab: "CustomerCarriersTab" });
				}
			}
		});
		*/
	},
	
	onCarrierSelected: function(event) {
		var selectedRow = event.element().up("tr");
		var carrierID = event.element().up("tr").down("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		
		Modules.CustomerCarriers.ForCustomer.launchEdit(carrierID);
		
		event.stop();
	},
	
	onCarrierDeleted: function(event) {
		var selectedRow = event.element().up("tr");
		var carrierID = event.element().up("tr").down("td").down("input").value;
		
		// Remove existing highlighting and add new highlight
		event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		selectedRow.addClassName("Highlight");
		$("CustomerCarriersForCustomer_DetailInfo").update("");
		
		if (confirm("Are you sure you wish to remove the selected carrier?"))
		{
			// Indicate loading is in process
			var win = mrs.showLoadingDialog();
			
			new Ajax.Request("/json/customerCarriers/carrierDelete/" + carrierID, {
				onComplete: function(transport) {
					win.destroy();
					
					if (!transport.headerJSON.success)
					{
						alert(transport.headerJSON.message);
						event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
					}
					else
					{
						$("CarrierSearchForm").fire("client:reloadTab", { tab: "CustomerCarriersTab" });
					}
				}
			});
		}
		else
		{
			event.element().up("table").select("tr").invoke("removeClassName", "Highlight");
		}
		
		event.stop();
	},
	
	onDetailLoaded: function(carrierID) {
		mrs.bindDatePicker("CustomerCarrierPolicyHolderDateOfBirth");
		mrs.bindDatePicker("CustomerCarrierPolicyEffectiveDate");
		mrs.bindDatePicker("CustomerCarrierPolicyTerminationDate");
		mrs.bindDatePicker("CustomerCarrierLastZirmedElectronicVobDate");
		mrs.bindPhoneFormatting(
			"CarrierPhoneNumber",
			"CarrierFaxNumber"
		);
		
		$("CustomerCarrierSourceOfPaymentForClaim").observe("change", function() {
			if ($F("CustomerCarrierSourceOfPaymentForClaim") == "B")
			{
				$("WorkersCompSection").show();
			}
			else
			{
				$("WorkersCompSection").hide();
			}
		});
		
		$("CustomerCarrierInsureeRelationship").observe("change", function() {
			if ($F("CustomerCarrierInsureeRelationship") == "S")
			{
				Modules.CustomerCarriers.ForCustomer.copyPolicyHolderInfo();
			}
		});
		
		$("CopyPolicyHolderLink").observe("click", function(event) {
			event.stop();
			Modules.CustomerCarriers.ForCustomer.copyPolicyHolderAddress();
		});
		
		$("CustomerCarrierSaveTop").observe("click", Modules.CustomerCarriers.ForCustomer.submitForm);
		$("CustomerCarrierSaveBottom").observe("click", Modules.CustomerCarriers.ForCustomer.submitForm);
	},
	
	copyPolicyHolderAddress: function() {
		new Ajax.Request("/json/customers/information/" + $F("CustomerAccountNumber"), {
			onSuccess: function (transport) {
				$("CustomerCarrierPolicyHolderAddress1").value = transport.headerJSON.record.address_1;
				$("CustomerCarrierPolicyHolderAddress2").value = transport.headerJSON.record.address_2;
				$("CustomerCarrierPolicyHolderCity").value = transport.headerJSON.record.city;
				$("CustomerCarrierPolicyHolderZipCode").value = transport.headerJSON.record.zip_code;
			}
		});
		
	},
	
	copyPolicyHolderInfo: function() {
		new Ajax.Request("/json/customers/information/" + $F("CustomerAccountNumber"), {
			onSuccess: function (transport) {
				if ($F("CustomerCarrierInsureeName") == "")
				{
					$("CustomerCarrierInsureeName").value = transport.headerJSON.record.name;
				}
				if ($F("CustomerCarrierPolicyHolderAddress1") == "")
				{
					$("CustomerCarrierPolicyHolderAddress1").value = transport.headerJSON.record.address_1;
				}
				if ($F("CustomerCarrierPolicyHolderAddress2") == "")
				{
					$("CustomerCarrierPolicyHolderAddress2").value = transport.headerJSON.record.address_2;
				}
				if ($F("CustomerCarrierPolicyHolderCity") == "")
				{
					$("CustomerCarrierPolicyHolderCity").value = transport.headerJSON.record.city;
				}
				if ($F("CustomerCarrierPolicyHolderZipCode") == "")
				{
					$("CustomerCarrierPolicyHolderZipCode").value = transport.headerJSON.record.zip_code;
				}
				if ($F("CustomerCarrierPolicyHolderSex") == "")
				{
					$("CustomerCarrierPolicyHolderSex").value = transport.headerJSON.billing.sex;
				}
				if ($F("CustomerCarrierPolicyHolderDateOfBirth") == "")
				{
					$("CustomerCarrierPolicyHolderDateOfBirth").value = transport.headerJSON.billing.date_of_birth;
				}
			}
		});
	},
	
	submitForm: function(event) {
		if (Modules.CustomerCarriers.ForCustomer.onBeforePost(event))
		{
			new Ajax.Request("/json/customerCarriers/saveDetail/" + $F("CustomerCarrierId"), {
				parameters: $("CustomerCarrierForm").serialize(true),
				onSuccess: Modules.CustomerCarriers.ForCustomer.onPostCompleted
			});
		}
	},
	
	onBeforePost: function(event) {
		valid = true;
		
		if ($F("CustomerCarrierCarrierType") == "")
		{
			$("CustomerCarrierCarrierType").addClassName("FieldError");
			valid = false;
		}
		if ($F("CustomerCarrierGrossChargePercentage") == "")
		{
			$("CustomerCarrierGrossChargePercentage").addClassName("FieldError");
			valid = false;
		}
		if ($F("CustomerCarrierSourceOfPaymentForClaim") == "")
		{
			$("CustomerCarrierSourceOfPaymentForClaim").addClassName("FieldError");
			valid = false;
		}
		if ($F("CustomerCarrierInsuranceTypeCode") == "")
		{
			$("CustomerCarrierInsuranceTypeCode").addClassName("FieldError");
			valid = false;
		}
		if ($F("CustomerCarrierPolicyHolderSex") == "")
		{
			$("CustomerCarrierPolicyHolderSex").addClassName("FieldError");
			valid = false;
		}
		
		if ($F("CustomerCarrierClaimNumber") == "" && (
			$F("CustomerCarrierCarrierGroupCode") == "MED" ||
			$F("CustomerCarrierCarrierGroupCode") == "INS" ||
			$F("CustomerCarrierCarrierGroupCode") == "NET" ||
			$F("CustomerCarrierCarrierGroupCode") == "WEL"))
		{
			$("CustomerCarrierClaimNumber").addClassName("FieldError");
			valid = false;
		}
		
		if (valid)
		{
			Modules.CustomerCarriers.ForCustomer.loadingWindow = mrs.showLoadingDialog();
		}
		else
		{
			alert("Please address the highlighted issues.");
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		Modules.CustomerCarriers.ForCustomer.loadingWindow.destroy();
		
		if (!transport.headerJSON.success)
		{
			alert("Save failed: " + transport.headerJSON.message);
		}
		else
		{
			$("CarrierSearchForm").fire("client:reloadTab", { tab: "CustomerCarriersTab" });
		}
	}
};