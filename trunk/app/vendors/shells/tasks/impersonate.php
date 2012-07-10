<?php
	Configure::write('Cache.disable', true);
	App::import('Model', 'User');
	
	/**
	 * Task to allow us to impersonate any user so that when records are written for models that have
	 * a created_by and/or modified_by field, the values written will be the username of the impersonated user.
	 * By default, the emrs user is impersonated.
	 */
	class ImpersonateTask extends Shell 
	{
		function __construct(&$dispatch)
		{
			parent::__construct($dispatch);
			$this->impersonate('emrs');
		}
		
		/**
		 * Begins impersonating the specified user.
		 * @param string $username The username of the user to impersonate.
		 */
		function impersonate($username)
		{
			if (!class_exists('User'))
			{
				App::import('Model', 'User');
			}
			
			User::current($username);
		}
	}
?>
