<?php
	class MagnificentRedemptionOptionsController extends AppController
	{
		var $pageTitle = 'Magnificents';
		
		/**
		 * Retrieve a list of redemption options.
		 */
		function index()
		{
			$this->paginate = array(
				'contain' => array(),
				'order' => array(
					'value',
					'description'
				)
			);
			
			$this->data = $this->paginate('MagnificentRedemptionOption');
		}
		
		/**
		 * Edit or create a redemption option.
		 * @param int $id The ID of the record to be edited or NULL for a new record.
		 */
		function edit($id = null)
		{
			if (isset($this->data))
			{
				if ($this->MagnificentRedemptionOption->save($this->data))
				{
					$this->redirect('index');
				}
			}
			else
			{
				$this->data = $this->MagnificentRedemptionOption->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
			}
			
			$this->set('id', $id);
		}
		
		/**
		 * Delete an existing record.
		 * @param int $id The ID of the record being deleted.
		 */
		function delete($id)
		{
			$this->autoRender = false;
			
			$this->MagnificentRedemptionOption->delete($id);
			
			$this->redirect('index');
		}
	}
?>