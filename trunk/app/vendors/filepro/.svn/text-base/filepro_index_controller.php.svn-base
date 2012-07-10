<?php	
	App::import('Set');
	
	/**
	 * B+ tree controller to handle filePro index files.
	 */
	class FileproIndexController extends BPlusTreeController
	{	
		/** The full path to the index file to control. */
		var $indexFile;
		
		/** Contains the filePro index header information. */
		var $header = array();
		
		/** The number of bytes in the header. */
		var $headerLength = 140;
		
		/** These are the ordinals that are assigned to fields not in the data portion of a record, but in the header */
		var $headerOrdinals = array(
			3001 => 'id',
			3003 => 'created',
			3004 => 'modified',
			3005 => 'created_by',
			3006 => 'modified_by'
		);
		
		/** Once an index has been loaded, this will tell you whether or not it is actually supported for searching. You should not attempt
		 *  to actually use one of the indexes if this is false.
		 */
		var $isSupported = true;
		
		/** These are the operators that are supported when searching the index. */
		var $supportedIndexOperators = '/^(=|>|>=|<|<=|like)$/i';
		
		/**
		 * Constructor.
		 * @param string $indexFile The full path to the filePro index file to control.
		 * @param array $fields An array of field definitions in the model. See the describe() method in the filepro driver
		 * for more info.
		 */
		function __construct($indexFile, $fields)
		{
			$this->indexFile = $indexFile;
			$this->loadHeader($fields);
		}
		
		/**
		 * Loads the header of the index. Can be called multiple times to refresh the index header information.
		 * @param array $fields An array of field definitions in the model. See the describe() method in the filepro driver
		 * for more info.
		 */
		function loadHeader($fields)
		{
			//open up the file real quick to read in the header
			$f = fopen($this->indexFile, 'rb');
			$data = fread($f, $this->headerLength);
			fclose($f);
			
			$this->header = array(
				'magic_number' => ByteConverter::bin2Hex(substr($data, 0, 2), true),
				'depth' => ByteConverter::bin2Number(substr($data, 2, 2), true),
				'root_in_block_zero' => ByteConverter::bin2Number(substr($data, 4, 2), true),
				'keys_per_node' => ByteConverter::bin2Number(substr($data, 6, 2), true),
				'node_size' => ByteConverter::bin2Number(substr($data, 8, 4), true),
				'root_block' => ByteConverter::bin2Number(substr($data, 12, 4), true),
				'record_count' => ByteConverter::bin2Number(substr($data, 16, 4), true),
				'sort_key_length' => ByteConverter::bin2Number(substr($data, 20, 2), true),
				'record_number_length_in_key' => ByteConverter::bin2Number(substr($data, 22, 2), true),
				'sort_info' => array(),
				'freechain_block' => ByteConverter::bin2Number(substr($data, 88, 4), true),
				'comment' => trim(ByteConverter::bin2String(substr($data, 92, 48)))
			);
			
			//if the root is in block zero, they set the root block number to -[header length] (go figure)
			if ($this->header['root_block'] == -$this->headerLength)
			{
				$this->header['root_block'] = 0;
			}
			
			$offset = 24;
			
			//make easy access to the field definitions by ordinal position
			$ordinals = array_combine(Set::extract($fields, '{s}.ordinal'), array_keys($fields));
			
			//load all of the sort info from the header so we know what fields are indexed
			for ($i = 0; $i < 8; $i++)
			{
				//the header contains 8 sets of sort info, even if it's not used - in which case
				//the first two bytes (field number) will be zero
				if (ByteConverter::bin2Number(substr($data, $offset, 2), true) != 0)
				{
					$info = array(
						'field_number' => ByteConverter::bin2Number(substr($data, $offset, 2), true),
						'field_instance' => ByteConverter::bin2Number(substr($data, $offset + 2, 1), true),
						'zero' => ByteConverter::bin2Number(substr($data, $offset + 3, 1), true),
						'field_length' => ByteConverter::bin2Number(substr($data, $offset + 4, 2), true),
						'descending' => ByteConverter::bin2Number(substr($data, $offset + 6, 1), true),
						'filepro_type' => ByteConverter::bin2Number(substr($data, $offset + 7, 1), true),
						'type' => null,
						'comparer' => null
					);
					
					//figure out the field name - it's either one of the special header fields, or a field from
					//the map file
					if (array_key_exists($info['field_number'], $this->headerOrdinals))
					{
						$info['field_name'] = $this->headerOrdinals[$info['field_number']];
					}
					else if (array_key_exists($info['field_number'], $ordinals))
					{
						$info['field_name'] = $ordinals[$info['field_number']];
					}
					else
					{
						$this->isSupported = false;
						//throw new Exception('Unsupported index on field (most likely an index on an an associated field)! Ordinal: ' . ($info['field_number']));
					}
						
					if ($this->isSupported)
					{
						//grab the type we use for comparison purposes (the ID field is a pseudo field that's not in
						//the schema - that's why we have the need for the explicit case)
						$info['type'] = $info['field_name'] == 'id' ? 'int' : $fields[$info['field_name']]['type'];
						
						//determine what comparer to use on the field based on the way it's stored in the index
						$info['comparer'] = $info['descending'] ? new DescendingComparer() : new AscendingComparer();
					}
					
					$this->header['sort_info'][] = $info;
				}
				
				$offset += 8;
			}
			
			$this->keysPerNode = $this->header['keys_per_node'];
		}
		
		/**
		 * Gets the physical offset of the root node.
		 * @return int The physical offset.
		 */
		function getRootAddress()
		{
			if ($this->header['root_in_block_zero'])
			{
				return $this->headerLength;
			}
			else
			{
				return $this->header['root_block'] * $this->header['node_size'];	
			}
		}
		
		/**
		 * Abstract method implementation that has been modified to support combined indexes.
		 * @param mixed $first The entire first key to compare.
		 * @param mixed $second The entire second key to compare.
		 * @param numeric $field The number of the field that we're comparing. Since filePro stores
		 * all of the fields of a combined index into one big concatenated key for an element, we have
		 * to know what part of the key to look at when we're comparing.
		 * @return see BPlusTreeController::compare.
		 */
		function compare($first, $second, $field = 1)
		{
			$offset = 0;
			
			//figure out the starting character to compare in the key
			for ($i = 0; $i < $field - 1; $i++)
			{
				$offset += $this->header['sort_info'][$i]['field_length'];
			}
			
			//make sure the first key is at least as long as what we need to compare (pad with spaces)
			if ($offset >= strlen($first))
			{
				$first .= str_pad($first, $offset + $this->header['sort_info'][$field - 1]['field_length'] + 1);
			}
			
			//make sure the second key is at least as long as what we need to compare (pad with spaces)
			if ($offset >= strlen($second))
			{
				$second .= str_pad($second, $offset + $this->header['sort_info'][$field - 1]['field_length'] + 1);
			}
			
			//grab the piece of the key from each that we're comparing
			$firstPart = substr($first, $offset, $this->header['sort_info'][$field - 1]['field_length']);
			$secondPart = substr($second, $offset, $this->header['sort_info'][$field - 1]['field_length']);
			
			//compare the two values with the proper comparer used for the given field
			$comparison = $this->header['sort_info'][$field - 1]['comparer']->compare($firstPart, $secondPart, $this->header['sort_info'][$i]['type']);
			
			//if they aren't equal, return the comparison. However, if they are equal and it's not the last field
			//being compared, recusively return the comparison of the following field until we hit the last field
			return $comparison != 0 ? $comparison : ($field == count($this->header['sort_info']) ? $comparison : $this->compare($first, $second, $field + 1));
		}
		
		function newNode()
		{
			$node = new BPlusTreeNode($this->keysPerNode);
			
			//TODO - how to determine address? (static stuff is just to simulate it for now)
			//I think we're going to just have to insert the new node at the end of the file
			//immediately and then grab its address
			static $address = 1;
			$node->address = $address++;
				
			$this->cache[$node->address] = $node;
			
			return $node;
		}
		
		function newElement($key, $value, $link = null)
		{
			return new BPlusTreeElement($key, array($value), $link);
		}
		
		/**
		 * Utility method to read one "block" from a filePro index.
		 * @param numeric $address The address of the block to read.
		 * @return string A string of the raw binary data read from the block.
		 */
		function readBlock($address)
		{
			$f = fopen($this->indexFile, 'rb');
			fseek($f, $address);
			$data = fread($f, $this->header['node_size']);
			fclose($f);
			
			return $data;
		}
			
		function loadNode($address, $depth = 1)
		{
			//based on index header info, load a particular node from disk
			if (!$address)
			{
				return false;
			}
			
			if (!array_key_exists($address, $this->cache))
			{
				//based on the depth passed we know if we have to load an internal or leaf node.
				$isLeaf = $depth == $this->header['depth'];				
				$this->cache[$address] = $isLeaf ? $this->_loadLeafNode($address) : $this->_loadInternalNode($address);
			}
			
			return $this->cache[$address];
		}
		
		/**
		 * Method to load an internal node.
		 * @param numeric $address The address of the node to load.
		 * @return BPlusTreeNode The node that was loaded.
		 * @access private
		 */
		function _loadInternalNode($address)
		{
			$data = $this->readBlock($address);
					
			$node = new BPlusTreeNode($this->keysPerNode);
			$node->address = $address;			
			$node->parent = null; //TODO - the node doesn't store a parent - how to deal with this for adding nodes?
			
			$pointerCount = ByteConverter::bin2Number(substr($data, 0, 2), true);
			$node->less = ByteConverter::bin2Number(substr($data, 2, 4), true) * $this->header['node_size'];
			
			$offset = 6;
			
			for ($i = 0; $i < $pointerCount; $i++)
			{
				$rawKey = ByteConverter::bin2Hex(substr($data, $offset, $this->header['sort_key_length']));
				
				//if the key is 00 or FF anything, it's one of Filepro's odd keys that's not really a key.
				//I >think< the 00 is more of a "start" marker and the FF is like an EOF marker. On an
				//ascending index, they actually don't pose a problem, but on a descending one, the location
				//of the keys are actually at incorrect locations in the resulting tree, causing searches
				//to fail. We'll probably have to come back to these when it's time to write to the index.
				if (in_array(strtolower(substr($rawKey, 0, 2)), array('00', 'ff')))
				{
					continue;
				}
				
				$node->add(new BPlusTreeElement(
					ByteConverter::bin2String(substr($data, $offset, $this->header['sort_key_length'])), 
					null,
					ByteConverter::bin2Number(substr($data, $offset + $this->header['sort_key_length'], 4), true)  * $this->header['node_size']
				), $this);
				
				$offset += $this->header['sort_key_length'] + 4;
			}
			
			return $node;
		}
		
		/**
		 * Method to load a leaf node.
		 * @param numeric $address The address of the node to load.
		 * @param bool $chainForward Internally used when dealing with chained blocks.
		 * @return BPlusTreeNode The node that was loaded.
		 * @access private
		 */
		function _loadLeafNode($address, $chainForward = true)
		{
			$data = $this->readBlock($address);
			
			$node = new BPlusTreeNode($this->keysPerNode);
			$node->address = $address;
			$node->parent = null; //TODO - the node doesn't store a parent - how to deal with this for adding nodes?
			$node->previous = ByteConverter::bin2Number(substr($data, 0, 4), true) * $this->header['node_size'];
			$node->next = ByteConverter::bin2Number(substr($data, 4, 4), true) * $this->header['node_size'];
			
			$position = 12;
			$keyOffset = 0;
			$numberOfKeys = ByteConverter::bin2Number(substr($data, 10, 2), true);
			
			//go through all of the keys in the node, loading up all them into elements with their values
			for ($i = 0; $i < $numberOfKeys; $i++)
			{
				$keyOffset = ByteConverter::bin2Number(substr($data, $position, 2), true);
				$position += 2;
				$endOffset = $i == $numberOfKeys - 1 ? $this->header['node_size'] : ByteConverter::bin2Number(substr($data, $position, 2), true);
				
				//if the key is 00 or FF anything, it's one of Filepro's odd keys that's not really a key.
				//I >think< the 00 is more of a "start" marker and the FF is like an EOF marker. On an
				//ascending index, they actually don't pose a problem, but on a descending one, the location
				//of the keys are actually at incorrect locations in the resulting tree, causing searches
				//to fail. We'll probably have to come back to these when it's time to write to the index.
				$rawKey = ByteConverter::bin2Hex(substr($data, $keyOffset, $this->header['sort_key_length']));
				
				if (in_array(strtolower(substr($rawKey, 0, 2)), array('00', 'ff')))
				{
					continue;
				}
				
				$element = new BPlusTreeElement(
					ByteConverter::bin2String(substr($data, $keyOffset, $this->header['sort_key_length'])),
					array()
				);
				
				$keyOffset += $this->header['sort_key_length'];
				
				for ($start = $keyOffset; $keyOffset < $endOffset; $keyOffset += $this->header['record_number_length_in_key'])
				{					
					$element->value[] = ByteConverter::bin2Number(substr($data, $keyOffset, $this->header['record_number_length_in_key']), true);
				}
				
				$node->add($element, $this);
			}
			
			//under normal circumstances, if we have a previous node, we need
			//to check to see if that node is the end of a chain. If it is, we need
			//to walk backwards until we get to the node at the start of the chain and
			//use that address as our previous link
			if ($node->previous != 0 && $chainForward)
			{
				$previousBlock = $this->readBlock($node->previous);
				
				if (ByteConverter::bin2Number(substr($previousBlock, 8, 2), true) == 2)
				{
					//notice we don't use loadNode - that way we don't cache the node
					$previousNode = $this->_loadLeafNode($node->previous, false);
					$node->previous = $previousNode->previous;
				}
			}
			
			//see if this node continues into the next block
			$chain = ByteConverter::bin2Number(substr($data, 8, 2), true);
			
			//if this is a chain and we're chaining forward, we need to continue
			//to load the other nodes in the chain to extract their values
			if ($chain == 1 && $chainForward)
			{
				//notice we don't use loadNode - this prevents the node from being cached
				$nextInChain = $this->_loadLeafNode($node->next);
				
				//the chained node's next pointer needs to become our node's
				//next pointer (effectively cutting the chained node out of the 
				//tree since we're merging the blocks together - remember - on a chained
				//node, it's not that we've hit the key limit of the node, it's just that
				//we've had our >values< take up too much space in a single block on disk,
				//so filepro starts another node in the next block)
				$node->next = $nextInChain->next;
						
				//now append all of the values for each key from the chain over to our original node
				for ($i = 0; $i < $numberOfKeys; $i++)
				{
					$node->elements[$i]->value = array_merge($node->elements[$i]->value, $nextInChain->elements[$i]->value);
				}
			}
			else if ($chain > 0 && !$chainForward)
			{
				//if we're in the middle of a chain while we are looking backwards,
				//we need to keep going back until we hit the start of the chain. This
				//is how, via recursion, we can find the "real" previous pointer for a node
				//who is connected to the end of another chain

				//keep walking back, adjusting the previous pointer along the way
				//so when the recursion unwinds, we'll have the address of the first node in the 
				//chain
				$previousInChain = $this->_loadLeafNode($node->previous, false);
				$node->previous = $previousInChain->previous;
			}
			
			return $node;
		}
		
		function addValueToElement($element, $value)
		{
			//filePro has element values as an array of record numbers that match the key,
			//so to add a value, we just append it to the key's value array.
			if (!in_array($value, $element->value))
			{
				$element->value[] = $value;
			}
		}
		
		function increaseDepth()
		{
			$this->header['depth']++;
		}
		
		/**
		 * FilePro search implementation.
		 * @param mixed $key The key should be an array, indexed by field, of arrays, indexed by operator. The value
		 * of the operator indexed array should be yet another array with the following keys:
		 *
		 * 		field - The field definition from the filepro driver (equivalent of $model->describe() for a particular field), 
		 * 		or null for the pseudo "id" field.
		 
		 * 		condition - The condition to test
		 * 
		 * So, for example, the following "query":
		 * 		where account_number > 10 and account_number < 20 and name = 'abc'
		 * would give us an array that looks like this:
		 * array(
		 * 		'account_number' => array(
		 * 			'>' => array(
		 * 				'field' => array(...),
		 * 				'condition' => 10
		 * 			),
		 * 			'>' => array(
		 * 				'field' => array(...),
		 * 				'condition' => 20
		 * 			),
		 * 		),
		 * 		'name' => array(
		 * 			'=' => array(
		 * 				'field' => array(...),
		 * 				'condition' => 'abc'
		 * 			)
		 * 		)
		 * )
		 * See the filepro driver's describe method for what the contents of the field element would contain.
		 * 
		 * The array should contain a condition for each part of the key to search. You cannot search on 
		 * latter parts of the key without having a value for a previous part.
		 * @return mixed An array of record numbers that matched the key, or false if the index could not
		 * be utilized.
		 */
		function search($key)
		{
			if (!$this->isSupported)
			{
				throw new Exception('This index contains features that are not supported! Unable to search.');
			}
			
			$start = microtime(true);
			$fields = array();
			
			//use as much of the index we can on the passed in conditions and get them
			//in the proper search order
			foreach ($this->header['sort_info'] as $info)
			{
				if (!array_key_exists($info['field_name'], $key))
				{
					break;
				}
				
				$fields[] = $key[$info['field_name']];
			}
			
			//if we are only able to check against a subset of the specified conditions, we can't guarantee
			//correct results
			if (count($fields) < count($key))
			{
				return false;
			}
			
			//load the root node if necessary
			if ($this->root == null)
			{
				$this->root = $this->loadNode($this->getRootAddress());
			}
			
			//use our internal recursive search method to find the matching records
			$driver = ConnectionManager::getDataSource('filepro');
			$matches = $this->_searchSingle($this->root, $driver, $fields);
			
			/* TODO - would fetching from disk be faster if we took the time to sort the results, or would the sort actually be more intensive?
			if ($matches !== false)
			{
				sort($matches, SORT_NUMERIC);
			}
			*/
			
			//spit out an elapsed time (we'll turn off later)
			$elapsed = microtime(true) - $start;
			//pr('Time: ' . number_format($elapsed, 4) . ' seconds');
			
			return $matches;
		}
		
		/**
		 * Internally used to search through the filePro index for the specified key. Currently
		 * only searches the first field of the index, and only the first operator in the key.
		 * @param BPlusTreeNode $node The current node being searched.
		 * @param object An instance of the filepro driver.
		 * @param array $key See $this->search() for details. The only difference is that the
		 * array will not be indexed by field, but will be in the same order as the parts of this index.
		 * @param numeric $depth The current depth at which the search is occuring.
		 * @return mixed An array of record numbers that matched the key, or false if the index could not
		 * be utilized.
		 * @access private
		 */
		function _searchSingle($node, $driver, $key, $depth = 1)
		{
			$records = array();
			$term = '';
			
			$comparisons = array();
			
			//this is where, for now, to search only the first field, we hard code
			//grabbing the first operator of the first field
			$operator = array_shift(array_keys($key[0]));
			$schema = $key[0][$operator]['field'];
			
			//this is for the pseudo "id" field
			if ($schema == null)
			{
				$schema = array('type' => 'int', 'fileproType' => '.0');
			}
			
			$value = $key[0][$operator]['condition'];
			$operator = strtolower($operator);
			
			//handle the "IN" operator
			if (is_array($value) && $operator == '=')
			{
				foreach ($value as $part)
				{
					$key[0][$operator]['condition'] = $part;
					$records = array_unique(array_merge($records, $this->_searchSingle($node, $driver, $key, $depth)));
				}
				
				return $records;
			}
			
			//In filepro, the index can actually be on only part of a field. This should really only ever
			//happen on string fields (and if it happens on any other type they have bigger problems and I'm not
			//gonna worry about it). So if the value being searched on is longer than the indexed value's field length, 
			//we'll truncate it to the length that can be searched.
			//
			//Now, this has an implication on the CakePHP filepro driver that we wrote. It means that even after
			//finding records via an index, we must still re-examine the condition that was evaluated by the index
			//because we can't be guaranteed that we don't have some extra records that don't actually match the
			//user's criteria. So there's some coupling due to this, but the driver and this index controller are
			//pretty specialized for each other anyways, so I'm not too worried.
			if ($schema['type'] == 'string' && strlen($value) > $this->header['sort_info'][0]['field_length'])
			{
				//if the operator is anything other than equality, we can't search the index because it would 
				//be possible to actually miss records that would normally match
				if ($operator != '=')
				{
					return false;
				}
				
				$value = substr($value, 0, $this->header['sort_info'][0]['field_length']);
			}
			
			//if it's a like operator, ditch the % at the end (we don't support any other type of like)
			if ($operator == 'like')
			{
				$value = preg_replace('/%$/', '', $value);
			}
			
			//if the field is an ALLUP or UP field, we'll automatically convert the search term to all uppercase
			if (strtoupper($schema['fileproType']) == 'ALLUP' || strtoupper($schema['fileproType']) == 'UP')
			{
				$value = strtoupper($value);
			}
			
			//we're going to keep track of visited child nodes so that we can make sure we don't visit the same node twice
			$visited = array();
			
			//go through all the elements in the node
			for ($i = 0; $i < count($node->elements); $i++)
			{
				//grab the part of the element key that we need to compare and massage it for 
				//comparison (we cheat and re-use some driver functionality for this)
				$against = $driver->_comparableValue($driver->_phpValue($this->_extractKeyPart($node->elements[$i]->key, 0), $schema['type']), $schema['type']);
								
				//for the like operator, we chop off everything beyond the length of the value being searched. That way, we can treat LIKE and = operators the same.
				if ($operator == 'like')
				{
					$against = substr($against, 0, strlen($value));
				}
				
				//compare the value to the key
				$comparison = $this->header['sort_info'][0]['comparer']->compare($value, $against, $this->header['sort_info'][0]['type']);
				
//pr(array($node->address, $value, $against, $comparison, $depth, $node->isLeaf() ? 'Leaf' : 'Internal', $this->header['sort_info'][0]['type']));

				switch ($operator)
				{
					case '<=':
						if ($comparison < 0)
						{
							if (!$node->isLeaf())
							{
								//if we're less than the current key, we need to look back at the previous subtree to look
								//for anything we might be greater than. If it's the first element, that would be the 
								//less pointer of the node. Otherwise it'd be the link pointer of the previous element.
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									//anything after the first element we may have already visited and searched the previous one so we only visit if necessary
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
							}
							
							//short circuit here because if we're less than the current value, everything from this
							//point on will be greater
							break 2;
						}
						else if ($comparison == 0)
						{
							if ($node->isLeaf())
							{
								//on a leaf node, if we're equal, take everything from the element
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
							else
							{
								//if we're equal, grab the entire previous subtree since we're >= to all of it. If it's 
								//the first element, that would be the less pointer of the node. Otherwise it'd be the 
								//link pointer of the previous element
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									//only get them all if we haven't visited it yet
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
								
								//grab whatever is in this element's link that we're equal to
								if (!in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
							
							//we can't short circuit here, because we may still have more entries that we're equal to on
							//combined indexes
						}
						else 
						{
							if (!$node->isLeaf())
							{
								//if we're greater than the current key, the entire previous subtree will be less than our value,
								//so grab the whole thing. If it's the first element, that would be the less pointer of the
								//node. Otherwise it'd be the link pointer of the previous element.
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
								
								//see what else we might be greater than or equal to in this element's link
							 	if (!in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
							else
							{
								//if we're on the leaf, gather the records from this element since we're greater than it
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
						}
						
						break;
					case '<':		
						if ($comparison < 0)
						{
							if (!$node->isLeaf())
							{
								//if we're less than the current key, we need to look back at the previous subtree to look
								//for anything we might be greater than. If it's the first element, that would be the 
								//less pointer of the node. Otherwise it'd be the link pointer of the previous element.
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
							}
							
							//short circuit here because if we're less than the current value, everything from this
							//point on will be greater
							break 2;
						}
						else if ($comparison == 0)
						{
							if ($node->isLeaf())
							{
								//nothing to do
							}
							else
							{								
								//if we're equal, search the previous subtree since we're at least greater than some of it (we can't
								//guarantee we're greater than all of it due to combined indexes). If it's 
								//the first element, that would be the less pointer of the node. Otherwise it'd be the 
								//link pointer of the previous element
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
							}
							
							//if we're equal to the current value, nothing from this point on will be less than us
							break 2;
						}
						else 
						{
							if (!$node->isLeaf())
							{
								//if we're greater than the current key, the entire previous subtree will be less than our value,
								//so grab the whole thing. If it's the first element, that would be the less pointer of the
								//node. Otherwise it'd be the link pointer of the previous element.
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
								
								//now follow this element's link to get whatever else is less than us
								if (!in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
							else
							{
								//if we're on the leaf, gather the records from this element since we're greater than it
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
						}
						
						break;
					case '=':
					case 'like':
						if ($comparison < 0)
						{
							if (!$node->isLeaf())
							{
								if ($i == 0)
								{
									//if the term is less than the key on an internal node and it's the first element
									//in the node, follow the less pointer
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									//if the term is greater than the key on an internal node and it's after the first
									//element, follow the previous element's child pointer to see if we're equal to anything there (as long as we haven't already visited the node)
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
							}
							
							//short circuit here because if we're less than the current value, everything from this
							//point on will be greater
							break 2;
						}
						else if ($comparison == 0)
						{
							if ($node->isLeaf())
							{
								//if we're equal on the leaf just grab the records
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
							else
							{
								//if we're equal on an internal node, search its link
								$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
								$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
								$visited[] = $node->elements[$i]->link;
								
								if ($i == 0)
								{
									//if we're equal on the first element, we still need to check the less pointer for 
									//possible matches due to combined indexes and the LIKE operator (TODO - could optimize this out maybe by
									//checking the number of fields in the index and if the operator isn't LIKE)
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									//if we're on anything but the first element, we need to do the same thing except with the previous element's
									//link pointer
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
							}
							
							//we can't short circuit here, because we may still have more entries that we're equal to on
							//combined indexes or for the LIKE operator
						}
						else if (!$node->isLeaf() && ($i == count($node->elements) - 1))
						{
							//if we're on the last element of an internal node and our term is still greater than the
							//key, go ahead and follow the element's child pointer, since that element's key is the lowest
							//key in its children. That means there's still the possibility of finding a match in the children.
							$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
							$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
							$visited[] = $node->elements[$i]->link;
						}	
											
						break;
					case '>':
						if ($comparison < 0)
						{
							if (!$node->isLeaf())
							{
								//if we're less than the current key, we need to look back at the previous subtree to look
								//for anything that is greater than us. For the first element, that's the less pointer, otherwise
								//it's the previous element's link pointer
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
																
								//since we're less than the current key, everything from this point on is greater than us,
								//so just get all subtrees from all elements from this point on
								for ($j = $i; $j < count($node->elements); $j++)
								{
									$child = $this->loadNode($node->elements[$j]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$j]->link;
								}
								
								//and now we can short circuit since we pulled everything
								break 2;
							}
							else
							{
								//if we're on the leaf, gather the records from this element since we're less than it
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
						}
						else if ($comparison == 0)
						{
							if ($node->isLeaf())
							{
								//nothing to do
							}
							else
							{
								$skip = false;
								
								//if we're equal, we need to examine the current subtree to see if there's anything greater than 
								//us, but we can omit the test if we have a next element and it's value comparison is still 
								//equal to us (only happens on combined indexes)
								if ($i != count($node->elements) - 1)
								{
									$nextAgainst = $driver->_comparableValue($driver->_phpValue($this->_extractKeyPart($node->elements[$i + 1]->key, 0), $schema['type']), $schema['type']);
									$nextComparison = $this->header['sort_info'][0]['comparer']->compare($value, $nextAgainst, $this->header['sort_info'][0]['type']);
									
									if ($nextComparison == 0)
									{
										$skip = true;
									}
								}
								
								if (!$skip && !in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
						}
						else 
						{
							if (!$node->isLeaf())
							{
								$skip = false;
								
								//if we're greater than the current key, examine the current subtree to see if there's anything
								//in there that's greater than us. We can skip this test however if we have a next element and we're
								//still greater than or equal to it as well
								if ($i != count($node->elements) - 1)
								{
									$nextAgainst = $driver->_comparableValue($driver->_phpValue($this->_extractKeyPart($node->elements[$i + 1]->key, 0), $schema['type']), $schema['type']);
									$nextComparison = $this->header['sort_info'][0]['comparer']->compare($value, $nextAgainst, $this->header['sort_info'][0]['type']);
									
									if ($nextComparison >= 0)
									{
										$skip = true;
									}
								}
								
								if (!$skip && !in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
							else
							{
								//nothing to do
							}
						}
						
						break;
					case '>=':
						if ($comparison < 0)
						{
							if (!$node->isLeaf())
							{
								//if we're less than the current key, we need to look back at the previous subtree to look
								//for anything that is >= than us. On the first element, that's the element's less pointer. Otherwise
								//it's the previous element's link pointer
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
								
								//since we're less than the current key, everything from this point on is greater than us,
								//so just get all subtrees from all elements past this
								for ($j = $i; $j < count($node->elements); $j++)
								{
									$child = $this->loadNode($node->elements[$j]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$j]->link;
								}
								
								//and now we can short circuit since we pulled everything
								break 2;
							}
							else
							{
								//if we're on the leaf, gather the records from this element since we're less than it
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
						}
						else if ($comparison == 0)
						{
							if ($node->isLeaf())
							{
								//if we're on the leaf, gather the records from this element since we're equal
								$records = array_unique(array_merge($records, $node->elements[$i]->value));
							}
							else
							{
								//if we're equal, we need to look back at the previous subtree to look
								//for anything that is = to us. On the first element, that's the element's less pointer. 
								//Otherwise it's the previous element's link pointer. (this scenario is only necessary because of combined indexes)
								if ($i == 0)
								{
									$child = $this->loadNode($node->less, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->less;
								}
								else if (!in_array($node->elements[$i - 1]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i - 1]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i - 1]->link;
								}
								
								//if we're equal, everything from here on is >= to us, so just grab it all
								for ($j = $i; $j < count($node->elements); $j++)
								{
									$child = $this->loadNode($node->elements[$j]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_fetchAll($child, $depth + 1)));
									$visited[] = $node->elements[$j]->link;
								}
								
								//and now we can short circuit since we pulled everything
								break 2;
							}
						}
						else 
						{
							if (!$node->isLeaf())
							{
								$skip = false;
								
								//if we're greater than the current key, examine the current subtree to see if there's anything
								//in there that's >= to us. We can skip this test however if we have a next element and we're
								//still greater than it as well (we can't skip if we're equal due to combined indexes)
								if ($i != count($node->elements) - 1)
								{
									$nextAgainst = $driver->_comparableValue($driver->_phpValue($this->_extractKeyPart($node->elements[$i + 1]->key, 0), $schema['type']), $schema['type']);
									$nextComparison = $this->header['sort_info'][0]['comparer']->compare($value, $nextAgainst, $this->header['sort_info'][0]['type']);
									
									if ($nextComparison > 0)
									{
										$skip = true;
									}
								}
								
								if (!$skip && !in_array($node->elements[$i]->link, $visited))
								{
									$child = $this->loadNode($node->elements[$i]->link, $depth + 1);
									$records = array_unique(array_merge($records, $this->_searchSingle($child, $driver, $key, $depth + 1)));
									$visited[] = $node->elements[$i]->link;
								}
							}
							else
							{
								//nothing to do
							}
						}
						
						break;
				}
			}
			
			return $records;
		}
		
		/**
		 * This will fetch all values (record numbers) of all of the leaf nodes from an entire subtree.
		 * @param BPlusTreeNode $node The root node of the subtree to gather.
		 * @param int $depth The depth where the node is located.
		 */
		function _fetchAll($node, $depth)
		{
			$values = array();
			
			if ($node->isLeaf())
			{
				foreach ($node->elements as $element)
				{
					$values = array_unique(array_merge($values, $element->value));
				}
			}
			else
			{
				//follow the less pointer
				$child = $this->loadNode($node->less, $depth + 1);
				$values = array_unique(array_merge($values, $this->_fetchAll($child, $depth + 1)));
						
				//follow every element's link
				foreach ($node->elements as $i => $element)
				{
					$child = $this->loadNode($element->link, $depth + 1);
					$values = array_unique(array_merge($values, $this->_fetchAll($child, $depth + 1)));
				}
			}
			
			return $values;
		}
		
		/**
		 * Extracts the value of a given field out of the key (the key being the concatenated value of all fields
		 * in the index.
		 * @param string $key The key from the tree element.
		 * @param int The field number of the index to get (zero-based).
		 * @return string The extracted key part.
		 */
		function _extractKeyPart($key, $fieldNumber)
		{
			$offset = 0;
			
			foreach ($this->header['sort_info'] as $i => $info)
			{
				if ($i < $fieldNumber)
				{
					$offset += $info['field_length'];
				}
				else if ($i == $fieldNumber)
				{
					return substr($key, $offset, $info['field_length']);
				}
				else
				{
					throw new Exception("Unable to extract key part!");
				}
			}
			
			throw new Exception("Unable to extract key part!");
		}
	}
?>