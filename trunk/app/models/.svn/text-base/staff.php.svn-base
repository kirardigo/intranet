<?php
	class Staff extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'STAFF';
		
		/**
		 * Determines whether employee class of employee shows them as a manager.
		 * @param string $username The username of the user to lookup.
		 * @return bool
		 */
		function isManager($username)
		{
			$employeeClass = $this->field('employee_class', array('user_id' => $username), null, 'F');
			return $employeeClass == 'XMNG';
		}
		
		/**
		 * Determines whether employee class of employee shows them as a manager or a supervisor.
		 * @param string $username The username of the user to lookup.
		 * @return bool
		 */
		function isManagerOrSupervisor($username)
		{
			$employeeClass = $this->field('employee_class', array('user_id' => $username), null, 'F');
			return in_array($employeeClass, array('XMNG', 'XSPV'));
		}
		
		/**
		 * Used to determine whether the employee can approve magnificents.
		 * @param string $username The username of the user to find the permission for.
		 * @return bool
		 */
		function canApproveMagnificents($username)
		{
			return $this->isManagerOrSupervisor($username);
		}
		
		/**
		 * Used to determine whether user can see magnificents history for all users.
		 * @param string $username The username of the user to find the permission for.
		 * @return bool
		 */
		function canSeeAllMagnificents($username)
		{
			return $this->isManagerOrSupervisor($username);
		}
		
		/**
		 * Get the user's full name or their username if not found or blank.
		 * @param string $username The username for the staff member.
		 * @return string
		 */
		function getStaffName($username)
		{
			$fullName = $this->field('full_name', array('user_id' => $username), null, 'F');
			return ($fullName !== false && trim($fullName) != '') ? $fullName : $username;
		}
		
		/**
		 * Get the full name for a user's manager or blank if not found.
		 * @param string $username The user name for the staff member.
		 * @return string
		 */
		function getManagerName($username)
		{
			$managerUsername = $this->field('manager', array('user_id' => $username), null, 'F');
			$fullName = $this->field('full_name', array('user_id' => $managerUsername), null, 'F');
			return ($fullName !== false) ? $fullName : '';
		}
		
		/**
		 * Get the full name for a user's supervisor or blank if not found.
		 * @param string $username The username for the staff member.
		 * @return string
		 */
		function getSupervisorName($username)
		{
			$supervisorUsername = $this->field('manager', array('user_id' => $username), null, 'F');
			$fullName = $this->field('full_name', array('user_id' => $supervisorUsername), null, 'F');
			return ($fullName !== false) ? $fullName : '';
		}
	}
?>