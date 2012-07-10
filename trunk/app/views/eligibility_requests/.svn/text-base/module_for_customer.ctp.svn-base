<div class="GroupBox">
	<h2>VOB</h2>
	<div class="Content">
		<table id="VobTable" class="Styled">
			<tr>
				<th>Carrier</th>
				<th>Name</th>
				<th>Last eVOB Check</th>
				<th class="Right">&nbsp;</th>
			</tr>
			<?php
				
				foreach ($carriers as $carrier)
				{
					echo $html->tableCells(
						array(
							h($carrier['CustomerCarrier']['carrier_number']),
							h($carrier['CustomerCarrier']['carrier_name']),
							h(formatDate($carrier['CustomerCarrier']['last_zirmed_electronic_vob_date'])),
							array(
								$html->link(
									$html->image('iconAdd.png', array('title' => 'New eVOB Request')), 
									$url . "/account:{$accountNumber}/carrier:{$carrier['CustomerCarrier']['carrier_number']}", 
									array('class' => 'NewRequest', 'escape' => false, 'target' => '_blank')
								) . ' ' .
								$html->link(
									$html->image('iconFolder.png', array('title' => 'View Existing eVOB Requests')), 
									$url . "/account:{$accountNumber}/carrier:{$carrier['CustomerCarrier']['carrier_number']}", 
									array('escape' => false, 'target' => '_blank')
								), 
								array('class' => 'Right')
							) 							
						),
						array(),
						array('class' => 'Alt')
					);
				}
			?>
		</table>
	</div>
</div>

<script type="text/javascript">
	Modules.EligibilityRequests.ForCustomer.initialize("<?= $accountNumber ?>", "VobTable", "<?= $url ?>");
</script>