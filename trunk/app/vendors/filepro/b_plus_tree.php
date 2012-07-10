<?php
	require_once 'byte_converter.php';
	require_once 'b_plus_tree_controller.php';
	require_once 'b_plus_tree_element.php';
	require_once 'b_plus_tree_node.php';
	require_once 'comparer.php';
	require_once 'ascending_comparer.php';
	require_once 'descending_comparer.php';
	
	/**
	 * Base class B+ tree that implements generic B+ tree algorithms. However, The actual
	 * storage, loading, updating, searching, and saving of the nodes in the tree is left
	 * to a BPlusTreeController object. 
	 */
	class BPlusTree
	{
		/** The controller for the tree. */
		var $controller;
		
		/**
		 * Constructor.
		 * @param BPlusTreeController $controller The controller responsible for handling
		 * the nodes in the tree.
		 */
		function __construct($controller)
		{
			$this->controller = $controller;
		}
		
		/**
		 * Adds a key and value to the tree.
		 * @param mixed $key The key to add.
		 * @param mixed $value The value to add.
		 */
		function add($key, $value)
		{
			//load the root if we haven't already
			if ($this->controller->root == null)
			{
				$this->controller->root = $this->controller->loadNode($this->controller->getRootAddress());
			}
			
			$index = -1;
			$parentIndex = 0;
			$found = false;
			$depth = 1;
			
			//find the node where this key should go
			$node = $this->findNode($this->controller->root, $key, $index, $parentIndex, $found, $depth);
			
			if (!$node)
			{
				//bad/corrupt index
				return false;
			}
			
			if ($found)
			{
				//the key already exists, don't need to create a node...
				
				//now that we found the node where the key is listed, see if it's
				//a leaf. If it's not, walk down to the leaf containing the key so 
				//we can insert the value
				if (!$node->isLeaf())
				{
					$link = $node->elements[$index]->link;
					$index = -1;
					$parentIndex = 0;
					$found = false;
					$depth += 1;
					
					$node = $this->findNode($this->controller->loadNode($link, $depth), $key, $index, $parentIndex, $found, $depth);
	
					if (!$found || !$node)
					{
						return false;
					}
				}
				
				//add the value for the existing key at a leaf
				$this->controller->addValueToElement($node->elements[$index], $value);
				return true;
			}			
			else if (!$node->isFull())
			{
				//new key/value pair for a leaf node that's not full
				$node->add($this->controller->newElement($key, $value), $this->controller);
				return true;
			}
			else
			{
				//full leaf node, needs split
				
				//create the new element for the key and value
				$median = $this->controller->newElement($key, $value);
				
				//grab the node's parent
				$parent = $this->controller->loadNode($node->parent);		
				
				//split the node 
				$rightNode = $this->splitNode($node, $median, $depth);
							
				if ($rightNode === false)
				{
					return false;
				}

				//continue walking up the tree, splitting as we go, until we find a non-full
				//internal node that we can place the median element into
				while ($parent !== false)
				{
					if ($parent->isFull())
					{			
						$depth -= 1;
						$rightNode = $this->splitNode($parent, $median, $depth);
						
						if ($rightNode === false)
						{
							return false;
						}
		
						$node = $parent;
						$parent = $this->controller->loadNode($parent->parent);
					}
					else
					{
						//we found a non-full parent - insert the median
						$parent->add($median, $this->controller);
						$rightNode->parent = $parent->address;
						break;
					}
				}
				
				//if we've split all the way up to the root node, we need to
				//create a new root node that contains 1 element - the median from
				//the split node. The new root node's less pointer will point to the
				//old root node
				if ($parent === false)
				{
					$this->controller->root = $this->controller->newNode();
					$node->parent = $this->controller->root->address;
					$rightNode->parent = $this->controller->root->address;
					$this->controller->root->add($median, $this->controller);
					$this->controller->root->elements[0]->link = $rightNode->address;
					$this->controller->root->less = $node->address;
					$this->controller->increaseDepth();
				}
			}
			
			return true;
		}
		
		/**
		 * Finds the value of a given key in the tree.
		 * @param mixed $key The key to find the value for.
		 * @return mixed The value of the element with the specified key.
		 */
		function find($key)
		{			
			//load the root node if we haven't already
			if ($this->controller->root == null)
			{
				$this->controller->root = $this->controller->loadNode($this->controller->getRootAddress());
			}
			
			$index = -1;
			$parentIndex = 0;
			$found = false;
			$depth = 1;
			
			//find the node where the key would be located
			$node = $this->findNode($this->controller->root, $key, $index, $parentIndex, $found, $depth);
			
			//if we can't find the key, return false
			if (!$found || !$node)
			{
				return false;
			}

			//now that we found the node where the key is listed, see if it's
			//a leaf. If it's not, walk down to the leaf containing the key so 
			//we can grab the value
			if (!$node->isLeaf())
			{
				$link = $node->elements[$index]->link;
				$index = -1;
				$parentIndex = 0;
				$found = false;
				$depth += 1;
				
				$node = $this->findNode($this->controller->loadNode($link, $depth), $key, $index, $parentIndex, $found, $depth);

				if (!$found || !$node)
				{
					return false;
				}
			}
			
			//return the element's value
			return $node->elements[$index]->value;
		}
		
		/**
		 * Searches the B+ tree for the given value. Search differs from the find method 
		 * in that the search is internally performed by the tree's controller.
		 */
		function search($key)
		{
			return $this->controller->search($key);
		}
		
		/**
		 * Finds the node with the given key.
		 * @param BPlusTreeNode $currentNode The current node being searched.
		 * @param mixed $key The key being searched for.
		 * @param numeric &$index The index of the element in the node where the key was found.
		 * Pass in -1 and if the node is found, this variable will contain the index when it returns.
		 * @param numeric &$parentIndex The index of the element in the parent node that was followed
		 * to get to the current node. Pass in -1 and if the node is found, this variable will 
		 * contain the index when it returns.
		 * @param bool &$found When the method returns, this will state whether or not the key was
		 * found in the returned node.
		 * @param numeric &$depth The current depth of passed in $currentNode variable. When the method
		 * returns, this variable will contain the depth at which the found node is located.
		 * @return BPlusTreeNode If the key was found, the node returned is the one that contains the
		 * element with that key. If the key was not found, the node returned is the leaf node where
		 * the key would be located if it existed. If there was a problem loading any of the nodes
		 * while traversing the tree, the return value is false.
		 */
		function findNode($currentNode, $key, &$index, &$parentIndex, &$found, &$depth = 1)
		{
			$found = false;
			$index = -1;
		
			//look for the element with the given key in the current node
			if ($currentNode->find($key, $index, $this->controller) !== false)
			{
				//if we found it we're done
				$found = true;
				return $currentNode;
			}
		
			$link = null;
		
			//if the returned index from the find operation is greater than zero,
			//we have to follow the previous element's link to keep traversing. Otherwise,
			//we need to follow the less pointer
			if ($index > 0)
			{
				$link = $currentNode->elements[$index - 1]->link;
			}
			else
			{
				$link = $currentNode->less;
			}
		
			//if we're at a leaf node, we're done
			if ($link == null)
			{
				//we're at a leaf, so this is the node to return
				return $currentNode;
			}
			else if ($link == $currentNode->address)
			{
				//cyclic reference (should never happen, but CYA)
				return false;
			}
			
			//keep traversing down the tree
			$depth += 1;
			$node = $this->controller->loadNode($link, $depth);
			
			if (!$node)
			{
				return false;
			}
			
			$parentIndex = $index;
			return $this->findNode($node, $key, $index, $parentIndex, $found, $depth);
		}
		
		/**
		 * Splits a full node.
		 * @param BPlusTreeNode $node The node to split.
		 * @param BPlusTreeElement The element to insert. When this method returns,
		 * this element will have been updated to be the median element of the node that
		 * was split that should be inserted into the parent.
		 * @param $depth The depth of the tree at which the splitting is occuring.
		 */
		function splitNode($node, $median, $depth = 1)
		{
			//the passed in median is the element that is going to be inserted
			//where the split occurs, so we copy that one first before we adjust the passed-in
			//reference to point to the current median that is pulled out
			$copy = BPlusTreeElement::copy($median);
			$temp = new BPlusTreeNode($this->controller->keysPerNode);
			$right = $this->controller->newNode();
		
			//add the new node, determine the median, and then remove the median
			$node->add($copy, $this->controller);
			$median->apply($node->elements[$this->getMedian($node)]);
			$node->removeAt($this->getMedian($node));
		
			//the new right node's less pointer is going to point to where the median originally pointed
			$right->less = $median->link;

			//and to complete the link, if the median did actually point to something, we need to 
			//set the target's parent to be the new right node
			if ($median->link != null)
			{
				$link = $this->controller->loadNode($median->link, $depth + 1);
				
				if ($link === false)
				{
					return false;
				}
				
				$link->parent = $right->address;
			}
			
			//our temporary node, which is going to end up being copied into the node being split,
			//is going to have its less pointer point to where the current node's less pointer is pointing.
			$temp->less = $node->less;
		
			//now go through all the elements in the node and split them between the temp node and the
			//new right now, depending on if they are before or after the median's key
			for ($i = 0; $i < count($node->elements); $i++)
			{
				if ($this->controller->compare($node->elements[$i]->key, $median->key) < 0)
				{					
					$temp->add($node->elements[$i], $this->controller);
				}
				else
				{					
					$right->add($node->elements[$i], $this->controller);
		
					//if the element being moved to the right actually linked to another node,
					//we need to complete the link by having that node's parent point to the new
					//right node
					if ($node->elements[$i]->link != null)
					{
						$link = $this->controller->loadNode($node->elements[$i]->link, $depth + 1);
						
						if ($link === false)
						{
							return false;
						}
						
						$link->parent = $right->address;
					}
				}
			}
	
			//the right node is going to become a child of the same parent as the original node
			$right->parent = $node->parent;
			
			//copy over some settings from the node so when we apply it they will remain intact
			$temp->parent = $node->parent;
			$temp->address = $node->address;
			$temp->previous = $node->previous;
			$temp->next = $node->next;

			//now apply the changes we made to the node
			$node->apply($temp);
			
			//the median that we pulled up out of the node should link to the new right node
			$median->link = $right->address;
			
			//if the right node is a leaf, we have to add the median with its values to that node,
			//and we also need to adjust the leaf pointers
			if ($right->isLeaf())
			{
				$right->add($this->controller->newElement($median->key, $median->value), $this->controller);

				//adjust the leaf pointers so we maintain all of the leaves being
				//connected in the right order
				$right->previous = $node->address;
				$right->next = $node->next;
				$node->next = $right->address;
				
				//if the right node now points to another leaf on the right, we need
				//to load that leaf to adjust its pointer so that it comes back to the 
				//right node
				if ($right->next != null)
				{
					$link = $this->controller->loadNode($right->next, $depth);
					
					if ($link == null)
					{
						return false;
					}
					
					$link->previous = $right->address;
				}
			}
			
			//remove the median value since we pulled it out of the leaf
			$median->value = null;
			return $right;
		}
		
		/**
		 * Gets index of the median element in a node.
		 * @param BPlusTreeNode The node to get the median index of.
		 * @return numeric The index of the median element in the node
		 */
		function getMedian($node)
		{
			return count($node->elements) >> 1;
		}
		
		/**
		 * Recursively displays the entire B+ tree. Be careful calling this on large indexes.
		 * @param BPlusTreeNode The node at which to start displaying the tree. Typically the
		 * root node.
		 * @param numeric $depth Internally used to know how far down we are in the tree.
		 * @param numeric $indent Internally used to control indentation as the tree is traversed.
		 * @param numeric $maxDepth The maximum depth to traverse.
		 */
		function display($node, $maxDepth = null, $depth = 1, $indent = 0)
		{
			if ($node == null)
			{
				$node = $this->controller->loadNode($this->controller->getRootAddress());
			}
			
			if ($depth == 1)
			{
				echo '<pre>';
			}
			
			echo str_repeat("\t", $indent) . "Node {$node->address}:\n";
			echo str_repeat("\t", $indent + 1) . 'Leaf: ' . ($node->isLeaf() ? 'Yes' : 'No');
			echo str_repeat("\t", $indent + 1) . "Previous: {$node->previous}\n";
			echo str_repeat("\t", $indent + 1) . "Next: {$node->next}\n";
			
			if ($node->less != null)
			{
				echo str_repeat("\t", $indent + 1) . "Less:\n";
				
				if ($maxDepth == null || $depth < $maxDepth)
				{
					$this->display($this->controller->loadNode($node->less, $depth + 1), $maxDepth, $depth + 1, $indent + 2);
				}
			}
			
			echo str_repeat("\t", $indent + 1) . "Element Values:\n";
			
			foreach ($node->elements as $element)
			{
				echo str_repeat("\t", $indent + 2) . "Key/Value/Link: <b>{$element->key}</b> / " . preg_replace('/[\r\n\t\s]+/', ' ', print_r($element->value, true)) . " / {$element->link}\n";
					
				if ($element->link != null)
				{
					if ($maxDepth != null && $depth >= $maxDepth)
					{
						continue;
					}
					
					echo str_repeat("\t", $indent + 3) . "Child Nodes:\n";
					$this->display($this->controller->loadNode($element->link, $depth + 1), $maxDepth, $depth + 1, $indent + 4);
				}
			}
			
			if ($depth == 1)
			{
				echo '</pre>';
			}
		}
		
		/**
		 * Recursively displays the entire B+ tree. Be careful calling this on large indexes. Works exactly
		 * like display but renders HTML that will allow you to visually see and traverse the tree.
		 * @param BPlusTreeNode The node at which to start displaying the tree. Typically the
		 * root node.
		 * @param numeric $depth Internally used to know how far down we are in the tree.
		 * @param numeric $indent Internally used to control indentation as the tree is traversed.
		 * @param numeric $maxDepth The maximum depth to traverse.
		 */
		function prettyDisplay($node, $maxDepth = null, $depth = 1, $indent = 0)
		{
			static $deepest = 1;
			
			if ($node == null)
			{
				$node = $this->controller->loadNode($this->controller->getRootAddress());
			}
			
			if ($depth == 1)
			{
				$deepest = 1;
			}
			
			$deepest = max($deepest, $depth);
			
			echo "<div id=\"Node_{$node->address}\" class=\"Depth_{$depth} Node\">";
			echo "<div class=\"Stats\" style=\"display:none;\">";
			
			if ($node->less != null)
			{
				echo "<b>Less:</b> <a href=\"#\">{$node->less}</a><br />";
			}
			
			if ($node->previous != null)
			{
				echo "<b>Previous:</b> <a href=\"#\">{$node->previous}</a><br />";
			}
			
			if ($node->next != null)
			{
				echo "<b>Next:</b> <a href=\"#\">{$node->next}</a><br />";
			}
			
			echo "</div>";
			echo "<div class=\"Elements\" style=\"display:none;\">";
			
			foreach ($node->elements as $element)
			{
				echo "<div class=\"Element\">";
				echo "{$element->key}";
				echo "<div class=\"ElementDetails\" style=\"display:none;\">";
				
				if ($element->link != null)
				{
					echo "<b>Link:</b> <a href=\"#\">{$element->link}</a><br />";
				}
				
				if ($node->isLeaf())
				{
					echo "<b>Value:</b> " . nl2br(print_r($element->value, true));
				}
				
				echo "</div>";
				echo "</div>";
			}
			
			echo "</div>";
			echo "</div>";
			
			if ($node->less != null)
			{
				if ($maxDepth == null || $depth < $maxDepth)
				{
					$this->prettyDisplay($this->controller->loadNode($node->less, $depth + 1), $maxDepth, $depth + 1, $indent + 2);
				}
			}
			
			foreach ($node->elements as $element)
			{					
				if ($element->link != null)
				{
					if ($maxDepth != null && $depth >= $maxDepth)
					{
						continue;
					}
					
					$this->prettyDisplay($this->controller->loadNode($element->link, $depth + 1), $maxDepth, $depth + 1, $indent + 4);
				}
			}
			
			if ($depth == 1)
			{
				for ($i = 1; $i <= $deepest; $i++)
				{
					echo "<div id=\"Depth_{$i}\" class=\"DepthContainer\"></div>";
				}
				
				echo '<br style="clear: both;" />';
				
				echo '
					<style type="text/css">
						.DepthContainer { border-bottom: 1px solid #DDD; clear: left; }
						.Node { height: 16px; width: 16px; border: 1px solid #DDD; background-color: #EEE; margin: 2px 5px; float: left; }
						.SelectedNode { background-color: #AAD; width: 700px; height: 300px; overflow: auto; }
						.Element { height: 16px; border: 1px solid #DDD; background-color: #EEE; margin: 2px 5px; float: left; padding: 2px; }
						.SelectedElement { background-color: #FFFBCF; width: 400px; height: 200px; overflow: auto; }
					</style>
					
					<script type="text/javascript" src="/js/prototype.js"></script>
					
					<script type="text/javascript">
						var selectedNode = null;
						var selectedElement = null;
						
						document.observe("dom:loaded", function() {
							$$(".DepthContainer").each(function(container) {
								var depth = container.id.split("_")[1];
								
								$$(".Depth_" + depth).each(function(node) {
									container.insert(node);
									
									node.select("a").each(function(link) {
										link.observe("click", function(e) {
											changeSelectedNode($("Node_" + link.innerHTML));
											e.stop();
										});
									});
									
									node.select("div.Element").each(function(element) {
										element.observe("click", function(e) {
											changeSelectedElement(this);
											e.stop();
										});	
									});
									
									node.observe("click", function(e) {
										changeSelectedNode(this);
										e.stop();
									});
								});
							});
						});
						
						function changeSelectedNode(node)
						{
							if (!node)
							{
								alert("Node not available. It is at a depth beyond what you have chosen to display.");
								return;
							}
							
							if (selectedNode != null)
							{
								selectedNode.select("div.Stats", "div.Elements").invoke("hide");
								selectedNode.removeClassName("SelectedNode");
								
								changeSelectedElement(null);
							}

							selectedNode = node;
							selectedNode.addClassName("SelectedNode");
							selectedNode.select("div.Stats", "div.Elements").invoke("show");
						}
						
						function changeSelectedElement(element)
						{			
							if (selectedElement != null)
							{
								selectedElement.select(".ElementDetails")[0].hide();
								selectedElement.removeClassName("SelectedElement");
							}

							if (element)
							{
								selectedElement = element;
								selectedElement.addClassName("SelectedElement");
								selectedElement.select(".ElementDetails")[0].show();
							}
						}
					</script>
				';
			}
		}
	}
?>