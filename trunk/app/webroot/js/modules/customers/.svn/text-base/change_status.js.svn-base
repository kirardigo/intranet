Modules.Customers.ChangeStatus = {	
	onStatusChanged: function(event) {
		
		if (!event.memo.success)
		{
			alert("There was a problem changing the status. Please try again.");
			return;
		}
		
		Event.fire(event.element(), "customer:statusChanged");
	},
	
	//this should be invoked by the object creating the module when it's finished
	destroy: function() {
		document.stopObserving("fileNote:postCompleted", Modules.Customers.ChangeStatus.onStatusChanged);
	}
}