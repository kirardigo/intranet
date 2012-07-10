<?php
	class DocumentsController extends AppController
	{
		var $uses = array('Document', 'Image', 'Setting', 'IndexQueueType', 'DocumentType');
		
		/**
		 * Module to display DocPop documents for a given account.
		 * @param string $accountNumber The account number to show documents for.
		 */
		function module_forCustomer($accountNumber)
		{
			//grab the account number field
			$field = $this->Setting->get('docpop_account_number_field');
			
			// Check for data
			if (isset($this->params['named']['checkForData']))
			{
				Configure::write('debug', 0);
				$this->autoRender = false;
				
				$count = $this->Document->find('count', array(
					'conditions' => array(
						"Document.{$field}" => $accountNumber,
						'Document.DeletedStatus' => null,
						'Document.QueueID <>' => 0
					),
					'contain' => array('Queue')
				));
				
				return ($count > 0);
			}
			
			//find all documents on the account
			$documents = $this->Document->find('all', array(
				'conditions' => array(
					"Document.{$field}" => $accountNumber,
					'Document.DeletedStatus' => null,
					'Document.QueueID <>' => 0
				),
				'contain' => array('Queue')
			));
			
			$this->set('documents', $documents);
		}
		
		/**
		 * Module to display a page of thumbnails of all pages for a given account.
		 * @param string $documentID The ID of the document to display thumbnails for.
		 */
		function module_thumbnails($documentID)
		{
			$images = $this->Image->find('all', array(
				'fields' => array('ImageID', 'PageNumber'),
				'conditions' => array(
					'DocID' => $documentID,
					'DeletedStatus' => null
				),
				'order' => array('PageNumber'),
				'contain' => array()
			));
			
			$this->set('images', $images);
		}
		
		/**
		 * Module to display index information for a given account.
		 * @param string $documentID The ID of the document to display index information for.
		 */
		function module_index($documentID)
		{
			//grab the doc
			$document = $this->Document->find('first', array(
				'conditions' => array(
					'DocID' => $documentID
				),
				'contain' => array('Queue')
			));
			
			if ($document === false)
			{
				die();
			}

			//grab the index information for the doc's queue type
			$indexes = $this->IndexQueueType->find('all', array(
				'fields' => array('IndexQueueType.FieldID', 'IndexQueueType.DisplayField', 'IndexField.*'),
				'conditions' => array('IndexQueueType.QueueTypeID' => $document['Queue']['QueueTypeID']),
				'contain' => array('IndexField'),
				'order' => 'IndexQueueType.FieldOrder'
			));
			
			$values = array();
			
			//go through each and load up the values from the proper fields in the document
			foreach ($indexes as $index)
			{
				$value = $document['Document'][$index['IndexField']['FieldName']];
				
				//if the index is marked as a display field, that actually means to go look up
				//the description from the document types table (go figure)
				if ($index['IndexQueueType']['DisplayField'])
				{
					$value = $this->DocumentType->field('Description', array('FormCode' => $value));
				}
				else
				{	
					//format the value when necessary
					switch (strtolower($index['IndexField']['DataType']))
					{
						case 'numeric':
							$value = number_format($value, $index['IndexField']['DecimalPlaces']);
							break;
						case 'date':
							$value = formatDate($value);
							break;
					}
				}
				
				$values[$index['IndexField']['FieldLabel']] = $value;
			}

			$this->set(compact('values'));
		}
	}
?>