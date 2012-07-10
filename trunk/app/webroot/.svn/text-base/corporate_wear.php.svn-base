<?php
	//define the receiver of the email
	$to = 'kaw@millers.com';
	
	//define who sent the email
	$from = 'no-reply@millers.com';
	
	//define the subject of the email
	$subject = 'Millers Corporate Wear Order';

/*
	DO NOT EDIT BELOW THIS LINE
========================================================================================================
*/
	
	//Setup our body content for the order
	$lines = split(':', $_POST['content']);
	$rows = array();
	$body = '<html><head><title></title><style type="text/css">th {text-align: left;}</style></head><body><table width="60%"><tr><th>Style</th><th>Color</th><th>Size</th><th>Price</th><th>Qty</th></tr>[tableContent]</table></body></html>';
	$content = '<b>From:</b> ' . $_POST['employee_name'] . '<br />' . '<b>Employee ID:</b> ' . $_POST['employee_id'];
	
	if ($_POST['payment'] == 'PayrollFour')
	{
		$content .= '<br /><b>Payment Method:</b> Weekly Pay Deduction';
	}
	else if ($_POST['payment'] == 'PayrollOne')
	{
		$content .= '<br /><b>Payment Method:</b> Commission Pay Deduction';
	}
	else if ($_POST['payment'] == 'Magnificents')
	{
		$content .= '<br /><b>Payment Method:</b> Magnificents';
	}
	
	foreach ($lines as $line)
	{
		$columns = split("\|", $line);
		
		$row = '<tr><td>' . implode("</td><td>",$columns) . '</td></tr>';
		$content .= $row;
	}
	
	$totalRow = '<tr><td colspan="4" align="right"><b>Total:</b></td><td>' . $_POST['total'];
	
	$content .= $totalRow;
	
	$body = str_replace('[tableContent]', $content, $body);
	
	//create a boundary string. It must be unique
	//so we use the MD5 algorithm to generate a random hash
	$random_hash = md5(date('r', time()));
	//define the headers we want passed. Note that they are separated with \r\n
	$headers = "From: {$from}\r\nReply-To: {$from}";
	
	if (isset($_POST['email']))
	{
		$headers .= "\r\nCc: {$_POST['email']}";
	}
	
	//add boundary string and mime type specification
	$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
	
	//define the body of the message.
	ob_start(); //Turn on output buffering
?>
--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<?= $body ?>

--PHP-alt-<?php echo $random_hash; ?>--
<?
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();
//send the email
$mail_sent = @mail( $to, $subject, $message, $headers );

//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
header("Location: http://intranet.millers.com");
?>
