<?php
	/**
	 * Represents a node in a B+ tree.
	 */
	class BPlusTreeNode
	{
		/** The address (unique identifier) of the node. */
		var $address;
		
		/** The array of elements in the node. */
		var $elements;
		
		/** The maximum number of elements that can be in the node. */
		var $size;
		
		/** The address (id) of the parent node, if any. */
		var $parent;
		
		/** The address (id) of the less node (only applicable to internal nodes), if any. */
		var $less;
		
		/** The address (id) of the previous leaf node (only applicable to leaf nodes), if any. */
		var $previous;
		
		/** The address (id) of the next leaf node (only applicable to leaf nodes), if any. */
		var $next;
		
		/**
		 * Constructor.
		 * @param numeric $size The maximum number of elements that can be in the node.
		 */
		function __construct($size)
		{
			$this->address = 0;
			$this->elements = array();
			$this->size = $size;
			$this->parent = null;
			$this->less = null;
			$this->previous = null;
			$this->next = null;
		}
		
		/**
		 * Finds the index at which a particular key occurs in the elements of the node.
		 * @param mixed $key The key to look for in the node.
		 * @param numeric $index If the key was not found, when the method returns, this will
		 * be the index of the lowest element in the node that has a key that is still greater than the key
		 * being searched for. This essentially tells the caller what element that the key should be
		 * inserted in front of, if the key is to be added.
		 * @param BPlusTreeController $controller The controller responsible for comparing keys.
		 * @return numeric The index at which the key was found, or false if the key was not found.
		 */
		function find($key, &$index, $controller)
		{
			$lower = 0;
			$upper = count($this->elements) - 1;

			while($lower <= $upper)
			{
				$index = ($lower + $upper) >> 1;
				$comparison = $controller->compare($key, $this->elements[$index]->key);
				
				if ($comparison < 0)
				{
					$upper = $index - 1;
				}
				else if ($comparison > 0)
				{
					$lower = $index + 1;
				}
				else
				{
					return $index;
				}
			}
		
			$index = $lower;
			return false;
		}
		
		/**
		 * Adds an element to the node.
		 * @param BPlusTreeElement The element to insert into the node.
		 * @param BPlusTreeController $controller The controller responsible for comparing keys.
		 * @return numeric The index in the node's elements array at which the element was inserted.
		 */
		function add($element, $controller)
		{
			//go through each element in the node
			for ($i = 0; $i < count($this->elements); $i++)
			{
				//if we get to a key that's greater than this element's key, we insert
				//the element right before it
				if ($controller->compare($this->elements[$i]->key, $element->key) > 0)
				{
					array_splice($this->elements, $i, 0, array($element));
					return $i;
				}
			}
			
			//if we still haven't found an element greater than the new one, insert the
			//new one at the end.
			$this->elements[] = $element;
			return count($this->elements) - 1;
		}
		
		/**
		 * Removes the element at the specified index from the node.
		 */
		function removeAt($index)
		{
			array_splice($this->elements, $index, 1);
		}
		
		/**
		 * Clears all elements out of the node.
		 */
		function clear()
		{
			$this->elements = array();
		}
		
		/** 
		 * Determins if the given node is full.
		 * @return bool True if it is, false otherwise.
		 */
		function isFull()
		{
			return count($this->elements) >= $this->size;
		}
		
		/**
		 * Determines if the given node is a leaf node or an internal node.
		 * @return bool True if the node is a leaf, false otherwise.
		 */
		function isLeaf()
		{
			//a node is a leaf if it doesn't have a less pointer and none
			//of its elements have child pointers
			
			if ($this->less != null)
			{
				return false;
			}
			
			foreach ($this->elements as $element)
			{
				if ($element->link != null)
				{
					return false;
				}
			}
			
			return true;
		}
		
		/**
		 * Applies all of the information of a node to the current node. Essentially making
		 * the current node an exact copy of the one passed in.
		 * @param BPlusTreeNode $node The node to copy.
		 */
		function apply($node)
		{
			$this->address = $node->address;
			$this->elements = $node->elements;
			$this->size = $node->size;
			$this->parent = $node->parent;
			$this->less = $node->less;
			$this->previous = $node->previous;
			$this->next = $node->next;
		}
	}
?>