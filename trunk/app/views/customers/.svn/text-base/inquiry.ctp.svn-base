<?php
	$html->css(array('tabs', 'mojozoom', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'tooltips', 
		'mojozoom',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=customers.core,customers.search,client_communication_log.for_customer,customer_carriers.customer_summary,customer_carriers.for_customer,customer_owned_equipment.for_customer,rentals.for_customer,purchases.for_customer,purchase_orders.for_customer,invoices.for_customer,invoices.ledger,documents.for_customer,file_notes.create,file_notes.for_customer,prior_authorizations.for_customer,oxygen.oxygen_for_customer,oxygen.rad_for_customer,eligibility_requests.for_customer'
	), false);
?>

<script type="text/javascript">
	var searchActivated = true;
	
	function onCustomerSelected(accountNumber, name)
	{
		//set the customer name field
		$("CustomerAccountNumber").value = accountNumber;
		$("AccountName").value = name;
		
		//get the customer status and phone
		getCustomerStatus(accountNumber);
		getCustomerPhone(accountNumber);
		
		//find out if customer is in competitive bid area
		getCustomerCompetitive(accountNumber);
		
		//as soon as we load the customer, we disable searching to "lock-in" the customer
		deactivateSearchControls();
		if (!isNaN("<?= $initialTab ?>"))
		{
			Tabs.select(<?= $initialTab ?>);
		}
		else
		{
			Tabs.select($$(".TabPage").indexOf($("<?= $initialTab ?>")));
		}
		
		// Lookup customer carrier balances for table in header
		new Ajax.Updater(
			"CustomerCarrierBalanceContainer",
			"/modules/customerCarriers/clientHeader/" + $F("CustomerAccountNumber")
		);
		
		// Dim the appropriate tabs
		new Ajax.Request("/json/customers/inquiryCheckForData/" + $F("CustomerAccountNumber"), {
			onSuccess: function(transport) {
				Tabs.tabs.each(function(element, i) {
					if (!transport.headerJSON[Tabs.pages[i].id])
					{
						Tabs.tabs[i].addClassName("Disabled");
					}
				});
			}
		});
	}
	
	function onCustomerNameSelected(input, li)
	{
		var account = li.select("span.AccountNumber")[0].innerHTML.strip();
		var name = $F(input);
		
		//fire the customer selected event
		onCustomerSelected(account, name);
	}
	
	function getCustomerStatus(accountNumber)
	{
		//go find the customer's status and update the text on screen
		new Ajax.Request("/json/customers/status", {
			parameters: { accountNumber: accountNumber },
			onSuccess: function(transport) {
				var status = $("AccountStatus");
				status.update((transport.headerJSON.status == "" ? "Unknown" : (transport.headerJSON.status + (transport.headerJSON.description == "" ? "" : (" - " + transport.headerJSON.description)))) + " ");

				if (transport.headerJSON.status != "" && parseInt(transport.headerJSON.status, 10) >= 30)
				{
					status.addClassName("Exception");
				}
				else
				{
					status.removeClassName("Exception");
				}
			}			
		});
	}
	
	function getCustomerPhone(accountNumber)
	{
		//go find the customer's phone and update the text on screen
		new Ajax.Request("/json/customers/phone", {
			parameters: { accountNumber: accountNumber },
			onSuccess: function(transport) {
				$("CustomerPhone").update(transport.headerJSON.phone == "" ? "Unknown" : transport.headerJSON.phone);
			}			
		});
	}
	
	function getCustomerCompetitive(accountNumber)
	{
		new Ajax.Request("/json/customers/competitive", {
			parameters: { accountNumber: accountNumber },
			onSuccess: function(transport) {
				if (transport.headerJSON.isCompetitive)
				{
					$("AccountName").up("div").setStyle({ backgroundColor: '#ffff00' });
				}
			}
		});
	}
	
	function clearDisplayFields()
	{
		$("AccountStatus").update();
		$("CustomerPhone").update();
		$("CustomerCarrierBalanceContainer").update();
		
		Tabs.tabs.each(function(element) {
			$(element).removeClassName("Disabled");
		});
	}
	
	function activateSearchControls()
	{
		if (!searchActivated)
		{
			//enable and clear the controls
			$A(["CustomerAccountNumber", "AccountName"]).each(function(element) {
				element = $(element);
				element.removeClassName("ReadOnly");
				element.removeAttribute("readOnly");
				element.removeAttribute("tabIndex");
				element.value = "";
			});
			
			//clear the read-only fields
			clearDisplayFields();
			
			//clear the tabs
			$$(".TabPage").each(function(element) {				
				if (!element.hasClassName("Static"))
				{
					element.update();
				}
			});
			
			//reset loaded modules
			loadedModules = $A();
			
			//show the advanced search
			$("AdvancedSearchButton").show();
			
			//hide the change button
			$("ChangeCustomerButton").hide();

			$("CustomerAccountNumber").focus();
			searchActivated = true;
		}
	}
	
	function deactivateSearchControls()
	{
		if (searchActivated)
		{
			$A(["CustomerAccountNumber", "AccountName"]).each(function(element) {
				element = $(element);
				
				element.addClassName("ReadOnly");
				element.setAttribute("readOnly", "readOnly");
				element.setAttribute("tabIndex", -1);
				
				element.blur();
			});
			
			//hide the advanced search
			$("AdvancedSearchButton").hide();
			
			//show the change button
			$("ChangeCustomerButton").show();
			
			searchActivated = false;
		}
	}
	
	var loadedModules = $A();
	
	function loadModule(page, url, args)
	{
		if (loadedModules.indexOf(page.id) == -1)
		{
			var parameters = arguments[3] || {};
			var callback = arguments[4] || Prototype.K;
			
			new Ajax.Updater(
				page.update("Loading. Please wait..."), 
				url + "/" + args.collect(function(a) { return encodeURIComponent(a); }).join("/"), 
				{ 
					parameters: parameters,
					evalScripts: true, 
					onComplete: function(transport) { 
						//invoke a custom callback handler if we have one
						callback(transport); 
						
						//toggle the tab read-only
						toggleTabReadOnly(page);
						
						// Activate tooltips
						Tooltips.apply();
					}
				}
			);
			
			loadedModules.push(page.id);
		}
	}
	
	function unloadModule(page)
	{
		loadedModules = loadedModules.without(page.id);
	}
	
	function changeTab(page)
	{
		if (searchActivated)
		{
			return;
		}
		
		switch (page.id)
		{
			case "CustomerCoreTab":
				loadModule(page, "/modules/customers/core", [ $F("CustomerAccountNumber") ]);
				break;
			case "CustomerCarriersTab":
				loadModule(page, "/modules/customerCarriers/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "AaaReferralsTab":
				loadModule(page, "/modules/aaaReferrals/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "COETab":
				loadModule(page, "/modules/customerOwnedEquipment/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "RentalEquipmentTab":
				loadModule(page, "/modules/rentals/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "PurchasesTab":
				loadModule(page, "/modules/purchases/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "OnOrderTab":
				loadModule(page, "/modules/purchase_orders/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "InvoicesTab":
				loadModule(page, "/modules/invoices/forCustomer", [ $F("CustomerAccountNumber"), "showPurchasesLink:1" ]);
				break;
			case "LedgerTab":
				loadModule(page, "/modules/invoices/ledger", [ $F("CustomerAccountNumber"), "0", "1" ]);
				break;
			case "DocPopTab":
				loadModule(page, "/modules/documents/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "AuthsTab":
				loadModule(page, "/modules/priorAuthorizations/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "VOBTab":
				loadModule(page, "/modules/eligibilityRequests/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "CCLTab":
				loadModule(page, "/modules/clientCommunicationLog/forCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "eFNTab":
				//loadModule(page, "/modules/fileNotes/create", [ $F("CustomerAccountNumber") ], { showTcnFields: true });
				loadModule(page, "/modules/fileNotes/forCustomer", [ $F("CustomerAccountNumber"), "1" ]);
				break;
			case "OxygenTab":
				loadModule(page, "/modules/oxygen/oxygenForCustomer", [ $F("CustomerAccountNumber") ]);
				break;
			case "RadTab":
				loadModule(page, "/modules/oxygen/radForCustomer", [ $F("CustomerAccountNumber") ]);
				break;
		}
	}
	
	function loadSpecial(page, callback)
	{
		page = $(page);

		//deactivate our callback to prevent the tab from loading
		Tabs.changeCallback = null;
		
		//remove the module from the cache in case we already loaded it
		unloadModule(page);
		
		//select the tab
		Tabs.select($$(".TabPage").indexOf(page));
		
		//run the callback
		callback(page);
		
		//toggle it read-only
		toggleTabReadOnly(page);
		
		// Activate tooltips
		Tooltips.apply();
		
		//activate the callback again
		Tabs.changeCallback = changeTab;
	}
	
	function toggleTabReadOnly(page)
	{
		if (page.hasClassName("AllowWrite"))
		{
			return;
		}
		
		page.select("input[type=text]").each(function(input) { 
			if (!input.hasClassName("DenyReadOnly"))
			{
				input.addClassName("ReadOnlyClient").setAttribute("readOnly", "readOnly"); 
			}
		});
		
		page.select("input[type=checkbox]").each(function(input) { 
			if (!input.hasClassName("DenyReadOnly"))
			{
				input.addClassName("ReadOnlyClient").setAttribute("readOnly", "readOnly");
				input.setAttribute("disabled", "disabled");
			}
		});
		
		page.select("select").each(function(s) { 
			if (!s.hasClassName("DenyReadOnly"))
			{
				s.hide(); 
				
				var input = new Element("input", { 
					type: "text", 
					value: s.options[s.selectedIndex].innerHTML 
				});
				
				if (s.getStyle("width") != null)
				{
					input.addClassName("ReadOnlyClient").setStyle({ width: s.getStyle("width") }).setAttribute("readOnly", "readOnly");
				}
				else
				{
					input.addClassName("ReadOnlyClient").setStyle({ width: s.getDimensions().width + "px" }).setAttribute("readOnly", "readOnly");
				}
				
				s.insert({ after: input }); 
			}
		});
	}
	
	function onCustomerRequested(event) 
	{
		if (event.keyCode == Event.KEY_RETURN)
		{
			var accountNumber = $F(event.element());
			
			new Ajax.Request("/json/customers/name", {
				parameters: { accountNumber: accountNumber },
				onComplete: function(transport) {
					if (transport.headerJSON.name != false)
					{
						onCustomerSelected(accountNumber, transport.headerJSON.name);
					}
					else
					{
						mrs.showDialog("Customer not found.", event.element());
					}
				}
			});
		
			event.stop();
		}
	}
	
	document.observe("dom:loaded", function() {
		Event.observe(window, "load", function() {
			//wire up the tabs to load on click. We do this on
			//window load instead of here so that there is no race condition with the 
			//tabs dom:loaded event
			Tabs.changeCallback = changeTab;  
			
			<?php if (isset($accountNumber)): ?>
				$("CustomerAccountNumber").value = "<?= $accountNumber ?>";
				
				onCustomerRequested({
					keyCode: Event.KEY_RETURN,
					element: function() { return $("CustomerAccountNumber"); },
					stop: Prototype.emptyFunction
				});
			<?php endif; ?>
		});
		
		mrs.fixAutoCompleter("AccountName");
		
		//wire up the account number field to find the customer when they press enter
		$("CustomerAccountNumber").observe("keypress", onCustomerRequested).focus();
		
		//wire up the advanced search module
		$("AdvancedSearchButton").observe("click", function(event) {
			event.stop();
			mrs.createWindow(900, 400).setAjaxContent(
				"/modules/customers/search/",
				{ evalScripts: true }
			).show(true).activate();
		});
		
		//wire up the button to allow a user to change customers
		$("ChangeCustomerButton").observe("click", function(event) {
			event.stop();
			location.href = '/customers/inquiry';
		});
	});
	
	document.observe("customer:accountSelected", function(event) {
		//close the search dialog
		UI.defaultWM.windows()[0].close();
		
		//choose the selected customer
		var account = $("CustomerAccountNumber");
		account.value = event.memo.accountNumber;
		onCustomerSelected(event.memo.accountNumber, event.memo.name);
	});
	
	function filterLedgerByInvoice(event)
	{
		loadSpecial("LedgerTab", function(page) {
			//load the ledger for the selected invoice
			loadModule(page, "/modules/invoices/ledger", [ $F("CustomerAccountNumber"), "0", "1", "invoiceNumber:" + event.memo.invoice]);
		});
	}
	
	document.observe("customerOwnedEquipment:ledgerRequested", filterLedgerByInvoice);
	document.observe("invoice:ledgerRequested", filterLedgerByInvoice);
	
	document.observe("invoice:ledgerFilter", function(event) {
		loadSpecial("LedgerTab", function(page) {
			url = "/modules/invoices/ledger/" + event.memo.accountNumber + "/0/0";
			
			if (event.memo.invoiceNumber != "")
			{
				url += "/invoiceNumber:" + event.memo.invoiceNumber;
			}
			
			if (event.memo.carrierNumber != "")
			{
				url += "/carrierNumber:" + event.memo.carrierNumber;
			}
			
			if (event.memo.transactionType != "")
			{
				url += "/transactionType:" + event.memo.transactionType;
			}
			
			loadModule(page, url, [ ]);
		});
	});
	
	document.observe("invoice:purchasesRequested", function(event) {

		loadSpecial("PurchasesTab", function(page) {
			//load the purchases for the selected invoice
			loadModule(page, "/modules/purchases/forCustomer", [ $F("CustomerAccountNumber"), event.memo.invoice], null, function(transport) {
				//wire up a link in the tab to allow the user to remove the invoice filter
				var a = new Element("a", { href: "#" }).update("View All Purchases");
				a.observe("click", function(event) {
					unloadModule(page);
					changeTab(page);
					event.stop();
				});
				
				page.insert({ top: a });
				a.insert({ after: "<br /><br />" });
			});
		});
	});
	
	document.observe("physician:updated", function(event) {
		// reload tabs with physician info on them
		document.fire("client:reloadTab", { tab: "AaaReferralsTab" });
		document.fire("client:reloadTab", { tab: "CustomerCoreTab" });
	});
	
	document.observe("rental:invoicesRequested", function(event) {

		loadSpecial("InvoicesTab", function(page) {
			//load the invoice for the selected rental
			loadModule(page, "/modules/invoices/forCustomer", [ $F("CustomerAccountNumber"), "showPurchasesLink:1", "rentalID:" + event.memo.recordID], null, function(transport) {
				//wire up a link in the invoices to allow the user to remove the filter
				var a = new Element("a", { href: "#" }).update("View All Invoices");
				a.observe("click", function(event) {
					unloadModule(page);
					changeTab(page);
					event.stop();
				});
				
				page.insert({ top: a });
				a.insert({ after: "<br /><br />" });
			});
		});
	});
	
	document.observe("customerCarriers:detailLoaded", function(event) {
		Tooltips.apply();
	});
	
	document.observe("customerOwnedEquipment:detailLoaded", function(event) {
		Tooltips.apply();
	});
	
	document.observe("rental:detailLoaded", function(event) {
		toggleTabReadOnly($("RentalEquipmentTab"));
		Tooltips.apply();
	});
	
	document.observe("purchase:detailLoaded", function(event) {
		toggleTabReadOnly($("PurchasesTab"));
		Tooltips.apply();
	});
	
	document.observe("customerCarriers:invoicesRequested", function(event) {
		loadSpecial("InvoicesTab", function(page) {
			//load the invoice for the selected rental
			loadModule(page, "/modules/invoices/forCustomer", [ $F("CustomerAccountNumber"), "showPurchasesLink:1", "carrierNumber:" + event.memo.carrierNumber], null, function(transport) {
				//wire up a link in the invoices to allow the user to remove the filter
				var a = new Element("a", { href: "#" }).update("View All Invoices");
				a.observe("click", function(event) {
					unloadModule(page);
					changeTab(page);
					event.stop();
				});
				
				page.insert({ top: a });
				a.insert({ after: "<br /><br />" });
			});
		});
	});
	
	document.observe("fileNote:postCompleted", function(event) {
		if (event.memo.success)
		{
			mrs.showDialog("eFN successfully created.");
			Modules.FileNotes.Create.reset();
		}
		else
		{
			mrs.showDialog("There was a problem creating the eFN. Please try submitting again.");
		}
	});
	
	document.observe("client:reloadHeaderInfo", function(event) {
		getCustomerStatus($F("CustomerAccountNumber"));
		
		new Ajax.Request("/json/customers/information/" + $F("CustomerAccountNumber"), {
			onSuccess: function(transport) {
				$("AccountName").value = transport.headerJSON.record.name;
				$("CustomerPhone").update(transport.headerJSON.record.phone_number);
			}
		});
	});	
	
	document.observe("client:reloadTab", function(event) {
		page = $(event.memo.tab);
		unloadModule(page);
		Tabs.select($$(".TabPage").indexOf(page));
	});
</script>

<div style="height: 92px">
	<div class="FormColumn">
		<?= $html->link($html->image('iconAdd.png', array('title' => 'Create Customer', 'style' => 'margin-top: 10px;')), 'create', array('escape' => false)); ?>
	</div>

	<div class="FormColumn">
		<?= $form->input('Customer.account_number') ?>
	</div>
	
	<div class="FormColumn">
		<label>Account Name</label>
		<?= $ajax->autoComplete('Customer.name', '/ajax/customers/autoCompleteByName', array('id' => 'AccountName', 'style' => 'width: 180px;', 'afterUpdateElement' => 'onCustomerNameSelected', 'minChars' => 4)) ?>
		<?= $html->link($html->image('iconSearch.png'), '#', array('id' => 'AdvancedSearchButton', 'title' => 'Advanced Search', 'escape' => false)) ?>
	</div>
	
	<div class="FormColumn">
		<label>Client Phone</label>
		<span id="CustomerPhone"></span>
	</div>
	
	<div class="FormColumn">
		<label>Account Status</label>
		<span id="AccountStatus"></span>
	</div>
	
	<div class="FormColumn">
		<?= $html->link($html->image('iconCancel.png', array('style' => 'margin-top: 7px;')), '#', array('id' => 'ChangeCustomerButton', 'title' => 'Change Customer', 'style' => 'display: none;', 'escape' => false)) ?>
	</div>
	
	<div id="CustomerCarrierBalanceContainer"></div>
</div>

<div class="ClearBoth"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Core</a></li>
	<li><a href="#">Carriers</a></li>
	<li><a href="#">Contacts</a></li>
	<li><a href="#">COE</a></li>
	<li><a href="#">Rentals</a></li>
	<li><a href="#">Purchases</a></li>
	<li><a href="#">On Order</a></li>
	<li><a href="#">Invoices</a></li>
	<li><a href="#">Ledger</a></li>
	<li><a href="#">DocPop</a></li>
	<li><a href="#">Auths</a></li>
	<li><a href="#">VOB</a></li>
	<li><a href="#">WIP</a></li>
	<li><a href="#">CCL</a></li>
	<li><a href="#">eFN</a></li>
	<li><a href="#">Oxygen</a></li>
	<li><a href="#">Sleep</a></li>
</ul>

<div class="TabContainer">
	<div id="CustomerCoreTab" class="TabPage AllowWrite"></div>
	<div id="CustomerCarriersTab" class="TabPage AllowWrite" style="display: none;"></div>
	<div id="AaaReferralsTab" class="TabPage" style="display: none;"></div>
	<div id="COETab" class="TabPage AllowWrite" style="display: none;"></div>
	<div id="RentalEquipmentTab" class="TabPage" style="display: none;"></div>
	<div id="PurchasesTab" class="TabPage" style="display: none;"></div>
	<div id="OnOrderTab" class="TabPage" style="display: none;"></div>
	<div id="InvoicesTab" class="TabPage" style="display: none;"></div>
	<div id="LedgerTab" class="TabPage AllowWrite" style="display: none;"></div>
	<div id="DocPopTab" class="TabPage" style="display: none;"></div>
	<div id="AuthsTab" class="TabPage" style="display: none;"></div>
	<div id="VOBTab" class="TabPage" style="display: none;"></div>
	<div id="WIPTab" class="TabPage Static" style="display: none;">
		<h2>Under Construction</h2>
	</div>
	<div id="CCLTab" class="TabPage" style="display: none;"></div>
	<div id="eFNTab" class="TabPage AllowWrite" style="display: none;"></div>
	<div id="OxygenTab" class="TabPage AllowWrite" style="display: none;"></div>
	<div id="RadTab" class="TabPage AllowWrite" style="display: none;"></div>
</div>
