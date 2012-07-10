Modules.Hcpc.Icd9 = {
	init: function() {		
		$$(".HcpcIcd9DeleteLink").invoke("observe", "click", Modules.Hcpc.Icd9.deleteIcd9);
		$("Icd9AddLink").observe("click", Modules.Hcpc.Icd9.addIcd9);
		
		new Ajax.Autocompleter("DiagnosisSearch", "Diagnosis_autoComplete", "/ajax/diagnoses/autoComplete", {
			minChars: 3,
			callback: Modules.Hcpc.Icd9.diagnosisSearchCallback,
			afterUpdateElement: Modules.Hcpc.Icd9.diagnosisSearchUpdate
		});
		mrs.fixAutoCompleter("DiagnosisSearch");
	},
	
	diagnosisSearchCallback: function() {
		return "data[Diagnosis][search]=" + $F("DiagnosisSearch");
	},
	
	diagnosisSearchUpdate: function(element, listItem) {
		if($F("DiagnosisSearch") != "")
		{
			new Ajax.Request("/json/diagnoses/information/" + listItem.id, {
				onSuccess: function(transport) {
					$("DiagnosisCode").value = transport.headerJSON.code;
				}
			});
		}
	},
	
	addIcd9: function(event) {
		event.stop();
	
		table = $("Icd9Table");
		hcpcCode = $F("Code");
		diagnosisCode = $F("DiagnosisCode");
		
		if (hcpcCode != "" && diagnosisCode != "")
		{
			new Ajax.Request("/json/hcpcIcd9Crosswalks/addDiagnosis/"  + hcpcCode + "/" + diagnosisCode, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						table.show();
						$$(".HcpcIcd9DeleteLink").invoke("stopObserving", "click",  Modules.Hcpc.Icd9.deleteIcd9);
						
						table.down("tbody").insert('<tr class="Auto">'
							+ '<td><input type="hidden" value="' + transport.headerJSON.id + '" />'	 + diagnosisCode + '</td>'
							+ '<td><a class="HcpcIcd9DeleteLink" href="#"><img src="/img/iconDelete.png"></a></td></tr>');
						
						$$(".HcpcIcd9DeleteLink").invoke("observe", "click",  Modules.Hcpc.Icd9.deleteIcd9);
					}
				}
			});
		}
		else
		{
			alert("The diagnosis code is required.");
		}
	},
	
	deleteIcd9: function(event) {
		event.stop();
		
		row = event.element().up("tr");
		row.addClassName("Highlight");
		recordID = row.down("td").down("input").value;
		
		if (confirm("Are you sure you wish to delete this row?"))
		{
			new Ajax.Request("/json/hcpcIcd9Crosswalks/removeDiagnosis/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
		
		row.up("table").select(".Highlight").invoke("removeClassName", "Highlight");
	}
};