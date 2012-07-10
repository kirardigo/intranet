<?php
	class MagnificentsController extends AppController
	{
		var $pageTitle = 'Magnificents';
		
		var $uses = array(
			'Magnificent',
			'MagnificentReport',
			'MillersFamilyValue',
			'Staff',
			'Lookup'
		);
		
		var $components = array('Email');
		
		/**
		 * Nominate a user.
		 */
		function nominate()
		{
			// Pull current user from login in case they are not in staff table
			$currentUser = $this->Session->read('user');
			$this->set('currentUser', $currentUser);
			
			$shouldNotify = false;
			$canApprove = $this->Staff->canApproveMagnificents($currentUser);
			
			if (isset($this->data))
			{
				$this->data['Magnificent']['nominating_user'] = $currentUser;
				
				// Comma-separated list of nominees needs parsed
				$nominatedUsers = explode(',', $this->data['Staff']['search']);
				
				// Validate and save
				$this->Magnificent->set($this->data);
				if ($this->Magnificent->validates())
				{
					foreach ($nominatedUsers as $singleUser)
					{
						$singleUser = trim($singleUser);
						
						// Ignore any manually entered manager names or self
						if ($this->Staff->isManager($singleUser) || $currentUser == $singleUser)
						{
							continue;
						}
						
						$this->data['Magnificent']['recipient_user'] = $singleUser;
						$this->data['Magnificent']['is_donation'] = 0;
						$this->Magnificent->create();
						$this->Magnificent->save($this->data);
						
						// If current user is a manager, then auto-approve
						if ($canApprove)
						{
							$ccRecipients = Set::filter(explode(',', $this->data['CarbonCopy']['recipients']));
							$this->Magnificent->approve($this->Magnificent->id, $currentUser, $ccRecipients);
						}
						else
						{
							// Notify manager if records need approval
							$shouldNotify = true;
						}
					}
					
					if ($shouldNotify)
					{
						// Notify manager about new pending record
						$approverEmail = $this->Staff->field('email', array(
							'user_id' => $this->data['Magnificent']['approving_recipient_user']
						));
						
						if ($approverEmail !== false && $approverEmail != '')
						{
							$settingsModel = ClassRegistry::init('Setting');
							
							// If in debug mode, send to tech support instead of recipient
							$this->Email->to = (Configure::read('debug') != 0) ? $settingsModel->get('tech_support_email') : $approverEmail;
							$this->Email->subject = 'Magnificents: Pending Approval';
							$this->Email->from = $settingsModel->get('default_mail_reply');
							$this->Email->template = 'magnificents_pending';
							$this->Email->sendAs = 'html';
							$this->Email->send();
						}
					}
					
					$this->flash('You have successfully submitted your nomination. Thank You.', 'nominate');
					return;
				}
			}
			
			$this->set('canApprove', $canApprove);
			$this->set('familyValues', $this->MillersFamilyValue->find('list'));
			$this->helpers[] = 'ajax';
		}
		
		/**
		 * Approve pending nominations.
		 */
		function pending($showAll = 0)
		{
			$currentUser = $this->Session->read('user');
			
			// TODO: Temporary until actual application security is enforced.
			$canApprove = $this->Staff->canApproveMagnificents($currentUser);
			if (!$canApprove)
			{
				$this->flash('You are not authorized to approve Magnificents.', '/');
			}
			
			$conditions = array(
				'is_approved' => 0,
				'is_cancelled' => 0
			);
			
			if (!$showAll)
			{
				$conditions['approving_recipient_user'] = $currentUser;
			}
			
			$this->paginate = array(
				'contain' => array(),
				'conditions' => $conditions,
				'order' => array(
					'created',
					'approving_recipient_user'
				)
			);
			
			$this->data['Magnificents'] = $this->paginate('Magnificent');
			$this->data['List']['show_all'] = $showAll;
		}
		
		/**
		 * Review the a pending nomination to approve or cancel.
		 * @param int $id The ID of the record to review.
		 */
		function review_pending($id)
		{
			$currentUser = $this->Session->read('user');
			
			// TODO: Temporary until actual application security is enforced.
			$canApprove = $this->Staff->canApproveMagnificents($currentUser);
			if (!$canApprove)
			{
				$this->flash('You are not authorized to approve Magnificents.', '/');
			}
			
			if (isset($this->data))
			{
				$this->Magnificent->save($this->data);
				
				if ($this->data['Magnificent']['status'] == 'approve')
				{
					$this->Magnificent->approve($id, $currentUser);
				}
				else if ($this->data['Magnificent']['status'] == 'reject')
				{
					$this->Magnificent->reject($id, $currentUser);
				}
				
				$this->redirect('pending');
			}
			
			$this->data = $this->Magnificent->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));

			
			$this->set('id', $id);
			$this->set('familyValues', $this->MillersFamilyValue->find('list'));
		}
		
		/**
		 * View the attachment for a given magnificent record.
		 * @param int $id The ID of the record.
		 */
		function view_attachment($id)
		{
			$this->autoRender = false;
			
			$data = $this->Magnificent->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			header("Content-type: {$data['Magnificent']['attachment_type']}");
			
			// Flush the content to the browser
			echo $data['Magnificent']['attachment'];
			flush();
		}
		
		/**
		 * Reporting on various summary metrics.
		 */
		function reporting($newSearch = 0)
		{
			$departmentModel = ClassRegistry::init('Department');
			
			if ($newSearch)
			{
				$this->Session->delete('magnificentReportData');
			}
			
			if (isset($this->data))
			{
				$this->Session->write('magnificentReportData', $this->data);
			}
			
			if ($this->Session->check('magnificentReportData'))
			{
				$this->data = $this->Session->read('magnificentReportData');
				
				// Gather the summary information anytime the report is run
				App::import('Sanitize');
				
				$startDateCondition = '';
				$endDateCondition = '';
				$profitCenterCondition = '';
				$departmentCondition = '';
				$groupEffortCondition = '';
				
				$postData = Set::filter($this->postConditions($this->data));
				unset($postData['MagnificentReport.show_details']);
				unset($postData['MagnificentReport.show_breakdown']);
				$postData['MagnificentReport.is_approved'] = 1;
				
				if (!$postData['MagnificentReport.include_group_effort'])
				{
					$postData['is_group_effort'] = 0;
					$groupEffortCondition = "and is_group_effort = 0";
				}
				
				unset($postData['MagnificentReport.include_group_effort']);
				
				if (isset($postData['MagnificentReport.start_date']))
				{
					$startDateCondition = "and created >= '" . databaseDate(Sanitize::escape($postData['MagnificentReport.start_date'])) . "'";
					$postData['created >='] = databaseDate($postData['MagnificentReport.start_date']);
					unset($postData['MagnificentReport.start_date']);
				}
				
				if (isset($postData['MagnificentReport.end_date']))
				{
					$endDateCondition = "and created <= '" . databaseDate(Sanitize::escape($postData['MagnificentReport.end_date'])) . "'";
					$postData['created <='] = databaseDate($postData['MagnificentReport.end_date']);
					unset($postData['MagnificentReport.end_date']);
				}
				
				if (isset($postData['MagnificentReport.profit_center_number']))
				{
					$profitCenterCondition = "and profit_center_number = '" . Sanitize::escape($postData['MagnificentReport.profit_center_number']) . "'";
				}
				
				if (isset($postData['MagnificentReport.department']))
				{
					$departmentCondition = "and department = '" . Sanitize::escape($postData['MagnificentReport.department']) . "'";
				}
				
				$fromClause = "
					from {$this->MagnificentReport->useTable} MagnificentReport
					where 1 = 1
					{$startDateCondition}
					{$endDateCondition}
					{$profitCenterCondition}
					{$departmentCondition}
					{$groupEffortCondition}
				";
				
				$summaryInfo = $this->MagnificentReport->query("
					select 
						(select count(value) {$fromClause} and is_approved = 1) as totalApproved,
						(select count(value) {$fromClause} and is_cancelled = 1) as totalCancelled,
						sum(value) as totalValue,
						(select sum(value) {$fromClause} and is_group_effort = 0) as totalIndividualValue
					{$fromClause}
				");
				
				$this->set('summaryInfo', $summaryInfo);
				
				$groupInfo = $this->MagnificentReport->find('all', array(
					'contain' => array(),
					'fields' => array(
						'profit_center_number',
						'department',
						'count(1) as totalCount',
						'sum(value) as totalValue'
					),
					'conditions' => $postData,
					'order' => array(
						'profit_center_number',
						'department'
					),
					'group' => array(
						'profit_center_number',
						'department'
					)
				));
				
				if ($groupInfo !== false)
				{
					foreach ($groupInfo as $key => $row)
					{
						$groupInfo[$key]['MagnificentReport']['profit_center_name'] = $this->Lookup->description('profit_centers', $row['MagnificentReport']['profit_center_number']);
						$groupInfo[$key]['MagnificentReport']['department_name'] = $departmentModel->field('name', array('abbreviation' => $row['MagnificentReport']['department']));
					}
				}
				
				$this->set('groupInfo', $groupInfo);
				
				// Only gather detailed information if extended details were desired
				if ($this->data['MagnificentReport']['show_details'])
				{
					$this->paginate = array(
						'contain' => array('MillersFamilyValue'),
						'page' => 1,
						'conditions' => $postData,
						'order' => array(
							'profit_center_number',
							'department',
							'recipient',
							'created desc'
						)
					);
					
					$this->set('detailedInfo', $this->paginate('MagnificentReport'));
				}
				
			}
			
			$this->set('profitCenters', $this->Lookup->get('profit_centers', true));
			$this->set('departments', $departmentModel->getCodeList());
			$this->set('millersFamilyValues', $this->MillersFamilyValue->find('list'));
		}
		
		/**
		 * Import the CSV version of the Excel document detailing previous records.
		 */
		function import_spreadsheet()
		{
			if (isset($this->data))
			{
				if ($this->data['Magnificent']['import']['size'] > 0 && file_exists($this->data['Magnificent']['import']['tmp_name']))
				{
					$filename = $this->data['Magnificent']['import']['tmp_name'];
					
					if (!($file = fopen($filename,"r")))
					{
						return;
					}
					
					$current_line = 0; // Specify the current line
					
					$keys = array(
						'Magnificent' => array(
							'nominating_user' => 0,
							'approving_recipient_user' => 0,
							'approving_user' => 0,
							'created' => 1,
							'recipient_user' => 2,
							'value' => 3
						),
						'MagnificentRedemption' => array(
							'recipient_user' => 2,
							'value' => 3,
							'requested_date' => 4,
							'ordered_date' => 4,
							'dispensed_date' => 4
						)
					);
					
					$redemptionModel = ClassRegistry::init('MagnificentRedemption');
					
					// Truncate existing data
					$this->Magnificent->query("truncate table {$this->Magnificent->useTable}");
					$this->Magnificent->query("truncate table {$redemptionModel->useTable}");
					
					// Read the data from the file
					while (!feof($file))
					{
						$line = rtrim(fgets($file, 2048));
						$current_line++;
						
						// Skip first & blank lines
						if ($current_line == 1 || strlen($line) == 0)
						{
							continue;
						}
						
						$datarow = preg_split("/,/", $line);
						
						// Setup default values
						$record = array(
							'Magnificent' => array(
								'is_group_effort' => 0,
								'is_donation' => 0,
								'is_approved' => 1,
								'is_cancelled' => 0,
								'attachment' => null,
								'attachment_name' => null,
								'attachment_type' => null,
								'millers_family_value_id' => 5
							),
							'MagnificentRedemption' => array(
								'description' => 'Prior Reward'
							)
						);
						
						// Build arrays with data structure to be saved
						foreach ($keys as $model_name => $fields )
						{
							foreach ($fields as $field_name => $column)
							{
								$record[$model_name][$field_name] = trim(ifset($datarow[$column]));
							}
						}
						
						// Save the values
						$this->Magnificent->create();
						$this->Magnificent->save($record);
						
						if ($record['MagnificentRedemption']['requested_date'] != '')
						{
							$redemptionModel->create();
							$redemptionModel->save($record);
						}
					}
					
					fclose($file);
					
					$this->redirect('/magnificent_redemptions/browse_history');
				}
			}
		}
	}
?>