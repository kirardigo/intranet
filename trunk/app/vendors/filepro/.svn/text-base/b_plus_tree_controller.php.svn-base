<?php
	/**
	 * Abstract class for a B+ tree controller.
	 */
	abstract class BPlusTreeController
	{
		/** The root node in the tree. */
		var $root = null;
		
		/** The maximum number of keys per node (order) */
		var $keysPerNode;
		
		/** The node cache. */
		var $cache = array();
		
		/**
		 * Used to compare two keys in a node.
		 * @param mixed $first The first value to compare.
		 * @param mixed $second The second value to compare.
		 * @return see Comparer::compare
		 */
		abstract function compare($first, $second);
		
		/**
		 * Gets the address of the root node.
		 * @return numeric The address of the root node.
		 */
		abstract function getRootAddress();
		
		/**
		 * Creates a new node for use in the tree.
		 * @return BPlusTreeNode The created node.
		 */
		abstract function newNode();
		
		/**
		 * Creates a new element for use in a node.
		 * @param mixed $key The key of the element.
		 * @param mixed $value The value of the element.
		 * @param numeric $link The address of the child node, if any.
		 * @return BPlusTreeElement An element with the specified key and value.
		 */
		abstract function newElement($key, $value, $link = null);
		
		/**
		 * Loads the node in the tree with the given address.
		 * @param numeric $address The address of the node to load.
		 * @param numeric $depth The depth at which the node is located.
		 * @return BPlusTreeNode The node, if loaded. False otherwise.
		 */
		abstract function loadNode($address, $depth = 1);
		
		/**
		 * Adds a new value to an element.
		 * @param BPlusTreeElement The element to add the value to.
		 * @param mixed $value The value to add to the element.
		 */
		abstract function addValueToElement($element, $value);
		
		/**
		 * Increases the depth of the tree by 1.
		 */
		abstract function increaseDepth();
		
		/**
		 * Searches through the tree for the given key.
		 * @param mixed The key to search for.
		 * @return array The value that matches the key.
		 */
		abstract function search($key);
	}
?>