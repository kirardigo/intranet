<?php
	//for some reason the connection manager isn't imported when requesting via wget(?!)
	App::import('ConnectionManager');
	
	class FileproController extends AppController
	{
		var $uses = array();
		
		/**
		 * This action is used by nightly processing scripts in order to see what filepro models it needs to rebuild indexes
		 * for because there are indexes on fields in the header of the key file. We have to rebuild these nightly because
		 * for any inserts/updates/deletes to those models, our driver can't keep the indexes in sync for the ones that are 
		 * for the header fields due to the way we have to interoperate with filepro. See the filepro driver's update and delete methods
		 * for more detail.
		 */
		function indexesWithSystemFields()
		{
			//suppress all output but what we write here
			Configure::write('debug', 0);
			$this->autoRender = false;
			
			$fileproDriver = ConnectionManager::getDataSource('filepro');
			$models = Configure::listObjects('model');
			$matches = array();
			
			//go through the filepro models
			foreach ($models as $name)
			{
				$model = ClassRegistry::init($name);
				
				if ($model->useDbConfig != 'filepro')
				{
					continue;
				}
				
				//see if the model has an index on any system fields
				$indexes = $fileproDriver->describe($model, 'indexes');

				foreach ($indexes as $index)
				{
					$fields = Set::extract('/field_name', $index->header['sort_info']);
					
					foreach ($fields as $field)
					{
						//as soon as we have a match on any header field, save the table name for later
						if (in_array($field, array('created', 'created_by', 'modified', 'modified_by')))
						{
							$matches[] = $model->useTable;
							break 2;
						}
					}
				}
			}
			
			//spit out everything that needs rebuilt
			echo implode("\n", $matches);
		}
		
		/**
		 * TODO - this will be probably be used for where we need to embed a terminal into the app to let them directly access filepro
		 */
		function console()
		{
			
		}
	}
?>