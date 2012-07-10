<?php
	class StaffPalCodesController extends AppController
	{
		var $pageTitle = 'Staff PAL Codes';
		
		/**
		 * Generate a record listing.
		 */
		function summary()
		{
			$this->paginate = array(
				'contain' => array(),
				'order' => 'code'
			);
			
			$records = $this->paginate('StaffPalCode');
			
			$this->set(compact('records'));
		}
		
		/**
		 * Create or edit a record.
		 * @param mixed $id The ID of the record or null to create a new record.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				$this->data['StaffPalCode']['id'] = $id;
				$this->StaffPalCode->save($this->data);
				
				$this->redirect('summary');
			}
			
			if ($id != null)
			{
				$this->data = $this->StaffPalCode->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set(compact('id'));
		}
		
		/**
		 * Delete an existing record.
		 * @param int $id The ID of the record to delete.
		 */
		function delete($id)
		{
			$this->autoRender = false;
			$this->StaffPalCode->delete($id);
			$this->redirect('/staffPalCodes/summary');
		}
	}
?>