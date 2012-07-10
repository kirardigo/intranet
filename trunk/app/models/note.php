<?php
	class Note extends AppModel
	{
		/**
		 * Lookup notes for record, indexed by type.
		 * @param string $target The target URI for the record.
		 * @param string $type The type of the note.
		 * @return array Array of results.
		 */
		function getNotes($target, $type = null)
		{
			$conditions['target'] = $target;
			
			if ($type !== null)
			{
				$conditions['type'] = $type;
			}
			
			$records = $this->find('all', array(
				'contain' => array(),
				'conditions' => $conditions
			));
			
			$results = array();
			
			// Index the results by note type
			foreach ($records as $row)
			{
				$results[$row[$this->alias]['type']] = $row[$this->alias];
			}
			
			return $results;
		}
		
		/**
		 * Add, edit or delete a note.
		 * @param string $target The target of the note.
		 * @param string $type The type of the note.
		 * @param string $note The text of the note.
		 */
		function saveNote($target, $type, $note)
		{
			$existing = $this->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'target' => $target,
					'type' => $type
				)
			));
			
			if ($existing !== false)
			{
				$id = $existing[$this->alias]['id'];
				
				if ($existing[$this->alias]['note'] == $note)
				{
					// Nothing to update
					return;
				}
				else if ($note === '')
				{
					// Remove the record if the note is empty
					$this->delete($id);
					return;
				}
				
				// Existing records already have an ID
				$saveData[$this->alias]['id'] = $id;
			}
			else if ($note === '')
			{
				// Nothing to add at this time
				return;
			}
			else
			{
				// New records need to be configured
				$saveData[$this->alias]['target'] = $target;
				$saveData[$this->alias]['type'] = $type;
			}
			
			$saveData[$this->alias]['note'] = $note;
			$this->create();
			$this->save($saveData);
		}
		
		/**
		 * Delete all notes for a given target. This is usually used when removing a parent record.
		 * @param string $target The target record to remove records for.
		 * @return bool True on success, false on failure.
		 */
		function deleteNotes($target)
		{
			return $this->deleteAll(array(
				'target' => $target
			));
		}
	}
?>