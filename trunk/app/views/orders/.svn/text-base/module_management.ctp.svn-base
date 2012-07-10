<h2>WIP</h2>
<table class="Styled">
	<tr>
		<th>Profit Center</th>
		<th class="Right">WIP</th>
		<th class="Right">Current<br/>Scheduled</th>
		<th class="Right">Next Month<br/>Scheduled</th>
		<th class="Right">Completed</th>
		<th class="Right">Credits</th>
		<th class="Right">Adjusted<br/>Scheduled</th>
		<th class="Right">Budget</th>
		<th class="Right">Revenue<br/>MTD</th>
	</tr>
	<?php
		foreach ($totals as $key => $row)
		{
			if ($key == 'all') { continue; }
			
			echo $html->tableCells(
				array(
					h($row['name']),
					array(h(number_format(ifset($row['wipTotal'], 0), 0)), array('class' => 'Right')),
					array(h(number_format(ifset($row['currentScheduled'], 0), 0)), array('class' => 'Right')),
					array(h(number_format(ifset($row['nextScheduled'], 0), 0)), array('class' => 'Right')),
					array(h(number_format(ifset($row['currentCompleted'], 0), 0)), array('class' => 'Right')),
					array(h(number_format(ifset($row['creditTotal'], 0), 0)), array('class' => 'Right')),
					array(h(number_format(ifset($row['currentScheduled'], 0) + ifset($row['creditTotal'], 0), 0)), array('class' => 'Right')),
					array(h(number_format($row['budgetTotal'], 0)), array('class' => 'Right')),
					array(h(number_format($row['revenueTotal'], 0)), array('class' => 'Right'))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	<tr>
		<th>Totals</th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['wipTotal'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['currentScheduled'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['nextScheduled'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['currentCompleted'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['creditTotal'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['currentScheduled'], 0) + ifset($totals['all']['creditTotal'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['budgetTotal'], 0), 0)) ?></th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['revenueTotal'], 0), 0)) ?></th>
	</tr>
</table>

<h2>Quotation</h2>
<table class="Styled">
	<tr>
		<th>Profit Center</th>
		<th class="Right">Completed</th>
	</tr>
	<?php
		foreach ($totals as $key => $row)
		{
			if ($key == 'all') { continue; }
			
			echo $html->tableCells(
				array(
					h($row['name']),
					array(h(number_format(ifset($row['quotationCompleted'], 0), 0)), array('class' => 'Right')),
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	<tr>
		<th>Totals</th>
		<th class="Right"><?= h(number_format(ifset($totals['all']['quotationCompleted'], 0), 0)) ?></th>
	</tr>
</table>