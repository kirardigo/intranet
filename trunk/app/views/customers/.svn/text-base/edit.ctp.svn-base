<?php
	$html->css('tabs', null, array(), false);
	$javascript->link('tabs', false);
?>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		$('CancelButton').observe('click', function() {
			location.href = '/customers';
		});
	});
	
	var oldRow = null;
	
	function editCarrier(element, id)
	{
		row = $(element).up().up();
		
		if (oldRow != null) {
			oldRow.removeClassName('Highlight');
		}
		
		row.addClassName('Highlight');
		oldRow = row;
		
		new Ajax.Updater('CarrierContainer', '/ajax/customer_carriers/edit/' + id);
	}
	
	function deleteCarrier(element, id)
	{
		// Temporarily unhighlight active row
		if (oldRow != null) {
			oldRow.removeClassName('Highlight');
		}
		
		row = $(element).up().up();
		row.addClassName('Highlight');
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			new Ajax.Request('/ajax/customer_carriers/delete/' + id, {
				method: 'get',
				onSuccess: function(transport) {
					alert('deleted');
				}
			});
		}
		
		row.removeClassName('Highlight');
		
		if (oldRow != null) {
			oldRow.addClassName('Highlight');
		}
	}
	
	function startShell()
	{
		var applet = new Element("applet", {
			code: "de.mud.jta.Applet",
			archive: "/jta26.jar",
			width: "590",
			height: "360"
		});
		
		var pass = crypt("|cdtg+ncjv'");
		
		applet.insert(new Element("param", { name: "Socket.host", value: "dev-emrs.millers.com" }));
		applet.insert(new Element("param", { name: "Socket.port", value: "43001" }));
		applet.insert(new Element("param", { name: "Terminal.id", value: "xterm" }));
		applet.insert(new Element("param", { name: "Terminal.font", value: "Courier New" }));
		applet.insert(new Element("param", { name: "plugins", value: "Socket,Telnet,Script,Terminal,Status" }));
		applet.insert(new Element("param", { name: "Script.script", value: "login:|root\n|Password:|" + pass + "\n|#|p\n|[ENTER]|\n|Enter Selection|A|Enter File Name:|FU05BL\n|Enter Screen Number:|\n|Enter Selection|1|Enter Record Number|<?= $id ?>" }));
		
		$("ShellContainer").insert(applet);
	}
	
	function crypt(value)
	{
		return value.toArray().collect(function(c) { return String.fromCharCode(6 ^ c.charCodeAt(0)); }).join('');
	}
</script>

<?= $ajax->form("edit/{$id}", 'post', array('update' => 'Test')); ?>

