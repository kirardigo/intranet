<?php
	class ProcessesController extends AppController
	{
		var $uses = array('Process', 'ProcessFile');
		
		var $pageTitle = 'Background Tasks';
		
		/**
		 * Display the background process manager screen.
		 */
		function manager($isUpdate = 0)
		{
			$filterName = 'ProcessesManagerFilter';
			
			if (isset($this->params['named']['reset']))
			{
				$this->Session->delete($filterName);
				$this->redirect('/processes/manager');
			}
			
			if ($isUpdate)
			{
				$this->layout = 'ajax';
				
				if (isset($this->data))
				{
					$this->Session->write($filterName, $this->data);
				}
			}
			
			if ($this->Session->check($filterName))
			{
				$this->data = $this->Session->read($filterName);
			}
			
			if (!isset($this->data))
			{
				$this->data['Process']['created_by'] = $this->Session->read('user');
			}
			
			$conditions = Set::filter($this->postConditions($this->data));
			
			$this->paginate = array(
				'contain' => array(
					'ProcessFile' => array(
						'fields' => array(
							'id'
						)
					)
				),
				'conditions' => $conditions,
				'order' => 'created desc'
			);
			
			$results = $this->paginate('Process');
			
			$this->helpers[] = 'ajax';
			$this->set(compact('results', 'isUpdate', 'showAll'));
		}
		
		/**
		 * Delete a process and the attached files.
		 * @param int $id The ID of the process to remove.
		 */
		function ajax_removeProcess($id)
		{
			$this->Process->removeProcess($id);
		}
		
		/**
		 * Interrupt a process.
		 * @param int $id The ID of the process.
		 */
		function ajax_interruptProcess($id)
		{
			$this->Process->interruptProcess($id);
		}
		
		/**
		 * Loads the output for a given process.
		 * @param int $id The ID of the process.
		 */
		function ajax_output($id)
		{
			$output = '<div style="padding: 10px;"><a href="/processes/output/' . $id . '" target="_blank">Printable Version</a><br/><br/><pre>';
			$output .= $this->Process->field('output', array('id' => $id));
			$output .= '</pre></div>';
			
			$this->set('output', $output);
		}
		
		/**
		 * Printable version of the process output.
		 * @param int $id The ID of the process.
		 */
		function output($id)
		{
			$this->layout = 'print';
			
			$this->data = $this->Process->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($this->data !== false)
			{
				$this->pageTitle = "{$this->data['Process']['name']} Output";
			}
		}
	}
?>