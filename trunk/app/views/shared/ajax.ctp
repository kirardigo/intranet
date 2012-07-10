<?php
	/**
	 * Modified the original array passed in by HTML escaping 
	 * all values for the given field. The array should be in the form
	 * of a Model->find() call.
	 * @param $data array $data The array to modify.
	 * @param $fields mixed The Model.field name or an array of Model.field names whose values should be escaped.
	 */
	function escapeArray(&$data, $fields)
	{
		if (!is_array($fields))
		{
			$fields = array($fields);
		}
		
		foreach ($fields as $field)
		{
			list($model, $name) = explode('.', $field);
			
			for ($i = 0; $i < count($data); $i++)
			{
				$data[$i][$model][$name] = h($data[$i][$model][$name]);
			}
		}
	}
	
	//if the output variable is an array, we have work to do. Otherwise
	//just render it as-is
	if (is_array($output))
	{
		echo '<ul>';
		
		if (count($output) > 0)
		{
			if (!array_key_exists('data', $output))
			{
				//if we don't have a data key, it means the array is just an array
				//of values, which we'll just spit out as-is
				echo '<li>' . implode("</li>\n<li>", array_map('h', $output)) . '</li>';
			}
			else
			{
				$format = '<li';
				$parts = array();
				$escape = array_key_exists('escape', $output) ? $output['escape'] : true;
				
				//see if we need to render an id attribute for each item
				if (array_key_exists('id_field', $output))
				{
					$format .= ' id="' . (array_key_exists('id_prefix', $output) ? $output['id_prefix'] : '') . '%s"';
					$parts[] = '{n}.' . $output['id_field'];
					
					if ($escape)
					{
						escapeArray($output['data'], $output['id_field']);
					}
				}
				
				//add the spot where the values to be displayed will be rendered
				$format .= '>';
				
				if (!array_key_exists('value_format', $output))
				{
					$format .= implode(' ', array_fill(0, count($output['value_fields']), '%s'));
				}
				else
				{
					$format .= $output['value_format'];
				}
				
				foreach ($output['value_fields'] as $field)
				{			
					$parts[] = '{n}.' . $field;
				}
				
				if ($escape)
				{
					escapeArray($output['data'], $output['value_fields']);
				}
				
				//add the informal span if necessary
				if (array_key_exists('informal_fields', $output))
				{
					$format .= '<span class="informal"> ';
					
					if (!array_key_exists('informal_format', $output))
					{
						$format .= implode(' ', array_fill(0, count($output['informal_fields']), '%s'));
					}
					else
					{
						$format .= $output['informal_format'];
					}
					
					$format .= '</span>';
					
					foreach ($output['informal_fields'] as $field)
					{
						$parts[] = '{n}.' . $field;
					}
					
					if ($escape)
					{
						escapeArray($output['data'], $output['informal_fields']);
					}
				}
				
				$format .= '</li>';
				
				echo implode("\n", Set::format($output['data'], $format, $parts));
			}
		}
		
		echo '</ul>';
	}
	else
	{
		echo $output;
	}
?>