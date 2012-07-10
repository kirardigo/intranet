<style type="text/css">
	#MagnificentInfo {
		width: 500px;
		border: 1px solid black;
		border-collapse: collapse;
	}
	
	#MagnificentInfo tr.Alt td {
		background-color: #e1dfd3;
	}
	
	.MagnificentLabel {
		font-weight: bold;
	}
</style>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right')); ?>

<table id="MagnificentInfo">
	<tr>
		<td class="MagnificentLabel">User:</td>
		<td><?= $data['user'] ?></td>
	</tr>
	<tr class="Alt">
		<td class="MagnificentLabel">Manager:</td>
		<td><?= $data['manager'] ?></td>
	</tr>
	<tr>
		<td class="MagnificentLabel">Supervisor:</td>
		<td><?= $data['supervisor'] ?></td>
	</tr>
	<tr class="Alt">
		<td class="MagnificentLabel">Earned Magnificents:</td>
		<td><?= $data['earnedCredits'] ?></td>
	</tr>
	<tr>
		<td class="MagnificentLabel">Redeemed Magnificents:</td>
		<td><?= $data['redeemedCredits'] ?></td>
	</tr>
	<tr class="Alt">
		<td class="MagnificentLabel">Available Magnificents:</td>
		<td><?= $data['availableCredits'] ?></td>
	</tr>
</table>

<br class="ClearBoth" />

<h2>Earned</h2>
<table class="Styled">
<?php
	echo $html->tableHeaders(array('Date', 'Nominated By', 'Approved By', 'MFV', 'Value', 'Reason', 'Goals', 'Attachment'));
	
	foreach ($earned as $row)
	{
		if ($row['Magnificent']['attachment_name'] != null)
		{
			$attachmentColumn = $html->link($row['Magnificent']['attachment_name'], "/magnificents/view_attachment/{$row['Magnificent']['id']}", array('target' => '_blank'));
		}
		else
		{
			$attachmentColumn = '';
		}
		
		echo $html->tableCells(
			array(
				$row['Magnificent']['created'],
				$row['Magnificent']['nominating_user'],
				$row['Magnificent']['approving_user'],
				$row['MillersFamilyValue']['name'],
				$row['Magnificent']['value'],
				$row['Magnificent']['reason'],
				($row['Magnificent']['is_group_effort']) ? 'Yes' : 'No',
				$attachmentColumn
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>

<br/><br/>
<h2>Redeemed</h2>
<table class="Styled">
<?php
	echo $html->tableHeaders(array('Date', 'Description', 'Value', 'Status'));
	
	foreach ($redeemed as $row)
	{
		$status = 'Requested';
		
		if ($row['MagnificentRedemption']['ordered_date'] != null)
		{
			$status = 'On Order';
		}
		if ($row['MagnificentRedemption']['dispensed_date'] != null)
		{
			$status = 'Delivered';
		}
		
		echo $html->tableCells(
			array(
				$row['MagnificentRedemption']['requested_date'],
				$row['MagnificentRedemption']['description'],
				$row['MagnificentRedemption']['value'],
				$status
			),
			array(),
			array('class' => 'Alt')
		);
	}
?>
</table>
