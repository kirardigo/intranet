<?= $javascript->link('scriptaculous.js?load=effects,controls', false) ?>

<script type="text/javascript">
	function closeWindow()
	{
		window.open("", "_self");
		window.close();
	}
	
	function stripeTable(table)
	{
		$(table).down("tbody").select("tr").each(function(row, i) {
			row.removeClassName("Alt");
			
			if (i % 2 == 1)
			{
				row.addClassName("Alt");
			}
		});
	}
	
	function addZip(event)
	{
		if (!$$R("NewZipCode", "Zip Code"))
		{
			return;
		}
		
		var zipTextbox = $("NewZipCode");
		var bid = $F("HcpcCompetitiveBidBidNumber");
		var zip = $F(zipTextbox);
		
		new Ajax.Request("/json/hcpcCompetitiveBidZipCodes/add/", {
			method: "post",
			parameters: {
				"data[HcpcCompetitiveBidZipCode][bid_number]": bid,
				"data[HcpcCompetitiveBidZipCode][zip_code]": zip
			},
			onSuccess: function(transport, json) {
				if (json.success)
				{
					//insert the zip they added into a new table row
					var row = new Element("tr");
					
					var cell = new Element("td").update(zip);
					row.insert(cell);
				
					cell = new Element("td").addClassName("Right").update();
					var link = new Element("a", { href: "#"}).addClassName("deleteLink").observe("click", deleteZip);
					var image = new Element("img", { src: "/img/iconDelete.png" });
					
					link.insert(image);
					cell.insert(link);
					row.insert(cell);
					
					var table = $("ZipCodeTable");
					table.down("tbody").insert(row);
					
					//restripe the table for coloring
					stripeTable(table);
					
					//take the user back to the zip code textbox
					zipTextbox.value = "";
					zipTextbox.focus();
				}
				else
				{
					alert("The zip code could not be added. Please verify that the zip code isn't already associated with this bid number.");
				}
			}
		});
	}
	
	function deleteZip(event)
	{
		var zip = this.up("td").previous("td").innerHTML.strip();
		
		if (confirm("Are you sure you want to delete zip code " + zip + "?"))
		{
			new Ajax.Request("/json/hcpcCompetitiveBidZipCodes/delete/", {
				method: "post",
				parameters: {
					"data[HcpcCompetitiveBidZipCode][bid_number]": $F("HcpcCompetitiveBidBidNumber"),
					"data[HcpcCompetitiveBidZipCode][zip_code]": zip
				},
				onSuccess: function(transport, json) {					
					this.up("tr").remove();
					stripeTable($("ZipCodeTable"));
				}.bind(this)
			});
		}
				
		event.stop();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			//on a successful postback, close the entire window
			closeWindow();
		<?php endif; ?>
		
		//validate the form client-side on submit
		$("SaveButton").observe("click", function(event) {
			if (!$$R("HcpcCompetitiveBidBidNumber") || !$$N("HcpcCompetitiveBidBidNumber") || !$$R("CarrierSearch"))
			{
				event.stop();
			}
		});
		
		//close the window on cancel
		$("CancelButton").observe("click", closeWindow);
		
		//wire up the carriers autocompleter
		new Ajax.Autocompleter("CarrierSearch", "Carrier_autoComplete", "/ajax/carriers/autoComplete", {
			minChars: 3
		});
		
		<?php if ($id !== null): ?>
			//set up the add button for a zip code
			$("AddButton").observe("click", addZip);
		
			//set up the delete links for the zip codes
			$$(".DeleteLink").invoke("observe", "click", deleteZip);
			
			$("NewZipCode").observe("keypress", function(event) {
				if (event.keyCode == Event.KEY_RETURN)
				{
					addZip(event);
					event.stop();
				}
			});
		<?php endif; ?>
	});
</script>
<?php
	echo $form->create('HcpcCompetitiveBid', array('url' => "/hcpcCompetitiveBids/edit/{$id}"));
	
	echo $form->hidden('id');
	echo $form->input('bid_number', $id == null ? array('class' => 'Text50') : array('class' => 'Text50 ReadOnly', 'readonly' => 'readonly'));
	echo $form->input('Carrier.search', array('label' => 'Carrier Number', 'class' => 'Text50'));
	echo '<div style="display: none;" id="Carrier_autoComplete" class="auto_complete AutoComplete550"></div>';
?>

<?php if ($id !== null): ?>

	<br /><br />

	<div class="GroupBox" style="width: 400px;">
		<h2>Zip Codes</h2>
		
		<div class="Content">
			Add a zip code: <?= $form->text('zip_code', array('id' => 'NewZipCode', 'maxlength' => 9, 'class' => 'Text75')) ?>
			<?= $form->button('Add', array('id' => 'AddButton', 'class' => 'StyledButton')) ?>
			<br />
			
			<table class="Styled" id="ZipCodeTable">
				<thead>
					<tr><th>Zip Code</th><th class="Right">&nbsp;</th></tr>
				</thead>
				<tbody>
					<?php
						foreach ($zips as $zip)
						{
							echo $html->tableCells(
								array(
									h($zip['HcpcCompetitiveBidZipCode']['zip_code']),
									array($html->link($html->image('iconDelete.png'), '#', array('class' => 'DeleteLink', 'escape' => false)), array('class' => 'Right'))
								),
								array(),
								array('class' => 'Alt')
							);
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?>

<?
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	
	echo $form->end();
?>