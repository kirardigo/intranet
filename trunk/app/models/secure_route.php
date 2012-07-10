<?php
	class SecureRoute extends AppModel
	{
		var $cachePrefix = 'secureRoute_';
		var $belongsTo = array('PermissionDomain');
		
		var $validate = array(
			'controller' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The controller is required.'
				)
			),
			'action' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The action is required.'
				)
			)
		);
		
		/**
		 * Checks to see if the given role has access to a particular route.
		 * @param int $roleID The ID of the role to check.
		 * @param string $prefix The prefix (if any) of the route.
		 * @param string $controller The controller name of the route.
		 * @param string $action The action name of the route.
		 * @return boolean True if the role has access, false otherwise.
		 */
		function check($roleID, $prefix, $controller, $action)
		{
			//try and grab the access list from the cache
			$key = $this->_generateCacheKey($prefix, $controller, $action);
			$allowedRoles = Cache::read($key, 'secureRoutes');
			
			//if we don't have it yet we need to figure it out
			if ($allowedRoles === false)
			{
				//go see if this route is a secure action
				$action = $this->find('first', array(
					'fields' => array('id'),
					'conditions' => array(
						'prefix' => $prefix,
						'controller' => $controller,
						'action' => $action
					),
					'contain' => array()
				));
				
				//if we can't find the route then it's not locked down in any way
				if ($action === false)
				{
					//save the fact that it's a wide open route first
					Cache::write($key, true, 'secureRoutes');
					return true;
				}
				
				//if we did find the route as a secure action, we need to see what roles have access 
				$allowedRoles = Set::extract('/RoleSecureRoute/role_id', ClassRegistry::init('RoleSecureRoute')->find('all', array(
					'fields' => array('role_id'),
					'conditions' => array('secure_route_id' => $action['SecureRoute']['id']),
					'contain' => array()
				)));
				
				//cache the roles for subsequent queries
				Cache::write($key, $allowedRoles, 'secureRoutes');
			}
			
			//if the route is wide open or if the specified role is in the list that has access, then we're good
			return $allowedRoles === true || in_array($roleID, $allowedRoles);
		}
		
		/**
		 * Clears any cached information regarding what roles have access to a given secure route which will force the
		 * next check to read directly from the database again.
		 * @param string $prefix The prefix (if any) of the route.
		 * @param string $controller The controller name of the route.
		 * @param string $action The action name of the route.
		 */
		function clearSecureRouteCache($prefix, $controller, $action)
		{
			Cache::delete($this->_generateCacheKey($prefix, $controller, $action), 'secureRoutes');
		}
		
		/**
		 * Clears the entire cache of secure routes.
		 */
		function clearCache()
		{
			Cache::clear(false, 'secureRoutes');
		}
		
		/**
		 * Generates the key used for caching the information about a particular route that states what roles
		 * have access.
		 * @param string $prefix The prefix (if any) of the route.
		 * @param string $controller The controller name of the route.
		 * @param string $action The action name of the route.
		 * @return string The cache key.
		 */
		function _generateCacheKey($prefix, $controller, $action)
		{
			return $this->cachePrefix . Inflector::slug($prefix . $controller . $action);
		}
	}
?>