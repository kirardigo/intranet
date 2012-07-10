<?php
	class ProcessFilesController extends AppController
	{
		var $uses = array('ProcessFile', 'Process');
		
		/**
		 * Loads the file list for a given process.
		 * @param int $processID The ID of the process.
		 */
		function ajax_fileList($processID)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->Process->find('first', array(
				'contain' => array(
					'ProcessFile' => array(
						'fields' => array(
							'id',
							'name',
							'filename',
							'mime_type',
							'OCTET_LENGTH(file_content) as size'
						)
					)
				),
				'fields' => array('name'),
				'conditions' => array('id' => $processID)
			));
			
			$this->helpers[] = 'number';
		}
		
		/**
		 * Display a process file.
		 * @param int $id The ID of the process file.
		 */
		function get($id)
		{
			$this->autoLayout = false;
			
			$this->data = $this->ProcessFile->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
		}
	}
?>