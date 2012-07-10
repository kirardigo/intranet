<div class="GroupBox">
	<h2>HCPC Carriers</h2>
	<div class="Content">
		<?php
			if ($showCode)
			{
				echo $form->input('Code', array(
					'value' => $code,
					'type' => 'text',
					'readonly' => true
				));
			}
			
			if (!$readonly)
			{
				echo $html->link('Add New Record', '/hcpcCarriers/add/' . $code, array('target' => '_blank'));
			}
		?>
		
		<div style="margin-bottom: 5px;"></div>
		<table id="HcpcCarriersTable" class="Styled">
			<thead>
				<tr>
					<th>Carrier Number</th>
					<th class="Right">Sale</th>
					<th class="Right">Rent</th>
					<th class="Right">Allow/Units</th>
					<th class="Right">Disc Date</th>
					<th class="Right">Updated</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($hcpcCarriers as $row)
					{
						if (!isset($editID))
						{
							$editID = $row['HcpcCarrier']['id'];
						}
						
						echo $html->tableCells(
							array(
								'<input type="hidden" value="' . $row['HcpcCarrier']['id'] . '" />' . 
								h($row['HcpcCarrier']['carrier_number']),
								array(h($row['HcpcCarrier']['allowable_sale']), array('class' => 'Right')),
								array(h($row['HcpcCarrier']['allowable_rent']), array('class' => 'Right')),
								array(h($row['HcpcCarrier']['allowable_units']), array('class' => 'Right')),
								array(formatDate($row['HcpcCarrier']['discontinued_date']), array('class' => 'Right')),
								array(formatDate($row['HcpcCarrier']['updated_date']), array('class' => 'Right')),
								$html->link($html->image('iconDetail.png'), '#', array(
										'escape' => false,
										'title' => 'Show details',
										'class' => 'HcpcCarrierDetail'
								))
							),
							array(),
							array('class' => 'Alt')
						);
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<div id="HcpcCarrier_DetailInfo" style="margin-top: 10px;"></div>

<script type="text/javascript">
	Modules.Hcpc.Carriers.initializeSortableTable();
	Modules.Hcpc.Carriers.addHandlers(<?= $readonly ? 'true' : 'false' ?>);
</script>