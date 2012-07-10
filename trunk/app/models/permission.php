<?php
	class Permission extends AppModel
	{
		var $cachePrefix = 'permission_';
		var $belongsTo = array('PermissionDomain');
		
		var $validate = array(
			'permission' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The permission name is required.'
				)
			)
		);
		
		/**
		 * Checks to see if the given role has access to a particular permission.
		 * @param int $roleID The ID of the role to check.
		 * @param string $permission The name of the permission. It should be in the form of "domain.permission".
		 * @return boolean True if the role has access, false otherwise.
		 */
		function check($roleID, $permission)
		{
			//try and grab the access list from the cache
			$key = $this->_generateCacheKey($permission);
			$allowedRoles = Cache::read($key, 'permissions');
			
			//if we don't have it yet we need to figure it out
			if ($allowedRoles === false)
			{
				$parts = explode('.', $permission);
				
				//if the caller forgot to specify a domain, force an empty one
				if (count($parts) == 1)
				{
					$parts[1] = $parts[0];
					$parts[0] = '';
				}
				
				//see if the permission exists
				$record = $this->find('first', array(
					'fields' => array('id'),
					'conditions' => array(
						'PermissionDomain.name' => $parts[0],
						'permission' => $parts[1]
					),
					'contain' => array('PermissionDomain')
				));
				
				//if we can't find the permission then we have a problem
				if ($record === false)
				{
					throw new Exception("Permission \"{$permission}\" does not exist!");
				}
				
				//if we did find the permission, we need to see what roles have access 
				$allowedRoles = Set::extract('/RolePermission/role_id', ClassRegistry::init('RolePermission')->find('all', array(
					'fields' => array('role_id'),
					'conditions' => array('permission_id' => $record['Permission']['id']),
					'contain' => array()
				)));
				
				//cache the roles for subsequent queries
				Cache::write($key, $allowedRoles, 'permissions');
			}
			
			//if the specified role is in the list that has access, then we're good
			return in_array($roleID, $allowedRoles);
		}
		
		/**
		 * Requires that a role have access to a given permission or a PermissionException is thrown.
		 * @param int $roleID The ID of the role to check.
		 * @param string $permission The name of the permission. It should be in the form of "domain.permission".
		 */
		function demand($roleID, $permission)
		{
			if (!$this->check($roleID, $permission))
			{
				throw new PermissionException("Role {$roleID} does not have access to the \"{$permission}\" permission!");
			}
		}
		
		/**
		 * Clears any cached information regarding what roles have access to a given permission which will force the
		 * next check or demand to read directly from the database again.
		 * @param string $permission The name of the permission. It should be in the form of "domain.permission".
		 */
		function clearPermissionCache($permission)
		{
			Cache::delete($this->_generateCacheKey($permission), 'permissions');
		}
		
		/**
		 * Clears the entire cache of permissions.
		 */
		function clearCache()
		{
			Cache::clear(false, 'permissions');
		}
		
		/**
		 * Generates the key used for caching the information about a particular permission that states what roles
		 * have access.
		 * @param string $permission The name of the permission. It should be in the form of "domain.permission".
		 * @return string The cache key.
		 */
		function _generateCacheKey($permission)
		{
			return $this->cachePrefix . Inflector::slug($permission);
		}
	}
	
	/** Custom exception class to throw when demanding permissions. */
	class PermissionException extends Exception { }
?>