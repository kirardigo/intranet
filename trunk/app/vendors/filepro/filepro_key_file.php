<?php
	/**
	 * Representation of a filePro key file. Confusingly enough, a key file is the file that contains the 
	 * actual record data.
	 */
	class FileproKeyFile
	{
		/** The key file's index file. */
		var $keyFile;
		
		/** The key file's map file. */
		var $mapFile;
		
		/** Array containing the key file's map information. */
		var $map = array();
		
		/** The header length on each record in the key file. */
		var $keyFileHeaderLength = 20;
		
		/**
		 * Constructor.
		 * @param string $path The full path to the key file.
		 */
		function __construct($path)
		{
			$this->keyFile = $path;
			$this->mapFile = dirname($path) . DS . 'map';
			
			$this->_loadSchema();
		}
		
		/**
		 * Internal method used to load the schema (a.k.a. map) information about the key file.
		 */
		function _loadSchema()
		{
			//grab the map file and explode it into its lines
			$lines = explode("\n", file_get_contents($this->mapFile));
			
			//the header is the first line in the file, with fields delimited by ':' characters
			$header = explode(':', array_shift($lines));
			
			$this->map['recordLength'] = $header[1];
			$this->map['fields'] = array();
			
			//now go through the rest of the lines in the map, which describe the fields
			//in each record
			foreach ($lines as $line)
			{
				if (trim($line) != '')
				{
					$parts = explode(':', $line);
					
					$this->map['fields'][trim($parts[0])] = array(
						'length' => trim($parts[1]),
						'type' => trim($parts[2])
					);
				}
			}
		}
		
		/**
		 * Reads the specified record from the key file.
		 * @param numeric $record The record number of the record to read.
		 * @return array A hash table containing two keys:
		 * 		header - contains a hash table containing the row's header information
		 * 		data - contains a hash table of all of the row's fields, indexed by field name. If
		 * 		the row is a deleted row, this array will be empty.
		 */
		function read($record)
		{
			//open to the file, seek to the record, and pull the whole row
			$f = fopen($this->keyFile, 'rb');
			fseek($f, $record * ($this->keyFileHeaderLength + $this->map['recordLength']));
			$buffer = fread($f, $this->keyFileHeaderLength + $this->map['recordLength']);
			fclose($f);
			
			//read the header
			$header = array('id' => $record, 'isDeleted' => !ByteConverter::bin2Number(substr($buffer, 0, 1)));
			$data = array();
			
			if ($header['isDeleted'])
			{
				$header['forwardFreechain'] = ByteConverter::bin2Number(substr($buffer, 2, 4), true);
				$header['backwardFreechain'] = ByteConverter::bin2Number(substr($buffer, 6, 4), true);
			}	
			else
			{
				$header['created'] = $this->convertFileproDate(ByteConverter::bin2Number(substr($buffer, 2, 2), true));
				$header['createdBy'] = ByteConverter::bin2Number(substr($buffer, 4, 2), true);
				$header['modified'] = $this->convertFileproDate(ByteConverter::bin2Number(substr($buffer, 6, 2), true));
				$header['modifiedBy'] = ByteConverter::bin2Number(substr($buffer, 8, 2), true);
				$header['batchModified'] = $this->convertFileproDate(ByteConverter::bin2Number(substr($buffer, 10, 2), true));
				
				$offset = $this->keyFileHeaderLength;
			
				//on an active record, pull all of the field values out
				foreach ($this->map['fields'] as $name => $schema)
				{
					$data[$name] = ByteConverter::bin2String(substr($buffer, $offset, $schema['length']));
					$offset += $schema['length'];
				}
			}
			
			return compact('header', 'data');
		}
		
		/**
		 * Reads all of the records with the given record numbers.
		 * @param array $records An array of record numbers to read.
		 */
		function readAll($records)
		{
			$results = array();
			
			foreach ($records as $record)
			{
				$results[] = $this->read($record);
			}
			
			return $results;
		}
		
		/**
		 * Converts a filePro date to a unix timestamp.
		 * @param numeric $date The filePro date to convert.
		 * @return numeric The unix timestamp equivalent of the filepro date.
		 */
		function convertFileproDate($date)
		{
			//filePro stores dates as the number of days since 1/1/1983
			return strtotime('January 1 1983 00:00:00') + ($date * 24 * 60 * 60);
		}
	}
?>