<script type="text/javascript">
	/**
	 * Fires an event so hosts of this ajax display can do what they want after the form is posted.
	 * Note the declaration has to be this way for the call to be able to resolve at runtime.
	 */
	onRoleApplicationSubmitComplete = function(request)
	{
		Event.fire($("RoleApplicationEditForm"), "roleApplication:submitComplete", { success: request.headerJSON.success });
	}
</script>

<div style="margin: 5px;">
	<?= $form->create('RoleApplication', array('id' => 'RoleApplicationEditForm', 'url' => '/ajax/roleApplications/edit')) ?>
	
	<table class="Styled" id="RoleApplicationTable">
		<tr><th>Application</th><th class="Center">Access</th></tr>
		<?php
			foreach ($applications as $i => $app)
			{
				echo $html->tableCells(
					array(
						h($app['Application']['name']),
						array(
							$form->hidden("RoleApplication.application_id.{$i}", array('value' => $app['Application']['id'])) . $form->checkbox("RoleApplication.checked.{$i}"),
							array('class' => 'Center')
						)
					),
					array(),
					array('class' => 'Alt')
				);
			}
		?>
		<tr class="GrandTotal"><td class="Right" colspan="2"><?= $html->link('Check All', '#', array('id' => 'CheckAllLink')) ?> <?= $html->link('Uncheck All', '#', array('id' => 'UncheckAllLink')) ?></td></tr>
	</table>
	
	<br /><br />
	<?php	
		echo $ajax->submit('Save', array(
			'class' => 'StyledButton',
			'url' => "/ajax/roleApplications/edit/{$roleID}/{$applicationFolderID}", 
			'complete' => 'onRoleApplicationSubmitComplete(request);'
		));
		
		echo $form->end();
	?>
</div>

<script type="text/javascript">
	$("CheckAllLink").observe("click", function() {
		$("RoleApplicationTable").select("input").each(function(input) {
			input.checked = true;
		});
	});
	
	$("UncheckAllLink").observe("click", function() {
		$("RoleApplicationTable").select("input").each(function(input) {
			input.checked = false;
		});
	});
</script>