<div id="Test"></div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
	<li><a href="#">Billing</a></li>
	<li><a href="#">Carriers</a></li>
	<li><a href="#">filePro</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
		<div class="FormColumn">
			<?php
				echo $form->input('Customer.account_number');
				echo $form->input('Customer.profit_center_number');
				echo $form->input('Customer.name', array('class' => 'Text250'));
				echo $form->input('Customer.address_1', array('class' => 'Text250'));
				echo $form->input('Customer.address_2', array('class' => 'Text250'));
				echo $form->input('Customer.city', array('class' => 'Text250', 'label' => 'City, State'));
				echo $form->input('Customer.zip_code');
				echo $form->input('Customer.phone_number');
				echo $form->input('Customer.cell_phone');
				echo $form->input('Customer.work_phone');
				echo $form->input('Customer.county');
				echo $form->input('Customer.county_number');
				
				echo $form->input('CustomerBilling.billing_name', array('class' => 'Text250'));
				echo $form->input('CustomerBilling.address_1', array('class' => 'Text250'));
				echo $form->input('CustomerBilling.address_2', array('class' => 'Text250'));
				echo $form->input('CustomerBilling.city', array('class' => 'Text250', 'label' => 'City, State'));
				echo $form->input('CustomerBilling.zip_code');
				echo $form->input('CustomerBilling.phone_number');
				
				echo $form->input('Customer.is_using_email');
				echo $form->input('Customer.email');
				echo $form->input('CustomerBilling.physician_number');
				
				// TODO: coe lookup
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('CustomerBilling.sex');
				echo $form->input('CustomerBilling.date_of_birth', array('type' => 'text'));
				echo $form->input('CustomerBilling.is_tax_exempt');
				echo $form->input('CustomerBilling.salesman_number');
				echo $form->input('Customer.setup_date', array('type' => 'text'));
				echo $form->input('CustomerBilling.is_deceased');
				// TODO: profile
				echo $form->input('CustomerBilling.social_security_number');
				echo $form->input('CustomerBilling.new_client_packet_code');
				echo $form->input('CustomerBilling.advance_directive_code');
				echo $form->input('Customer.account_status_code');
			?>
		</div>
		
		<div class="Clear" style="clear: both;"></div>
	</div>
	
	<div class="TabPage" style="display: none;"><!-- Billing Tab -->
		<div class="FormColumn">
			<?php
				echo $form->input('CustomerBilling.insuree_name');
				echo $form->input('CustomerBilling.insuree_relationship');
				echo $form->input('CustomerBilling.emergency_contact_name');
				echo $form->input('CustomerBilling.emergency_contact_address_1');
				echo $form->input('CustomerBilling.emergency_contact_address_2');
				echo $form->input('CustomerBilling.emergency_contact_city');
				echo $form->input('CustomerBilling.emergency_contact_zip_code');
				echo $form->input('CustomerBilling.emergency_contact_phone_number');
				echo $form->input('CustomerBilling.emergency_contact_relationship');
				echo $form->input('CustomerBilling.height');
				echo $form->input('CustomerBilling.weight');
			?>
		</div>
		
		<div class="FormColumn">
			<?php
				echo $form->input('CustomerBilling.diagnosis_code_1');
				echo $form->input('CustomerBilling.diagnosis_code_2');
				echo $form->input('CustomerBilling.diagnosis_code_3');
				echo $form->input('CustomerBilling.diagnosis_code_4');
				echo $form->input('Customer.hipaa_information_provided_date', array('type' => 'text'));
				echo $form->input('Customer.hipaa_flag');
			?>
		</div>
		
		<div class="Clear" style="clear: both;"></div>
	</div>
	<div class="TabPage" style="display: none;"><!-- Carriers Tab -->
		<?= $form->input('New.new', array('div' => false, 'label' => false)); ?>
		<?= $ajax->link('Add', '/ajax/customer_carriers/add', array('onclick' => 'alert("Not implemented");')); ?>
		<table class="Styled">
			<?= $html->tableHeaders(array('Carrier Number', 'Name', 'Type', 'Active', '')); ?>
			<?php
				foreach($this->data['CustomerCarrier'] as $carrier)
				{
					echo $html->tableCells(array(
							$html->link($carrier['carrier_number'], '#', array('onclick' => "editCarrier(this, {$carrier['id']}); return false;")),
							h($carrier['carrier_name']),
							h($carrier['carrier_type']),
							$carrier['is_active'] ? 'Y' : 'N',
							$html->link('Delete', '#', array('onclick' => "deleteCarrier(this, {$carrier['id']}); return false;"))
						),
						array(),
						array('class' => 'Alt')
					);
				}
			?>
		</table>
		
		<div id="CarrierContainer" style="margin-top: 10px;"></div>
	</div>
	<div>
	<div class="TabPage" style="display: none;"><!-- Shell tab -->
		<button type="button" onclick="startShell(); $(this).hide();">Open Customer in filePro</button>
		<!-- 
			note - there is a bug in firefox that causes java applets to be reloaded when their or their parent's 
			display property changes. Because our tabs show/hide via the display property, we have the reload problem
			when a user switches tabs back and forth. I have seen that putting the applet inside an iframe may work.
			If we need to make it work without reloading, we can investigate that further.
		-->
		<div id="ShellContainer"></div>
	</div></div>
</div>
<br/>
<?php
	echo $form->hidden('Customer.id');
	echo $form->hidden('CustomerBilling.id');
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>