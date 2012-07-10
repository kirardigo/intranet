<?php
	/**
	 * Abstract comparer class that can be used to compare two values.
	 */
	abstract class Comparer
	{
		/** 
		 * This comes from a filepro setting that causes non-Y2K compliant years to wrap from 19XX
		 * to 20XX - 1, where XX is the value of the limit. If this filepro setting is changed,
		 * this constant needs to change too (it's a user-centric setting in /etc/profile.d/filepro.sh).
		 */
		static $y2kYearLimit = 20;
		
		/** 
		 * Compares two values.
		 * @param mixed $first The first value to compare.
		 * @param mixed $second The second value to compare.
		 * @return 
		 * 		-1 if $first < $second
		 * 		0 if $first == $second
		 * 		1 if $first > $second
		 */
		abstract function compare($first, $second, $type = 'string');
		
		/**
		 * Takes a date in m/d/y format (that's a 2 digit year) and converts it to
		 * a Y2K compliant year based on the limit defined in this class.
		 * @param string $date The date to make compliant.
		 * @param string $format The format to return the date in. Defaults to Ymd.
		 * @return The compliant date.
		 */
		function makeDateY2kCompliant($date, $format = 'Ymd')
		{
			$year = substr($date, 6, 2);
			return date($format, strtotime(substr($date, 0, 6) . ($year >= Comparer::$y2kYearLimit ? '19' : '20') . $year));
		}
		
		/**
		 * Sanitizes a value based on the type. This should be used when
		 * comparing the values inside of the compare() method.
		 * @param mixed $value The value to sanitize.
		 * @param string $type The type of the value.
		 * @return The sanitized value.
		 */
		function sanitize($value, $type)
		{
			//non-string empty values get coerced to null
			if ($value === null || ($type != 'string' && trim($value) == ''))
			{
				return null;
			}
			
			switch ($type)
			{
				case 'boolean':
					$value = ($value == 'Y' || $value === true) ? 'Y' : (($value == 'N' || $value === false) ? 'N' : null);
				case 'date':
					$value = (
						//Y-m-d -> Ymd
						preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
						? (substr($value, 0, 4) . substr($value, 5, 2) . substr($value, 8, 2))
						: (
							//mdY -> Ymd (the explicit test in the beginning is to make sure we don't
							//reverse dates that are already in Ymd)
							preg_match('/^(0[1-9]|1[012])\d{6}$/', $value)
							? (substr($value, 4) . substr($value, 0, 4))
							: (
								//Ymd -> Ymd
								preg_match('/^\d{8}$/', $value)
								? $value
								: null
							)
						)
					);
					break;
				case 'time':
					//TODO - /OV time values?
					$value = (
						//Y-m-d -> Ymd
						preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
						? (substr($value, 0, 4) . substr($value, 5, 2) . substr($value, 8, 2))
						: (
							//mdy -> Ymd (Y2K fun)
							preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $value)
							? ($this->makeDateY2kCompliant($value))
							: (		
								//mdY -> Ymd (the explicit test in the beginning is to make sure we don't
								//reverse dates that are already in Ymd)				
								preg_match('/^(0[1-9]|1[012])\d{6}$/', $value)
								? (substr($value, 4) . substr($value, 0, 4))
								: (
									//Ymd -> Ymd
									preg_match('/^\d{8}$/', $value)
									? $value
									: null
								)
							)
						)
					);
					break;
				case 'int':
					$value = (int)trim($value);
					break;
				case 'float':
					$value = (float)trim($value);
					break;
				case 'string':
					$value = rtrim($value);
				default:
					//nothing i can think of
			}
			
			return $value;
		}
	}
?>