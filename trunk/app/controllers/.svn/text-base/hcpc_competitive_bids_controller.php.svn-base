<?php
	class HcpcCompetitiveBidsController extends AppController
	{
		var $pageTitle = 'HCPC Competitive Bid';
		var $uses = array('HcpcCompetitiveBid', 'HcpcCompetitiveBidZipCode');
		
		/**
		 * Displays a pageable listing of all HCPC competitive bids.
		 */
		function summary()
		{
			$postDataName = 'HcpcCompetitiveBidPost';
			$filterName = 'HcpcCompetitiveBidFilter';
			$conditions = array();
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				//store the post and filter for paging purposes				
				$this->Session->write($postDataName, $this->data);
				$this->Session->write($filterName, $conditions);
			}
			else if ($this->Session->check($filterName))
			{
				//if we're not on a postback but we have a saved search, filter by it
				$conditions = $this->Session->read($filterName);
				$this->data = $this->Session->read($postDataName);
			}
			
			//set up the pagination
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => 'bid_number'
			);
			
			$this->set('records', $this->paginate('HcpcCompetitiveBid'));
		}
		
		/**
		 * Allows the user to create or edit HCPC competitive bids.
		 */
		function edit($id = null)
		{
			$zips = array();
			
			if (!empty($this->data))
			{
				//bring over the autocompleter data into the carrier number
				$this->data['HcpcCompetitiveBid']['assigned_carrier_number'] = $this->data['Carrier']['search'];
				
				//save the record
				if ($this->HcpcCompetitiveBid->save($this->data) !== false)
				{
					if (empty($this->data['HcpcCompetitiveBid']['id']))
					{
						$this->redirect("/hcpcCompetitiveBids/edit/{$this->HcpcCompetitiveBid->id}");
					}
					else
					{
						$this->set('close', true);
					}
				}
			}
			else if ($id !== null)
			{
				//load the existing record if we have one
				$this->data = $this->HcpcCompetitiveBid->find('first', array('conditions' => array('id' => $id)));
				$this->data['Carrier']['search'] = $this->data['HcpcCompetitiveBid']['assigned_carrier_number'];
			}
			
			//on a postback or not, if we're editing a record, we need to load the zips for it
			if ($id !== null)
			{
				$zips = $this->HcpcCompetitiveBidZipCode->find('all', array(
					'conditions' => array('bid_number' => $this->data['HcpcCompetitiveBid']['bid_number']),
					'contain' => array(),
					'order' => 'zip_code'
				));
			}
			
			$this->set(compact('id', 'zips'));
		}
	}
?>