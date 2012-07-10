<?php
	echo '<table>';
	
	foreach ($values as $key => $value)
	{
		echo '<tr><th>' . h($key) . ':</th><td>' . h($value) . '</td></tr>';
	}
	
	echo '</table>';
?>