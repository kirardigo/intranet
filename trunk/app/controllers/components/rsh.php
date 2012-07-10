<?php
	/**
	 * Encapsulates RSH commands.
	 */
	class RshComponent extends Object
	{
		const ResultCodeVariable = 'RSH_RESULT_CODE';
		
		/**
		 * Writes data to a remote file.
		 * @param string $fqdn The fully qualified domain name of the server to use.
		 * @param string $username The username to use to connect to the server.
		 * @param string $remotePath The absolute path to the file to write to.
		 * @param string $data The data to write.
		 */
		function writeFile($fqdn, $username, $remotePath, $data)
		{
			$fqdn = escapeshellarg($fqdn);
			$username = escapeshellarg($username);
			$remotePath = escapeshellarg($remotePath);
			
			$h = popen("rsh {$fqdn} -l {$username} \"cat > {$remotePath}\"", 'w');
			fwrite($h, $data);
			fclose($h);
		}
		
		/**
		 * Reads a remote file.
		 * @param string $fqdn The fully qualified domain name of the server to use.
		 * @param string $username The username to use to connect to the server.
		 * @param string $remotePath The absolute path to the file to write to.
		 * @return string The contents of the file.
		 */
		function readFile($fqdn, $username, $remotePath)
		{
			$h = popen("rsh {$fqdn} -l {$username} \"cat {$remotePath}\"", 'r');
			$data = '';
			
			while (!feof($h)) 
			{
				$data .= fread($h, 8192);
			}
			
			fclose($h);
			return $data;
		}
		
		/**
		 * Deletes a remote file.
		 * @param string $fqdn The fully qualified domain name of the server to use.
		 * @param string $username The username to use to connect to the server.
		 * @param string $remotePath The absolute path to the file to delete.
		 * @return string The contents of the file.
		 */
		function deleteFile($fqdn, $username, $remotePath)
		{
			$this->execute($fqdn, $username, "test -f " . escapeshellarg($remotePath) . " && rm " . escapeshellarg($remotePath));
		}
		
		/**
		 * Executes a remote command.
		 * @param string $fqdn The fully qualified domain name of the server to use.
		 * @param string $username The username to use to connect to the server.
		 * @param string $command The command to execute. This command is NOT escaped in any way so
		 * make sure you sanitize it first.
		 * @param array $arguments An array of arguments to the command. The arguments are automatically
		 * escaped for the shell.
		 * @param int $retries The number of times to retry the command if it fails.
		 * @return mixed The output of the command on success. If the remote server cannot be reached, false
		 * is returned.
		 */
		function execute($fqdn, $username, $command, $arguments = array(), $retries = 0)
		{
			set_time_limit(0);
			$attempts = 0;
			
			//keep trying to connect to the remote server until we hit our retry limit
			while ($attempts <= $retries)
			{
				//e("rsh {$fqdn} -l {$username} \"{$command} " . implode(' ', array_map('escapeshellarg', $arguments)) . "\"" . ' 2>&1; echo ' . RshComponent::ResultCodeVariable . '=${?}');
				$h = popen("rsh {$fqdn} -l {$username} \"{$command} " . implode(' ', array_map('escapeshellarg', $arguments)) . "\"" . '; echo ' . RshComponent::ResultCodeVariable . '=${?}', 'r');
				
				//if we still don't have a pipe the remote server must be down
				if ($h === false)
				{
					return false;
				}
				
				$data = '';
				
				//keep reading from standard out until the command finishes
				while (!feof($h)) 
				{
					$data .= fread($h, 8192);
				}
				
				//close up shop
				fclose($h);
				
				//make sure the rsh command itself didn't fail
				if (!preg_match('/' . RshComponent::ResultCodeVariable . '=(.*?)\n?$/', $data, $matches) || $matches[1] != '0')
				{
					$attempts++;
					sleep(1);
					continue;
				}
				
				//remove our return value
				$data = preg_replace('/\n?' . RshComponent::ResultCodeVariable . '=.*\n?$/', '', $data);
				
				//return the output of the command
				return $data;
			}
			
			return false;
		}
	}
?>