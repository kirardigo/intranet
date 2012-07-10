<?php
	class UserCache extends AppModel
	{
		var $useTable = 'user_cache';
		
		/**
		 * Clears all records from the cache.
		 */
		function clear()
		{
			$this->query("truncate table {$this->useTable}");
		}
		
		/**
		 * Adds a user to the cache in the database.
		 * @param string $username The Linux username of the user to cache.
		 * @param int $uid The Linux UID of the user.
		 */
		function addToCache($username, $uid)
		{
			$this->create();
			
			$this->save(array('UserCache' => array(
				'username' => $username,
				'uid' => $uid
			)));
		}
		
		/**
		 * Resolves a UID to a username.
		 * @param int $uid The UID to resolve.
		 * @return mixed The username of the user if it was able to be resolved, false otherwise.
		 */
		function resolveUid($uid)
		{
			return $this->field('username', array('uid' => $uid));
		}
		
		/**
		 * Resolves a username to a UID.
		 * @param string $username The username to resolve.
		 * @return mixed The UID of the user if it was able to be resolved, false otherwise.
		 */
		function resolveUsername($username)
		{
			return $this->field('uid', array('username' => $username));
		}
	}
?>