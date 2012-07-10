<?php
	class RoleApplicationsController extends AppController
	{
		var $helpers = array('Ajax');
		
		/**
		 * Action action to allow a user to edit the role permissions for all applications underneath a particular application folder.
		 */
		function ajax_edit($roleID, $applicationFolderID)
		{
			$this->autoRenderAjax = false;
			
			if (!empty($this->data))
			{
				//ditch any existing role application associations for the given role and app folder
				$this->RoleApplication->deleteAll(array(
					'role_id' => $roleID,
					'Application.application_folder_id' => $applicationFolderID
				));
				
				//recreate those that are checked
				foreach ($this->data['RoleApplication']['application_id'] as $i => $app)
				{
					if ($this->data['RoleApplication']['checked'][$i])
					{
						$this->RoleApplication->create();
						
						$this->RoleApplication->save(array(
							'RoleApplication' => array(
								'role_id' => $roleID,
								'application_id' => $app
							)
						));
					}
				}
				
				//postback results will be in JSON indicating the success of the operation
				$this->layout = 'json';
				$this->params['json'] = true;
				$this->set('json', array('success' => true));
				return;
			}
			
			$applications = $this->RoleApplication->Application->find('all', array(
				'conditions' => array('application_folder_id' => $applicationFolderID),
				'contain' => array()
			));
			
			//on the initial load, figure out what applications the role has access to
			if (!isset($this->data['RoleApplication']))
			{
				$allowedApplications = Set::extract('/RoleApplication/application_id', $this->RoleApplication->find('all', array(
					'conditions' => array('role_id' => $roleID),
					'contain' => array()
				)));
				
				foreach ($applications as $i => $app)
				{
					$this->data['RoleApplication']['checked'][$i] = in_array($app['Application']['id'], $allowedApplications) ? true : false;
				}
			}
			
			$this->set(compact('roleID', 'applicationFolderID', 'applications'));
		}	
	}
?>