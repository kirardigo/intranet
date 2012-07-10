<?php
	
	/**
	 * Helper for checking permissions.
	 */
	class PermissionHelper extends AppHelper 
	{
		var $helpers = array('Session');
		
		/**
		 * Checks to see if the user has a specific permission.
		 * @param string $permission The name of the permission to check. The permission should be in the form
		 * of "domain.permission".
		 */
		function check($permission)
		{
			//if the user is logged in...
			if ($this->Session->check('userInfo'))
			{
				//check permission for their role against the desired permission
				$info = $this->Session->read('userInfo');
				return ClassRegistry::init('Permission')->check($info['role_id'], $permission);
			}
			
			//if they aren't logged in, they can't have permission
			return false;
		}
	}
?>