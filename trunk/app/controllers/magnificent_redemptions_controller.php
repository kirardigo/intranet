<?php
	class MagnificentRedemptionsController extends AppController
	{
		var $pageTitle = 'Magnificents';
		
		var $uses = array(
			'MagnificentRedemption',
			'MagnificentRedemptionOption',
			'Magnificent',
			'Staff'
		);
		
		/**
		 * Redeem available magnificents for the current user.
		 */
		function redeem()
		{
			$currentUser = $this->Session->read('user');
			$availableCredits = $this->MagnificentRedemption->availableMagnificents($currentUser);
			
			if (isset($this->data))
			{
				$this->data['MagnificentRedemption']['recipient_user'] = $currentUser;
				$this->data['MagnificentRedemption']['requested_date'] = date('Y-m-d');
				$this->MagnificentRedemption->set($this->data);
				
				if ($this->MagnificentRedemption->validates())
				{
					if ($this->data['MagnificentRedemption']['value'] != '')
					{
						// Handle donations
						$this->data['MagnificentRedemption']['is_donation'] = 1;
						$this->data['MagnificentRedemption']['description'] = "Donation to {$this->data['Staff']['search']}";
					}
					else
					{
						// Handle redemptions
						$option = $this->MagnificentRedemptionOption->find('first', array(
							'contain' => array(),
							'conditions' => array(
								'id' => $this->data['MagnificentRedemption']['magnificent_redemption_option_id']
							)
						));
						
						$this->data['MagnificentRedemption']['is_donation'] = 0;
						$this->data['MagnificentRedemption']['value'] = $option['MagnificentRedemptionOption']['value'];
						$this->data['MagnificentRedemption']['description'] = $option['MagnificentRedemptionOption']['description'];
					}
					
					$this->MagnificentRedemption->save($this->data);
					
					$this->flash('Thank you. Your request has been submitted.', 'redeem');
					return;
				}
			}
			
			$this->set('currentUser', $currentUser);
			$this->set('availableCredits', $availableCredits);
			$this->set('availableOptions', $this->MagnificentRedemptionOption->getAvailable($availableCredits));
			$this->helpers[] = 'ajax';
		}
		
		/**
		 * See the list of magnificents redemption requests that are still outstanding.
		 */
		function pending()
		{
			$this->paginate = array(
				'contain' => array('MagnificentRedemptionOption'),
				'conditions' => array(
					'or' => array(
						'ordered_date' => null,
						'dispensed_date' => null
					)
				),
				'order' => array(
					'requested_date'
				)
			);
			
			$this->data = $this->paginate('MagnificentRedemption');
		}
		
		/**
		 * Review a pending order.
		 * @param int $id The ID of the order to update.
		 */
		function review_pending($id)
		{
			if (isset($this->data))
			{
				$this->MagnificentRedemption->save($this->data);
				
				$this->redirect('pending');
			}
			else
			{
				$this->data = $this->MagnificentRedemption->find('first', array(
					'contain' => array('MagnificentRedemptionOption'),
					'conditions' => array('MagnificentRedemption.id' => $id)
				));
				
				// Make sure they don't mess with a closed order
				if ($this->data === false || ($this->data['MagnificentRedemption']['ordered_date'] != '' &&
					$this->data['MagnificentRedemption']['dispensed_date'] != ''))
				{
					$this->flash('This order has already been closed.', 'pending');
					return;
				}
			}
			
			$this->set('id', $id);
		}
		
		/**
		 * Show an overview of all magnificent history.
		 */
		function browse_history()
		{
			$currentUser = $this->Session->read('user');
			
			if (!$this->Staff->canSeeAllMagnificents($currentUser))
			{
				$this->redirect("history/{$currentUser}");
			}
			
			// Get all usernames from magnificents and staff tables
			$staffUsers = $this->Staff->find('all', array(
				'contain' => array(),
				'fields' => array(
					'user_id',
					'full_name'
				),
				'conditions' => array('is_active' => 1),
				'order' => array('full_name'),
				'index' => 'G'
			));
			
			foreach ($staffUsers as $row)
			{
				$this->data[] = $this->Magnificent->getUserInfo($row['Staff']['user_id']);
			}
			
			$this->set('currentUser', $currentUser);
		}
		
		/**
		 * Show a user's magnificent history.
		 * @param string $username Determine which username to filter with.
		 */
		function history($username)
		{
			$currentUser = $this->Session->read('user');
			
			// Make sure users don't see what they should not
			if (!$this->Staff->canSeeAllMagnificents($currentUser) && $currentUser != $username)
			{
				$this->redirect("history/{$currentUser}");
			}
			
			$earned = $this->Magnificent->find('all', array(
				'contain' => array('MillersFamilyValue'),
				'conditions' => array(
					'recipient_user' => $username,
					'is_approved' => 1
				),
				'order' => 'created desc'
			));
			
			$redeemed = $this->MagnificentRedemption->find('all', array(
				'contain' => array('MagnificentRedemptionOption'),
				'conditions' => array(
					'recipient_user' => $username
				),
				'order' => 'requested_date desc'
			));
			
			$this->set('data', $this->Magnificent->getUserInfo($username));
			$this->set('earned', $earned);
			$this->set('redeemed', $redeemed);
		}
	}
?>