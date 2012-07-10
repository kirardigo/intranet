<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe('dom:loaded', function() {
		<?php if (isset($close) && $close): ?>
			//on a successful postback, close the entire window
			closeWindow();
		<?php endif; ?>
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
	});
</script>

<div class="GroupBox" style="width: 400px;">
	<h2>Secure Route</h2>
	<div class="Content">
		<?php
			echo $form->create('SecureRoute', array('url' => '/secureRoutes/edit'));
			
			echo $form->hidden('id');
			echo $form->input('permission_domain_id', array('options' => $domains));
			echo $form->input('prefix');
			echo $form->input('controller');
			echo $form->input('action');
		?>
	</div>
</div>

<?php if (isset($roles)): ?>
	<div class="GroupBox" style="width: 400px;">
		<h2>Allowed Roles</h2>
		<div class="Content">
			<table class="Styled">
				<tr><th>Role</th><th class="Center">Access</th></tr>
				<?php
					foreach ($roles as $i => $role)
					{
						echo $html->tableCells(
							array(
								h($role['Role']['name']),
								array(
									$form->hidden("RoleSecureRoute.role_id.{$i}", array('value' => $role['Role']['id'])) . $form->checkbox("RoleSecureRoute.checked.{$i}"),
									array('class' => 'Center')
								)
							),
							array(),
							array('class' => 'Alt')
						);
					}
				?>
			</table>
		</div>
	</div>

<?php endif; ?>

<?php	
	echo $form->submit('Save', array('class' => 'StyledButton', 'style' => 'float: left;'));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>