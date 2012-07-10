<?php
	class Image extends AppModel
	{
		var $useDbConfig = 'docpop';
		var $useTable = 'Image';
		var $primaryKey = 'ImageID'; 
		
		var $belongsTo = array('Document' => array('foreignKey' => 'DocID'));
		
		/** This will be used to cache the ID of the binary image format in DocPop */
		var $_binaryFormatID = null;
		
		/**
		 * Appends a file (non-image) to a DocPop document.
		 * @param int $documentID The ID of the document to add the image to.
		 * @param int $filename The full path to the file to append.
		 * @return bool True if successful, false otherwise.
		 */
		function appendBinaryFileToDocument($documentID, $filename)
		{
			if ($this->_binaryFormatID == null)
			{
				$this->_binaryFormatID = ClassRegistry::init('ImageFormat')->binaryImageFormatID();
			}
			
			$this->create();
			
			return !!$this->save(array('Image' => array(
				'DocID' => $documentID,
				'PageNumber' => $this->field('((ifnull(max(PageNumber), 0) + 1))', array('DocID' => $documentID)),
				'Image' => file_get_contents($filename),
				'FormatID' => $this->_binaryFormatID,
				'FileName' => basename($filename),
				'XResolution' => 0,
				'YResolution' => 0,
				'ErrorText' => '',
				'State' => '',
				'Thumbnail' => '',
				'DeletedStatus' => null,
				'WorkStation' => 'eMRS',
				'CreatedBy' => 'MILLERS\\' . User::current(),
				'CreatedAt' => date('Y-m-d'),
				'LastSavedBy' => 'MILLERS\\' . User::current(),
				'LastSavedAt' => date('Y-m-d'),
				'LastSavedMilli' => 0,
				'LastSavedSession' => mt_rand(1000000, 9999999) . mt_rand(1000000, 9999999)
			)));
		}
	}
?>