<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * This shell is responsible for rebuilding all of the FU05 indexes that we store in MySQL.
	 */
	class BuildIndexesShell extends Shell 
	{
		var $tasks = array('ReportParameters', 'Logging');
		
		var $parameters = array(
			array(
				'type' => 'string',
				'model' => 'Index',
				'field' => 'model',
				'flag' => 'm',
				'description' => 'The model whose indexes should be built.'
			),
			array(
				'type' => 'string',
				'model' => 'Index',
				'field' => 'field',
				'flag' => 'f',
				'description' => 'The field in the model whose index should be built. Only works in conjunction with the m switch.'
			),
			array(
				'type' => 'flag',
				'model' => 'Index',
				'field' => 'all_models',
				'flag' => 'a',
				'description' => 'States that all models should have their indexes rebuilt.'
			),
			array(
				'type' => 'flag',
				'model' => 'Index',
				'field' => 'swap_only',
				'flag' => 's',
				'description' => 'States that the build and live index tables should simply be swapped and not rebuilt.'
			),
			array(
				'type' => 'flag',
				'model' => 'Index',
				'field' => 'build_only',
				'flag' => 'b',
				'description' => 'States that the indexes should be rebuilt but not swapped into production.'
			)
		);
		
		/**
		 * Main entry point for the shell.
		 */
		function main()
		{
			//grab our arguments
			$data = $this->ReportParameters->parse($this->parameters);
			
			Cache::clear();
			$models = array();
			$field = null;
			
			//build all models if that's what they want
			if ($data['Index']['all_models'])
			{
				$models = Configure::listObjects('model');
			}
			else if (isset($data['Index']['model']))
			{
				//otherwise, build a specific model's indexes or single index
				$models[] = $data['Index']['model'];
				$field = ifset($data['Index']['field'], null);
			}
			else
			{
				//if the user didn't specify any flags, we need to show them the usage
				$this->ReportParameters->usage($this->parameters);
				$this->_stop();
			}
			
			$this->Logging->startTimer();
			$success = true;

			//if we're not just swapping we need to build the indexes
			if (!$data['Index']['swap_only'])
			{
				//go through all the models we're going to index
				foreach ($models as $modelName)
				{
					$model = ClassRegistry::init($modelName);
					
					//make sure the model is indexable
					if ($model->Behaviors->enabled('Indexable'))
					{
						//rebuild the index						
						$this->Logging->write("Rebuilding {$modelName}" . ($field != null ? ".{$field}" : '') . '...');
						
						if (!$model->rebuildIndexes($field, false))
						{
							$success = false;
							$this->Logging->write('FAILED.');
						}
						else
						{
							$this->Logging->write('Done.');
						}
					}
				}
			}
						
			//if the build is ok, swap them all in
			if ($success || $data['Index']['swap_only'])
			{
				if (!$data['Index']['build_only'])
				{
					foreach ($models as $modelName)
					{
						$model = ClassRegistry::init($modelName);
						
						//make sure the model is indexable
						if ($model->Behaviors->enabled('Indexable'))
						{
							$this->Logging->write("Swapping indexes for {$modelName}" . ($field != null ? ".{$field}" : '') . '...');
							$model->swapBuildTables($field);
							$this->Logging->write('Done.');
						}
					}
				}
			}
			else
			{
				$this->Logging->write('Indexes will not be swapped into production due to errors.');
			}
			
			$this->Logging->writeElapsedTime();
			$this->Logging->write('Done');
		}
	}
?>