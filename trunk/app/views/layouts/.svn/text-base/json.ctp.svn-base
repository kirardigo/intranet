<?php
//we need this so cake doesn't render the execution time at the end of the response
Configure::write('debug', 0);

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, max-age=0, must-revalidate');
header('Content-Type: application/json');

//it is possible for the json content to be too large to fit in the header. For that case, the controller action
//can set the $suppressJsonHeader variable, which will cause the JSON to only be output in the body. On the client side,
//if this is used, the json argument of the onSuccess method of prototype's callback will not be set. You'll have to
//manually do:
//		var json = transport.responseText.evalJSON();
if (!isset($suppressJsonHeader)) { header('X-JSON: ' . $content_for_layout); }
 
echo $content_for_layout;
?>
