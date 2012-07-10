<script type="text/javascript">
	
	var loadedModules = $A();
	var id = "<?= $this->data['PurchaseOrderDetail']['id'] ?>";
	var poId = "<?= $poId ?>";
	
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
		//purchaseOrderNumber = $("InventoryInventoryNumber").value;

	
		switch (page.id)
		{		
			case "PurchaseOrderItemTab": 
				loadModule(page, "/modules/purchase_order_details/item_detail", [ poId, id ]);
				//loadModule(page, "/modules/purchase_order_details/item_detail", [ 47680, 215447 ]);
				break;
		}
	}
	
	function unloadModule(page)
	{
		loadedModules = loadedModules.without(page.id);
	}
	
	Event.observe(window, "load", function() {
		//Tabs.changeCallback = changeTab;
		changeTab($("PurchaseOrderItemTab"));		
	});
	
</script>

<?php if($this->params['pass']) : ?>
<?php
	echo $form->hidden('PurchaseOrderDetail.id');	
?>
<?php endif ?>

<div class="">
	<div id="PurchaseOrderItemTab" class="TabPage"></div>
</div>

<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	
	$javascript->link(array(
		'window',
		'tabs',
		'scriptaculous.js?load=effects,controls',
		'modules.js?load=purchase_order_details.core'
	), false);
?>