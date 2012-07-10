<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=inventory.core,inventory_profit_center.profit_center,inventory_bundles.products,inventory_assemblies.products,hcpc.hcpc_carriers,inventory_special_orders.inventory'
	), false);
?>

<script type="text/javascript">
	var loadedModules = $A();
	var id = "<?= $this->data['Inventory']['id'] ?>";
	
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
		<?php if ($this->params['pass']): ?>
			inventoryNumber = $("InventoryNumberView").value;
		<?php endif; ?>
		switch (page.id)
		{		
			case "InventoryItemTab": 
				loadModule(page, "/modules/inventory/inventory_core", [ id ]);
				break;
		
			case "InventoryProfitCenterTab":
				loadModule(page, "/modules/inventoryProfitCenter/profit_center_summary", [ inventoryNumber ]);
				break;
			case "InventoryBundlesTab":
				loadModule(page, "/modules/inventoryBundles/products", [ inventoryNumber ]);
				break;
			case "InventoryAssembliesTab":
				loadModule(page, "/modules/inventoryAssemblies/products", [ inventoryNumber ]);
				break;
			case "InventorySpecialOrdersTab":
				loadModule(page, "/modules/inventorySpecialOrders/inventory", [ inventoryNumber ]);
				break;
			
		}
	}
	
	function unloadModule(page)
	{
		loadedModules = loadedModules.without(page.id);
	}
	
	Event.observe(window, "load", function() {
		Tabs.changeCallback = changeTab;
		changeTab($("InventoryItemTab"));
	});
	
	document.observe("inventory:reloadTab", function(event) {
		page = $(event.memo.tab);
		unloadModule(page);
		Tabs.select($$(".TabPage").indexOf(page));
	});
	
	document.observe("inventoryBundle:reloadTab", function(event) {
		page = $("InventoryBundlesTab");
		unloadModule(page);
		Tabs.select($$(".TabPage").indexOf(page));
	});
	
	document.observe("inventoryAssembly:reloadTab", function(event) {
		page = $("InventoryAssembliesTab");
		unloadModule(page);
		Tabs.select($$(".TabPage").indexOf(page));
	});
</script>

<?php if ($id != null): ?>
<div id="InventoryInformation" style="margin-bottom: 10px;">
	<?php
		
		echo $form->create('', array('id' => 'InventoryInformationForm', 'style' => 'margin: 0;'));
		echo $form->input('Inventory.inventory_number', array(
			'label' => 'Inventory#',
			'class' => 'Text100 ReadOnly',
			'readonly' => 'readonly',
			'div' => array('class' => 'Horizontal'),
			'id' => 'InventoryNumberView'
		));
		echo $form->input('Inventory.description', array(
			'class' => 'Text300 ReadOnly',
			'readonly' => 'readonly',
			'id' => 'InventoryDescriptionView'
		));
		echo $form->end();
	?>
	<div class="ClearBoth"></div>
</div>
<?php else: ?>
	<?php if ($this->params['pass']): ?>
		<?= $form->hidden('Inventory.inventory_number', array('id' => 'IventoryNumberView')); ?>
	<?php endif; ?>
<?php endif; ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Inventory</a></li>
	<?php if ($this->params['pass']): ?>
	<li><a href="#">Profit Centers</a></li>
	<li><a href="#">Bundles</a></li>
	<li><a href="#">Assemblies</a></li>
	<li><a href="#">Specials</a></li>
	<?php endif; ?>
</ul>

<div class="TabContainer">
	<div id="InventoryItemTab" class="TabPage"></div>
	<div id="InventoryProfitCenterTab" class="TabPage" style="display: none;"></div>
	<div id="InventoryBundlesTab" class="TabPage" style="display: none;"></div>
	<div id="InventoryAssembliesTab" class="TabPage" style="display: none;"></div>
	<div id="InventorySpecialOrdersTab" class="TabPage" style="display: none;"></div>
</div>