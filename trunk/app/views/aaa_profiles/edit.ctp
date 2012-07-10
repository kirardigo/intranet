<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	function addFact(event)
	{	
		event.stop();
		
		table = $("FactsTable");
		fact = $F("FactNew");
		profileID = $F("AaaProfileId");
		
		if(fact != "")
		{
			new Ajax.Request("/json/aaaProfileFacts/add/" + profileID + "/" + fact, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						table.show();
						$$(".FactDeleteLink").invoke("stopObserving", "click", deleteFact);
						
						table.down("tbody").insert('<tr class="Auto">'
							+ '<td><input type="hidden" value="' + transport.headerJSON.id + '" />'	+ fact + '</td>'
							+ '<td><a class="FactDeleteLink" href="#"><img src="/img/iconDelete.png"></a></td></tr>');
						
						$$(".FactDeleteLink").invoke("observe", "click", deleteFact);
					}					
				}
			});	
		}
	}
	
	function deleteFact(event)
	{
		event.stop();
		
		row = event.element().up("tr");
		row.addClassName("Highlight");
		recordID = row.down("td").down("input").value;
		
		if (confirm("Are you sure you wish to delete this row?"))
		{
			new Ajax.Request("/json/aaaProfileFacts/remove/" + recordID, {
				onSuccess: function(transport) {
					if (transport.headerJSON.success)
					{
						row.remove();
					}
				}
			});
		}
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("aaaProfile:updated", {
				id: $F("AaaProfileId")
			});
			closeWindow();
		<?php endif; ?>
		
		$("SaveButton").observe("click", function() {
			$("AaaProfileEditForm").submit();
		});
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$("FactAddLink").observe("click", addFact);
		$$(".FactDeleteLink").invoke("observe", "click", deleteFact);
		
		$("AaaProfileAaaNumber").observe("change", function() {
			new Ajax.Request("/json/AaaReferrals/information/" + $F("AaaProfileAaaNumber"), {
				onSuccess: function(transport) {
					$("AaaReferralName").innerHTML = transport.headerJSON.facility_name;
				}
			});
		});
	});
</script>

<?= $form->create('', array('url' => "edit/{$id}", 'id' => 'AaaProfileEditForm')); ?>

<div class="GroupBox">
	<h2>AAA Profile</h2>
	<div class="Content">
	<?php
		echo $form->input('AaaProfile.aaa_number', array(
			'class' => 'Text75',
			'style' => 'margin-right: 20px;',
			'after' => '<span id="AaaReferralName">' . ifset($this->data['AaaReferral']['facility_name']) . '</span>'
		));
		echo $form->input('AaaProfile.department_code', array(
			'options' => $departments,
			'empty' => true
		));
	?>
	</div>
</div>

<?php if ($id != null): ?>
<div class="GroupBox">
	<h2>Facts</h2>
	<div class="Content">
		<?php
			echo $form->input('Fact.new', array(
				'label' => false,
				'class' => 'Text400',
				'div' => false
			));
			echo $html->link($html->image('iconAdd.png'), '#', array('escape' => false, 'id' => 'FactAddLink'));
		?>
		<table id="FactsTable" class="Styled" style="width: 600px;">
		<?php
			echo $html->tableHeaders(array('Fact', ''));
			
			foreach ($this->data['AaaProfileFact'] as $row)
			{
				echo $html->tableCells(
					array(
						$form->hidden('', array('value' => $row['id'])) . h($row['fact']),
						$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'FactDeleteLink'))
					),
					array('class' => 'Auto'),
					array('class' => 'Auto Alt')
				);
			}
		?>
		</table>
	</div>
</div>

<div class="GroupBox">
	<h2>Notes</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.opportunities.note', array(
			'label' => 'Opportunities',
			'value' => isset($noteRecord['opportunities']['note']) ? $noteRecord['opportunities']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['opportunities']));
		
		echo $form->input('Note.history.note', array(
			'label' => 'History',
			'value' => isset($noteRecord['history']['note']) ? $noteRecord['history']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['history']));
		
		echo $form->input('Note.inservice.note', array(
			'label' => 'Inservice',
			'value' => isset($noteRecord['inservice']['note']) ? $noteRecord['inservice']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['inservice']));
		
		echo $form->input('Note.general.note', array(
			'label' => 'General',
			'value' => isset($noteRecord['general']['note']) ? $noteRecord['general']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['general']));
	?>
	</div>
</div>
<?php endif; ?>

<?php
	echo $form->hidden('AaaProfile.id');
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>