<?php
	class StaffController extends AppController
	{
		/**
		 * Ajax action to find an active staff member by name or login.
		 * @param bool $allowSelfSelection Determines whether a user can select themselves.
		 * @param bool $allowManagerSelection Determines whether managers can be selected.
		 */
		function ajax_autoComplete($allowSelfSelection = 1, $allowManagerSelection = 1)
		{
			$currentUser = $this->Session->read('user');
			
			$recentSearch = trim($this->data['Staff']['search']);
		
			if ($recentSearch == '')
			{
				die();
			}
			
			$conditions = array(
				'is_active' => 1,
				'or' => array(
					'user_id like' => $recentSearch . '%',
					'full_name like' => $recentSearch . '%'
				)
			);
			
			if (!$allowSelfSelection)
			{
				$conditions['user_id <>'] = $currentUser;
			}
			
			if (!$allowManagerSelection)
			{
				$conditions['employee_class !='] = 'XMNG';
			}
			
			$matches = $this->Staff->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'full_name', 'user_id'),
				'conditions' => $conditions
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Staff.id', 
				'id_prefix' => 'staff_',
				'value_fields' => array('Staff.user_id'),
				'informal_fields' => array('Staff.full_name')
			));
		}
		
		/**
		 * Ajax action to find a staff member by last name or full name and return full name.
		 */
		function ajax_autoCompleteName()
		{
			$currentUser = $this->Session->read('user');
			
			$recentSearch = trim($this->data['Staff']['search']);
			
			if ($recentSearch == '')
			{
				die();
			}
			
			$conditions = array(
				'or' => array(
					'user_id like' => $recentSearch . '%',
					'full_name like' => $recentSearch . '%',
					'last_name like' => $recentSearch . '%'
				)
			);
			
			$matches = $this->Staff->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'full_name', 'user_id'),
				'conditions' => $conditions
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Staff.id',
				'value_fields' => array('Staff.full_name')
			));
		}
		
		/**
		 * Get the manager for a specified user.
		 * Expects $this->params['form']['username'].
		 */
		function json_getManager()
		{
			$managerUsername = $this->Staff->field('manager', array('user_id' => $this->params['form']['username']), null, 'F');
			$this->set('json', array('manager' => strtolower($managerUsername)));
		}
		
		/**
		 * Get profit center & department for a given user.
		 * Expects $this->params['form']['username'].
		 */
		function json_getProfitCenter()
		{
			$profitCenter = '';
			$department = '';
			
			$record = $this->Staff->find('first', array(
				'contain' => array(),
				'fields' => array('profit_center_number', 'department'),
				'conditions' => array('user_id' => $this->params['form']['username']),
				'index' => 'F'
			));
			
			if ($record !== false)
			{
				$profitCenter = $record['Staff']['profit_center_number'];
				$department = $record['Staff']['department'];
			}
			
			$this->set('json', array('profitCenter' => $profitCenter, 'department' => $department));
		}
		
		/**
		 * Lookup information necessary for KB Publisher.
		 * @param string $username The username to find the information for.
		 */
		function ajax_kbPublisherInfo($username)
		{
			$this->autoRender = false;
			
			$record = $this->Staff->find('first', array(
				'contain' => array(),
				'fields' => array(
					'id',
					'first_name',
					'last_name',
					'email',
					'kb_role_name',
					'kb_privilege_name'
				),
				'conditions' => array('user_id' => $username)
			));
			
			if ($record !== false)
			{
				echo implode("|", $record['Staff']);
			}
		}
	}
?>