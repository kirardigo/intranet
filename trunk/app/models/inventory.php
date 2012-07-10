<?php
	class Inventory extends AppModel
	{
		var $useDbConfig = 'fu05';
		var $useTable = 'FU05AP';
		
		var $actsAs = array(
			'Indexable',
			'Defraggable'
		);
	}
?>