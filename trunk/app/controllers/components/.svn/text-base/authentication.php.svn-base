<?php
	/**
	 * Wrapper arround our authentication mechanism. This way we can change how we auth
	 * without having to change other code in the website.
	 */
	class AuthenticationComponent extends Object
	{
		/** The authentication engine being used. Can be overridden. */
		var $engine = 'pam';
		
		/**
		 * Overridden. Initializes the component. There is only one setting, "engine" which
		 * specifies what authentication engine to use. The default is PAM.
		 */
		function initialize(&$controller, $settings = array()) 
		{
			$this->_set($settings);
		}
		
		/**
		 * Authenticates a user.
		 * @param string $username The user to authenticate.
		 * @param string $password The user's password.
		 * @return bool True if successfully authenticated, false otherwise.
		 */
		function authenticate($username, $password)
		{
			$f = '__' . $this->engine;
			return $this->{$f}($username, $password);
		}
		
		/**
		 * PAM authentication handler. Requires the PECL PAM package to be installed
		 * and configured for PHP.
		 * @param string $username The user to authenticate.
		 * @param string $password The user's password.
		 * @return bool True if successfully authenticated, false otherwise.
		 */
		function __pam($username, $password)
		{
			return pam_auth($username, $password);
		}
		
		/**
		 * Database authentication handler. 
		 * @param string $username The user to authenticate.
		 * @param string $password The user's password.
		 * @return bool True if successfully authenticated, false otherwise.
		 */
		function __database($username, $password)
		{	
			//not implemented
			return false;
		}
	}
?>