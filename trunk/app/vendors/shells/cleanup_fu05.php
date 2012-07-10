<?php
	Configure::write('Cache.disable', true);
	
	class CleanupFu05Shell extends Shell 
	{
		var $tasks = array('ReportParameters', 'Logging');
		
		var $parameters = array();
		
		/**
		 * The program entry point.
		 */
		function main()
		{
			$data = $this->ReportParameters->parse($this->parameters);
			
			$this->Logging->write("Starting cleanup");
			
			set_time_limit(0);
			
			$inventory = ClassRegistry::init('Inventory');
			
			$id = 0;
			$count = 0;
			$deleteArray = array('', 'DELETE');
			
			$currentStart = $inventory->find('first', array(
				'contain' => array(),
				'fields' => array('id', 'inventory_number'),
				'conditions' => array(
					'id >' => $id,
					'inventory_number' => $deleteArray
				)
			));
			
			if ($currentStart !== false)
			{
				$currentEnd = $inventory->find('first', array(
					'contain' => array(),
					'fields' => array('id', 'inventory_number'),
					'conditions' => array(
						'id >' => $currentStart['Inventory']['id'],
						'inventory_number <>' => $deleteArray
					)
				));
			}
			else
			{
				$currentEnd = false;
			}
			
			while ($currentEnd !== false)
			{
				$start = $currentStart['Inventory']['id'];
				$end = $currentEnd['Inventory']['id'] - 1;
				$id = $currentEnd['Inventory']['id'];
				
				if ($inventory->deleteAll(array('id >=' => $start, 'id <=' => $end)))
				{
					$this->Logging->write("Deleting from {$start} to {$end}");
					$count += ($end - $start);
				}
				
				$currentStart = $inventory->find('first', array(
					'contain' => array(),
					'fields' => array('id', 'inventory_number'),
					'conditions' => array(
						'id >' => $id,
						'inventory_number' => $deleteArray
					)
				));
			
				if ($currentStart !== false)
				{
					$currentEnd = $inventory->find('first', array(
						'contain' => array(),
						'fields' => array('id', 'inventory_number'),
						'conditions' => array(
							'id >' => $currentStart['Inventory']['id'],
							'inventory_number <>' => $deleteArray
						)
					));
				}
				else
				{
					$currentEnd = false;
				}
			}
			
			$this->Logging->write("{$count} records deleted.");
			$this->Logging->writeElapsedTime();
		}
		
		/**
		 * Override the default welcome screen.
		 */
		function startup()
		{
			$this->Logging->startTimer();
		}
	}
?>