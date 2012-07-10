<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=carriers.benefits,carriers.department,carriers.claims'
	), false);
?>

<script type="text/javascript">
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
					}
				}
			);
			
			loadedModules.push(page.id);
		}
	}
	
	function changeTab(page)
	{
		switch (page.id)
		{
			case "BenefitsTab": 
				loadModule(page, "/modules/carriers/benefits", [ $F("CarrierId") ]);
				break;
			case "DepartmentTab": 
				loadModule(page, "/modules/carriers/department", [ $F("CarrierId") ]);
				break;
			case "ClaimsTab":
				loadModule(page, "/modules/carriers/claims", [ $F("CarrierId") ]);
				break;
		}
	}
	
	function validateForm(event)
	{
		event.stop();
		
		valid = true;
		
		if ($("CarrierName") == undefined || $F("CarrierName") == "")
		{
			$("CarrierName").addClassName("FieldError");
			valid = false;
		}
		if ($("CarrierAddress1") == undefined || $F("CarrierAddress1") == "")
		{
			$("CarrierAddress1").addClassName("FieldError");
			valid = false;
		}
		if ($("CarrierCity") == undefined || $F("CarrierCity") == "")
		{
			$("CarrierCity").addClassName("FieldError");
			valid = false;
		}
		if ($("CarrierZipCode") == undefined || $F("CarrierZipCode") == "")
		{
			$("CarrierZipCode").addClassName("FieldError");
			valid = false;
		}
		if ($("CarrierPhoneNumber") == undefined || $F("CarrierPhoneNumber") == "")
		{
			$("CarrierPhoneNumber").addClassName("FieldError");
			valid = false;
		}
		if ($("CarrierStatementType") != undefined && $F("CarrierStatementType") == "")
		{
			$("CarrierStatementType").addClassName("FieldError");
			valid = false;
		}
		
		if (!valid)
		{
			alert("Please address highlighted issues.");
		}
		else
		{
			$("CarrierForm").submit();
		}
	}
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("BenefitsTab"));
		
		$("SaveButton").observe("click", validateForm);
		$("CancelButton").observe("click", closeWindow);
	});
</script>

<?php
	echo $form->create('', array('url' => "/carriers/edit/{$this->data['Carrier']['id']}", 'id' => 'CarrierForm'));
	echo $form->hidden('Carrier.id');
	echo $form->input('Carrier.carrier_number', array(
		'label' => 'Carr#',
		'class' => 'Text50 ReadOnly',
		'readonly' => 'readonly',
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('Carrier.name', array(
		'label' => 'Name',
		'class' => 'Text300 ReadOnly',
		'readonly' => 'readonly'
	));
?>

<div class="ClearBoth"></div><br/>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Benefits</a></li>
	<li><a href="#">Department</a></li>
	<li><a href="#">Claims/Contract</a></li>
</ul>

<div class="TabContainer">
	<div id="BenefitsTab" class="TabPage"></div>
	<div id="DepartmentTab" class="TabPage" style="display: none;"></div>
	<div id="ClaimsTab" class="TabPage" style="display: none;"></div>
</div>

<?php
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>