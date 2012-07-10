<?php
	class Process extends AppModel
	{
		var $hasMany = array(
			'ProcessFile' => array(
				'dependent' => true,
				'order' => array('name', 'filename')
			)
		);
		
		/**
		 * Create a process to track in the process list.
		 * @param string $name The name of the running application.
		 * @param bool $isInterruptible Determine whether the process can be interrupted.
		 * @param string $username The user to create the process for.
		 * @return int The ID of the new process.
		 */
		function createProcess($name, $isInterruptible = false, $username = null)
		{
			$saveData['Process'] = array(
				'name' => $name,
				'percent_complete' => 0,
				'is_complete' => 0,
				'is_interruptible' => $isInterruptible,
				'is_interrupted' => 0
			);
			
			// Only add the created_by field if we are overriding the default behavior of filling in the current user
			if ($username != null)
			{
				$saveData['Process']['created_by'] = $username;
			}
			
			$this->create();
			
			if (!$this->save($saveData))
			{
				return false;
			}
			
			return $this->id;
		}
		
		/**
		 * Update an active process.
		 * @param int $processID The ID of the process.
		 * @param int $percentComplete The current progress value.
		 * @param string $statusMessage A short status message to display on the process screen.
		 */
		function updateProcess($processID, $percentComplete, $statusMessage = null)
		{
			$saveData['Process'] = array(
				'id' => $processID,
				'percent_complete' => $percentComplete
			);
			
			// Only update the status message if it was specified
			if ($statusMessage !== null)
			{
				$saveData['Process']['status_message'] = $statusMessage;
			}
			
			$this->create();
			$this->save($saveData);
		}
		
		/**
		 * Mark the process as interrupted so that it knows to safely stop execution.
		 * @param int $processID The ID of the process.
		 */
		function interruptProcess($processID)
		{
			$saveData['Process'] = array(
				'id' => $processID,
				'is_interrupted' => 1
			);
			
			$this->create();
			$this->save($saveData);
		}
		
		/**
		 * Check to see if the process has been interrupted.
		 * @param int $processID The ID of the process.
		 * @return bool Determines whether the process has been cancelled.
		 */
		function isProcessInterrupted($processID)
		{
			return $this->field('is_interrupted', array('id' => $processID));
		}
		
		/**
		 * Mark a process as finished.
		 * @param int $processID The ID of the process.
		 * @param string $output The output from the background process to attach.
		 */
		function finishProcess($processID, $output = '')
		{
			$saveData['Process'] = array(
				'id' => $processID,
				'is_complete' => 1,
				'output' => $output
			);
			
			$this->create();
			$this->save($saveData);
		}
		
		/**
		 * Remove a process and all associated files.
		 * @param $processID The ID of the process.
		 */
		function removeProcess($processID)
		{
			$this->delete($processID);
		}
		
		/**
		 * Add a file attachment for a particular process.
		 * @param int $processID The ID of the process.
		 * @param string $name The display name for the file.
		 * @param string $filename The file name which needs to be unique per process.
		 * @param string $mimeType The file's MIME type.
		 * @param blob $data The file to blob into the database.
		 * @return bool True if successful, false otherwise.
		 * @throws Exception Error if filename is not unique for the process.
		 */
		function addFile($processID, $name, $filename, $mimeType, $data)
		{
			$fileNameCount = $this->ProcessFile->find('count', array(
				'contain' => array(),
				'conditions' => array(
					'process_id' => $processID,
					'filename' => $filename
				)
			));
			
			if ($fileNameCount > 0)
			{
				throw new Exception('Filename must be unique for the process.');
			}
			
			$saveData['ProcessFile'] = array(
				'process_id' => $processID,
				'name' => $name,
				'filename' => $filename,
				'mime_type' => $mimeType,
				'file_content' => $data
			);
			
			$this->ProcessFile->create();
			return !!$this->ProcessFile->save($saveData);
		}
		
		/**
		 * Remove a file attachment for a particular process.
		 * @param int $processID The ID of the process.
		 * @param int $fileID The ID of the file record.
		 */
		function removeFile($processID, $fileID)
		{
			$this->ProcessFile->delete($fileID);
		}
		
		/**
		 * Tests to see if a process with the given name is running.
		 * @param string $processName The name of the process to look for.
		 * @param string $username Optional argument to see if a process is running for a particular user.
		 * @return bool True if the process is running, false otherwise.
		 */
		function isProcessRunning($processName, $username = null)
		{
			$conditions = array(
				'is_complete' => 0,
				'name' => $processName,
			);
			
			if ($username != null)
			{
				$conditions['created_by'] = $username;
			}
				
			return $this->find('count', array('conditions' => $conditions)) > 0;
		}
	}
?>