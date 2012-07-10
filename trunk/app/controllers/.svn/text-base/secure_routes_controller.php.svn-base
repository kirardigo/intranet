<?php
	class SecureRoutesController extends AppController
	{
		var $helpers = array('Paginator');
		var $uses = array('SecureRoute', 'PermissionDomain', 'RoleSecureRoute');
		
		/**
		 * Action to show all secure routes.
		 */
		function index()
		{
			$this->paginate = array(
				'contain' => array('PermissionDomain'),
				'order' => array('PermissionDomain.name', 'prefix', 'controller', 'action'),
				'limit' => 25
			);
			
			$this->set('records', $this->paginate('SecureRoute'));
		}
		
		/**
		 * Action to edit or create a new secure route.
		 * @param int $id If specified, the ID of the record to edit. Otherwise a new record is assumed.
		 */
		function edit($id = null)
		{
			if (!empty($this->data))
			{
				//try and save the route
				if ($this->SecureRoute->save($this->data))
				{
					//store this for later so the insert will now become an update on the next postback
					$this->data['SecureRoute']['id'] = $this->SecureRoute->id;
					
					//on an edit we need to save role associations
					if (isset($this->data['RoleSecureRoute']))
					{
						//ditch any existing role associations
						$this->RoleSecureRoute->deleteAll(array('secure_route_id' => $this->SecureRoute->id));
						
						//recreate those that are checked
						foreach ($this->data['RoleSecureRoute']['role_id'] as $i => $role)
						{
							if ($this->data['RoleSecureRoute']['checked'][$i])
							{
								$this->RoleSecureRoute->create();
								
								$this->RoleSecureRoute->save(array(
									'RoleSecureRoute' => array(
										'role_id' => $role,
										'secure_route_id' => $this->SecureRoute->id
									)
								));
							}
						}
						
						//clear any cache for this route so that the new roles are read
						$this->SecureRoute->clearSecureRouteCache($this->data['SecureRoute']['prefix'], $this->data['SecureRoute']['controller'], $this->data['SecureRoute']['action']);
						
						//on an edit, we close the window, but on an insert we want to give them a chance to 
						//associate roles
						$this->set('close', true);
					}
				}
			}
			else if ($id != null)
			{
				//load the existing record to edit if we have one
				$this->data = $this->SecureRoute->find('first', array(
					'conditions' => array('id' => $id),
					'contain' => array()
				));
			}
			
			//if we are on an existing record, load up roles that have access to it
			if (isset($this->data['SecureRoute']['id']) && !empty($this->data['SecureRoute']['id']))
			{
				//grab the roles
				$roles = $this->RoleSecureRoute->Role->find('all', array('contain' => array()));
				
				//grab what roles have access to the route
				$allowedRoles = Set::extract('/RoleSecureRoute/role_id', $this->RoleSecureRoute->find('all', array(
					'fields' => array('role_id'),
					'conditions' => array(
						'secure_route_id' => $this->data['SecureRoute']['id']
					),
					'contain' => array()
				)));
				
				//if we don't have form data yet (i.e. it's not a postback) for the RoleSecureRoute, let's set which
				//roles currently have access
				if (!isset($this->data['RoleSecureRoute']))
				{
					foreach ($roles as $i => $role)
					{
						$this->data['RoleSecureRoute']['checked'][$i] = in_array($role['Role']['id'], $allowedRoles) ? true : false;
					}
				}
				
				$this->set(compact('roles', 'allowedRoles'));
			}
			
			$this->set('domains', $this->PermissionDomain->find('list'));
		}
		
		/**
		 * Action to delete a secure route.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$record = $this->SecureRoute->find('first', array('conditions' => array('id' => $id), 'contain' => array()));
			
			if ($record !== false)
			{
				//ditch role associations
				$this->RoleSecureRoute->deleteAll(array('secure_route_id' => $id));
				
				//ditch the record
				$this->SecureRoute->delete($id);
				
				//clear any cache so that the correct routes can be calculated on subsequent checks
				$this->SecureRoute->clearSecureRouteCache($record['SecureRoute']['prefix'], $record['SecureRoute']['controller'], $record['SecureRoute']['action']);
			}
						
			$this->redirect('/secureRoutes');
		}
	}
?>