<?php
	class FileNote extends AppModel
	{
		var $useDbConfig = 'filepro';
		var $useTable = 'ORD_MEMO';
		
		var $actsAs = array(
			'Migratable' => array('key' => 'account_number')
		);
		
		/**
		 * Creates a file note in filePro.
		 * @param array $data Data used to save the record (same structure as used for a save()).
		 * @param string $createdBy The username of the user creating the note. This must be a valid
		 * user on the actual filePro server.
		 * @param array $recipients An array of email addresses to send a copy of the eFN to.
		 * @return boolean True if successful, false otherwise.
		 */
		function createNote($data, $createdBy, $recipients = array())
		{			
			try
			{
				//if any of the remarks are filled out, we need to set the has_remarks flag in the record
				foreach (array('remarks_1', 'remarks_2', 'remarks_3', 'remarks_4', 'memo') as $field)
				{
					if (isset($data['FileNote'][$field]) && trim($data['FileNote'][$field]) != '')
					{
						$data['FileNote']['has_remarks'] = '*';
					}
				}
				
				//grab the account name if the account number is specified but not the name
				if (isset($data['FileNote']['account_number']) && !trim($data['FileNote']['account_number']) == '' && !isset($data['FileNote']['name']))
				{
					$data['FileNote']['name'] = ClassRegistry::init('Customer')->field('name', array('account_number' => $data['FileNote']['account_number']));
				}
				
				//look up the model schema 
				$schema = $this->schema();
				
				//convert dates for use in the database
				foreach ($schema as $field => $info)
				{
					if (isset($data['FileNote'][$field]) && $info['type'] == 'date')
					{
						$data['FileNote'][$field] = databaseDate($data['FileNote'][$field]);
					}
				}
				
				//save it 
				if ($this->save($data) === false)
				{
					return false;
				}
				
				//now we have to send an email of the efn to the recipients
							
				//go through the recipients and append the default domain if the user didn't specify one
				foreach ($recipients as $i => $email)
				{
					$email = trim($email);
					
					if (strpos($email, '@') === false)
					{
						$email .= '@' . $settings['default_mail_domain'];
					}
									
					$recipients[$i] = $email;
				}
	
				if (count($recipients) > 0)
				{			
					App::import('Component', 'Email');
					$email = new EmailComponent();
					
					$email->to = implode(',', $recipients);
					$email->from = 'noreply@millers.com';
					$email->subject = 'eMRS File Note';
					$email->sendAs = 'html';
					$email->send($this->_noteMessageBody($createdBy, $data));
				}
			}
			catch (Exception $ex) {}
			
			return true;
		}
		
		/**
		 * Used to generate an email body for an eFN.
		 * @param string $createdBy The username of the user who created the note.
		 * @param array $data The note data.
		 * @return string An HTML email body containing the eFN details.
		 */
		function _noteMessageBody($createdBy, $data)
		{
			$output = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">

				<html>
					<head>
						<title>eMRS File Note</title>
						
						<style type="text/css">
							* {
								font-family: Verdana, Arial, Helvetica, sans-serif;
								font-size: 11px;
							}
							
							h1 {
								font-size: 16px;
								color: #C00;
							}
							
							label {
								display: block;
								width: 215px;
								font-weight: bold;
								float: left;
								clear: left;	
								text-align: right;
							}
							
							p {
								margin: 0 0 0 220px;
							}
						</style>
					</head>
					<body>
						<h1>File Note created by ' . h($createdBy) . ' on ' . date('m/d/Y H:i:s') . '</h1>
						<label>Account Number:</label><p>' . ifset($data['FileNote']['account_number'], '&nbsp;') . '</p>
						<label>Memo:</label><p>' . ifset($data['FileNote']['memo'], '&nbsp;') . '</p>
						<label>Remarks 1:</label><p>' . ifset($data['FileNote']['remarks_1'], '&nbsp;') . '</p>
						<label>Remarks 2:</label><p>' . ifset($data['FileNote']['remarks_2'], '&nbsp;') . '</p>
						<label>Remarks 3:</label><p>' . ifset($data['FileNote']['remarks_3'], '&nbsp;') . '</p>
						<label>Remarks 4:</label><p>' . ifset($data['FileNote']['remarks_4'], '&nbsp;') . '</p>
						<label>Department Code:</label><p>' . ifset($data['FileNote']['department_code'], '&nbsp;') . '</p>
						<label>FUP Date:</label><p>' . ifset($data['FileNote']['followup_date'], '&nbsp;') . '</p>
						<label>Invoice Number:</label><p>' . ifset($data['FileNote']['invoice_number'], '&nbsp;') . '</p>
						<label>FUP INI:</label><p>' . ifset($data['FileNote']['followup_initials'], '&nbsp;') . '</p>
						<label>Priority:</label><p>' . ifset($data['FileNote']['priority_code'], '&nbsp;') . '</p>
						<label>TCN Number:</label><p>' . ifset($data['FileNote']['transaction_control_number'], '&nbsp;') . '</p>
						<label>TCN File:</label><p>' . ifset($data['FileNote']['transaction_control_number_file'], '&nbsp;') . '</p>
					</body>
				</html>
			';
			
			return $output;
		}
	}
?>