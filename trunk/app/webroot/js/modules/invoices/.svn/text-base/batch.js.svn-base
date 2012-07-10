Modules.Invoices.Batch = {	
	periodPostingDate: null,
	
	initialize: function(periodPostingDate) {
		//hold on to the period posting date
		Modules.Invoices.Batch.periodPostingDate = periodPostingDate;
		
		//wire up the dropdown to show/hide controls
		var type = $("BatchInvoicingModuleInvoicingType");
		type.observe("change", Modules.Invoices.Batch.adjustFilters);
		
		//wire up validation
		$("BatchInvoicingModuleForm").observe("submit", Modules.Invoices.Batch.post);
		
		//give 'em date pickers
		mrs.bindDatePicker("BatchInvoicingModuleBeginDate");
		mrs.bindDatePicker("BatchInvoicingModuleEndDate");
		
		Modules.Invoices.Batch.adjustFilters({ element: function() { return type; }});
	},
	
	post: function(event) {
		var begin = $("BatchInvoicingModuleBeginDate");
		var end = $("BatchInvoicingModuleEndDate");
		
		if (!$$R(begin) || !$$D(begin) || !$$R(end) || !$$D(end))
		{
			event.stop();
		}
	},
	
	adjustFilters: function(event) {
		var type = $F(event.element());
		var profitCenter = $("BatchInvoicingModuleProfitCenterNumber").up("div");
		var accountNumber = $("BatchInvoicingModuleAccountNumber").up("div");
		
		switch (type)
		{
			case "1":
				profitCenter.show();
				accountNumber.hide();
				break;
			case "2":
				profitCenter.hide();
				accountNumber.show();
				break;
			case "3":
				profitCenter.show();
				accountNumber.hide();
				
				var beginDate = $("BatchInvoicingModuleBeginDate");
				var endDate = $("BatchInvoicingModuleEndDate");
				
				//default the begin date to the period posting date
				if ($F(beginDate).strip() == "")
				{
					beginDate.value = Modules.Invoices.Batch.periodPostingDate;
				}
				
				if ($F(endDate).strip() == "")
				{
					//default the ending date to the last day of the month
					var endOfMonth = new Date(Modules.Invoices.Batch.periodPostingDate);
					endOfMonth.setMonth(endOfMonth.getMonth() + 1);
					endOfMonth.setDate(0);
					
					endDate.value = endOfMonth.toShortDateString();
				}
				
				break;
		}
	}
}