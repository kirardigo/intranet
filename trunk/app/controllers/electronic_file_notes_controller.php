<?php
	class ElectronicFileNotesController extends AppController
	{
		var $uses = array('ElectronicFileNote', 'Lookup', 'Department');
		
		/**
		 * Container action for EFN modules.
		 */
		function efn()
		{
			$this->pageTitle = 'EFN Tickle Management';
		}
		
		/**
		 * Show the EFN tickles.
		 * @param bool $isUpdate Determines whether the response is an update.
		 */
		function module_tickles($isUpdate = 0)
		{
			$this->helpers[] = 'ajax';
			$filterName = 'ElectronicFileNotesTicklesFilter';
			$postDataName = 'ElectronicFileNotesTicklesPost';
			$isPreFiltered = false;
			
			if (isset($this->params['named']['tcn']))
			{
				$isPreFiltered = true;
				$conditions = array('ElectronicFileNote.transaction_control_number' => $this->params['named']['tcn']);
			}
			if (isset($this->params['named']['accountNumber']) && isset($this->params['named']['invoiceNumber']))
			{
				$isPreFiltered = true;
				$conditions = array(
					'ElectronicFileNote.account_number' => $this->params['named']['accountNumber'],
					'ElectronicFileNote.invoice_number' => $this->params['named']['invoiceNumber']
				);
			}
			
			if ($isUpdate)
			{
				$isExport = 0;
					
				if (!$isPreFiltered)
				{
					if (isset($this->data['ElectronicFileNote']['is_export']))
					{
						$isExport = $this->data['ElectronicFileNote']['is_export'];
						unset($this->data['ElectronicFileNote']['is_export']);
					}
					
					if (isset($this->data))
					{
						$this->Session->write($postDataName, $this->data);
						
						$conditions = Set::filter($this->postConditions($this->data));
						
						if (isset($conditions['ElectronicFileNote.followup_date']))
						{
							$conditions['ElectronicFileNote.followup_date <='] = databaseDate($conditions['ElectronicFileNote.followup_date']);
							unset($conditions['ElectronicFileNote.followup_date']);
						}
						
						$this->Session->write($filterName, $conditions);
					}
					else if ($this->Session->check($filterName))
					{
						$conditions = $this->Session->read($filterName);
						$this->data = $this->Session->read($postDataName);
					}
					else
					{
						$this->Session->delete($filterName);
						$this->Session->delete($postDataName);
					}
				}
				
				$results = $this->ElectronicFileNote->find('all', array(
					'contain' => array(),
					'conditions' => $conditions
				));
				
				foreach ($results as $key => $row)
				{
					$results[$key]['ElectronicFileNote']['days'] = ($row['ElectronicFileNote']['followup_date'] != '') ? weekdayDiff($row['ElectronicFileNote']['followup_date'], date('Y-m-d')) : '';
				}
				
				$this->set(compact('results'));
				
				if ($isExport)
				{
					$this->render('/electronic_file_notes/csv_tickles');
				}
			}
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$departments = $this->Department->getCodeList();
			$this->set(compact('profitCenters', 'departments', 'isUpdate', 'isPreFiltered'));
		}
	}
?>