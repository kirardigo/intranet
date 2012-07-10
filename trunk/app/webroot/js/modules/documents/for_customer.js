Modules.Documents.ForCustomer = {
	
	initialize: function(table) {
		Modules.Documents.ForCustomer.addDocumentHandlers();
		
		var table = $("DocPopDocumentTable");
		
		mrs.makeScrollable(table, { sScrollY: "120px" });
	},
	
	addDocumentHandlers: function() {
		$("DocPopDocuments").select("a").each(function(a) {
			a.observe("click", Modules.Documents.ForCustomer.loadDocument);
		});
	},
	
	loadDocument: function(event) {
		var documentID = event.element().id.split("_")[1];
		
		Modules.Documents.ForCustomer.loadIndexInformation(documentID);
		Modules.Documents.ForCustomer.loadThumbnails(documentID);
		
		event.stop();
	},
	
	loadIndexInformation: function(documentID) {
		var indexContainer = $("DocPopIndexInformation").select(".ScrolledContent")[0];
		new Ajax.Updater(indexContainer, "/modules/documents/index/" + documentID);
	},
	
	loadThumbnails: function(documentID) {
		var win = mrs.showLoadingDialog();
		var thumbContainer = $("DocPopCanvas").select(".ScrolledContent")[0];
		new Ajax.Updater(thumbContainer, "/modules/documents/thumbnails/" + documentID, { 
			onComplete: function() { 
			
				$$("#DocPopCanvas img").each(function(img) {
					MojoZoom.makeZoomable(img, img.src.gsub(/thumbnail/, "get"), $("DocPopZoom"), 250, 172, false);
				});
			
				win.destroy(); 
			}
		});
	}
}
