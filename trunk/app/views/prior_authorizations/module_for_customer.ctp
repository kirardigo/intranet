<?php
	$paginator->options(
		array(
			'url' => array(
				'controller' => 'modules/priorAuthorizations',
				'action' => "forCustomer/{$accountNumber}"
			),
			'update' => 'PriorAuthorizationsForCustomerContainer'
		)
	);
	
	if (!$isUpdate)
	{
		echo '<div id="PriorAuthorizationsForCustomerContainer">';
	}
?>

<?= $html->link('Create New Record', '#', array('id' => 'CreatePriorAuthLink')); ?>
<table id="PriorAuthorizationsForCustomerTable" class="Styled" style="margin-top: 5px;">
	<thead>
		<tr>
			<?php
				echo $paginator->sortableHeader('Descr', 'description');
				echo $paginator->sortableHeader('Carr#', 'carrier_number');
				echo $paginator->sortableHeader('Dept', 'department_code');
				echo $paginator->sortableHeader('TCN#', 'transaction_control_number');
				echo $paginator->sortableHeader('Invoice #', 'invoice_number');
				echo $paginator->sortableHeader('Requested', 'date_requested');
				echo $paginator->sortableHeader('Approved', 'date_approved');
				echo $paginator->sortableHeader('Denied', 'date_denied');
				echo $paginator->sortableHeader('Expiration', 'date_expiration');
			?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
		if (!count($this->data))
		{
			echo '<tr><td colspan="10"><div class="NoRecordNotice">No prior authorizations for this customer.</div></td></tr>';
		}
		else
		{
			foreach ($this->data as $row)
			{
				echo $html->tableCells(
					array(
						h($row['PriorAuthorization']['description']),
						h($row['PriorAuthorization']['carrier_number']),
						h($row['PriorAuthorization']['department_code']),
						h($row['PriorAuthorization']['transaction_control_number']),
						h($row['PriorAuthorization']['invoice_number']),
						h($row['PriorAuthorization']['date_requested']),
						h($row['PriorAuthorization']['date_approved']),
						h($row['PriorAuthorization']['date_denied']),
						h($row['PriorAuthorization']['date_expiration']),
						$html->link($html->image('iconDetail.png'), '#', array(
							'escape' => false,
							'title' => 'Show details',
							'class' => 'Detail'
						)) . $form->hidden('id', array('value' => $row['PriorAuthorization']['id']))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		}
	?>
	</tbody>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	// Clear details when paging
	$('PriorAuthorizationsForCustomerDetailInfo').update();
	Modules.PriorAuthorizations.ForCustomer.addHandlers();
	
	<?php
		if ($load == 'new')
		{
			echo 'Modules.PriorAuthorizations.ForCustomer.onRecordCreated();';
		}
		else if ($load != '')
		{
			echo  "Modules.PriorAuthorizations.ForCustomer.selectRecord({$load});";
		}
	?>
</script>

<?php if (!$isUpdate): ?>

</div>

<div id="PriorAuthorizationsForCustomerDetailInfo" style="margin-top: 20px;"></div>

<?php endif; ?>