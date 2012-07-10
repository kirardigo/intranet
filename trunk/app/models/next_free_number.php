<?php
	class NextFreeNumber extends AppModel
	{
		/**
		 * Gets the next free number for the given name.
		 * @param string $name The name/key of the next free number to get.
		 * @return int The next free number.
		 */
		function next($name)
		{
			$next = 0;
			$lock = "emrs.{$name}";
			
			//acquire a lock for this number
			$result = $this->query("select get_lock('{$lock}', 60) as locked");

			if ($result[0][0]['locked'] == 1)
			{
				//grab the next free number
				$data = $this->find('first', array('conditions' => array('name' => $name), 'contain' => array()));
				
				if ($data === false)
				{
					throw new Exception('Invalid free number!');
				}
				
				//store it and then increment it back in the database
				$next = $data['NextFreeNumber']['next']++;
				$this->save($data);
				
				//release the lock
				$this->query("do release_lock('{$lock}')");
			}
			else
			{
				//die if we can't acquire the lock
				throw new Exception('Could not acquire lock to get the next free number.');
			}

			return $next;
		}
	}
?>