<?php
	class ClientCommunicationLog extends AppModel
	{
		var $useTable = 'client_communication_log';
		var $order = 'incident_time desc';
		
		var $belongsTo = array(
			'ClientCommunicationLogType',
			'ClientCommunicationLogStatus'
		);
	}
?>