<div class="GroupBox FormColumn" style="width: 400px;">
	<h2>General Info</h2>
	<div class="Content">
		<div class="FormColumn">
		<?php
			echo $form->input('Carrier.claims_phone_number', array(
				'label' => 'Claims Phone',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.claims_toll_free_phone_number', array(
				'label' => 'Claims 800#',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.claims_fax_number', array(
				'label' => 'Claims Fax',
				'class' => 'Text100'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Carrier.days_claim_filing_limit', array(
				'label' => 'Claims Filing Limit(days)',
				'class' => 'Text50'
			));
			echo $form->input('Carrier.days_claim_review_limit', array(
				'label' => 'Claims Review Limit(days)',
				'class' => 'Text50'
			));
		?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox FormColumn" style="width: 480px; margin-right: 0;">
	<h2>Provider Numbers</h2>
	<div class="Content">
		<div style="margin-bottom: 5px;">
		<?php
			echo $form->input('CarrierProviderNumber.profit_center', array(
				'label' => 'PCtr',
				'class' => 'Text50',
				'div' => array('class' => 'FormColumn')
			));
			echo $form->input('CarrierProviderNumber.number', array(
				'label' => 'Number',
				'class' => 'Text150',
				'div' => array('class' => 'FormColumn')
			));
			echo $html->link($html->image('iconAdd.png', array('style' => 'margin-top: 12px')), '#', array('escape' => false, 'id' => 'ProviderAddLink'));
		?>
			<div class="ClearBoth"></div>
		</div>
		
		<table id="ProvidersTable" class="Styled" style="width: 300px;<?php if (count($providers) == 0): ?> display: none;<?php endif; ?>" >
			<tr>
				<th>PCtr</th>
				<th>Number</th>
				<th style="width: 50px;"></th>
			</tr>
		<?php
			foreach ($providers as $row)
			{
				echo $html->tableCells(
					array(
						$form->hidden('recordID', array('value' => $row['CarrierProviderNumber']['id'])) . 
						h($row['CarrierProviderNumber']['profit_center']),
						h($row['CarrierProviderNumber']['number']),
						$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'ProviderDeleteLink'))
					),
					array('class' => 'Auto'),
					array('class' => 'Alt Auto')
				);
			}
		?>
		</table>
	</div>
</div>
<div class="ClearBoth"></div>

<div class="GroupBox">
	<h2>Claims Billing/Review Notes</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.claims.note', array(
			'label' => false,
			'value' => isset($noteRecord['claims']['note']) ? $noteRecord['claims']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['claims']));
	?>
	</div>
</div>

<div class="GroupBox">
	<h2>Carrier Info</h2>
	<div class="Content">
		<div class="FormColumn" style="width: 450px;">
		<?php
			echo $form->input('Carrier.name_to_print_on_statement', array(
				'class' => 'Text300'
			));
			echo $form->input('Carrier.receive_payments_via_eft', array(
				'label' => array('text' => 'Electronic Funds Transfer?', 'class' => 'Checkbox'),
				'div' => array('class' => 'FormColumn', 'style' => 'margin-top: 12px')
			));
			echo $form->input('Carrier.eft_start_date', array(
				'type' => 'text',
				'label' => 'EFT Start Date',
				'class' => 'Text75'
			));
			echo $form->input('Carrier.remit_methods', array(
				'label' => 'Remitance Advice Method',
				'options' => $remitMethods,
				'empty' => true
			));
			echo $form->input('Carrier.national_provider_identification_number', array(
				'label' => 'NPI#',
				'class' => 'Text150',
				'div' => array('class' => 'FormColumn')
			));
			echo $form->input('Carrier.is_carrier_inactive', array(
				'label' => array('text' => 'Inactive?', 'class' => 'Checkbox'),
				'div' => array('class' => 'FormColumn', 'style' => 'margin-top: 12px;')
			));
			echo $form->input('Carrier.carrier_number_replacement', array(
				'label' => 'Placement Carrier',
				'class' => 'Text50'
			));
		?>
		
		</div>
		<div class="FormColumn" style="width: 400px;">
		<?php
			echo $form->input('Carrier.statement_type', array(
				'label' => 'Stmt Type',
				'options' => $statementTypes,
				'empty' => true,
				'style' => 'width: 400px;'
			));
			echo $form->input('Carrier.group_code', array(
				'readonly' => 'readonly',
				'class' => 'Text50 ReadOnly'
			));
			echo $form->input('Carrier.payor_carrier_number', array(
				'label' => 'Payor Carr#',
				'class' => 'Text50'
			));
			echo $form->input('Carrier.network', array(
				'class' => 'Text150'
			));
		?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Provider Relations / Contract Info</h2>
	<div class="Content">
		<div class="FormColumn" style="margin-right: 30px;">
		<?php
			echo $form->input('Carrier.official_name', array(
				'class' => 'Text400'
			));
			echo $form->input('Carrier.provider_relations_address_1', array(
				'label' => 'Address 1',
				'class' => 'Text300'
			));
			echo $form->input('Carrier.provider_relations_address_2', array(
				'label' => 'Address 1',
				'class' => 'Text300'
			));
			echo $form->input('Carrier.provider_relations_city_state', array(
				'label' => 'City, State',
				'class' => 'Text300'
			));
			echo $form->input('Carrier.contact_name', array(
				'class' => 'Text300'
			));
			echo $form->input('Carrier.contact_email', array(
				'class' => 'Text300'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Carrier.provider_relations_phone', array(
				'label' => 'P/R Phone',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.provider_relations_fax', array(
				'label' => 'P/R Fax',
				'class' => 'Text100'
			));
			echo $form->input('Carrier.is_contract_on_file', array(
				'label' => array('text' => 'Contract On File?', 'class' => 'Checkbox'),
				'div' => array('style' => 'margin: 2px 0;')
			));
			echo $form->input('Carrier.contract_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
			echo $form->input('Carrier.recredentialed_date', array(
				'type' => 'text',
				'class' => 'Text75'
			));
			
			if ($id != null)
			{
				echo $form->input('Virtual.modified_date', array(
					'value' => $this->data['Carrier']['modified_date'] . ' ' . $this->data['Carrier']['modified_time'],
					'class' => 'Text150 ReadOnly',
					'readonly' => 'readonly'
				));
				echo $form->input('Carrier.modified_by', array(
					'class' => 'Text75 ReadOnly',
					'readonly' => 'readonly'
				));
			}
		?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Contract Note</h2>
	<div class="Content">
	<?php
		echo $form->input('Note.contract.note', array(
			'label' => false,
			'value' => isset($noteRecord['contract']['note']) ? $noteRecord['contract']['note'] : '',
			'class' => 'TextArea800'
		));
		echo $this->element('note_info', array('noteRecord' => &$noteRecord['contract']));
	?>
	</div>
</div>

<script type="text/javascript">
	Modules.Carriers.Claims.init();
</script>