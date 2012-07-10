<?php
	/**
	 * Utility class to read raw data.
	 */
	class ByteConverter
	{
		/**
		 * Converts binary data to its hexidecimal equivalent.
		 * @param mixed $value The raw binary data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The binary data in a hexidecimal format.
		 */
		function bin2Hex($value, $littleEndian = false)
		{
			return array_pop(unpack("H*", $littleEndian ? strrev($value) : $value));
		}
		
		/**
		 * Converts hexidecimal data into its binary equivalent.
		 * @param mixed $value The raw hex data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The hex data in a binary format.
		 */
		function hex2Bin($value, $littleEndian = false)
		{
			if ($littleEndian)
			{
				$reversed = '';
				
				for ($i = strlen($value) - 2; $i >= 0; $i -= 2)
				{
					$reversed .= substr($value, $i, 2);
				}
				
				$value = $reversed;
			}
			
			return pack("H*", $value);
		}
		
		/**
		 * Converts binary data to its ASCII string equivalent.
		 * @param mixed $value The raw binary data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The binary data in an ASCII string format.
		 */
		function bin2String($value, $littleEndian = false)
		{
			$string = '';
			$value = ByteConverter::bin2Hex($value, $littleEndian);
			
			for ($i = 0; $i < strlen($value); $i += 2)
			{
				$string .= chr(hexdec(substr($value, $i, 2)));
			}
			
			return $string;
		}
		
		/**
		 * Converts ASCII data into its binary equivalent.
		 * @param mixed $value The raw ASCII data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The ASCII data in binary format.
		 */
		function string2Bin($value, $littleEndian = false)
		{
			$hex = '';
			
			for ($i = 0; $i < strlen($value); $i++)
			{
				$hex .= str_pad(dechex(ord(substr($value, $i, 1))), 2, '0', STR_PAD_LEFT);
			}
			
			return ByteConverter::hex2Bin($hex, $littleEndian);
		}
		
		/**
		 * Converts binary data to its numeric equivalent.
		 * @param mixed $value The raw binary data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The binary data in a numeric format.
		 */
		function bin2Number($value, $littleEndian = false)
		{
			return hexdec(ByteConverter::bin2Hex($value, $littleEndian));
		}
		
		/**
		 * Converts numeric data to its binary equivalent.
		 * @param mixed $value The raw numeric data.
		 * @param bool $littleEndian Big-endian byte order is assumed, but pass true 
		 * to read in little-endian byte order.
		 * @return string The numeric data in a binary format.
		 */
		function number2Bin($value, $littleEndian = false)
		{
			$value = dechex($value);
			$pad = 4 - strlen($value) % 4;
			
			if ($pad != 4)
			{
				$value = str_pad($value, strlen($value) + $pad, '0', STR_PAD_LEFT);
			}
			
			return ByteConverter::hex2Bin($value, $littleEndian);
		}
	}
?>