<?php
	class NavigationController extends AppController
	{
		var $uses = array('ApplicationFolder', 'RoleApplication', 'RoleApplicationFolder');
		
		/**
		 * Used by the navigation javascript to generate the navigation menu links.
		 */
		function json_tree($id = 1)
		{
			$user = $this->Session->read('userInfo');
			
			//make sure the user has access to this folder
			if ($this->RoleApplicationFolder->field('id', array('role_id' => $user['role_id'], 'application_folder_id' => $id)) === false)
			{
				$children = array();
				$applications = array();
			}
			else
			{
				//grab subfolders the user has access to
				$children = $this->RoleApplicationFolder->find('all', array(
					'fields' => array(
						'ApplicationFolder.id', 
						'ApplicationFolder.folder_name',
						"((select count(*) from role_applications a inner join applications b on b.id = a.application_id where a.role_id = {$user['role_id']} and b.application_folder_id = ApplicationFolder.id)) as app_count",
						"((select count(*) from role_application_folders a inner join application_folders b on b.id = a.application_folder_id where a.role_id = {$user['role_id']} and b.parent_id = ApplicationFolder.id)) as subfolder_count"
					),
					'conditions' => array(
						'RoleApplicationFolder.role_id' => $user['role_id'], 
						'ApplicationFolder.parent_id' => $id
					),
					'order' => array(
						'ApplicationFolder.display_order',
						'ApplicationFolder.folder_name'
					)
				));
				
				//grab applications for this folder that the user has access to
				$applications = Set::extract('/Application', $this->RoleApplication->find('all', array(
					'conditions' => array(
						'RoleApplication.role_id' => $user['role_id'],
						'Application.application_folder_id' => $id
					),
					'order' => array(
						'Application.display_order',
						'Application.name'
					)
				)));
			}

			$this->set('suppressJsonHeader', true);
			$this->set('json', compact('children', 'applications'));
		}
		
		/**
		 * Displays a landing page for a given applciation folder.
		 */
		function landing($folderName = null)
		{
			$conditions = array();

			//if we don't have a folder, pull the root node
			if ($folderName == null)
			{
				$folderName = "eMRS";
				$conditions = array('ApplicationFolder.parent_id' => null);
			}
			else
			{
				$folderName = Inflector::humanize(Inflector::underscore($folderName));
				$conditions = array('ApplicationFolder.folder_name' => $folderName);
			}
			
			$this->pageTitle = $folderName . " Home";
			$user = $this->Session->read('userInfo');
			
			//find the folder for the landing page image (and only if the user has access to it)
			$folder = $this->RoleApplicationFolder->find('first', array(
				'fields' => array('ApplicationFolder.id', 'ApplicationFolder.folder_name', 'ApplicationFolder.landing_page_image'),
				'conditions' => array_merge($conditions, array('RoleApplicationFolder.role_id' => $user['role_id'])),
				'contain' => array('ApplicationFolder')
			));			
			
			if ($folder === false)
			{
				die();
			}
			
			//grab any sub folders the user has access to
			$subfolders = $this->RoleApplicationFolder->find('all', array(
				'fields' => array('ApplicationFolder.id', 'ApplicationFolder.folder_name', 'ApplicationFolder.thumbnail_image'),
				'conditions' => array(
					'RoleApplicationFolder.role_id' => $user['role_id'], 
					'ApplicationFolder.parent_id' => $folder['ApplicationFolder']['id']
				),
				'order' => array(
					'ApplicationFolder.display_order',
					'ApplicationFolder.folder_name'
				)
			));
			
			//grab any applications the user has access to
			$applications = Set::extract('/Application', $this->RoleApplication->find('all', array(
				'conditions' => array(
					'RoleApplication.role_id' => $user['role_id'],
					'Application.application_folder_id' => $folder['ApplicationFolder']['id']
				),
				'order' => array(
					'Application.display_order',
					'Application.name'
				)
			)));
			
			$this->set(compact('folder', 'subfolders', 'applications'));
		}
	}
?>