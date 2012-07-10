<?php
	Configure::write('Cache.disable', true);
	App::import('ConnectionManager');
	
	/**
	 * This shell is meant to be used to scan through an entire filePro file and fix the created by and modified by 
	 * fields stored in the record header of each record. The problem occurred during the unix -> linux migration when UIDs
	 * changed across systems. At the time, a filePro file was created called UserMap that tried to resolve the changes in UIDs.
	 * Furthermore, for some reason, some UIDs when stored in the headers got a "#" symbol stuck in front of the number.
	 *
	 * This shell fixes the headers by using the UserMap table to try and resolve UIDs to the new ones, and for any that 
	 * cannot be remapped, root's UID is used instead.
	 */
	class FixFileproHeadersShell extends Shell 
	{
		var $tasks = array('ReportParameters', 'Logging');
		
		var $parameters = array(
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'model',
				'flag' 			=> 'model',
				'required'		=> true,
				'description'	=> 'The model to fix.'
			),
			array(
				'type' 			=> 'string',
				'model' 		=> 'Virtual',
				'field' 		=> 'blocksize',
				'flag' 			=> 'blocksize',
				'description'	=> 'The number of records to process per find call.',
				'default' 		=> '100'
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			//pr(Set::flatten(ClassRegistry::init('FileNote')->find('all', array('fields' => array('created_by'), 'conditions' => array('account_number' => 'A56699'), 'index' => 'G'))));
		
			$this->Logging->startTimer();
			$parameters = $this->ReportParameters->parse($this->parameters);
			
			$filepro = ConnectionManager::getDataSource('filepro');
			$model = ClassRegistry::init($parameters['Virtual']['model']);
			
			$map = ClassRegistry::init(array(
				'class' => 'UserMap', 
				'alias' => 'UserMap', 
				'table' => 'UserMap',
				'ds' => 'fu05'
			));
			
			$translator = Set::combine($map->find('all'), '/UserMap/Old UID', '/UserMap/User Id');
			//pr($translator);
			//die();
				
			$id = 0;
			
			while ($records = $model->find('all', array('fields' => array('id', 'created_by', 'modified_by'), 'conditions' => array('id >' => $id), 'limit' => $parameters['Virtual']['blocksize'], 'contain' => array())))
			{					
				foreach ($records as $record)
				{
					$newCreated = $record[$model->alias]['created_by'];
					$newModified = $record[$model->alias]['modified_by'];
//TODO - should I move array_key test inside and add another test where if the array key doesn't exist, we run it through resolve username... that way any user we can't find gets set to root on old data
					if (is_numeric($newCreated) && array_key_exists('#' . $newCreated, $translator))
					{
//						$this->Logging->write('created translated');
						$newCreated = $translator['#' . $newCreated];
					}
					
					if (is_numeric($newModified) && array_key_exists('#' . $newModified, $translator))
					{
//						$this->Logging->write('modified translated');
						$newModified = $translator['#' . $newModified];
					}
					
					if (!is_numeric($newCreated))
					{
//						pr('C' . $newCreated);
						$newCreated = $filepro->_resolveUsername($newCreated);
					}
					
					if (!is_numeric($newModified))
					{
//						pr('M' . $newModified);
						$newModified = $filepro->_resolveUsername($newModified);
					}
					
					$this->Logging->write("{$record[$model->alias]['id']}: {$record[$model->alias]['created_by']} -> {$newCreated} : {$record[$model->alias]['modified_by']} -> {$newModified}");
					$filepro->_updateRecordHeaders($model, $record[$model->alias]['id'], null, $newCreated, null, $newModified);
					
					$id = $record[$model->alias]['id'];
				}
				
				$this->Logging->write($id);
			}
			
			$this->Logging->writeElapsedTime();
			$this->Logging->write('Done');
		}
	}
?>