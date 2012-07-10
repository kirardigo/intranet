<?php if (!isset($matches)): ?>
	<div id="ModuleCustomerSearchContainer" style="width: 97%; margin: 0 auto;">
<?php endif; ?>

	<?= $form->create('Customer', array('id' => 'ModuleCustomerSearchForm', 'style' => 'margin: 0;')) ?>
	
	<table id="ModulesCustomerSearchTable" class="Styled" style="border-width: 0; margin: 0;">
		<thead>
			<tr>
				<th style="white-space: nowrap;">Account #</th>
				<th>Name</th>
				<th>Address</th>
				<th>Phone</th>
				<th>SSN</th>
				<th>Claim #</th>
			</tr>
			<tr>
				<th><?= $form->text('Customer.account_number', array('id' => 'ModuleCustomerSearchCustomerAccountNumber', 'class' => 'Text85')) ?></th>
				<th><?= $form->text('Customer.name', array('id' => 'ModuleCustomerSearchCustomerName')) ?></th>
				<th><?= $form->text('Customer.address_1', array('id' => 'ModuleCustomerSearchCustomerAddress1')) ?></th>
				<th><?= $form->text('Customer.phone_number', array('id' => 'ModuleCustomerSearchCustomerPhoneNumber', 'class' => 'Text85')) ?></th>
				<th><?= $form->text('CustomerBilling.social_security_number', array('id' => 'ModuleCustomerSearchCustomerBillingSocialSecurityNumber', 'class' => 'Text75')) ?></th>
				<th><?= $form->text('CustomerCarrier.claim_number', array('id' => 'ModuleCustomerSearchCustomerCarrierClaimNumber', 'class' => 'Text75')) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				if (isset($matches))
				{
					if (count($matches) == 0)
					{
						echo '<tr><td colspan="6">Sorry, there were no matching customers. Please adjust your criteria and try again.</td></tr>';
					}
					else
					{
						foreach ($matches as $match)
						{
							echo $html->tableCells(
								array(
									$html->link($match['Customer']['account_number'], '#'),
									h($match['Customer']['name']),
									h($match['Customer']['address_1']),
									h($match['Customer']['phone_number']),
									isset($match['CustomerBilling']['social_security_number']) ? h($match['CustomerBilling']['social_security_number']) : '&nbsp;',
									trim($this->data['CustomerCarrier']['claim_number']) != '' ? h($this->data['CustomerCarrier']['claim_number']) : '&nbsp;'
								),
								array(),
								array('class' => 'Alt')
							);
						}
					}
				}
			?>
			
		</tbody>
	</table>
	
	<?= $form->end() ?>
	
<?php if (!isset($matches)): ?>
	</div>
	
	<script type="text/javascript">
		Modules.Customers.Search.initialize();
	</script>
<?php endif; ?>