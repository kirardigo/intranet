<script type="text/javascript">
	function deleteRow(event)
	{
		row = event.element().up("tr");
		recordID = row.down("td").down("input").value;
		
		$$("tr.Highlight").invoke("removeClassName", "Highlight");
		row.addClassName("Highlight");
		
		if (confirm("Are you sure you wish to delete this record?"))
		{
			location.href = "/priorAuthorizations/delete/" + recordID;
		}
		
		row.removeClassName("Highlight");
		event.stop();
	}
	
	function editRow(event)
	{
		event.stop();
		row = event.element().up("tr");
		accountNumber = $F(row.down("td").down("input.accountNumber"));
		recordID = $F(row.down("td").down("input.recordID"));
		
		window.open("/customers/inquiry/accountNumber:" + accountNumber + "/tab:AuthsTab/load:" + recordID, "_blank");
	}
	
	function resetFilters()
	{
		$("PriorAuthorizationAccountNumber").clear();
		$("PriorAuthorizationCarrierNumber").clear();
		$("CustomerProfitCenterNumber").clear();
		
		$("PriorAuthorizationDepartmentCode").clear();
		$("PriorAuthorizationType").clear();
		$("PriorAuthorizationStatus").clear();
		
		$("PriorAuthorizationDateOfServiceStart").clear();
		$("PriorAuthorizationDateOfServiceEnd").clear();
		$("PriorAuthorizationDateRequestedStart").clear();
		$("PriorAuthorizationDateRequestedEnd").clear();
		
		$("PriorAuthorizationDateApprovedStart").clear();
		$("PriorAuthorizationDateApprovedEnd").clear();
		$("PriorAuthorizationDateDeniedStart").clear();
		$("PriorAuthorizationDateDeniedEnd").clear();
	}
	
	document.observe("dom:loaded", function() {
		mrs.bindDatePicker("PriorAuthorizationDateOfServiceStart");
		mrs.bindDatePicker("PriorAuthorizationDateOfServiceEnd");
		mrs.bindDatePicker("PriorAuthorizationDateRequestedStart");
		mrs.bindDatePicker("PriorAuthorizationDateRequestedEnd");
		mrs.bindDatePicker("PriorAuthorizationDateApprovedStart");
		mrs.bindDatePicker("PriorAuthorizationDateApprovedEnd");
		mrs.bindDatePicker("PriorAuthorizationDateDeniedStart");
		mrs.bindDatePicker("PriorAuthorizationDateDeniedEnd");
		
		$$(".editLink").invoke("observe", "click", editRow);
		$("FormSearchButton").observe("click", function() {
			$("PriorAuthSummaryForm").submit();
		});
		$("ExportButton").observe("click", function() {
			$("VirtualIsExport").value = 1;
			$("PriorAuthSummaryForm").submit();
			$("VirtualIsExport").value = 0;
		});
		$("ExportMitsButton").observe("click", function() {
			$("VirtualIsMitsExport").value = 1;
			$("PriorAuthSummaryForm").submit();
			$("VirtualIsMitsExport").value = 0;
		});
		$("ResetButton").observe("click", resetFilters);
	});
</script>

