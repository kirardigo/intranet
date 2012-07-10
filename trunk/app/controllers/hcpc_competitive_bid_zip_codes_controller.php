<?php
	class HcpcCompetitiveBidZipCodesController extends AppController
	{
		var $pageTitle = 'HCPC Competitive Bid Zip Codes';
		
		/**
		 * Adds a particular competitive bid zip code. The bid number and zip code should be in the $this->data array indexed by HcpcCompetitiveBidZipCode.
		 */
		function json_add()
		{
			$this->set('json', array('success' => !!$this->HcpcCompetitiveBidZipCode->save($this->data)));
		}
		
		/**
		 * Deletes a particular competitive bid zip code. The bid number and zip code should be in the $this->data array indexed by HcpcCompetitiveBidZipCode.
		 */
		function json_delete()
		{
			$id = $this->HcpcCompetitiveBidZipCode->field('id', $this->postConditions($this->data));
			$this->HcpcCompetitiveBidZipCode->delete($id);
			$this->set('json', array('success' => true));
		}
	}
?>