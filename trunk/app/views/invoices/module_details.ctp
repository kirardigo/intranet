<?= $form->create('', array('id' => 'InvoiceDetailForm')) ?>

<div class="GroupBox">
	<h2>Invoice Detail</h2>
	<div class="Content">
		<div class="FormColumn" style="width: 210px;">
		<?php
			echo $form->input('Invoice.invoice_number', array(
				'class' => 'Text100',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.rental_or_purchase', array(
				'label' => 'R/P',
				'class' => 'Text25',
				'div' => array('class' => 'Horizontal')
			));
			echo $form->input('Invoice.department_code', array(
				'label' => 'Dept',
				'class' => 'Text25'
			));
			echo $form->input('Invoice.transaction_control_number', array(
				'label' => 'TCN#',
				'class' => 'Text100'
			));
			echo $form->input('Invoice.cmn', array(
				'label' => 'CMN',
				'class' => 'Text100'
			));
			echo $form->input('Invoice.auth', array(
				'label' => 'Auth #',
				'class' => 'Text100'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Invoice.date_of_service', array('type' => 'text', 'class' => 'Text75'));
			echo $form->input('Invoice.billing_date', array('type' => 'text', 'class' => 'Text75'));
			echo $form->input('Invoice.posting_period_date', array(
				'type' => 'text',
				'label' => 'Post Period',
				'class' => 'Text75'
			));
			echo $form->input('Invoice.creation_date', array(
				'type' => 'text',
				'label' => 'Data Entry',
				'class' => 'Text75'
			));
		?>
		</div>
		<div class="FormColumn">
		<?php
			echo $form->input('Invoice.amount', array(
				'class' => 'Text75 Right'
			));
			echo $form->input('Invoice.account_balance', array(
				'label' => 'Balance',
				'class' => 'Text75 Right'
			));
			echo $form->input('Invoice.payments', array(
				'class' => 'Text75 Right'
			));
			echo $form->input('Invoice.credits', array(
				'class' => 'Text75 Right'
			));
		?>
		</div>
		<br class="ClearBoth" /><br/>
	</div>
</div>

<div class="GroupBox">
	<h2>Customer Carrier Detail</h2>
	<div class="Content">
		<table class="Styled">
			<?php
				echo $html->tableHeaders(array('P/S/N', 'Carr#', 'Amount', 'Group', 'Claim#', 'Phone'));
			?>	
			<tr>
				<td>P</td>
				<td><?= $this->data['Invoice']['carrier_1_code'] ?></td>
				<td><?= $this->data['Invoice']['carrier_1_balance'] ?></td>
				<td><?= ifset($carriers[1]['Carrier']['group_code']) ?></td>
				<td><?= ifset($carriers[1]['CustomerCarrier']['claim_number']) ?></td>
				<td><?= ifset($carriers[1]['Carrier']['phone_number']) ?></td>
			</tr>
			<tr>
				<td>S</td>
				<td><?= $this->data['Invoice']['carrier_2_code'] ?></td>
				<td><?= $this->data['Invoice']['carrier_2_balance'] ?></td>
				<td><?= ifset($carriers[2]['Carrier']['group_code']) ?></td>
				<td><?= ifset($carriers[2]['CustomerCarrier']['claim_number']) ?></td>
				<td><?= ifset($carriers[2]['Carrier']['phone_number']) ?></td>
			</tr>
			<tr>
				<td>N</td>
				<td><?= $this->data['Invoice']['carrier_3_code'] ?></td>
				<td><?= $this->data['Invoice']['carrier_3_balance'] ?></td>
				<td><?= ifset($carriers[3]['Carrier']['group_code']) ?></td>
				<td><?= ifset($carriers[3]['CustomerCarrier']['claim_number']) ?></td>
				<td><?= ifset($carriers[3]['Carrier']['phone_number']) ?></td>
			</tr>
		</table>
	</div>
</div>

<div class="GroupBox">
	<h2>Other Detail</h2>
	<div class="Content">
		<div class="FormColumn">
			<table class="Styled" style="width: 700px;">
				<tr>
					<th>L#</th>
					<th>L Value</th>
					<th>Status</th>
					<th>Ini</th>
					<th>Date</th>
					<th>#</th>
					<th class="Right">Amount</th>
				</tr>
				<tr>
					<td>L1</td>
					<td>Billing</td>
					<td><?= $this->data['Invoice']['line_1_status'] ?></td>
					<td><?= $this->data['Invoice']['line_1_initials'] ?></td>
					<td><?= ifset($this->data['Invoice']['line_1_date']) ?></td>
					<td><?= ifset($carriers[$this->data['Invoice']['line_1_carrier_number']]['CustomerCarrier']['carrier_number']) ?></td>
					<td class="Right"><?= $this->data['Invoice']['line_1_amount'] ?></td>
				</tr>
				<tr>
					<td>L2</td>
					<td>Just/CMN</td>
					<td><?= $this->data['Invoice']['line_2_status'] ?></td>
					<td><?= $this->data['Invoice']['line_2_initials'] ?></td>
					<td><?= ifset($this->data['Invoice']['line_2_date']) ?></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>L3</td>
					<td>More Info</td>
					<td><?= $this->data['Invoice']['line_3_status'] ?></td>
					<td><?= $this->data['Invoice']['line_3_initials'] ?></td>
					<td><?= ifset($this->data['Invoice']['line_3_date']) ?></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>L4</td>
					<td>Rev/Hrg</td>
					<td><?= $this->data['Invoice']['line_4_status'] ?></td>
					<td><?= $this->data['Invoice']['line_4_initials'] ?></td>
					<td><?= ifset($this->data['Invoice']['line_4_date']) ?></td>
					<td></td>
					<td></td>
				</tr>
			</table>
		</div>		
		<div class="FormColumn">
			<?php
				echo $form->input('Invoice.team', array('class' => 'Text100'));
				echo $form->input('Invoice.remittance_date', array(
					'type' => 'text',
					'label' => 'Remit Date',
					'class' => 'Text75'
				));
				echo $form->input('Invoice.print_statement', array(
					'label' => 'Statement',
					'class' => 'Text25'
				));
			?>
		</div>
		<br class="ClearBoth" />
		<?php
			echo $form->input('Invoice.denial_flag', array('class' => 'Text300'));
			echo $form->input('Invoice.reimbursement_memo', array('class' => 'Text500'));
		?>
	</div>
</div>
<?= $form->end(); ?>
<script type="text/javascript">

</script>