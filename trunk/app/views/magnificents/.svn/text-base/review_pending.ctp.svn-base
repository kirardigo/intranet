<?php
	echo $html->css('tabs', false);
	echo $javascript->link('tabs', false);
?>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		$('CancelButton').observe('click', function() {
			location.href = '/magnificents/pending';
		});
		$('ApproveButton').observe('click', function() {
			$('MagnificentStatus').value = 'approve';
			$('ApprovalForm').submit();
		});
		$('RejectButton').observe('click', function() {
			$('MagnificentStatus').value = 'reject';
			$('ApprovalForm').submit();
		});
	});
</script>

<style type="text/css">
	#MagnificentInfo {
		width: 100%;
		border: 1px solid black;
		border-collapse: collapse;
		margin-bottom: 8px;
	}
	
	#MagnificentInfo tr.Alt td {
		background-color: #e1dfd3;
	}
	
	.MagnificentLabel {
		font-weight: bold;
	}
</style>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Review Pending</h1>
<br class="ClearBoth" />

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
	<table id="MagnificentInfo">
		<tr>
			<td class="MagnificentLabel">Created:</td>
			<td><?= $this->data['Magnificent']['created'] ?></td>
		</tr>
		<tr class="Alt">
			<td class="MagnificentLabel">Recipient:</td>
			<td><?= $this->data['Magnificent']['recipient_user'] ?></td>
		</tr>
		<tr>
			<td class="MagnificentLabel">Nominated By:</td>
			<td><?= $this->data['Magnificent']['nominating_user'] ?></td>
		</tr>
		<tr class="Alt">
			<td class="MagnificentLabel">Part of Monthly Goals?:</td>
			<td><?= ($this->data['Magnificent']['is_group_effort']) ? 'Yes' : 'No' ?></td>
		</tr>
		<tr>
			<td class="MagnificentLabel">Millers Family Value:</td>
			<td><?= $familyValues[$this->data['Magnificent']['millers_family_value_id']] ?></td>
		</tr>
		<tr class="Alt">
			<td class="MagnificentLabel">Reason:</td>
			<td><?= $this->data['Magnificent']['reason'] ?></td>
		</tr>
		
		<?php if ($this->data['Magnificent']['attachment'] != null): ?>
		<tr>
			<td class="MagnificentLabel">Attachment:</td>
			<td>
				<?= $html->link($this->data['Magnificent']['attachment_name'], "view_attachment/{$this->data['Magnificent']['id']}", array('target' => '_blank')) ?>
				(Opens in new tab)
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
		echo $form->create('', array('id' => 'ApprovalForm', 'url' => "review_pending/{$id}"));
		echo $form->input('Magnificent.id');
		echo $form->hidden('Magnificent.status');
		echo $form->input('Magnificent.value');
		echo $form->input('Magnificent.narrative', array('class' => 'StandardTextArea'));
		echo $form->input('Magnificent.message', array('class' => 'StandardTextArea', 'label' => 'Personalize the E-mail Message'));
	?>
	</div>
</div>

<?php
	echo $form->button('Approve', array('id' => 'ApproveButton', 'class' => 'StyledButton', 'div' => false));
	echo $form->button('Reject', array('id' => 'RejectButton', 'class' => 'StyledButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>