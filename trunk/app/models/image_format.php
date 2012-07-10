<?php
	class ImageFormat extends AppModel
	{
		var $useDbConfig = 'docpop';
		var $useTable = 'ImageFormats';
		var $primaryKey = 'FormatID';
		
		/**
		 * Gets the ID of the image format to use for binary files.
		 * @return int The ID of the image format to use for binary (non-image) files.
		 */
		function binaryImageFormatID()
		{
			return $this->field('FormatID', array('FormatName' => 'NonImage'));
		} 
	}
?>