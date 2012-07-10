<?php
	/**
	 * Represents a single element that is contained inside of a BPlusTreeNode.
	 */
	class BPlusTreeElement
	{
		/** The element's key. */
		var $key;
		
		/** The element's value. */
		var $value;
		
		/** The address (id) of the element's child node (only applicable to internal nodes), if any. */
		var $link;
		
		/**
		 * Constructor.
		 * @param mixed $key The key of the element.
		 * @param mixed $value The value of the element.
		 * @param numeric $link The address (id) of the element's child node, if any.
		 */
		function __construct($key, $value, $link = null)
		{
			$this->key = $key;
			$this->value = $value;
			$this->link = $link;
		}
		
		/**
		 * Creates a deep copy of the given element.
		 * @param BPlusTreeElement $element The element to create a copy of.
		 * @return BPlusTreeElement A new element that is an exact copy of the one passed in.
		 */
		function copy($element)
		{
			return new BPlusTreeElement($element->key, $element->value, $element->link);
		}
		
		/**
		 * Applies all of the information of an element to the current element. Essentially making
		 * the current element an exact copy of the one passed in.
		 * @param BPlusTreeElement $element The element to copy.
		 */
		function apply($element)
		{
			$this->key = $element->key;
			$this->value = $element->value;
			$this->link = $element->link;
		}
	}
?>