<?php
	/**
	 * Comparer that compares value in descending order.
	 */
	class DescendingComparer extends Comparer
	{
		function compare($first, $second, $type = 'string')
		{
			$first = $this->sanitize($first, $type);
			$second = $this->sanitize($second, $type);
			
			//first handle null values - we treat null as the smallest possible value.
			if ($first === null && $second === null)
			{
				return 0;
			}
			else if ($second === null)
			{
				return -1;
			}
			else if ($first === null)
			{
				return 1;
			}

			//string values are compared via case-sensitive comparison
			if ($type == 'string')
			{
				return strcmp($second, $first);
			}
			//everything else is just a simple comparison test
			else if ($first === $second)
			{
				return 0;
			}
			else if ($second < $first)
			{
				return -1;
			}
			else
			{
				return 1;
			}
		}
	}
?>