<?php
	class PermissionsController extends AppController
	{
		var $helpers = array('Paginator');
		var $uses = array('Permission', 'PermissionDomain', 'RolePermission');
		
		/**
		 * Action to show all permissions.
		 */
		function index()
		{
			$this->paginate = array(
				'contain' => array('PermissionDomain'),
				'order' => array('permission', 'PermissionDomain.name'),
				'limit' => 25
			);
			
			$this->set('records', $this->paginate('Permission'));
		}
		
		/**
		 * Action to edit or create a new permission.
		 * @param int $id If specified, the ID of the record to edit. Otherwise a new record is assumed.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				//try and save the permission
				if ($this->Permission->save($this->data))
				{
					//store this for later so the insert will now become an update on the next postback
					$this->data['Permission']['id'] = $this->Permission->id;
					
					//on an edit we need to save role associations
					if (isset($this->data['RolePermission']))
					{
						//ditch any existing role associations
						$this->RolePermission->deleteAll(array('permission_id' => $this->Permission->id));
						
						//recreate those that are checked
						foreach ($this->data['RolePermission']['role_id'] as $i => $role)
						{
							if ($this->data['RolePermission']['checked'][$i])
							{
								$this->RolePermission->create();
								
								$this->RolePermission->save(array(
									'RolePermission' => array(
										'role_id' => $role,
										'permission_id' => $this->Permission->id
									)
								));
							}
						}
						
						//clear any cache for this permission so that the new roles are read
						$this->Permission->clearPermissionCache($this->PermissionDomain->field('name', array('id' => $this->data['Permission']['permission_domain_id'])) . '.' . $this->data['Permission']['permission']);
						
						//on an edit, we close the window, but on an insert we want to give them a chance to 
						//associate roles
						$this->set('close', true);
					}
				}
			}
			else if ($id != null)
			{
				//load the existing record to edit if we have one
				$this->data = $this->Permission->find('first', array(
					'conditions' => array('id' => $id),
					'contain' => array()
				));
			}
			
			//if we are on an existing record, load up roles that have access to it
			if (isset($this->data['Permission']['id']) && !empty($this->data['Permission']['id']))
			{
				//grab the roles
				$roles = $this->RolePermission->Role->find('all', array('contain' => array()));
				
				//grab what roles have access to the permission
				$allowedRoles = Set::extract('/RolePermission/role_id', $this->RolePermission->find('all', array(
					'fields' => array('role_id'),
					'conditions' => array(
						'permission_id' => $this->data['Permission']['id']
					),
					'contain' => array()
				)));
				
				//if we don't have form data yet (i.e. it's not a postback) for the RolePermission, let's set which
				//roles currently have access
				if (!isset($this->data['RolePermission']))
				{
					foreach ($roles as $i => $role)
					{
						$this->data['RolePermission']['checked'][$i] = in_array($role['Role']['id'], $allowedRoles) ? true : false;
					}
				}
				
				$this->set(compact('roles', 'allowedRoles'));
			}
			
			$this->set('domains', $this->PermissionDomain->find('list'));
		}
		
		/**
		 * Action to delete a permission.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$record = $this->Permission->find('first', array('conditions' => array('Permission.id' => $id), 'contain' => array('PermissionDomain')));
			
			if ($record !== false)
			{
				//ditch role associations
				$this->RolePermission->deleteAll(array('permission_id' => $id));
				
				//ditch the record
				$this->Permission->delete($id);
				
				//clear any cache so that the correct permissions can be calculated on subsequent checks
				$this->Permission->clearPermissionCache($record['PermissionDomain']['name'] . '.' . $record['Permission']['permission']);
			}

			$this->redirect('/permissions');
		}
	}
?>