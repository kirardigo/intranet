<?php 
	if (!$isPostback)
	{
		echo $ajax->form('',
			'post',
			array(
				'id' => 'COESummaryForm',
				'url' => '/modules/customerOwnedEquipment/management',
				'update' => 'COEManagementContainer',
				'before' => 'Modules.CustomerOwnedEquipment.Management.showLoadingDialog();',
				'complete' => 'Modules.CustomerOwnedEquipment.Management.closeLoadingDialog();'
			)
		);
		
		echo $form->input('CustomerOwnedEquipment.account_number', array(
			'label' => 'Acct#',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.is_active', array(
			'label' => 'Active',
			'options' => array(1 => 'Yes', 0 => 'No'),
			'empty' => true,
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.date_of_purchase_start', array(
			'label' => 'DOP Start',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.date_of_purchase_end', array(
			'label' => 'DOP End',
			'type' => 'text',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.manufacturer_frame_code', array(
			'label' => 'MFG',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.model_number', array(
			'label' => 'Model#',
			'class' => 'Text200',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.purchase_healthcare_procedure_code', array(
			'label' => 'HCPC',
			'class' => 'Text75',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.initial_carrier_number', array(
			'label' => 'Carr#',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerOwnedEquipment.tilt_manufacturer_code', array(
			'label' => 'Tilt MFG',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('Customer.profit_center_number', array(
			'label' => 'PCtr',
			'class' => 'Text50'
		));
		echo '<div class="ClearBoth"></div>';
		
		echo $form->input('CustomerBilling.program_options', array(
			'label' => 'Prog',
			'options' => array(
				0 => 'Use Filter',
				1 => 'Blank',
				2 => 'Not Blank'
			),
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerBilling.school_or_program_number_from_aaa_file', array(
			'label' => 'Prog',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerBilling.ltcf_options', array(
			'label' => 'LTCF',
			'options' => array(
				0 => 'Use Filter',
				1 => 'Blank',
				2 => 'Not Blank'
			),
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerBilling.long_term_care_facility_number', array(
			'label' => 'LTCF',
			'class' => 'Text50',
			'div' => array('class' => 'Horizontal')
		));
		echo $form->input('CustomerBilling.salesman_number', array(
			'label' => 'Sls*',
			'maxlength' => false,
			'class' => 'Text100',
		));
		
		echo '<div class="ClearBoth"></div><div style="margin: 5px 0 0 0;">';
			echo $form->submit('Search', array('id' => 'SearchButton', 'div' => array('class' => 'Horizontal')));
			echo $form->button('Reset', array('id' => 'ResetButton', 'class' => 'StyledButton', 'style' => 'margin-right: 10px;'));
			echo '* Separate multiple values with commas';
		echo '</div>';
		
		echo $form->end();
	}
?>

<?php if (!$isPostback): ?>
	<div id="COEManagementContainer" style="margin-top: 5px;">
<?php endif; ?>

<?php if ($isPostback): ?>
	<?php 
		$paginator->options(array(
			'url' => array(
				'controller' => 'modules/customerOwnedEquipment', 
				'action' => 'management'
			),
			'params' => $this->passedArgs
		));	
		
		echo $paginator->link('Export to Excel', array('controller' => 'ajax/customerOwnedEquipment', 'action' => 'exportManagementResults'));
		
		//now that we wrote a non-ajax link for the Excel, we can go ahead and make the rest of the links be ajax
		$paginator->options['update'] = 'COEManagementContainer';
	?>
	
	<br/><br/>
	
	<div style="margin-bottom: 5px;"></div>
	<table id="COEManagementTable" class="Styled" style="width: 1800px;">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<?php
					echo $paginator->sortableHeader('Acct#', 'account_number');
					echo '<th>Status</th>';
					echo '<th>Name</th>';
					echo '<th>PCtr</th>';
					echo '<th>Profile</th>';
					echo '<th>Prog</th>';
					echo '<th>LTCF</th>';
					echo $paginator->sortableHeader('COE#', 'customer_owned_equipment_id_number');
					echo $paginator->sortableHeader('Active', 'is_active');
					echo $paginator->sortableHeader('DOP', 'date_of_purchase');
					echo '<th>Service</th>';
					echo '<th>Sleep</th>';
					echo $paginator->sortableHeader('MFG', 'manufacturer_frame_code');
					echo '<th>Desc</th>';
					echo $paginator->sortableHeader('Model#', 'model_number');
					echo $paginator->sortableHeader('Serial#', 'serial_number');
					echo $paginator->sortableHeader('HCPC', 'purchase_healthcare_procedure_code');
					echo $paginator->sortableHeader('Carr#', 'initial_carrier_number');
					echo $paginator->sortableHeader('Invoice#', 'invoice_number');
					echo $paginator->sortableHeader('TCN#', 'transaction_control_number');
					echo $paginator->sortableHeader('Tilt MFG', 'tilt_manufacturer_code');
					echo $paginator->sortableHeader('Tilt Model#', 'tilt_model_number');
					echo $paginator->sortableHeader('Tilt Serial#', 'tilt_serial_number');
				?>
			</tr>
		</thead>
		<tbody>
		<?php
			foreach ($records as $row)
			{
				echo $html->tableCells(
					array(array(
						'<input type="hidden" class="accountNumber" value="' . $row['CustomerOwnedEquipment']['account_number'] . '" />' .
						'<input type="hidden" class="recordID" value="' . $row['CustomerOwnedEquipment']['original_id'] . '" />' .
						$html->link($html->image('iconEdit.png'), '#', array('class' => 'editLink', 'escape' => false)),
						$html->link($row['CustomerOwnedEquipment']['account_number'], "/customers/inquiry/accountNumber:{$row['CustomerOwnedEquipment']['account_number']}", array('target' => '_blank')),
						h($row['CustomerOwnedEquipment']['account_status_code']),
						h($row['CustomerOwnedEquipment']['customer_name']),
						h($row['CustomerOwnedEquipment']['profit_center_number']),
						h($row['CustomerOwnedEquipment']['stats_profile']),
						array($html->div('COEManagementAAATip TooltipContainer', h($row['CustomerOwnedEquipment']['aaa_program_number']), array(), true), array('class' => 'Right')),
						array($html->div('COEManagementAAATip TooltipContainer', h($row['CustomerOwnedEquipment']['aaa_ltcf_number']), array(), true), array('class' => 'Right')),
						h($row['CustomerOwnedEquipment']['customer_owned_equipment_id_number']),
						($row['CustomerOwnedEquipment']['is_active'] ? 'Yes' : 'No'),
						formatDate($row['CustomerOwnedEquipment']['date_of_purchase']),
						formatDate($row['CustomerOwnedEquipment']['last_service_date']),
						formatDate($row['CustomerOwnedEquipment']['last_sleep_date']),
						h($row['CustomerOwnedEquipment']['manufacturer_frame_code']),
						h($row['CustomerOwnedEquipment']['description']),
						h($row['CustomerOwnedEquipment']['model_number']),
						h($row['CustomerOwnedEquipment']['serial_number']),
						h($row['CustomerOwnedEquipment']['purchase_healthcare_procedure_code']),
						h($row['CustomerOwnedEquipment']['initial_carrier_number']),
						$html->link($row['CustomerOwnedEquipment']['invoice_number'], "/customers/inquiry/accountNumber:{$row['CustomerOwnedEquipment']['account_number']}/tab:LedgerTab/ledgerInvoice:{$row['CustomerOwnedEquipment']['invoice_number']}", array('target' => '_blank')),
						h($row['CustomerOwnedEquipment']['transaction_control_number']),
						h($row['CustomerOwnedEquipment']['tilt_manufacturer_code']),
						h($row['CustomerOwnedEquipment']['tilt_model_number']),
						h($row['CustomerOwnedEquipment']['tilt_serial_number'])
					)),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
		</tbody>
	</table>
	
	<?= $this->element('page_links') ?>
	<br /><br />
	
	<script type="text/javascript">
		Modules.CustomerOwnedEquipment.Management.detailsLoaded();
	</script>
<?php endif; ?>

<?php if (!$isPostback): ?>
	</div>
	
	<script type="text/javascript">
		Modules.CustomerOwnedEquipment.Management.addHandlers();
	</script>
<?php endif; ?>
