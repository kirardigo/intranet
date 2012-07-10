<?php
	class InvoiceTemp extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05FI';
		
		var $actsAs = array(
			'FormatDates'
		);
	}
?>