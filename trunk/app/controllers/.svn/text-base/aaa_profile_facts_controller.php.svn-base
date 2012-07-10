<?php
	class AaaProfileFactsController extends AppController
	{
		/**
		 * Adds a Fact.
		 * @param int $ProfileId. The aaa_profile_id of the record we are adding.
		 */
		function json_add($ProfileId, $fact)
		{
			$saveData['AaaProfileFact'] = array(
				'aaa_profile_id' => $ProfileId,
				'fact' => $fact
			);
			
			$this->AaaProfileFact->create();
			
			$success = $this->AaaProfileFact->save($saveData);
			
			$this->set('json', array('success' => ($success !== false), 'id' => $this->AaaProfileFact->id));		
		}
		
		/**
		 * Remove a Fact.
		 * @param int $id The ID of the record to remove.
		 */
		function json_remove($id)
		{
			$success = $this->AaaProfileFact->delete($id);
			
			$this->set('json', array('success' => $success));
		}
	}
?>