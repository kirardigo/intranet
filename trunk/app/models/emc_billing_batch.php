<?php
	class EmcBillingBatch extends AppModel
	{
		/**
		 * Overridden to set the transmission number after creation of a new record.
		 * The transmission number is always (ID % 1 million) + 1. The 5010 spec says the transmission number is a 9 character field, so
		 * after 1 million transmissions we just roll the transmission number back to 1.
		 */
		function afterSave($created) 
		{
			if ($created)
			{
				$this->saveField('transmission_number', ($this->id % 1000000) + 1);
			}
		}
	}
?>