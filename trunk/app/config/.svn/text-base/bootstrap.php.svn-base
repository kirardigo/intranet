<?php
/* SVN FILE: $Id: bootstrap.php 7945 2008-12-19 02:16:01Z gwoo $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @version       $Revision: 7945 $
 * @modifiedby    $LastChangedBy: gwoo $
 * @lastmodified  $Date: 2008-12-18 18:16:01 -0800 (Thu, 18 Dec 2008) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 *
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php is loaded
 * This is an application wide file to load any function that is not used within a class define.
 * You can also use this to include or require any files in your application.
 *
 */
/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * $modelPaths = array('full path to models', 'second full path to models', 'etc...');
 * $viewPaths = array('this path to views', 'second full path to views', 'etc...');
 * $controllerPaths = array('this path to controllers', 'second full path to controllers', 'etc...');
 *
 */
 
function ifset(&$var,$default='')
{
	if (isset($var)) return $var;
	return $default;
}

function ifnull(&$var,$default='')
{
	if (isset($var) && $var !== null && strlen(trim($var)) > 0) return $var;
	return $default;
}

/**
 *
 */
function formatNumber(&$value, $decimalPlaces)
{
	if ($value == '')
	{
		return '';
	}
	else
	{
		return number_format($value, $decimalPlaces);
	}
}

/**
 * Format the dates contained within an array.
 * @param array $dataArray Reference to an array of data containing fields which need to be formatted.
 * @param mixed $keys An array of fields to format or a string containing a single field name.
 */
function formatDatesInArray(&$dataArray, $keys)
{
	if (!is_array($keys))
	{
		$keys = array($keys);
	}
	
	foreach ($keys as $key)
	{
		if (array_key_exists($key, $dataArray))
		{
			$dataArray[$key] = formatDate($dataArray[$key]);
		}
	}
}

/**
 * Display a name as "FirstName LastName". If a name is in "LastName, FirstName"
 * format, we must flip it. In cases where there is no comma, return the string as-is.
 * @param string $name The name to format.
 * @return string The formatted version of the name.
 */
function formatName($name)
{
	if (strpos($name, ',') != 0)
	{
		$chunks = explode(',', $name, 2);
		return trim($chunks[1]) . ' ' .  trim($chunks[0]);
	}
	
	return $name;
}

/**
 * Formats date strings to MM/DD/YYYY.
 * @param string $date The date to convert.
 * @return string The date in MM/DD/YYYY format.
 */
function formatDate($date)
{
	if (trim($date) == '')
	{
		return '';
	}
	
	//$d = new DateTime($date);
	//return $d->format('m/d/Y');
	return date('m/d/Y', strtotime($date));
}

/**
 * Formats date strings to MM/DD/YYYY h:m A.
 * @param string $date The date to convert.
 * @return string The date in MM/DD/YYYY format.
 */
function formatDateTime($date)
{
	if (trim($date) == '')
	{
		return '';
	}
	
	//$d = new DateTime($date);
	//return $d->format('m/d/Y h:i A');
	return date('m/d/Y h:i A', strtotime($date));
}

/**
 * Formats date strings to YYYY-MM-DD.
 * @param string $date The date to convert.
 * @param string $now Optional date/time string to create the date relative to (see strtotime).
 * @return string The date in YYYY-MM-DD format.
 */
function databaseDate($date, $now = null)
{
	if (trim($date) == '')
	{
		return null;
	}
	
	return date('Y-m-d', $now == null ? strtotime($date) : strtotime($date, $now));
	
	/*
	$d = null;
	
	if ($now != null)
	{		
		$d = new DateTime($now);
		$d->add(date_interval_create_from_date_string($date));	
	}
	else
	{
		$d = new DateTime($date);
	}
	
	return $d->format('Y-m-d');
	*/
}

/**
 * Take a U05 date and return it as a formatted string. Mainly for use with the Default file.
 * @param string $date An eight digit number sequence representing a date in U05.
 * @return string The date in MM/DD/YYYY format.
 */
function formatU05Date($date)
{
	if (preg_match('/^\d{8}$/', $date))
	{
		return substr($date, 0, 2) . '/' . substr($date, 2, 2) . '/' . substr($date, 4, 4);
	}
	else
	{
		return '';
	}
}

/**
 * Calculate the number of weekdays between two dates.
 * @param string $dateFrom The starting date.
 * @param string $dateTo The ending date.
 * @return int The number of weekdays between the two.
 */
function weekdayDiff($dateFrom, $dateTo)
{
	$fromTimestamp = strtotime($dateFrom);
	$toTimestamp = strtotime($dateTo);
	
	$differenceSeconds = $toTimestamp - $fromTimestamp;
	$differenceDays = round($differenceSeconds / 86400);
	$differenceWeeks = floor($differenceDays / 7);
	$remainderDays = floor($differenceDays % 7);
	
	$firstDay = date('w', $fromTimestamp);
	$oddDays = $firstDay + $remainderDays;
	
	if ($oddDays > 7)
	{
		$remainderDays--;
	}
	if ($oddDays > 6)
	{
		$remainderDays--;
	}
	
	return ($differenceWeeks * 5) + $remainderDays;
}

/**
 * Adds a month to a date string. When adding a month to a date, this method will never increase the date by 
 * more than the end of the next physical month. So adding a month to 1/31/2010 would result in 2/28/2010 (or 
 * 2/29/2010 on a leap year). This is opposed to the regular '+1 month' strtotime string that would result in 3/3/2010.
 * @param string $date The date to add a month to. The date can be any date format that can be parsed by strtotime.
 * @return string A date string in m/d/Y format that has had a month added to it.
 */
function addMonth($date)
{
	$time = strtotime($date);
	
	//split the date
	$month = date('m', $time);
	$day = date('d', $time);
	$year = date('Y', $time);
	
	//add a month
	$month++;
	
	//account for the year changeover
	if ($month > 12)
	{
		$month = 1;
		$year++;
	}
	
	//respect 30 day months, feb., and leap years
	if (in_array($month, array(4, 6, 9, 11)) && $day > 30)
	{
		$day = 30;
	}
	else if ($month == 2 && $day > 28)
	{
		$day = ($year % 400 == 0 || ($year % 100 <> 0 && $year % 4 == 0)) ? 29 : 28;
	}
	
	return "{$month}/{$day}/{$year}";
}

/**
 * Adds one or more months to a date string. Internally uses addMonth, so it exhibits the same behavior.
 */
function addMonths($date, $months)
{
	for ($i = 0; $i < $months; $i++)
	{
		$date = addMonth($date);
	}
	
	return $date;
}

/**
 * Returns how many days are in month of the given date.
 * @param int $month The month to check.
 * @param int $year The year to check.
 * @return int The number of days in the month.
 */
function daysInMonth($month, $year)
{
	return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

?>