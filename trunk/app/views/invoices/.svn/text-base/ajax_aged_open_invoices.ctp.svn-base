<table class="Styled">
	<tr>
		<th>&nbsp;</th>
		<th class="Right">0-30 Days</th>
		<th class="Right">30-60 Days</th>
		<th class="Right">60-90 Days</th>
		<th class="Right">90-120 Days</th>
		<th class="Right">120-150 Days</th>
		<th class="Right">&gt; 150 Days</th>
		<th class="Right">Total</th>
	</tr>
	
	<?php
		foreach ($open as $carrier => $buckets)
		{
			echo $html->tableCells(
				array(
					$carrier,
					array(number_format(isset($buckets[0]) ? $buckets[0] : 0, 2), array('class' => 'Right')),
					array(number_format(isset($buckets[1]) ? $buckets[1] : 0, 2), array('class' => 'Right')),
					array(number_format(isset($buckets[2]) ? $buckets[2] : 0, 2), array('class' => 'Right')),
					array(number_format(isset($buckets[3]) ? $buckets[3] : 0, 2), array('class' => 'Right')),
					array(number_format(isset($buckets[4]) ? $buckets[4] : 0, 2), array('class' => 'Right')),
					array(number_format(isset($buckets[5]) ? $buckets[5] : 0, 2), array('class' => 'Right')),
					array(number_format(array_sum($buckets), 2), array('class' => 'Right'))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
</table>