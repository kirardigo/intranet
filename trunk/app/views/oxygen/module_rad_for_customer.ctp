<?= $form->create('', array('id' => 'RadForCustomerForm')) ?>

<div class="GroupBox FormColumn" style="width: 100%;">
	<a href="http://kb.millers.com/download/114/" target="_blank" style="float: right; margin: 3px 4px;"><?= $html->image('help.gif') ?></a>
	<h2>Sleep Information</h2>
	<div class="Content">
		<div style="float: right; width: 350px; height: 80px; padding: 10px; border: 1px solid black;">
			<div style="float: right;">
				<label>Lab AAA#</label>
				<input name="data[Oxygen][osa_aaa_lab_code]" style="width: 100px;" id="OxygenOsaAaaLabCode" autocomplete="off" value="<?= $this->data['Oxygen']['osa_aaa_lab_code'] ?>" type="text">
				<div style="display: none;" id="OxygenOsaAaaLabCode_autoComplete" class="auto_complete"></div>
			</div>
			<?php	
				if ($lab === false)
				{
					echo '<b>' . $this->data['Oxygen']['osa_sleep_lab'] . '</b><br/>';
				}
				else
				{
					echo '<b>' . h($lab['AaaReferral']['facility_name']) . '</b><br/>';
					echo h($lab['AaaReferral']['contact_name']) . '<br/>';
					echo h($lab['AaaReferral']['address_1']) . '<br/>';
					echo h("{$lab['AaaReferral']['city_state']} {$lab['AaaReferral']['zip_code']}") . '<br/>';
					?>
					
					<table class="Unstyled" width="90%">
						<tr>
							<td width="50%">Phone: <?= h($lab['AaaReferral']['phone_number']) ?></td>
							<td>Fax: <?= h($lab['AaaReferral']['fax_number']) ?></td>
						</tr>
						<tr>
							<td colspan="2">Email: <?= h($lab['AaaReferral']['contact_email']) ?></td>
						</tr>
					</table>
					
					<?php
				}
			?>
		</div>
		<div style="float: left; width: 500px;">
			<?php
				echo $form->input('Virtual.last_trx_date', array(
					'type' => 'text',
					'label' => 'Last Trx Date',
					'class' => 'Text75 ReadOnly',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.date_last_updated', array(
					'type' => 'text',
					'label' => 'Last Updated',
					'class' => 'Text75 ReadOnly',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.last_updated_ini', array(
					'label' => 'Ini',
					'class' => 'Text50 ReadOnly'
				));
				
				echo '<div style="clear: left; margin-bottom: 5px;">';
				echo $form->input('Oxygen.osa_type', array(
					'label' => 'Type',
					'options' => $oxygenTypes,
					'empty' => true,
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.osa_status', array(
					'label' => 'Status',
					'options' => $oxygenStatuses,
					'empty' => true,
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.osa_status_date', array(
					'type' => 'text',
					'label' => 'Status Date',
					'class' => 'Text75 ReadOnly'
				));
				echo '</div>';
				echo '<div style="clear: left; margin-bottom: 5px;">';
				echo $form->input('Oxygen.osa_pressure_setting', array(
					'label' => 'Pressure',
					'class' => 'Text150',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.osa_rdi_ahi', array(
					'label' => 'RDI/AHI',
					'class' => 'Text150',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.is_osa_oxygen', array(
					'label' => array('text' => 'Oxygen?', 'class' => 'Checkbox'),
					'div' => array('class' => 'Horizontal', 'style' => 'margin: 14px 0px 0px 10px;')
				));
				echo '</div>';
				
				echo '<div style="clear: left; margin-bottom: 5px;">';
				echo $form->input('Oxygen.osa_setup_date', array(
					'type' => 'text',
					'label' => 'Setup Date',
					'class' => 'Text75',
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('Oxygen.first_night_sleep_study_date', array(
					'type' => 'text',
					'class' => 'Text75'
				));
				echo '</div>';
				
				echo '<div style="clear: left; margin-bottom: 5px;">';
				echo $form->input('Oxygen.is_30_day_followup_returned', array(
					'label' => array('text' => '1 Month FUP Returned?', 'class' => 'Checkbox'),
					'div' => array('class' => 'Horizontal', 'style' => 'margin: 5px 10px 5px 0;')
				));
				echo $form->input('Oxygen.is_90_day_followup_returned', array(
					'label' => array('text' => '3 Month FUP Returned?', 'class' => 'Checkbox'),
					'div' => array('class' => 'Horizontal', 'style' => 'margin: 5px 10px 5px 0;')
				));
				echo '<div class="ClearBoth"></div>';
				echo '</div><br/><br/>';
				
				echo $ajax->submit('Save', array(
					'id' => 'OxygenRadSave',
					'class' => 'StyledButton',
					'style' => 'margin-left: 0px; margin-top: 5px;',
					'url' => "/modules/oxygen/radForCustomer/{$accountNumber}", 
					'condition' => 'Modules.Oxygen.RadForCustomer.onBeforePost(event)',
					'complete' => 'Modules.Oxygen.RadForCustomer.onPostCompleted(request)'
				));
				
				echo $form->hidden('Oxygen.id');
			?>
		</div>
		<div style="float: right; clear: right; width: 350px; height: 80px; padding: 10px; margin-top: 5px; border: 1px solid black;">
			<div style="float: right;">
				<label>Referral AAA#</label>
				<input name="data[Oxygen][osa_aaa_referral_code]" style="width: 100px;" id="OxygenOsaAaaReferralCode" autocomplete="off" value="<?= $this->data['Oxygen']['osa_aaa_referral_code'] ?>" type="text">
				<div style="display: none;" id="OxygenOsaAaaReferralCode_autoComplete" class="auto_complete"></div>
			</div>
			<?php				
				if ($aaa !== false)
				{
					echo '<b>' . h($aaa['AaaReferral']['facility_name']) . '</b><br/>';
					echo h($aaa['AaaReferral']['contact_name']) . '<br/>';
					echo h($aaa['AaaReferral']['address_1']) . '<br/>';
					echo h("{$aaa['AaaReferral']['city_state']} {$aaa['AaaReferral']['zip']}") . '<br/>';
					?>
					
					<table class="Unstyled" width="90%">
						<tr>
							<td width="50%">Phone: <?= h($aaa['AaaReferral']['phone_number']) ?></td>
							<td>Fax: <?= h($aaa['AaaReferral']['fax_number']) ?></td>
						</tr>
						<tr>
							<td colspan="2">Email: <?= h($aaa['AaaReferral']['email']) ?></td>
						</tr>
					</table>
					
					<?php
				}
			?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<br class="ClearBoth" />
<h2>Sleep Rentals</h2>
<table class="Styled" id="OxygenRadRentalTable">
	<thead>
		<tr>
			<th>Inventory#</th>
			<th>Description</th>
			<th>HCPC</th>
			<th>Setup Date</th>
			<th>Return Date</th>
			<th class="Right"># Months</th>
			<th>Rx Date</th>
			<th class="Right">Rx Months</th>
			<th class="Right">Total Gross</th>
			<th class="Right">Total Net</th>
		</tr>
	</thead>
	<tbody>
		<?php
		
			foreach ($rentals as $row)
			{
				$totalGross = $row['Rental']['carrier_1_gross_amount'] 
					+ $row['Rental']['carrier_2_gross_amount'] 
					+ $row['Rental']['carrier_3_gross_amount'];
				$totalNet = $row['Rental']['carrier_1_net_amount'] 
					+ $row['Rental']['carrier_2_net_amount'] 
					+ $row['Rental']['carrier_3_net_amount'];
				
				echo $html->tableCells(
					array(
						h($row['Rental']['inventory_number']),
						h($row['Rental']['inventory_description']),
						h($row['Rental']['healthcare_procedure_code']),
						h($row['Rental']['setup_date']),
						h($row['Rental']['returned_date']),
						array(h($row['Rental']['number_of_rental_months']), array('class' => 'Right')),
						h($row['Rental']['prescription_date']),
						array(h($row['Rental']['prescription_duration']), array('class' => 'Right')),
						array(number_format($totalGross, 2), array('class' => 'Right')),
						array(number_format($totalNet, 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<br class="ClearBoth" />
<h2>Sleep Supplies and Equipment</h2>
<table class="Styled" id="OxygenRadPurchaseTable">
	<thead>
		<tr>
			<th>Inventory#</th>
			<th>Description</th>
			<th>HCPC</th>
			<th>DOS</th>
			<th>Carr 1</th>
			<th class="Right">Total Gross</th>
			<th class="Right">Total Net</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ($purchases as $row)
			{
				$totalGross = $row['Purchase']['carrier_1_gross_amount'] 
					+ $row['Purchase']['carrier_2_gross_amount'] 
					+ $row['Purchase']['carrier_3_gross_amount'];
				$totalNet = $row['Purchase']['carrier_1_net_amount'] 
					+ $row['Purchase']['carrier_2_net_amount'] 
					+ $row['Purchase']['carrier_3_net_amount'];
				
				echo $html->tableCells(
					array(
						h($row['Purchase']['inventory_number']),
						h($row['Purchase']['inventory_description']),
						h($row['Purchase']['healthcare_procedure_code']),
						h($row['Purchase']['date_of_service']),
						h($row['Purchase']['carrier_1_code']),
						array(number_format($totalGross, 2), array('class' => 'Right')),
						array(number_format($totalNet, 2), array('class' => 'Right'))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
	</tbody>
</table>

<?= $form->end(); ?>

<script type="text/javascript">
	Modules.Oxygen.RadForCustomer.addHandlers();
	Modules.Oxygen.RadForCustomer.initializeTables();
</script>