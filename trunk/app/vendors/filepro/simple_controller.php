<?php	
	/**
	 * Simple memory-based B+ tree controller to use for unit-testing.
	 */
	class SimpleController extends BPlusTreeController
	{		
		var $comparer;
		
		function __construct($comparer)
		{
			$this->keysPerNode = 4;
			$this->comparer = $comparer;
		}
		
		function compare($first, $second)
		{
			return $this->comparer->compare($first, $second);
		}
		
		function getRootAddress()
		{
			return 1;
		}
		
		function newNode()
		{
			$node = new BPlusTreeNode($this->keysPerNode);
			
			static $address = 2;
			$node->address = $address++;
				
			$this->cache[$node->address] = $node;
			
			return $node;
		}
		
		function newElement($key, $value, $link = null)
		{
			return new BPlusTreeElement($key, $value, $link);
		}
		
		function loadNode($address, $depth = 1)
		{
			if (!$address)
			{
				return false;
			}
			
			if (!array_key_exists($address, $this->cache))
			{
				$node = new BPlusTreeNode($this->keysPerNode);
				$node->address = $address;
				$this->cache[$address] = $node;
			}
			
			return $this->cache[$address];
		}
		
		function addValueToElement($element, $value)
		{
			$element->value = $value;
		}
		
		/** We don't care about depth on this tree. */
		function increaseDepth() {}
		
		/** Search is not implemented for this tree */
		function search($key) { return null; }
	}
?>