<?php
	echo $form->create('', array('url' => '/priorAuthorizations/summary', 'id' => 'PriorAuthSummaryForm'));
	
	echo '<div class="Horizontal" style="min-width: 450px;">';
		echo $form->input('PriorAuthorization.account_number', array(
			'label' => 'Acct#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.carrier_number', array(
			'label' => 'Carr#',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Customer.profit_center_number', array(
			'label' => 'PCtr',
			'options' => $profitCenters,
			'empty' => true
		));
		echo $form->input('PriorAuthorization.date_of_service_start', array(
			'label' => 'DOS Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_of_service_end', array(
			'label' => 'DOS End',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_requested_start', array(
			'type' => 'text',
			'label' => 'Requested Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_requested_end', array(
			'type' => 'text',
			'label' => 'Requested End',
			'class' => 'Text75'
		));
		echo $form->input('PriorAuthorization.date_approved_start', array(
			'type' => 'text',
			'label' => 'Approved Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_approved_end', array(
			'type' => 'text',
			'label' => 'Approved End',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_denied_start', array(
			'type' => 'text',
			'label' => 'Denied Start',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('PriorAuthorization.date_denied_end', array(
			'type' => 'text',
			'label' => 'Denied End',
			'class' => 'Text75'
		));
		echo '<div class="ClearBoth"></div>';
	echo '</div>';
	echo $form->input('PriorAuthorization.department_code', array(
		'label' => 'Dept',
		'options' => $departments,
		'empty' => true,
		'multiple' => 'multiple',
		'size' => 6,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('PriorAuthorization.type', array(
		'options' => $types,
		'empty' => true,
		'multiple' => 'multiple',
		'size' => 6,
		'div' => array('class' => 'Horizontal')
	));
	echo $form->input('PriorAuthorization.status', array(
		'options' => $statuses,
		'empty' => true,
		'multiple' => 'multiple',
		'size' => 6
	));
	
	echo '<div class="ClearBoth"></div><div style="margin: 5px 0 10px;">';
	echo $form->hidden('Virtual.is_export', array('value' => 0));
	echo $form->hidden('Virtual.is_mits_export', array('value' => 0));
	echo $form->submit('Search', array('id' => 'FormSearchButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Export', array('id' => 'ExportButton', 'style' => 'margin-right: 5px;'));
	echo $form->button('Export MITS', array('id' => 'ExportMitsButton', 'style' => 'margin-right: 5px;'));
	echo $form->button('Reset', array('id' => 'ResetButton'));
	echo $form->end();
	
	echo '</div>';
?>
<div style="margin-bottom: 5px;"></div>
<table class="Styled" style="width: 1200px;">
	<tr>
		<th>&nbsp;</th>
		<?php
			echo $paginator->sortableHeader('Carr PA#', 'authorization_id_number');
			echo '<th>Billing#</th>';
			echo '<th>Name</th>';
			echo '<th>DOB</th>';
			echo $paginator->sortableHeader('Acct#', 'account_number');
			echo $paginator->sortableHeader('TCN#', 'transaction_control_number');
			echo '<th>Desc</th>';
			echo $paginator->sortableHeader('Status', 'status');
			echo $paginator->sortableHeader('Carr#', 'carrier_number');
			echo $paginator->sortableHeader('Requested', 'date_requested');
			echo $paginator->sortableHeader('Approved', 'date_approved');
			echo $paginator->sortableHeader('Denied', 'date_denied');
			echo $paginator->sortableHeader('Appealed', 'appeals_date');
		?>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" class="accountNumber" value="' . $row['PriorAuthorization']['account_number'] . '" />' .
					'<input type="hidden" class="recordID" value="' . $row['PriorAuthorization']['id'] . '" />' .
					$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
					h($row['PriorAuthorization']['carrier_authorization_number']),
					h($row['CustomerCarrier']['claim_number']),
					h($row['Customer']['name']),
					h(formatDate($row['CustomerBilling']['date_of_birth'])),
					$html->link($row['PriorAuthorization']['account_number'], "/customers/inquiry/accountNumber:{$row['PriorAuthorization']['account_number']}", array('target' => '_blank')),
					h($row['PriorAuthorization']['transaction_control_number']),
					h($row['PriorAuthorization']['description']),
					ifset($statuses[$row['PriorAuthorization']['status']]),
					h($row['PriorAuthorization']['carrier_number']),
					formatDate($row['PriorAuthorization']['date_requested']),
					formatDate($row['PriorAuthorization']['date_approved']),
					formatDate($row['PriorAuthorization']['date_denied']),
					formatDate($row['PriorAuthorization']['appeals_date'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>
<?= $this->element('page_links'); ?>