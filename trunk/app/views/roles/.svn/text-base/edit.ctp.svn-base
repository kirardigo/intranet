
<?php
	$html->css(array('tabs', 'window/window', 'window/mac_os_x'), null, array(), false);
	$javascript->link(array('tabs', 'window'), false);
	
	/**
	 * Recursively builds the table rows for the application folders. Indents folders so that their hierarchy can be observed visually.
	 * @param object $html The HtmlHelper.
	 * @param object $form The FormHelper.
	 * @param array $folders The array of application folders that has been set for the view in the controller.
	 * @param int $offset Used internally to make sure all of the form fields being generated are uniquely named.
	 * @param int $indent Used internally to keep track of the indentation level in the hierarchy.
	 */
	function buildApplicationFolderRows($html, $form, $folders, $offset = 0, $indent = 0)
	{
		foreach ($folders as $folder)
		{
			echo $html->tableCells(
				array(
					str_repeat('&nbsp;', $indent * 4) . h($folder['ApplicationFolder']['folder_name']),
					array(
						$form->hidden("RoleApplicationFolder.application_folder_id.{$offset}", array('value' => $folder['ApplicationFolder']['id'])) . $form->checkbox("RoleApplicationFolder.checked.{$offset}"),
						array('class' => 'Center')
					),
					array(
						$html->link($html->image('iconDetail.png'), '#', array('escape' => false, 'class' => 'AppLink')),
						array('class' => 'Center')
					)
				),
				array(),
				array('class' => 'Alt')
			);
			
			$offset = buildApplicationFolderRows($html, $form, $folder['children'], ++$offset, $indent + 1);
		}
		
		return $offset;
	}
?>

<script type="text/javascript">
	var win = null;
	
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	function showApplications(event)
	{
		var applicationFolderID = event.element().up("td").previous().down("input").value;
		
		win = mrs.createWindow(500, 400).setAjaxContent(
			"/ajax/roleApplications/edit/<?= isset($this->data['Role']['id']) ? $this->data['Role']['id'] : '' ?>/" + encodeURIComponent(applicationFolderID),
			{ evalScripts: true }
		).show(true).activate();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			//on a successful postback, close the entire window
			closeWindow();
		<?php endif; ?>
		
		$("CancelButton").observe("click", function() {
			closeWindow();
		});
		
		$$(".AppLink").invoke("observe", "click", showApplications);

		//make the routes table scrollable right now
		mrs.makeScrollable("RoutesTable", { aoColumns: [null, { bSortable: false }] });
		
		var permissionsScrollableApplied = false;
		
		//set up the permissions table to be scrollable once it is displayed. If we did it now, the column sizes would be screwed
		//up because the widths can't be correctly calculated when invisible.
		Tabs.changeCallback = function(page) {
			if (page.id == "PermissionsTab" && !permissionsScrollableApplied) {
				mrs.makeScrollable("PermissionsTable", { aoColumns: [null, { bSortable: false }] });
				permissionsScrollableApplied = true;
			}
		};
	});
	
	//when role application assignments have been completed, close the popup dialog
	document.observe("roleApplication:submitComplete", function() {
		UI.defaultWM.windows()[0].close();
	});
</script>

<?= $form->create('Role', array('url' => '/roles/edit')) ?>

<div class="GroupBox" style="width: 400px;">
	<h2>Role</h2>
	<div class="Content">
		<?php
			echo $form->hidden('id');
			echo $form->input('name');
		?>
	</div>
</div>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Routes</a></li>
	<li><a href="#">Permissions</a></li>
	<li><a href="#">Menus</a></li>
</ul>

<div class="TabContainer">
	<?php if (isset($routes)): ?>
	
		<div id="RoutesTab" class="TabPage">
			<h2>Routes</h2>
			<table class="Styled" id="RoutesTable">
				<thead>
					<tr><th>Route</th><th class="Center">Access</th></tr>
				</thead>
				<tbody>
					<?php
						foreach ($routes as $i => $route)
						{
							echo $html->tableCells(
								array(
									h('/' . ($route['SecureRoute']['prefix'] != '' ? ($route['SecureRoute']['prefix'] . '/') : '') . $route['SecureRoute']['controller'] . '/' . $route['SecureRoute']['action']),
									array(
										$form->hidden("RoleSecureRoute.secure_route_id.{$i}", array('value' => $route['SecureRoute']['id'])) . $form->checkbox("RoleSecureRoute.checked.{$i}"),
										array('class' => 'Center')
									)
								),
								array(),
								array('class' => 'Alt')
							);
						}
					?>
				</tbody>
			</table>
		</div>
		
		<div id="PermissionsTab" class="TabPage" style="display: none;">
			<h2>Permissions</h2>
			<table class="Styled" id="PermissionsTable">
				<thead>
					<tr><th>Permission</th><th class="Center">Access</th></tr>
				</thead>
				<tbody>
					<?php
						foreach ($permissions as $i => $permission)
						{
							echo $html->tableCells(
								array(
									h($permission['PermissionDomain']['name'] . '.' . $permission['Permission']['permission']),
									array(
										$form->hidden("RolePermission.permission_id.{$i}", array('value' => $permission['Permission']['id'])) . $form->checkbox("RolePermission.checked.{$i}"),
										array('class' => 'Center')
									)
								),
								array(),
								array('class' => 'Alt')
							);
						}
					?>
				</tbody>
			</table>
		</div>
	
		<div id="ApplicationFoldersTab" class="TabPage" style="display: none;">
			<h2>Menus</h2>
			<table class="Styled">
				<tr><th>Folder</th><th class="Center">Access</th><th class="Center">Apps</th></tr>
				<?php buildApplicationFolderRows($html, $form, $folders); ?>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php	
	echo '<br style="clear:left;" /><br />';
	echo $form->submit('Save', array('class' => 'StyledButton', 'style' => 'float: left;'));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	echo $form->end();
?>