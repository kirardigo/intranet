<?= $form->create('', array('id' => 'PriorAuthorizationsDetailForm', 'url' => '/priorAuthorizations/fakeSave')); ?>

<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="PriorAuthSaveTop" value="Save" />
</div>
<div class="GroupBox">
	<h2>General Info</h2>
	<div class="Content">
		<div class="FormColumn">
			<?php
				echo $form->hidden('PriorAuthorization.id');
				echo $form->hidden('PriorAuthorization.account_number');
				echo $form->hidden('Virtual.date_requested_backup', array('value' => ifset($this->data['PriorAuthorization']['date_requested'])));
				echo $form->hidden('Virtual.mits_request_response_date_backup', array('value' => ifset($this->data['PriorAuthorization']['mits_request_response_date'])));
				echo $form->hidden('Virtual.date_approved_backup', array('value' => ifset($this->data['PriorAuthorization']['date_approved'])));
				echo $form->hidden('Virtual.date_denied_backup', array('value' => ifset($this->data['PriorAuthorization']['date_denied'])));
				
				echo $form->input('PriorAuthorization.authorization_id_number', array(
					'label' => 'MRS Auth ID#',
					'readonly' => 'readonly',
					'class' => 'Text100 ReadOnly'
				));
				echo $form->input('PriorAuthorization.department_code', array(
					'options' => $departments,
					'empty' => true,
					'tabindex' => 4,
					'label' => 'Dept'
				));
			?>
		</div>
		<div class="FormColumn" style="width: 200px">
			<?php
				echo $form->input('PriorAuthorization.transaction_control_number_file', array(
					'label' => 'TCN File',
					'options' => $tcnFileTypes,
					'empty' => true,
					'tabindex' => 1,
					'div' => array('class' => 'FormColumn')
				));
				echo $form->input('PriorAuthorization.transaction_control_number', array(
					'label' => 'TCN#',
					'tabindex' => 2,
					'class' => 'Text100'
				));
				echo $form->input('PriorAuthorization.date_of_service', array(
					'type' => 'text',
					'tabindex' => 5,
					'class' => 'Text75'
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.invoice_number', array(
					'label' => 'Invoice#',
					'tabindex' => 3,
					'class' => 'Text100'
				));
				echo $form->input('PriorAuthorization.carrier_authorization_number', array(
					'label' => 'Carrier Auth#',
					'tabindex' => 6,
					'class' => 'Text150'
				));
			?>
		</div>
		<div class="ClearBoth"></div>
		<?= $form->input('PriorAuthorization.description', array(
			'class' => 'Text400',
			'tabindex' => 7
		)); ?>
	</div>
</div>

<div class="GroupBox">
	<h2>Approval Info</h2>
	<div class="Content">
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.carrier_number', array(
					'label' => 'Carr#',
					'class' => 'Text50',
					'tabindex' => 8
				));
				echo $form->input('PriorAuthorization.status', array(
					'options' => $statuses,
					'empty' => '',
					'tabindex' => 11
				));
				echo $form->input('PriorAuthorization.date_activated', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 14
				));
				echo $form->input('PriorAuthorization.authorization_start_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 17
				));
				echo $form->input('PriorAuthorization.is_renewal', array(
					'label' => array('class' => 'Checkbox'),
					'div' => array('style' => 'margin: 12px 0px 5px;'),
					'tabindex' => 21
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.carrier_description', array(
					'label' => 'Carrier Desc',
					'class' => 'Text300',
					'tabindex' => 9
				));
				echo $form->input('PriorAuthorization.date_requested', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 12,
					'div' => array('class' => 'Horizontal')
				));
				echo $form->input('PriorAuthorization.mits_request_response_date', array(
					'type' => 'text',
					'label' => 'MITS IR Response',
					'class' => 'Text75',
					'tabindex' => 13
				));
				echo $form->input('PriorAuthorization.date_approved', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 15
				));
				echo $form->input('PriorAuthorization.authorization_end_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 18
				));
				echo $form->input('PriorAuthorization.number_of_months', array(
					'label' => '# of Months',
					'class' => 'Text25',
					'div' => array('class' => 'FormColumn'),
					'tabindex' => 22
				));
				echo $form->input('PriorAuthorization.is_insulin_dependent', array(
					'label' => array('class' => 'Checkbox'),
					'div' => array('style' => 'margin: 12px 0px 5px;'),
					'tabindex' => 23
				));
			?>
		</div>
		<div class="FormColumn" style="width: 250px;">
			<?php
				echo $form->input('PriorAuthorization.type', array(
					'options' => $types,
					'empty' => '',
					'tabindex' => 10
				));
				echo $form->input('PriorAuthorization.amount_requested', array(
					'class' => 'Text75 Right',
					'tabindex' => 13
				));
				echo $form->input('PriorAuthorization.amount_approved', array(
					'class' => 'Text75 Right',
					'tabindex' => 16
				));
				echo $form->input('PriorAuthorization.date_expiration', array(
					'type' => 'text',
					'class' => 'Text75',
					'div' => array('class' => 'FormColumn'),
					'tabindex' => 19
				));
				echo $form->input('PriorAuthorization.authorization_cmn', array(
					'label' => 'Auth / CMN',
					'options' => array('A' => 'Auth', 'C' => 'CMN'),
					'empty' => true,
					'tabindex' => 20
				));
				echo '<div class="ClearBoth"></div>';
				echo $form->input('PriorAuthorization.tests_per_day', array(
					'class' => 'Text50',
					'tabindex' => 24
				));
			?>
		</div>
		<div class="ClearBoth"></div>
	</div>
</div>

<div class="GroupBox">
	<h2>Appeal / Denial Info</h2>
	<div class="Content">
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.date_denied', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 25
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.is_appealed', array(
					'label' => array('class' => 'Checkbox'),
					'div' => array('style' => 'margin: 12px 0px 5px;'),
					'tabindex' => 26
				));
				
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.appeals_date', array(
					'type' => 'text',
					'class' => 'Text75',
					'tabindex' => 27
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('PriorAuthorization.appeals_amount', array(
					'class' => 'Text75 Right',
					'tabindex' => 28
				));
			?>
		</div>
		<div class="ClearBoth"></div>
		
		<?= $form->input('PriorAuthorization.appeals_note', array(
			'class' => 'Text500',
			'tabindex' => 29
		)); ?>
		
		<?php
			if ($id != null)
			{
				echo $form->input('Note.general.note', array(
					'type' => 'textarea',
					'value' => ifset($noteRecord['general']['note']),
					'class' => 'TextArea400',
					'tabindex' => 30
				));
				
				echo $this->element('note_info', array('noteRecord' => $noteRecord['general']));
			}
		?>
		
		<div id="DenialCodeTable"></div>
	</div>
</div>
<div class="ClearBoth"></div>

<div style="margin: 10px 0;">
	<input type="button" class="StyledButton" id="PriorAuthSaveBottom" tabindex="31" value="Save" />
	<?php if ($id != null): ?>
		<input type="button" class="StyledButton" id="PriorAuthDelete" tabindex="32" value="Delete" style="margin-left: 20px;" />
	<?php endif; ?>
</div>
<?= $form->end(); ?>
<script type="text/javascript">
	Modules.PriorAuthorizations.ForCustomer.addDetailHandlers();
</script>