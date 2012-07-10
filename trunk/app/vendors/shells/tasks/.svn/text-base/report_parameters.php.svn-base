<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Task to make it easy to work with report shells. It provides an easy way to take
	 * command-line arguments and prepare them into an array suitable for a data array used in a requestAction().
	 * The parameters array used by the methods in this task is expected to be an array of arrays with the following
	 * keys:
	 * 		'type' - can be one of:
	 * 			'date' - parses the option value supplied from the command line as a date.
	 * 			'flag' - if the option is present, the value will be a 1. If the option is not present, 
	 * 					 the value will be a zero.
	 * 			'string' - parses the option value as-is.
	 * 			'array' - parses the value as an array of strings, delimited by commas.
	 *		'model' - the name of the model the field belongs to.
	 *		'field' - the name of the field in the model that the option is linked to.
	 *		'flag' - the letter(s) to use for the command line switch.
	 *		'description' - a description of the parameter that will be used for usage purposes.
	 *		'default' - the default value for the field if not otherwise specified.
	 * 		'required' - specify this key if the parameter is required.
	 */
	class ReportParametersTask extends Shell 
	{
		/**
		 * Parses and returns the parameters passed as an array that matches a $data array in a Cake post.
		 * @param array $reportParameters A report parameters array.
		 */
		function parse($reportParameters)
		{
			if (!$this->verify($reportParameters))
			{
				$this->out("");
				$this->out('Invalid report parameters. Please check your shell configuration.');
				$this->out("");
				$this->_stop();
			}
			
			$parameters = array();
			
			if (array_key_exists('h', $this->params))
			{
				$this->usage($reportParameters);
				$this->_stop();
			}
			
			foreach ($reportParameters as $parameter)
			{
				if (array_key_exists($parameter['flag'], $this->params))
				{
					$value = $this->params[$parameter['flag']];
					$f = '_process' . ucwords($parameter['type']);
					$parameter['value'] = $this->$f($value);
					$parameters[] = $parameter;
				}
				else if (isset($parameter['default']))
				{
					$parameter['value'] = $parameter['default'];
					$parameters[] = $parameter;
				}
				else if ($parameter['type'] == 'flag')
				{
					$parameter['value'] = 0;
					$parameters[] = $parameter;
				}
				else if (isset($parameter['required']) && $parameter['required'])
				{
					$this->out("");
					$this->out("Missing required -{$parameter['flag']} switch.");
					$this->usage($reportParameters);
					$this->_stop();
				}
			}
			
			$data = array();
			$selected = Set::extract('/.[value]', $parameters);
			
			foreach ($selected as $parameter)
			{
				$data[$parameter['model']][$parameter['field']] = $parameter['value'];
			}
			
			return $data;
		}
		
		/**
		 * Verifies that the report parameters don't contain duplicate flags.
		 * @param array $reportParameters A report parameters array.
		 */
		function verify($reportParameters)
		{
			$flags = Set::extract($reportParameters, '{n}.flag');
			return count($flags) == count(array_unique($flags));
		}
		
		/**
		 * Prints usage for the hosting shell that displays every possible report parameter and their flags.
		 * @param array $reportParameters A report parameters array.
		 */
		function usage($reportParameters)
		{
			$labelLength = 14;
			$typeLength = 6;
			$totalLength = $labelLength + $typeLength + 3;
			
			$this->out("");
			$this->out("Usage: {$this->shell} [options]");
			$this->out(implode("\n", Set::format($reportParameters, "  -%-{$labelLength}s(%{$typeLength}s) %s", array('{n}.flag', '{n}.type', '{n}.description'))));
			$this->out(sprintf("  %-{$totalLength}s %s", '-h', 'Display this help and exit'));
			$this->out("");
		}
		
		/**
		 * Processes flag parameters.
		 * @param string $value The value from the command line.
		 * @return string The processed value.
		 */
		function _processFlag($value)
		{
			return 1;
		}
		
		/**
		 * Processes date parameters.
		 * @param string $value The value from the command line.
		 * @return string The processed value.
		 */
		function _processDate($value)
		{
			return databaseDate($value);
		}
		
		/**
		 * Processes string parameters.
		 * @param string $value The value from the command line.
		 * @return string The processed value.
		 */
		function _processString($value)
		{
			return $value;
		}
		
		/**
		 * Processes array parameters.
		 * @param string $value The value from the command line.
		 * @return string The processed value.
		 */
		function _processArray($value)
		{
			return array_map('trim', explode(',', $value));
		}
	}