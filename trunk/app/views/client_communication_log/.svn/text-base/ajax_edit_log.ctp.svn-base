<?= $form->create('', array('id' => 'CCLDetailForm')); ?>
	
<div class="GroupBox">
	<h2><?= isset($this->data['ClientCommunicationLog']['id']) ? 'Edit' : 'Create' ?></h2>
	<div class="Content">
		<?php
			echo $form->hidden('ClientCommunicationLog.id');
			echo $form->input('ClientCommunicationLog.incident_time', array('type' => 'text'));
			echo $form->input('ClientCommunicationLog.client_communication_log_type_id', array(
				'label' => 'CCL Type',
				'options' => $cclTypes,
				'empty' => ''
			));
			echo $form->input('ClientCommunicationLog.client_communication_log_status_id', array(
				'label' => 'CCL Status',
				'options' => $cclStatuses,
				'empty' => ''
			));
			echo $form->input('ClientCommunicationLog.client_comments', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.staff_summary', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.location', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.millers_property_damage', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.others_property_damage', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.millers_staff_personal_injury', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.others_personal_injury', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.was_police_report_filed', array(
				'label' => array('class' => 'Checkbox'),
				'div' => array('style' => 'margin: 10px 0px;')
			));
			echo $form->input('ClientCommunicationLog.was_insurance_company_notified', array(
				'label' => array('class' => 'Checkbox'),
				'div' => array('style' => 'margin: 10px 0px;')
			));
			echo $form->input('ClientCommunicationLog.investigation_date', array('type' => 'text'));
			echo $form->input('ClientCommunicationLog.investigated_by');
			echo $form->input('ClientCommunicationLog.contributing_incident_cause', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.immediate_actions', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.suggested_corrective_action', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
			echo $form->input('ClientCommunicationLog.notes', array(
				'type' => 'textarea',
				'class' => 'StandardTextArea'
			));
		?>
	</div>
</div>
<br class="ClearBoth" />

<?= $form->button('Save', array('id' => 'CCLSaveButton')); ?>
<?= $form->end(); ?>

<script type="text/javascript">
	$("CCLSaveButton").observe("click", Modules.ClientCommunicationLog.ForCustomer.postLogRecord);
</script>
