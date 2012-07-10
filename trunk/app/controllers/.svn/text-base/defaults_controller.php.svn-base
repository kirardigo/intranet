<?php
	class DefaultsController extends AppController
	{
		var $components = array('DefaultFile');
		var $uses = array();
		
		/**
		 * Action to view/edit the Default File.
		 */
		function index()
		{
			$this->pageTitle = 'Default File';
			
			//read the default file
			$this->DefaultFile->load();
			
			if (!empty($this->data))
			{
				//go through each posted field and update the default file
				//data with the posted data as long as the key exists in the default
				//file.
				foreach ($this->data['DefaultFile'] as $key => $value)
				{
					if (array_key_exists($key, $this->DefaultFile->data))
					{
						$this->DefaultFile->data[$key] = $value;
					}
				}
				
				//save the changes and we're done
				if ($this->DefaultFile->save())
				{
					$this->flash('Default File successfully updated.', '/defaults');
					return;
				}
				else
				{
					$this->set('error', true);
				}
			}

			$this->data = array('DefaultFile' => array_map('trim', $this->DefaultFile->data));
			$this->set('fields', $this->DefaultFile->fields);
		}
	}
?>