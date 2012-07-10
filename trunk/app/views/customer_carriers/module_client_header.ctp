<table class="Styled" style="width: 200px; float: right;">
	<?php
		$i = 0;
		
		foreach (ifset($results['CustomerCarrier'], array()) as $row)
		{
			$header = ($i++ == 0) ? 'Balances' : '';
			
			echo $html->tableCells(
				array(array(
					array($header, array('style' => 'font-weight: bold;')),
					h($row['carrier_number']),
					array(h(number_format(ifset($row['Transaction']['carrier_balance_due'], 0), 2)), array('class' => 'Right'))
				)),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>