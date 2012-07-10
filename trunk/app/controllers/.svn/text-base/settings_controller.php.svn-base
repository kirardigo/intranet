<?php
	class SettingsController extends AppController
	{
		var $pageTitle = 'Settings';
		
		function edit()
		{			
			//on a postback, save every setting. The settings don't post in a standard
			//way since we're showing all setting records on the same page.
			if (!empty($this->data))
			{
				$validated = true;
				
				//see if we have a new advertisement uploaded
				if ($this->data['Invoice']['advertisement_image']['size'] != 0 && $this->data['Invoice']['advertisement_image']['error'] == 0)
				{
					if ($this->data['Invoice']['advertisement_image']['size'] > 100000)
					{
						$this->set('fileSizeExceeded', true);
						$validated = false;
					}
					else
					{
						move_uploaded_file($this->data['Invoice']['advertisement_image']['tmp_name'], WWW_ROOT . 'files' . DS . $this->Setting->get('batch_invoicing_advertisement_image'));
					}
				}
				
				if ($validated)
				{
					//the key of each array element in the value array is the ID of the record
					//to update
					foreach($this->data['Setting']['value'] as $id => $value)
					{
						$this->Setting->id = $id;
						$this->Setting->save(array('Setting' => array('value' => $value)));
					}
					
					$this->flash('Settings have been updated.', 'edit');
					return;
				}
			}
			
			//load all settings that are exposed to the user
			$this->set('settings', $this->Setting->find('all', array(
				'conditions' => array('is_hidden' => 0)
			)));
		}
	}
?>