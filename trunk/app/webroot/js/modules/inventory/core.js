Modules.Inventory.Core = {
	loadingWindow: null,
	popupWindow: null,
	
	init: function()
	{	
		mrs.bindDatePicker("InventoryVendorCostDate");
		mrs.bindDatePicker("InventoryCostOfGoodsSoldUpdateDate");
		mrs.bindDatePicker("InventoryLastPriceDate");
		mrs.bindDatePicker("InventoryReplacementOrDiscontinuationDate");
	
		$("CancelButtonTop").observe("click", Modules.Inventory.Core.closeWindow);
		$("CancelButtonBottom").observe("click", Modules.Inventory.Core.closeWindow);
		//$("CopyButton").observe("click", Modules.Inventory.Core.copyInventoryItem);
		
		$("InventoryFlatRateCode").observe("change", Modules.Inventory.Core.onFlatRateCodeChange);
		$("InventoryGeneralLedgerSalesCode").observe("change", Modules.Inventory.Core.onGeneralLedgerSalesCodeChange);
		$("InventoryGeneralLedgerRentalCode").observe("change", Modules.Inventory.Core.onGeneralLedgerSalesCodeChange);
		$("InventoryMedicareHealthcareProcedureCode").observe("change", Modules.Inventory.Core.onMedicareHCPCChange);
		$("InventoryReplacementOrDiscontinuationInventory").observe("change", Modules.Inventory.Core.onReplacementChange);
		
		Modules.Inventory.Core.displayHCPCCarriers();
		Modules.Inventory.Core.flatRateCode();
		Modules.Inventory.Core.lookupHCPCDescription($("InventoryMedicareHealthcareProcedureCode"));
		Modules.Inventory.Core.lookupGeneralLedgerDescription($("InventoryGeneralLedgerSalesCode"));
		Modules.Inventory.Core.lookupGeneralLedgerDescription($("InventoryGeneralLedgerRentalCode"));
		Modules.Inventory.Core.lookupInventoryDescription($("InventoryReplacementOrDiscontinuationInventory"));
	},
	
	onGeneralLedgerSalesCodeChange: function(event)
	{
		event.stop();
		Modules.Inventory.Core.lookupGeneralLedgerDescription(this);
	},
	
	lookupGeneralLedgerDescription: function(element)
	{
		if (element.value != "")
		{
			new Ajax.Request("/json/general_ledger/description/", {
				parameters: {
					code: element.value
				},
				onSuccess: function(transport) {
					element.next(".GLDescription").innerHTML = transport.headerJSON.description;
				}
			});
		}
		else
		{
			element.next(".GLDescription").update();
		}
	},
	
	onMedicareHCPCChange: function(event)
	{
		event.stop();
		Modules.Inventory.Core.lookupHCPCDescription(this);
	},
	
	lookupHCPCDescription: function(element)
	{
		if (element.value != "")
		{
			new Ajax.Request("/ajax/hcpc/description/", {
				parameters: { code: element.value },
				onSuccess: function(transport) {
					$("HealthcareProcedureCodeDescription").value = transport.responseText;
				}
			});
		}
		else
		{
			$("HealthcareProcedureCodeDescription").clear();
		}
	},
	
	onReplacementChange: function(event)
	{
		event.stop();
		Modules.Inventory.Core.lookupInventoryDescription(this)
	},
	
	lookupInventoryDescription: function(element)
	{
		if (element.value != "")
		{
			new Ajax.Request("/ajax/inventory/description/", {
				parameters: { inventory_number: element.value },
				onSuccess: function(transport) {
					$("InventoryReplacementDescription").value = transport.responseText;
				}
			});
		}
		else
		{
			$("InventoryReplacementDescription").clear();
		}
	},
	
	copyInventoryItem: function(event) {
		event.stop();
		
		Modules.Inventory.Core.popupWindow = mrs.createWindow(700, 200).setAjaxContent(
			"/modules/inventory/copy/" + $("InventoryId").value,
			{ evalScripts: true }
		).show(true).activate();
	},
	
	displayHCPCCarriers: function()
	{
		var hcpcCode = $("InventoryMedicareHealthcareProcedureCode").value;
		
		if (hcpcCode)
		{
			new Ajax.Updater($("divCarriers").update(), "/modules/hcpc/hcpc_carriers/" + hcpcCode, {
				evalScripts: true
			});	
		}
				
	},
	
	onFlatRateCodeChange: function(event)
	{
		event.stop();
		Modules.Inventory.Core.flatRateCode();			
	},
	
	flatRateCode: function()
	{
		new Ajax.Request("/json/serviceFlatRates/selectCarrierCode/" + $("InventoryFlatRateCode").value, {
				onSuccess: function(transport) {
					$("divFlatRateDescription").show();
					
					$("DescriptionValue").innerHTML = transport.headerJSON.description;
					$("MrsFlatRateValue").innerHTML = transport.headerJSON.mrs_flat_rate;
					$("CmsFlatRateValue").innerHTML = transport.headerJSON.cms_flat_fate;
				}
		});
	},
	
	onServiceFlatRateSelected: function(input, li)
	{
		//get our value from our ajax response
		var description = li.select("span.Description")[0].innerHTML.strip();
		var mrsFlatRate = li.select("span.MrsFlatRate")[0].innerHTML.strip();
		var csmFlatRate = li.select("span.CmsFlatRate")[0].innerHTML.strip();
		
		//set the view control values and show the containing div
		$("DescriptionValue").innerHTML = description;
		$("divFlatRateDescription").show();
		
		$("MrsFlatRateValue").innerHTML = mrsFlatRate;
		//$("divMrsFlatRate").show();
		
		$("CmsFlatRateValue").innerHTML = mrsFlatRate;
		//$("divCmsFlatRate").show()
	},
	
	onBeforePost: function(event)
	{
		event.stop();
		
		$$(".FieldError").invoke("removeClassName", "FieldError");
		
		valid = true;
		
		if($F("InventoryCustomaryRateOrRetailSalesRate") != "" && !$$N("InventoryCustomaryRateOrRetailSalesRate", true))
		{
			alert("This MSRP Sale field must be numeric.");
			$("InventoryCustomaryRateOrRetailSalesRate").focus();
			return false;
		}
		
		if ($F("InventoryCustomaryRateOrRetailRentalRate") != "" && !$$N("InventoryCustomaryRateOrRetailRentalRate", true))
		{
			alert("This MSRP Rental field must be numeric.");
			$("InventoryCustomaryRateOrRetailRentalRate").focus();
			return false;
		}
		
		if ($("InventoryCostOfGoodsSoldMfg") != undefined && $F("InventoryCostOfGoodsSoldMfg") != "" && !$$N("InventoryCostOfGoodsSoldMfg", true))
		{
			alert("This Vendor Cost field must be numeric.");
			$("InventoryCostOfGoodsSoldMfg").focus();
			return false;
		}
		
		if ($("InventoryVendorCostDate") != undefined && $F("InventoryVendorCostDate") != "" && !$$D("InventoryVendorCostDate"))
		{
			alert("This Vendor Cost Date field does not contain a valid date.");
			$("InventoryVendorCostDate").focus();
			return false;
		}
		
		//**inventory number
		valid &= $$R("InventoryInventoryNumber");
		
		//**inventory description
		valid &= $$R("InventoryDescription");
		
		//**mrsp sale and rental
		valid &= $$R("InventoryCustomaryRateOrRetailSalesRate");
		
		valid &= $$R("InventoryCustomaryRateOrRetailRentalRate");
		
		//**unit of measure
		valid &= $$R("InventoryRetailUnitOfMeasure");
		
		//**cost
		valid &= $$R("InventoryCostOfGoodsSoldMfg");
		
		//**cost date
		valid &= $$R("InventoryVendorCostDate");
		
		//**GL Codes
		valid &= $$R("InventoryGeneralLedgerSalesCode");
		valid &= $$R("InventoryGeneralLedgerRentalCode");
		
		//check the validity of the gl sales code again
		new Ajax.Request("/json/general_ledger/description/", {
			parameters: {
				code: $F("InventoryGeneralLedgerSalesCode")
			},
			onSuccess: function(transport) {
				if (transport.headerJSON.description == "")
				{
					alert("Invalid GL Code");
					$(currentControlID).focus()	
					valid = false;
				}
			}
		});
		
		//check the validity of the gl rental code again
		new Ajax.Request("/json/general_ledger/description/", {
			parameters: {
				code: $F("InventoryGeneralLedgerRentalCode")
			},
			onSuccess: function(transport) {
				if (transport.headerJSON.description == "")
				{
					alert("Invalid GL Code");
					$(currentControlID).focus()	
					valid = false;
				}
			}
		});	
	
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			Modules.Inventory.Core.loadingWindow = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	copyOnBeforePost: function(event)
	{
		event.stop();
	
		valid = true;
		
		//inventory section
		valid &= $$R("InventoryNewInventoryNumber");
		
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
		else
		{
			Modules.Inventory.Core.loadingWindow = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	copyOnPostCompleted: function(transport)
	{
		var json = transport.responseText.evalJSON();
		
		Modules.Inventory.Core.loadingWindow.destroy();
		Modules.Inventory.Core.popupWindow.close();
		
		document.location.href = "/inventory/edit/" + json.insertedID;
	},
	
	onPostCompleted: function(transport)
	{
		Modules.Inventory.Core.loadingWindow.destroy();
		
		var json = transport.responseText.evalJSON();
		
		if (!json.success)
		{				
			alert("There was a problem saving the Inventory record. Please try again.");
		}
		else
		{
			document.location.href = "/inventory/edit/" + json.id;
		}
	},
	
	closeCopyWindow: function()
	{
		Modules.Inventory.Core.popupWindow.close();
	},
	
	closeWindow: function()
	{
		window.open("","_self");
		window.close();
	}
}