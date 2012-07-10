Modules.Customers.Core = {
	loadingWindow: null,
	
	addHandlers: function() {
		mrs.bindMailto("CustomerEmail");
		
		mrs.bindDatePicker("CustomerBillingHomeHealthAgencyDate");
		mrs.bindDatePicker("CustomerBillingDateOfBirth");
		mrs.bindDatePicker("CustomerBillingDateOfInjury");
		mrs.bindDatePicker("CustomerSetupDate");
		mrs.bindDatePicker("CustomerArchiveDate");
		mrs.bindDatePicker("CustomerHipaaInformationProvidedDate");
		mrs.bindDatePicker("CustomerAddressVerificationDate");
		
		mrs.bindPhoneFormatting(
			"CustomerPhoneNumber",
			"CustomerCellPhone",
			"CustomerWorkPhone",
			"CustomerBillingPhoneNumber",
			"CustomerBillingEmergencyContactPhoneNumber"
		);
		
		mrs.bindSSNFormatting("CustomerBillingSocialSecurityNumber");
		
		// Wire up handler for county autocompleter
		new Ajax.Autocompleter("CustomerCounty", "CustomerCounty_autoComplete", "/ajax/counties/autoComplete", {
			minChars: 2,
			callback: Modules.Customers.Core.countySearchCallback,
			afterUpdateElement: Modules.Customers.Core.countySearchUpdate
		});
		mrs.fixAutoCompleter("CustomerCounty");
		
		// Wire up handler to check for cleared county
		$("CustomerCounty").observe("change", Modules.Customers.Core.countyClearCheck);
		
		// Wire up handlers for physician actions
		for (i = 1; i <= 2; i++)
		{
			$("PhysicianCore" + i + "Search").observe("click", Modules.Customers.Core.physicianSearch.curry(i));
			new Ajax.Autocompleter("Physician" + i + "Name", "Physician" + i + "_autoComplete", "/ajax/physicians/autoComplete", {
				minChars: 3,
				indicator: "Physician" + i + "Indicator",
				callback: Modules.Customers.Core.physicianSearchCallback.curry(i),
				afterUpdateElement: Modules.Customers.Core.physicianSearchUpdate.curry(i)
			});
			mrs.fixAutoCompleter("Physician" + i + "Name");
			$("Physician" + i + "Name").observe("blur", Modules.Customers.Core.disableAutocomplete.curry("Physician" + i + "Name"));
			
			if ($("PhysicianCore" + i + "Delete") != undefined)
			{
				$("PhysicianCore" + i + "Delete").observe("click", Modules.Customers.Core.physicianRemove.curry(i));
				$("PhysicianCore" + i + "Edit").observe("click", Modules.Customers.Core.physicianEdit.curry(i));
			}
		}
		
		// Wire up handlers for aaa actions
		for (i = 1; i <= 3; i++)
		{
			$("AaaReferralCore" + i + "Search").observe("click", Modules.Customers.Core.aaaSearch.curry(i));
			new Ajax.Autocompleter("AaaReferral" + i + "FacilityName", "AaaReferral" + i + "_autoComplete", "/ajax/aaaReferrals/autoCompleteByName", {
				minChars: 3,
				indicator: "AaaReferral" + i + "Indicator",
				callback: Modules.Customers.Core.aaaSearchCallback.curry(i),
				afterUpdateElement: Modules.Customers.Core.aaaSearchUpdate.curry(i)
			});
			mrs.fixAutoCompleter("AaaReferral" + i + "FacilityName");
			$("AaaReferral" + i + "FacilityName").observe("blur", Modules.Customers.Core.disableAutocomplete.curry("AaaReferral" + i + "FacilityName"));
			
			if ($("AaaReferralCore" + i + "Delete") != undefined)
			{
				$("AaaReferralCore" + i + "Delete").observe("click", Modules.Customers.Core.aaaRemove.curry(i));
				$("AaaReferralCore" + i + "Edit").observe("click", Modules.Customers.Core.aaaEdit.curry(i));
			}
		}
		
		// Wire up handlers for diagnosis actions
		for (i = 1; i <= 6; i++)
		{
			$("DiagnosisCore" + i + "Search").observe("click", Modules.Customers.Core.diagnosisSearch.curry(i));
			new Ajax.Autocompleter("Diagnosis" + i + "Description", "Diagnosis" + i + "_autoComplete", "/ajax/diagnoses/autoComplete", {
				minChars: 3,
				callback: Modules.Customers.Core.diagnosisSearchCallback.curry(i),
				afterUpdateElement: Modules.Customers.Core.diagnosisSearchUpdate.curry(i)
			});
			mrs.fixAutoCompleter("Diagnosis" + i + "Description");
			$("Diagnosis" + i + "Description").observe("blur", Modules.Customers.Core.disableAutocomplete.curry("Diagnosis" + i + "Description"));
			
			if ($("DiagnosisCore" + i + "Delete") != undefined)
			{
				$("DiagnosisCore" + i + "Delete").observe("click", Modules.Customers.Core.diagnosisRemove.curry(i));
				$("DiagnosisCore" + i + "Edit").observe("click", Modules.Customers.Core.diagnosisEdit.curry(i));
			}
		}
		
		$("CopyClientInfoLink").observe("click", Modules.Customers.Core.billingInfoCopy);
		
		document.observe("physician:updated", function() {
			if ($F("Physician1Id") != "")
			{
				new Ajax.Request("/json/physicians/information/" + $F("Physician1Id"), {
					onSuccess: function(transport) {
						$("Physician1Name").value = transport.headerJSON.name;
						$("Physician1PhoneNumber").value = transport.headerJSON.phone_number;
					}
				});
			}
			if ($F("Physician2Id") != "")
			{
				new Ajax.Request("/json/physicians/information/" + $F("Physician2Id"), {
					onSuccess: function(transport) {
						$("Physician2Name").value = transport.headerJSON.name;
						$("Physician2PhoneNumber").value = transport.headerJSON.phone_number;
					}
				});
			}
		});
		
		document.observe("diagnosis:updated", function(event) {
			for (i = 1; i <= 6; i++)
			{
				if ($F("Diagnosis" + i + "Id") == event.memo.id)
				{
					Modules.Customers.Core.updateDiagnosisValues(i);
				}
			}
		});
	},
	
	updateDiagnosisValues: function(num) {
		new Ajax.Request("/json/diagnoses/information/" + $F("Diagnosis" + num + "Id"), {
			onSuccess: function(transport) {
				$("CustomerBillingDiagnosisCode" + num).value = transport.headerJSON.code;
				$("Diagnosis" + num + "Code").value = transport.headerJSON.code;
				$("Diagnosis" + num + "Description").value = transport.headerJSON.description;
			}
		});
	},
	
	countySearchCallback: function() {
		return "data[County][name]=" + $F("CustomerCounty");
	},
	
	countySearchUpdate: function(element, listItem) {
		new Ajax.Request("/json/counties/information/" + listItem.id, {
			onSuccess: function(transport) {
				$("CustomerCountyNumber").value = transport.headerJSON.number;
			}
		});
	},
	
	countyClearCheck: function() {
		if ($F("CustomerCounty") == "")
		{
			$("CustomerCountyNumber").clear();
			$("CustomerCounty").focus();
		}
	},
	
	billingInfoCopy: function(event) {
		event.stop();
		$("CustomerBillingBillingName").value = $F("CustomerName");
		$("CustomerBillingAddress1").value = $F("CustomerAddress1");
		$("CustomerBillingAddress2").value = $F("CustomerAddress2");
		$("CustomerBillingCity").value = $F("CustomerCity");
		$("CustomerBillingZipCode").value = $F("CustomerZipCode");
		$("CustomerBillingPhoneNumber").value = $F("CustomerPhoneNumber");
		
		if ($F("CustomerBillingInsureeRelationship") == "S")
		{
			$("CustomerBillingInsureeName").value = $F("CustomerName");
		}
	},
	
	updateCarrierInfo: function(accountNumber) {
		new Ajax.Updater(
			$('CustomerCoreCarriers').update("Loading. Please wait..."), 
			'/modules/customerCarriers/customerSummary/' + accountNumber,
			{
				evalScripts: true
			}
		);
	},
	
	notSetup: function(event) {
		alert("Not setup yet");
		event.stop();
	},
	
	physicianEdit: function(num, event) {
		window.open("/physicians/edit/" + $F("Physician" + num + "Id"), "_blank");
		event.stop();
	},
	
	physicianRemove: function(num, event) {
		event.stop();
		
		if (confirm("Are you sure you wish to remove this physician from this account?"))
		{
			var confirmation = false;
			var message = "";
			
			new Ajax.Request("/json/physicians/outstandingCustomerRental/" + $F("Physician" + num + "PhysicianNumber") + "/" + $F("CustomerAccountNumber"), {
				onSuccess: function(transport) {
					if (transport.headerJSON.warning)
					{
						confirmation = true;
						message = transport.headerJSON.message;
					}
					
					if (!confirmation || confirm(message + " Are you sure you wish to proceed?"))
					{
						$("Physician" + num + "Id").clear();
						$("Physician" + num + "PhysicianNumber").clear();
						$("Physician" + num + "Name").clear();
						$("Physician" + num + "PhoneNumber").clear();
						
						Modules.Customers.Core.physicianHideControls(num);
					}
				}
			});
		}
	},
	
	physicianSearch: function(num, event) {
		event.stop();
		
		var confirmation = false;
		var message = "";
		
		if ($F("Physician" + num + "PhysicianNumber") != "")
		{
			new Ajax.Request("/json/physicians/outstandingCustomerRental/" + $F("Physician" + num + "PhysicianNumber") + "/" + $F("CustomerAccountNumber"), {
				onSuccess: function(transport) {
					if (transport.headerJSON.warning)
					{
						confirmation = true;
						message = transport.headerJSON.message;
					}
					
					if (!confirmation || confirm(message + " Are you sure you wish to proceed?"))
					{
						Modules.Customers.Core.enableAutocomplete("Physician" + num + "Name");
					}
				}
			});
		}
		else
		{
			Modules.Customers.Core.enableAutocomplete("Physician" + num + "Name");
		}
	},
	
	physicianSearchCallback: function(num) {
		return "data[Physician][search]=" + $F("Physician" + num + "Name");
	},
	
	physicianSearchUpdate: function(num, element, listItem) {
		Modules.Customers.Core.disableAutocomplete(element);
		element.blur();
		
		new Ajax.Request("/json/physicians/information/" + listItem.id, {
			onSuccess: function(transport) {
				$("Physician" + num + "Id").value = listItem.id;
				$("Physician" + num + "PhysicianNumber").value = transport.headerJSON.physician_number;
				$("Physician" + num + "PhoneNumber").value = transport.headerJSON.phone_number;
				
				Modules.Customers.Core.physicianShowControls(num);
			}
		});
	},
	
	physicianShowControls: function(num) {
		$("PhysicianCore" + num + "Delete").show();
		$("PhysicianCore" + num + "Edit").show();
	},
	
	physicianHideControls: function(num) {
		$("PhysicianCore" + num + "Delete").hide();
		$("PhysicianCore" + num + "Edit").hide();
	},
	
	enableAutocomplete: function(element) {
		element = $(element);
		element.writeAttribute("readonly", false);
		element.removeClassName("ReadOnly");
		element.activate();
	},
	
	disableAutocomplete: function(element) {
		element = $(element);
		element.writeAttribute("readonly", "readonly");
		element.addClassName("ReadOnly");
	},
	
	aaaEdit: function(num, event) {
		window.open("/aaaReferrals/edit/" + $F("AaaReferral" + num + "Id"), "_blank");
		event.stop();
	},
	
	aaaRemove: function(num, event) {
		event.stop();
		
		if (confirm("Are you sure you wish to remove this AAA referral from this account?"))
		{
			$("AaaReferral" + num + "Id").clear();
			$("AaaReferral" + num + "AaaNumber").clear();
			$("AaaReferral" + num + "FacilityName").clear();
			$("AaaReferral" + num + "PhoneNumber").clear();
			
			Modules.Customers.Core.aaaHideControls(num);
		}
	},
	
	aaaSearch: function(num, event) {
		event.stop();
		Modules.Customers.Core.enableAutocomplete("AaaReferral" + num + "FacilityName");
	},
	
	aaaSearchCallback: function(num) {
		return "data[AaaReferral][search]=" + $F("AaaReferral" + num + "FacilityName");
	},
	
	aaaSearchUpdate: function(num, element, listItem) {
		Modules.Customers.Core.disableAutocomplete(element);
		element.blur();
		
		new Ajax.Request("/json/aaaReferrals/information/" + listItem.id, {
			onSuccess: function(transport) {
				$("AaaReferral" + num + "Id").value = listItem.id;
				$("AaaReferral" + num + "FacilityName").value = transport.headerJSON.facility_name + ": " + transport.headerJSON.contact_name;
				$("AaaReferral" + num + "AaaNumber").value = transport.headerJSON.aaa_number;
				$("AaaReferral" + num + "PhoneNumber").value = transport.headerJSON.phone_number;
				
				Modules.Customers.Core.aaaShowControls(num);
			}
		});
	},
	
	aaaShowControls: function(num) {
		$("AaaReferralCore" + num + "Delete").show();
		$("AaaReferralCore" + num + "Edit").show();
	},
	
	aaaHideControls: function(num) {
		$("AaaReferralCore" + num + "Delete").hide();
		$("AaaReferralCore" + num + "Edit").hide();
	},
	
	diagnosisEdit: function(num, event) {
		window.open("/diagnoses/edit/" + $F("Diagnosis" + num + "Id"), "_blank");
		event.stop();
	},
	
	diagnosisRemove: function(num, event) {
		event.stop();
		
		if (confirm("Are you sure you wish to remove this diagnosis from this account?"))
		{
			$("CustomerBillingDiagnosisCode" + num).clear();
			$("Diagnosis" + num + "Code").clear();
			$("Diagnosis" + num + "Description").clear();
			
			Modules.Customers.Core.diagnosisHideControls(num);
		}
	},
	
	diagnosisSearch: function(num, event) {
		event.stop();
		Modules.Customers.Core.enableAutocomplete("Diagnosis" + num + "Description");
	},
	
	diagnosisSearchCallback: function(num) {
		return "data[Diagnosis][search]=" + $F("Diagnosis" + num + "Description");
	},
	
	diagnosisSearchUpdate: function(num, element, listItem) {
		Modules.Customers.Core.disableAutocomplete(element);
		element.blur();
		
		new Ajax.Request("/json/diagnoses/information/" + listItem.id, {
			onSuccess: function(transport) {
				$("Diagnosis" + num + "Id").value = listItem.id;
				$("Diagnosis" + num + "Code").value = transport.headerJSON.code;
				$("CustomerBillingDiagnosisCode" + num).value = transport.headerJSON.number;
				
				Modules.Customers.Core.diagnosisShowControls(num);
			}
		});
	},
	
	diagnosisShowControls: function(num) {
		$("DiagnosisCore" + num + "Delete").show();
		$("DiagnosisCore" + num + "Edit").show();
	},
	
	diagnosisHideControls: function(num) {
		$("DiagnosisCore" + num + "Delete").hide();
		$("DiagnosisCore" + num + "Edit").show();
	},
	
	onBeforePost: function(event) {
		$$("#CustomerModuleCoreForm .FieldError").invoke("removeClassName", "FieldError");
		$$("#CustomerModuleCoreForm .error-message").invoke("remove");
		
		if (!$F("CustomerName").include(", "))
		{
			if (!confirm("Name should be Last Name, First Name. Press Cancel to correct."))
			{
				return false;
			}
		}
		
		if ($F("CustomerHipaaInformationProvidedDate") != "" && $F("CustomerHipaaFlag") == "")
		{
			$("CustomerHipaaFlag").addClassName("FieldError");
			$("CustomerHipaaFlag").up("div").insert(new Element("div").addClassName("error-message").update("Flag must be specified if date is set."));
			$("CustomerHipaaFlag").scrollTo();
			return false;
		}
		
		if ($F("CustomerHipaaFlag") == "PNR" && $F("CustomerHipaaNote") == "")
		{
			$("CustomerHipaaNote").addClassName("FieldError");
			$("CustomerHipaaNote").up("div").insert(new Element("div").addClassName("error-message").update("Note must be specified based on flag."));
			$("CustomerHipaaNote").scrollTo();
			return false;
		}
		
		Modules.Customers.Core.loadingWindow = mrs.showLoadingDialog();
		return true;
	},
	
	onPostCompleted: function(transport) {
		Modules.Customers.Core.loadingWindow.destroy();
		
		var json = transport.responseText.evalJSON();
		
		if (!json.success)
		{
			$$("#CustomerModuleCoreForm .FieldError").invoke("removeClassName", "FieldError");
			$$("#CustomerModuleCoreForm .error-message").invoke("remove");
			
			if (json.message != "")
			{
				alert(json.message);
			}
			
			fieldErrors = false;
			
			if (json.customerErrors != "")
			{
				$H(json.customerErrors).each(function(row) {
					$(row[0]).addClassName("FieldError");
					$(row[0]).up("div").insert(new Element("div").addClassName("error-message").update(row[1]));
					fieldErrors = true;
				});
			}
			
			if (json.billingErrors != "")
			{
				$H(json.billingErrors).each(function(row) {
					$(row[0]).addClassName("FieldError");
					$(row[0]).up("div").insert(new Element("div").addClassName("error-message").update(row[1]));
					fieldErrors = true;
				});
			}
			
			if (fieldErrors)
			{
				fieldErrors = false;
				alert("Please address the highlighted issues");
			}
		}
		else
		{
			$("CustomerModuleCoreForm").fire("client:reloadHeaderInfo");
			$("CustomerModuleCoreForm").fire("client:reloadTab", { tab: "CustomerCoreTab" });
		}
	}
}