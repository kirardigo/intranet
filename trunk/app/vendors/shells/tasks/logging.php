<?php
	Configure::write('Cache.disable', true);
	
	/**
	 * Task to ensure we log consistently from shell scripts.
	 */
	class LoggingTask extends Shell 
	{
		var $startTime;
		var $logFile = null;
		var $startingTimestamp = 0;
		var $keepBuffer = false;
		var $buffer = '';
		var $indentLevel = 0;
		
		/**
		 * Write logging data with timestamp.
		 * @param string $data The data to log.
		 */
		function write($data, $showExtended = 0)
		{
			list($usec, $sec) = explode(' ', microtime());
			$currentTimestamp = (float)$usec + (float)$sec;
			
			if ($this->startingTimestamp == 0) { $this->startingTimestamp = $currentTimestamp; }
			
			if ($showExtended)
			{
				$usec = sprintf('%0-4.0f', floor($usec * 10000));
				$difference = number_format($currentTimestamp - $this->startingTimestamp, 3);
				
				if ($this->logFile == null)
				{
					$this->displayOutput(date('Y-m-d H:i:s', $currentTimestamp) . ".{$usec} [{$difference} secs] - " . str_repeat("\t", $this->indentLevel) . $data);
				}
				else
				{
					$this->log(".{$usec} [{$difference} secs] - " . str_repeat("\t", $this->indentLevel) . $data, $this->logFile);
				}
			}
			else
			{
				if ($this->logFile == null)
				{
					$this->displayOutput(date('Y-m-d H:i:s', $currentTimestamp) . ' - ' . str_repeat("\t", $this->indentLevel) . $data);
				}
				else
				{
					$this->log(str_repeat("\t", $this->indentLevel) . $data, $this->logFile);
				}
			}
			
			$this->startingTimestamp = $currentTimestamp;
		}
		
		/**
		 * Tell the logging task to maintain an internal buffer of output that goes to stdout.
		 */
		function maintainBuffer()
		{
			$this->keepBuffer = true;
		}
		
		/**
		 * Increases the indent level of the output written to the log.
		 */
		function increaseIndent()
		{
			$this->indentLevel++;
		}
		
		/**
		 * Decreases the indent level of the output written to the log.
		 */
		function decreaseIndent()
		{
			$this->indentLevel--;
		}
		
		/**
		 * Removes all indentation from any further output written to the log.
		 */
		function clearIndent()
		{
			$this->indentLevel = 0;
		}
		
		/**
		 * Pass text on to stdout while optionally buffering the output text.
		 * @param string $text The text to display.
		 * @param bool $newline Determines whether to end the line after the current text.
		 */
		function displayOutput($text, $newline = true)
		{
			$this->out($text, $newline);
			
			if ($this->keepBuffer)
			{
				$this->buffer .= ($newline) ? "{$text}\n" : $text;
			}
		}
		
		/**
		 * Return the buffered output.
		 * @return string The buffered output text.
		 */
		function getBufferedOutput()
		{
			return $this->buffer;
		}
		
		/**
		 * Set the start time for later elapsed time comparisons.
		 */
		function startTimer()
		{
			$this->startTime = getMicrotime();
		}
		
		/**
		 * This will make use of the built-in logging in CakePHP. The specified logfile will be in app/tmp/logs/.
		 * @params string $logFileName The name of the logfile to write to.
		 */
		function setLogFile($logFileName)
		{
			$this->logFile = $logFileName;
		}
		
		/**
		 * Retrieve & display the elapsed time.
		 * @param string $message The message to display before the elapsed time.
		 * @param bool $resetTimer Determines whether the timer should be reset, defaults to true.
		 */
		function writeElapsedTime($message = 'Elapsed Time -', $resetTimer = true)
		{
			$timeMessage = '';
			$elapsedTime = getMicrotime() - $this->startTime;
			
			if ($elapsedTime > 3600)
			{
				$timeMessage .= floor($elapsedTime / 3600) . ':';
			}
			else
			{
				$timeMessage .= '00:';
			}
			
			if ($elapsedTime >= 60)
			{
				$timeMessage .= sprintf('%02d:', floor(fmod($elapsedTime, 3600) / 60));
			}
			else
			{
				$timeMessage .= '00:';
			}
			
			$timeMessage .= sprintf('%07.4f', fmod(fmod($elapsedTime, 3600), 60));
			
			$this->write("{$message} {$timeMessage}");
			
			if ($resetTimer)
			{
				$this->startTimer();
			}
		}
	}
?>
