<?php
	class Magnificent extends AppModel
	{
		var $belongsTo = array('MillersFamilyValue');
		
		var $actsAs = array('FormatDates');
		
		var $validate = array(
			'reason' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The reason must be specified.'
				)
			),
			'value' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The value must be specified.'
				),
				'range' => array(
					'rule' => array('range', 0, 31),
					'message' => 'The value must be between 1 and 30.'
				)
			),
			'millers_family_value_id' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'The exhibited Millers Family Value must be specified.'
				)
			)
		);
		
		/**
		 * Callback implementation to massage data before saving.
		 * @return bool Indicates success or failure.
		 */
		function beforeSave()
		{
			if (!parent::beforeSave())
			{
				return false;
			}
			
			if (isset($this->data[$this->alias]['attachment_file']['tmp_name']))
			{
				if ($this->data[$this->alias]['attachment_file']['size'] > 0 && file_exists($this->data[$this->alias]['attachment_file']['tmp_name']))
				{
					$this->data[$this->alias]['attachment'] = file_get_contents($this->data[$this->alias]['attachment_file']['tmp_name']);
					$this->data[$this->alias]['attachment_name'] = $this->data[$this->alias]['attachment_file']['name'];
					$this->data[$this->alias]['attachment_type'] = $this->data[$this->alias]['attachment_file']['type'];
				}
				else
				{
					$this->data[$this->alias]['attachment'] = null;
					$this->data[$this->alias]['attachment_name'] = null;
					$this->data[$this->alias]['attachment_type'] = null;
				}
			}
			
			return true;
		}
		
		/**
		 * Approve a pending magnificent nomination.
		 * @param int $id The ID of the magnificent record.
		 * @param int $currentUser The login name of the managerial user.
		 * @param array $carbonCopy Array of email addresses to carbon copy.
		 */
		function approve($id, $currentUser, $carbonCopy = array())
		{
			$record = $this->find('first', array(
				'contain' => array('MillersFamilyValue'),
				'conditions' => array(
					'Magnificent.id' => $id
				)
			));
			
			// Do not continue if the record does not exist
			if ($record === false)
			{
				return;
			}
			
			$record[$this->alias]['is_approved'] = 1;
			$record[$this->alias]['is_cancelled'] = 0;
			$record[$this->alias]['approving_user'] = $currentUser;
			
			$this->create();
			$this->save($record);
			
			// Find email address
			$staffModel = ClassRegistry::init('Staff');
			$recipientEmail = $staffModel->field('email', array('user_id' => $record[$this->alias]['recipient_user']));
			
			if ($recipientEmail !== false)
			{
				$record[$this->alias]['recipient_name'] = $staffModel->getStaffName($record[$this->alias]['recipient_user']);;
				$record[$this->alias]['nominator_name'] = $staffModel->getStaffName($record[$this->alias]['nominating_user']);;
				$record[$this->alias]['approver_name'] = $staffModel->getStaffName($record[$this->alias]['approving_user']);;
				
				App::import('Component', 'Email');
				$emailComponent = new EmailComponent();
				$settingsModel = ClassRegistry::init('Setting');
				
				// Send recipient an email on approval
				// If in debug mode, send to tech support instead of recipient
				$emailComponent->to = (Configure::read('debug') != 0) ? $settingsModel->get('tech_support_email') : $recipientEmail;
				$emailComponent->subject = 'Magnificents: Congratulations';
				$emailComponent->from = $settingsModel->get('default_mail_reply');
				$emailComponent->cc = $carbonCopy;
				$emailComponent->sendAs = 'html';
				$emailComponent->send($this->_renderApprovalEmail($record));
			}
		}
		
		/**
		 * Render the email for the Magnificent recipient.
		 * @return string The body of the email.
		 */
		function _renderApprovalEmail ($record)
		{
			$body = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<style type="text/css">
						.TableLabel {
							width: 200px;
							font-weight: bold;
						}
						h1 {
							font-size: 20px;
							font-family: Arial;
						}
					</style>
				</head>
				<body>
					<img src="http://' . $_SERVER['SERVER_NAME'] . '/img/magnificents_small.jpg" title="Magnificents" />
					<h1>You have earned Magnificents.</h1>
										
					<p>' . nl2br($record[$this->alias]['message']) . '</p>

					<table>
						<tr>
							<td class="TableLabel">Recipient:</td>
							<td>' . $record[$this->alias]['recipient_name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Date:</td>
							<td>' . formatDate($record[$this->alias]['created']) . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Reason:</td>
							<td>' . $record[$this->alias]['reason'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Value:</td>
							<td>' . $record[$this->alias]['value'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">MFV Exhibited:</td>
							<td>' . $record['MillersFamilyValue']['name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Nominated By:</td>
							<td>' . $record[$this->alias]['nominator_name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Approved By:</td>
							<td>' . $record[$this->alias]['approver_name'] . '</td>
						</tr>
					</table>
					
					<p><a href="http://' . $_SERVER['SERVER_NAME'] . '/magnificent_redemptions/history/' . $record[$this->alias]['recipient_user'] . '">View your Magnificents history</a></p>
				</body>
				</html>
			';
			
			return $body;
		}
		
		/**
		 * Reject a pending magnificent nomination.
		 * @param int $id The ID of the magnificent record.
		 * @param int $currentUser The login name of the managerial user.
		 */
		function reject($id, $currentUser)
		{
			$record = $this->find('first', array(
				'contain' => array('MillersFamilyValue'),
				'conditions' => array(
					'Magnificent.id' => $id
				)
			));
			
			// Do not continue if the record does not exist
			if ($record === false)
			{
				return;
			}
			
			$record[$this->alias]['is_approved'] = 0;
			$record[$this->alias]['is_cancelled'] = 1;
			$record[$this->alias]['approving_user'] = $currentUser;
			
			$this->create();
			$this->save($record);
			
			// Find email address
			$staffModel = ClassRegistry::init('Staff');
			$recipientEmail = $staffModel->field('email', array('user_id' => $record['Magnificent']['nominating_user']));
			
			if ($recipientEmail !== false)
			{
				$record[$this->alias]['recipient_name'] = $staffModel->getStaffName($record[$this->alias]['recipient_user']);;
				$record[$this->alias]['nominator_name'] = $staffModel->getStaffName($record[$this->alias]['nominating_user']);;
				$record[$this->alias]['approver_name'] = $staffModel->getStaffName($record[$this->alias]['approving_user']);;
				
				App::import('Component', 'Email');
				$emailComponent = new EmailComponent();
				$settingsModel = ClassRegistry::init('Setting');
				
				// Send nominator an email on rejection
				// If in debug mode, send to tech support instead of recipient
				$emailComponent->to = (Configure::read('debug') != 0) ? $settingsModel->get('tech_support_email') : $recipientEmail;
				$emailComponent->subject = 'Magnificents: Nomination Rejected';
				$emailComponent->from = $settingsModel->get('default_mail_reply');
				$emailComponent->sendAs = 'html';
				$emailComponent->send($this->_renderRejectionEmail($record));
			}
		}
		
		/**
		 * Render the email for the Magnificent nominator.
		 * @return string The body of the email.
		 */
		function _renderRejectionEmail ($record)
		{
			$body = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<style type="text/css">
						.TableLabel {
							width: 200px;
							font-weight: bold;
						}
						h1 {
							font-size: 20px;
							font-family: Arial;
						}
					</style>
				</head>
				<body>
					<img src="http://' . $_SERVER['SERVER_NAME'] . '/img/magnificents_small.jpg" title="Magnificents" />
					<h1>Your nomination has been declined.</h1>
					
					<p>' . nl2br($record[$this->alias]['message']) . '</p>
					
					<table>
						<tr>
							<td class="TableLabel">Recipient:</td>
							<td>' . $record[$this->alias]['recipient_name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Date:</td>
							<td>' . formatDate($record[$this->alias]['created']) . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Reason:</td>
							<td>' . $record[$this->alias]['reason'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Value:</td>
							<td>' . $record[$this->alias]['value'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">MFV Exhibited:</td>
							<td>' . $record['MillersFamilyValue']['name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Nominated By:</td>
							<td>' . $record[$this->alias]['nominator_name'] . '</td>
						</tr>
						<tr>
							<td class="TableLabel">Rejected By:</td>
							<td>' . $record[$this->alias]['approver_name'] . '</td>
						</tr>
					</table>
				</body>
				</html>
			';
			
			return $body;
		}
		
		/**
		 * 
		 */
		function getUserInfo($username)
		{
			$staffModel = ClassRegistry::init('Staff');
			$magnificentRedemptionModel = ClassRegistry::init('MagnificentRedemption');
			
			$earnedCredits = $this->totalMagnificents($username);
			$redeemedCredits = $magnificentRedemptionModel->usedRedemptions($username);
			
			$data = array(
				'username' => $username,
				'user' => $staffModel->getStaffName($username),
				'manager' => $staffModel->getManagerName($username),
				'supervisor' => $staffModel->getSupervisorName($username),
				'earnedCredits' => $earnedCredits,
				'redeemedCredits' => $redeemedCredits,
				'availableCredits' => $earnedCredits - $redeemedCredits
			);
			
			return $data;
		}
		
		/**
		 * Find the number of approved magnificents for a user.
		 * @param string $user The user to query for.
		 */
		function totalMagnificents($user)
		{
			$data = $this->find('first', array(
				'contain' => array(),
				'fields' => array('SUM(value) as used'),
				'conditions' => array(
					'recipient_user' => $user,
					'is_approved' => 1
				)
			));
			
			return is_numeric($data[0]['used']) ? $data[0]['used'] : 0;
		}
	}
?>