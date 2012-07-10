<?php
	uses('Folder');
	
	/**
	 * Behavior for FU05 models to state that they are allowed
	 * to be defragged nightly.
	 */
	class DefraggableBehavior extends ModelBehavior
	{
		function defrag(&$model, $rebuildIndexes = true)
		{
			set_time_limit(0);
			
			//do not allow chainable models to be defragged
			if ($model->Behaviors->enabled('Chainable'))
			{
				return false;
			}
			
			$id = 0;
			
			//ensure that our temp folder exists
			$setting = ClassRegistry::init('Setting');
			$folder = new Folder($setting->get('defrag_temp_path'), true);
			$filePath = $folder->path . DS . $model->useTable . '.DAT';
			
			//grab ahold of the FU05 driver
			$db = ConnectionManager::getDataSource($model->useDbConfig);

			//open up our temp file we're going to write into
			$f = fopen($filePath, 'w');
			
			if ($f === false)
			{
				return false;
			}
			
			//go through all the data that's not deleted and write each record out to a temp file
			while (($data = $model->find('first', array('conditions' => array('id >' => $id)))) !== false)
			{
				//we're going to cheat and use a private method of the FU05 driver that can create the
				//buffer that gets written for the record
				$buffer = $db->_createRecordBuffer($model, array_keys($data[$model->alias]), array_values($data[$model->alias]));
				
				//write the buffer
				fwrite($f, $buffer);
				
				//prep to find the next available record
				$id = $data[$model->alias]['id'];
			}

			fclose($f);
			
			//copy the file over the original (we copy and unlink instead of move to retain file attributes)
			copy($filePath, $db->dataPath($model));
			unlink($filePath);
			
			//rebuild indexes if we're supposed to
			if ($rebuildIndexes && $model->Behaviors->enabled('Indexable'))
			{
				$model->rebuildIndexes();
			}
			
			return true;
		}
	}
?>