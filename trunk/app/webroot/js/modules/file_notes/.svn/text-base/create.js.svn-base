Modules.FileNotes.Create = {
	_dialog: null,
	
	init: function() {
		mrs.bindDatePicker("FileNoteCreateFollowupDate");
		$("FileNoteCreateDepartmentCode").observe("change", function() {
			new Ajax.Request("/json/fileNotes/getActionCodes/" + $F("FileNoteCreateDepartmentCode"), {
				onSuccess: function(transport) {
					var codes = $H(transport.headerJSON.codes);
					$("FileNoteCreateActionCode").options.length = 1;
					
					if (transport.headerJSON.codes.length != 0)
					{
						codes.each(function(row) {
							var opt = document.createElement("option");
							opt.text = row.value;
							opt.value = row.key;
							
							$("FileNoteCreateActionCode").options.add(opt);
						});
					}
					
					$("FileNoteCreateActionCode").focus();
				}
			});
		});
	},
	
	onBeforePost: function(event) {
		
		var button = event.element();
		
		var memo = { cancel: false };
		Event.fire(event.element(), "fileNote:beforePost", memo);
		
		var valid = !memo.cancel;
		var invoice = $("FileNoteCreateInvoiceNumber");
		var tcn = $("FileNoteCreateTransactionControlNumber");
		
		//validate our fields
		valid &= $$R("FileNoteCreateRemarks1");
		valid &= $$R("FileNoteCreateDepartmentCode");
		
		if ($("FileNoteCreateActionCode").options.length > 1)
		{
			valid &= $$R("FileNoteCreateActionCode");
		}
		else
		{
			Validation.clearError($("FileNoteCreateActionCode"));
		}
		
		//if we don't have TCN fields, make the invoice required
		if (!tcn)
		{
			valid &= $$R(invoice);
		}
		else
		{
			Validation.clearError(invoice);
		}
		
		valid &= $$D("FileNoteCreateFollowupDate");
		
		//verify the invoice number
		if (!$F(invoice).blank())
		{
			new Ajax.Request("/json/invoices/verify", {
				parameters: { accountNumber: $F("FileNoteCreateAccountNumber"), invoiceNumber: $F(invoice), carrierNumber: "XXX" },
				asynchronous: false,
				onSuccess: function(transport) {
					if (!transport.headerJSON.exists)
					{
						//fake validation to force an error
						$$P(invoice, /^foo$/, "Invoice Number", "contains an invoice number that is not valid for this account.");
						valid = false;
					}
				}
			});
		}
		
		if (valid)
		{
			_dialog = mrs.showLoadingDialog();
		}
		
		return valid;
	},
	
	onPostCompleted: function(transport) {
		_dialog.destroy();
		Event.fire($("FileNoteModuleForm"), "fileNote:postCompleted", { success: transport.headerJSON.success });
	},
	
	reset: function() {
		$("FileNoteModuleForm").reset();
	}
}