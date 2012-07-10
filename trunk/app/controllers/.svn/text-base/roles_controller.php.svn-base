<?php
	class RolesController extends AppController
	{
		var $uses = array('Role', 'RoleSecureRoute', 'RolePermission', 'User', 'RoleApplication', 'RoleApplicationFolder');
		
		/**
		 * Action to show all roles.
		 */
		function index()
		{
			$this->paginate = array(
				'contain' => array(),
				'order' => array('Role.name'),
				'limit' => 25
			);
			
			$records = $this->paginate('Role');
			
			foreach ($records as $i => $record)
			{
				$records[$i]['Role']['is_in_use'] = $this->User->find('count', array(
					'conditions' => array('role_id' => $record['Role']['id']),
					'contain' => array()
				)) > 0;
			}
			
			$this->set('records', $records);
		}
		
		/**
		 * Action to edit or create a new role.
		 * @param int $id If specified, the ID of the record to edit. Otherwise a new record is assumed.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				//try and save the role
				if ($this->Role->save($this->data))
				{
					//store this for later so the insert will now become an update on the next postback
					$this->data['Role']['id'] = $this->Role->id;
					
					//on an edit we need to save the secure route and permission associations
					if (isset($this->data['RoleSecureRoute']))
					{
						//ditch any existing secure route associations
						$this->RoleSecureRoute->deleteAll(array('role_id' => $this->Role->id));
						
						//recreate those that are checked
						foreach ($this->data['RoleSecureRoute']['secure_route_id'] as $i => $route)
						{
							if ($this->data['RoleSecureRoute']['checked'][$i])
							{
								$this->RoleSecureRoute->create();
								
								$this->RoleSecureRoute->save(array(
									'RoleSecureRoute' => array(
										'role_id' => $this->Role->id,
										'secure_route_id' => $route
									)
								));
							}
						}
						
						//clear all secure route caches so the role's routes will be re-read
						$this->RoleSecureRoute->SecureRoute->clearCache();
					}
					
					//now for the permissions
					if (isset($this->data['RolePermission']))
					{
						//ditch any existing secure route associations
						$this->RolePermission->deleteAll(array('role_id' => $this->Role->id));
						
						//recreate those that are checked
						foreach ($this->data['RolePermission']['permission_id'] as $i => $permission)
						{
							if ($this->data['RolePermission']['checked'][$i])
							{
								$this->RolePermission->create();
								
								$this->RolePermission->save(array(
									'RolePermission' => array(
										'role_id' => $this->Role->id,
										'permission_id' => $permission
									)
								));
							}
						}
						
						//clear all secure route caches so the role's routes will be re-read
						$this->RolePermission->Permission->clearCache();
					}
					
					//now for the app folders
					if (isset($this->data['RoleApplicationFolder']))
					{
						//ditch any existing secure route associations
						$this->RoleApplicationFolder->deleteAll(array('role_id' => $this->Role->id));
						
						//recreate those that are checked
						foreach ($this->data['RoleApplicationFolder']['application_folder_id'] as $i => $folder)
						{
							if ($this->data['RoleApplicationFolder']['checked'][$i])
							{
								$this->RoleApplicationFolder->create();
								
								$this->RoleApplicationFolder->save(array(
									'RoleApplicationFolder' => array(
										'role_id' => $this->Role->id,
										'application_folder_id' => $folder
									)
								));
							}
						}
					}
					
					//on an edit, we close the window, but on an insert we want to give them a chance to 
					//associate routes and permissions
					if (isset($this->data['RoleSecureRoute']) || isset($this->data['RolePermission']))
					{					
						$this->set('close', true);
					}
				}
			}
			else if ($id != null)
			{
				//load the existing record to edit if we have one
				$this->data = $this->Role->find('first', array(
					'conditions' => array('id' => $id),
					'contain' => array()
				));
			}

			//if we are on an existing record, load up all the accessible related objects and which ones the role has access to
			if (isset($this->data['Role']['id']) && !empty($this->data['Role']['id']))
			{
				//grab all of the app folders
				$folders = $this->RoleApplicationFolder->ApplicationFolder->find('threaded', array('contain' => array()));
				
				//grab which folders the user can see
				$allowedFolders = Set::extract('/RoleApplicationFolder/application_folder_id', $this->RoleApplicationFolder->find('all', array(
					'fields' => array('application_folder_id'),
					'conditions' => array(
						'role_id' => $this->data['Role']['id']
					),
					'contain' => array()
				)));
				
				//if we don't have form data yet (i.e. it's not a postback) for the RoleApplicationFolder, let's set which
				//folders the role currently has access to
				if (!isset($this->data['RoleApplicationFolder']))
				{
					$this->data['RoleApplicationFolder']['checked'] = $this->_buildApplicationFolderStatuses($folders, $allowedFolders);
				}
				
				//grab the secure routes
				$routes = $this->RoleSecureRoute->SecureRoute->find('all', array('contain' => array()));
				
				//grab what routes the role has access to 
				$allowedRoutes = Set::extract('/RoleSecureRoute/secure_route_id', $this->RoleSecureRoute->find('all', array(
					'fields' => array('secure_route_id'),
					'conditions' => array(
						'role_id' => $this->data['Role']['id']
					),
					'contain' => array()
				)));
				
				//if we don't have form data yet (i.e. it's not a postback) for the RoleSecureRoute, let's set which
				//routes the role currently has access to
				if (!isset($this->data['RoleSecureRoute']))
				{
					foreach ($routes as $i => $route)
					{
						$this->data['RoleSecureRoute']['checked'][$i] = in_array($route['SecureRoute']['id'], $allowedRoutes) ? true : false;
					}
				}
				
				//grab the permissions
				$permissions = $this->RolePermission->Permission->find('all', array('contain' => array('PermissionDomain')));
				
				//grab what permissions the role has access to 
				$allowedPermissions = Set::extract('/RolePermission/permission_id', $this->RolePermission->find('all', array(
					'fields' => array('permission_id'),
					'conditions' => array(
						'role_id' => $this->data['Role']['id']
					),
					'contain' => array()
				)));
				
				//if we don't have form data yet (i.e. it's not a postback) for the RolePermission, let's set which
				//permissions the role currently has access to
				if (!isset($this->data['RolePermission']))
				{
					foreach ($permissions as $i => $permission)
					{
						$this->data['RolePermission']['checked'][$i] = in_array($permission['Permission']['id'], $allowedPermissions) ? true : false;
					}
				}
				
				$this->set(compact('folders', 'routes', 'permissions'));
			}
		}
		
		/**
		 * Recursively builds the true/false status for each application folder into a form suitable for the data array in the view.
		 * @param array $folders The threaded array of every application folder.
		 * @param array $allowedFolders An array of application folder IDs that are allowed by the role.
		 * @return array An array of true/false values; in order by the original $folders array but in a flattened hierarchy, i.e.
		 * 
		 * [0] => true,
		 * [1] => false,
		 * ...
		 * [n] => true
		 */
		function _buildApplicationFolderStatuses($folders, $allowedFolders)
		{
			$data = array();
			
			foreach ($folders as $folder)
			{
				$data[] = in_array($folder['ApplicationFolder']['id'], $allowedFolders) ? true : false;
				
				$children = $this->_buildApplicationFolderStatuses($folder['children'], $allowedFolders);
				
				if (!empty($children))
				{
					foreach ($children as $child)
					{
						$data[] = $child;
					}
				}
			}
			
			return $data;
		}
		
		/**
		 * Action to delete a role.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$record = $this->Role->find('first', array('conditions' => array('id' => $id), 'contain' => array()));
			
			if ($record !== false)
			{
				//ditch route associations
				$this->RoleSecureRoute->deleteAll(array('role_id' => $id));
				
				//ditch permission associations
				$this->RolePermission->deleteAll(array('role_id' => $id));
				
				//ditch application associations
				$this->RoleApplication->deleteAll(array('role_id' => $id));
				
				//ditch app folder associations
				$this->RoleApplicationFolder->deleteAll(array('role_id' => $id));
				
				//ditch the record
				$this->Role->delete($id);
				
				//clear any caches so that the correct routes and permissions can be calculated on subsequent checks
				$this->RoleSecureRoute->SecureRoute->clearCache();
				$this->RolePermission->Permission->clearCache();
			}
						
			$this->redirect('/roles');
		}
	}
?>