<?php
	$paginator->options(
		array(
			'url' => array(
				'controller' => 'modules/clientCommunicationLog',
				'action' => "forCustomer/{$accountNumber}"
			),
			'update' => 'CCLForCustomerContainer'
		)
	);
	
	if (!$isUpdate)
	{
		echo '<div id="CCLForCustomerContainer">';
	}
?>

<div style="margin-bottom: 5px;">
	<a href="#" id="CCLNewRecord">Create Entry</a>
</div>

<table id="CCLForCustomerTable" class="Styled">
	<thead>
		<tr>
			<?php
				echo $paginator->sortableHeader('Date', 'incident_time');
				echo $paginator->sortableHeader('CCL Type', 'ClientCommunicationLogType.name');
				echo $paginator->sortableHeader('CCL Status', 'ClientCommunicationLogStatus.name');
			?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($this->data as $row)
		{
			echo $html->tableCells(
				array(
					h($row['ClientCommunicationLog']['incident_time']),
					h($row['ClientCommunicationLogType']['name']),
					h($row['ClientCommunicationLogStatus']['name']),
					$html->link($html->image('iconDetail.png'), '#', array(
						'escape' => false,
						'title' => 'Show details',
						'class' => 'Detail'
					)) . $form->hidden('id', array('value' => $row['ClientCommunicationLog']['id']))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</tbody>
</table>
<?= $this->element('page_links'); ?>

<script type="text/javascript">
	// Clear details when paging
	$('CCLForCustomerDetailInfo').update();
	Modules.ClientCommunicationLog.ForCustomer.addHandlers();
</script>

<?php if (!$isUpdate): ?>

</div>

<div id="CCLForCustomerDetailInfo" style="margin-top: 20px;"></div>

<?php endif; ?>