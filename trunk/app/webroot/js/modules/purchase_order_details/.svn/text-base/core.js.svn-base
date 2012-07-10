Modules.PurchaseOrderDetails.Core = {
	loadingWindow: null,
	
	init: function() 
	{
		$("DetailSaveButton").observe("click", Modules.PurchaseOrderDetails.Core.validateForm);
		
		mrs.bindDatePicker("PurchaseOrderDetailOrderDate");
		mrs.bindDatePicker("PurchaseOrderDetailAcknowledgementDate");
		
		$("CancelButton").observe("click", Modules.PurchaseOrderDetails.Core.closeWindow);
		
		new Ajax.Autocompleter("PurchaseOrderDetailAccountNumber", "PurchaseOrderDetailAccountNumber_autoComplete", "/ajax/customers/autoCompleteForPurchaseDetail", {
			minChars: 3,
			callback: Modules.PurchaseOrderDetails.Core.accountCodeCallback,
			afterUpdateElement: Modules.PurchaseOrderDetails.Core.accountCodeAfterUpdate
		});
		
		mrs.fixAutoCompleter("PurchaseOrderDetailAccountNumber");
		
	},
	
	accountCodeCallback: function()
	{
		return "data[PurchaseOrderDetail][search]=" + $F("PurchaseOrderDetailAccountNumber");		
	},
	
	accountCodeAfterUpdate: function(element, listItem)
	{
		var id = listItem.id;
		
		var editedId = id.replace(/customer_/i, "");
		
		new Ajax.Request("/json/customers/informationById/" + editedId, {
			onSuccess: function(transport) {
				
				$("PurchaseOrderAccountNameView").value = transport.headerJSON.name;
				//$("AaaReferralMailAddress1").value = transport.headerJSON.mail_address_1;
				//$("AaaReferralMailAddress2").value = transport.headerJSON.mail_address_2;
				//$("AaaReferralMailCityStateZip").value = transport.headerJSON.mail_city_state_zip;
			}
		});
	},
	
	validateForm: function(event)
	{
		event.stop();
		
		//alert($F("PurchaseOrderDetailInventoryNumber"));
				
		
		//check number values
		if(!$$N("PurchaseOrderDetailQuantityOrdered"))
		{
			alert("This field must be numeric.");
			$("PurchaseOrderDetailQuantityOrdered").focus();
			return false;
		}
		
		if(!$$N("PurchaseOrderDetailQuantityReceived"))
		{
			alert("This field must be numeric.");
			$("PurchaseOrderDetailQuantityReceived").focus();
			return false;
		}
		
		if(!$$N("PurchaseOrderDetailQuantityBackOrdered"))
		{
			alert("This field must be numeric.");
			$("PurchaseOrderDetailQuantityBackOrdered").focus();
			return false;
		}
		
		if(!$$N("PurchaseOrderDetailCost"))
		{
			alert("This field must be numeric.");
			$("PurchaseOrderDetailCost").focus();
			return false;
		}		
		
		//check date values
		if(!$$D("PurchaseOrderDetailOrderDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderDetailOrderDate").focus();
			return false;
		}
		
		if(!$$D("PurchaseOrderDetailAcknowledgementDate"))
		{
			alert("This field must be a date.");
			$("PurchaseOrderDetailAcknowledgementDate").focus();
			return false;
		}
		
		//check to make sure the vendor code is valid before save
		new Ajax.Request("/json/inventory/checkIfValidInventoryNumber/" + $F("PurchaseOrderDetailInventoryNumber"), {		
			onSuccess: function(transport) {
				if (transport.headerJSON.count == 0)
				{
					alert("Invalid Inventory Number");
					$("PurchaseOrderDetailInventoryNumber").focus()	
					return false;
				}
			}
		});
		
		valid = true;
		
		valid = $$R("PurchaseOrderDetailPurchaseOrderNumber");
		valid &= $$R("PurchaseOrderDetailInventoryNumber");
		valid &= $$R("PurchaseOrderDetailInventoryDescription"); 
		valid &= $$R("PurchaseOrderDetailManufacturerProductCode");
		valid &= $$R("PurchaseOrderDetailShipToProfitCenterNumber");
		valid &= $$R("PurchaseOrderDetailUnitOfMeasure");

		valid &= $$R("PurchaseOrderDetailOrderDate");
		valid &= $$R("PurchaseOrderDetailAcknowledgementDate");
		valid &= $$R("PurchaseOrderDetailQuantityOrdered");		
		valid &= $$R("PurchaseOrderDetailQuantityReceived");		
 		valid &= $$R("PurchaseOrderDetailQuantityBackOrdered");
		valid &= $$R("PurchaseOrderDetailCost");
		valid &= $$R("PurchaseOrderDetailAccountNumber");
 		valid &= $$R("PurchaseOrderDetailAccountingCode");
		valid &= $$R("PurchaseOrderDetailSalesmanNumber");
		valid &= $$R("PurchaseOrderDetailTransactionControlNumber");
		valid &= $$R("PurchaseOrderDetailTransactionControlNumberFile");
		valid &= $$R("PurchaseOrderDetailSpecialOrderFor");
		valid &= $$R("PurchaseOrderDetailCreatedByAndOn");
		//valid &= $$R("PurchaseOrderDetailComments");

				
		if (!valid)
		{
			alert("Highlighted fields are required.");
		}
	},
	
	closeWindow: function()
	{
		window.open("","_self");
		window.close();
	}
}