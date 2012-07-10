<table>
	<tr>
		<th>Carrier</th>
		<th>Rent</th>
		<th>Sale</th>
		<th>HCPC</th>
		<th>Qty Allowed</th>
		<th>UCR Bump</th>
	</tr>
	<?php
		foreach ($records as $row)
		{
			echo $html->tableCells(
				array(
					'<input type="hidden" value="' . $row['Hcpc']['id'] . '" />' .
					$html->link($html->image('iconDetail.png'), '#', array('class' => 'viewLink', 'escape' => false)),
					h($row['Hcpc']['carrier_number']),
					h($row['Hcpc']['allowable_sale']),
					h($row['Hcpc']['allowable_rent']),
					h($row['Hcpc']['hcpc_code'])
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>