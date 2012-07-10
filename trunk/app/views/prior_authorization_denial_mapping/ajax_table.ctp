<?php
	echo $form->hidden('PriorAuthorizationDenialMapping.authorization_id_number', array(
		'id' => 'PriorAuthDenialMappingID',
		'value' => $priorAuthorizationID
	));
	echo $form->input('PriorAuthorizationDenialMapping.denial_code', array(
		'id' => 'PriorAuthDenialMappingCode',
		'label' => 'Add Denial Code',
		'class' => 'Text50',
		'style' => 'margin-right: 5px',
		'after' => $html->link($html->image('iconAdd.png'), '#', array(
			'id' => 'PriorAuthDenialMappingAdd',
			'escape' => false
		))
	));
?>
<table class="Styled" style="margin: 10px 0px;">
	<tr>
		<th style="width: 100px;">Code</th>
		<th>Description</th>
		<th>&nbsp;</th>
	</tr>
	<?php
		if (isset($this->data['PriorAuthorizationDenial']))
		{
			foreach ($this->data['PriorAuthorizationDenial'] as $row)
			{
				echo $html->tableCells(
					array(
						$form->hidden('id', array('value' => $row['id'])) . 
						h($row['code']),
						h($row['description']),
						$html->link($html->image('iconDelete.png'), '#', array('class' => 'PriorAuthDenialDeleteLink', 'escape' => false))
					),
					array(),
					array('class' => 'Alt')
				);
			}
		}
		else
		{
			echo '<tr><td colspan="2">There are no denial codes for this record</td></tr>';
		}
	?>
</table>

<script type="text/javascript">
	Modules.PriorAuthorizations.ForCustomer.addDenialCodeHandlers();
</script>