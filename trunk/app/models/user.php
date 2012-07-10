<?php
	class User extends AppModel
	{
		var $belongsTo = array('Role');
		
		/**
		 * Static method that returns or sets the username of the currently logged in user. If a username is passed in,
		 * that will be the name that is set. Otherwise the method returns the last ID that was set.
		 */
		function current($username = null)
		{
			static $_username = null;
			
			if ($username != null)
			{
				$_username = $username;
			}
			
			return $_username;
		}
	}
?>