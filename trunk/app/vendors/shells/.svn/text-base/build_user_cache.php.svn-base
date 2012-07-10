<?php
	Configure::write('Cache.disable', true);
	App::import('Folder');
	
	/**
	 * This shell builds a cache of UID to Linux username mappings that is used by our filepro driver to
	 * resolve UIDs that are stored in the header of filepro records. The reason we need to cache this is because
	 * the getent command that is used to retrieve the username from a UID is too expensive of a call and noticeably affects
	 * the performance of the driver. This shell reads all of the users from /home and runs a getent on every one of them to find
	 * out their UIDs. The results are stored in a table in MySQL called user_cache.
	 */
	class BuildUserCacheShell extends Shell
	{
		var $uses = array('UserCache');
		var $tasks = array('Logging');
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			$this->Logging->startTimer();
			
			//ditch the existing cache
			$this->UserCache->clear();
			
			//grab the usernames in /home
			$home = new Folder('/home');
			$users = array_shift($home->read());
			
			foreach ($users as $user)
			{
				$this->Logging->write("{$user}...");
			
				//grab the user's uid
				$uid = exec('getent passwd ' . escapeshellarg($user) . ' | cut -f3 -d:');
				
				//if the uid is null, it means this isn't a user that can log into the system, so we don't worry about those ones
				if ($uid != null)
				{
					$this->UserCache->addToCache($user, $uid);
				}
			}

			$this->Logging->writeElapsedTime();
			$this->Logging->write('Done');
		}
	}
?>