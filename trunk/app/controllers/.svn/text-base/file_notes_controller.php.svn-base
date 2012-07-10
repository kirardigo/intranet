<?php
	class FileNotesController extends AppController
	{
		var $uses = array('FileNote', 'FileNoteActionCode', 'Department', 'Invoice');
		var $helpers = array('Ajax');
		var $pageTitle = 'eFN';
		
		/**
		 * Module for creating a file note. Since file notes are used throughout the system
		 * and based on where they are created they may need extra fields saved to a different table
		 * when the note is created, there is support for extra parameters when invoking the module. They are:
		 *
		 * $this->params['form']['invoice'] - Specifies the invoice the file note is for. If this is specified, the
		 * user will not be able to change the chosen invoice.
		 *
		 * $this->params['form']['before'] - This is any HTML markup (i.e. form fields) that should be included
		 * at the top of the file note form.
		 *
		 * $this->params['form']['after'] - This is any HTML markup (i.e. form fields) that should be included
		 * at the bottom of the file note form.
		 *
		 * $this->params['form']['handler'] - This is the name of a method in a model that will handle the processing
		 * of any necessary postback data that may have been injected into the form via the before or after keys.
		 * The method should be specified as "Model.methodName". The method should accept an array argument, which
		 * essentially ends up being the $this->data array. The method should return true or false depending on if
		 * it successfully did it's job.
		 *
		 * $this->params['form']['showTcnFields'] - Specifies whether or not to show TCN fields. False by default.
		 *
		 * On the client-side, the module fires two events:
		 *
		 * 		onBeforePost - occurs before the form is submitted. If any extra fields were injected into the form,
		 * 					   it is the responsibility of the caller to implement validation at this time. The memo
		 * 					   that is sent to the method contains one variable called "cancel" that should be set to
		 * 					   false if the validation failed.
		 * 		onPostCompleted - invoked when the post to create the file note has completed. The memo has one variable
		 * 						  called "success" which can be used by callers to decide what to do. 
		 * 
		 * @param string $accountNumber The account number to create the note for.
		 */
		function module_create($accountNumber)
		{
			if (!empty($this->data))
			{
				$this->data['FileNote'] = $this->data['FileNoteCreate'];
				unset($this->data['FileNoteCreate']);
				
				$success = true;
				
				//see if we have a handler to invoke
				if (isset($this->data['FileNote']['handler']))
				{
					list($model, $method) = explode('.', $this->data['FileNote']['handler']);
					$success = ClassRegistry::init($model)->{$method}($this->data);
				}
				
				//if the handler succeeded, save the note
				if ($success)
				{
					//pull the TCN info from the invoice before we save if we don't already have the fields
					
					if (!isset($this->data['FileNote']['transaction_control_number']))
					{
						$invoice = $this->Invoice->find('first', array(
							'fields' => array('transaction_control_number', 'transaction_control_number_file'),
							'conditions' => array('invoice_number' => $this->data['FileNote']['invoice_number']),
							'contain' => array()
						));
						
						if ($invoice !== false)
						{
							$this->data['FileNote']['transaction_control_number'] = $invoice['Invoice']['transaction_control_number'];
							$this->data['FileNote']['transaction_control_number_file'] = $invoice['Invoice']['transaction_control_number_file'];
						}
					}
					
					//extract the recipients we're going to mail the note to
					$recipients = trim($this->data['FileNote']['email_to']) == "" ? array() : explode(",", $this->data['FileNote']['email_to']);
					unset($this->data['FileNote']['email_to']);

					$success = $this->FileNote->createNote($this->data, $this->Session->read('user'), $recipients);
				}
				
				//postback results will be in JSON indicating the success of the operation
				$this->layout = 'json';
				$this->params['json'] = true;
				$this->set('json', array('success' => $success));
				return;
			}
			
			//see if we have an invoice and if so, pre-populate it
			$invoice = isset($this->params['form']['invoice']) ? $this->params['form']['invoice'] : null;
			
			if ($invoice != null)
			{
				$this->data['FileNote']['invoice_number'] = $invoice;
			}
			
			//pre-populate our hidden account number field
			$this->data['FileNote']['account_number'] = $accountNumber;
			
			$departments = $this->Department->getCodeList();
			$actionCodes = $this->FileNoteActionCode->getCodeList();
			
			//remap for form which seeks to avoid naming collisions
			$this->data['FileNoteCreate'] = $this->data['FileNote'];
			unset($this->data['FileNote']);
			
			//set any optional arguments we may have received
			$this->set(compact('invoice', 'departments', 'actionCodes'));
			$this->set('before', isset($this->params['form']['before']) ? $this->params['form']['before'] : null);
			$this->set('after', isset($this->params['form']['after']) ? $this->params['form']['after'] : null);
			$this->set('handler', isset($this->params['form']['handler']) ? $this->params['form']['handler'] : null);
			$this->set('showTcnFields', isset($this->params['form']['showTcnFields']) ? $this->params['form']['showTcnFields'] : false);
		}
		
		/**
		 * Find the eFN records for a particular customer.
		 * @param string $accountNumber The account to view eFN records for.
		 * @param bool $refreshData Indicates whether to regenerate
		 */
		function module_forCustomer($accountNumber, $refreshData = 0)
		{
			$sessionFilter = 'FileNoteCustomerFilter';
			
			//this can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_customerTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			//just in case the temp table is missing, we need to regenerate it
			$count = $db->query("
				select count(*) as count
				from information_schema.tables
				where table_name = '{$table}'
			");
			
			if ($count[0][0]['count'] == 0)
			{
				$refreshData = 1;
			}
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			if ($refreshData)
			{
				//create the temp table in MySQL
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						original_id int not null,
						account_number varchar(6) not null,
						transaction_control_number varchar(6) not null,
						transaction_control_number_file varchar(1) not null,
						name varchar(30) not null,
						memo varchar(50) not null,
						remarks varchar(240) not null,
						has_remarks tinyint(1) not null,
						action_code varchar(5) not null,
						department_code varchar(1) not null,
						followup_date date null,
						invoice_number varchar(7) not null,
						followup_initials varchar(3) not null,
						priority_code varchar(3) not null,
						is_client_responsibility tinyint(1) not null,
						should_be_deleted tinyint(1) not null,
						created date null
					)
				");
				
				$records = $this->FileNote->find('all', array(
					'contain' => array(),
					'conditions' => array(
						'account_number' => $accountNumber
					),
					'index' => 'G'
				));
				
				foreach ($records as $record)
				{
					$fields = array_map(array('Sanitize', 'escape'), $record['FileNote']);
					
					$db->query("
						insert into {$table} (
							original_id,
							account_number,
							transaction_control_number,
							transaction_control_number_file,
							name,
							memo,
							remarks,
							has_remarks,
							action_code,
							department_code,
							followup_date,
							invoice_number,
							followup_initials,
							priority_code,
							is_client_responsibility,
							should_be_deleted,
							created
						)
						values (
							" . $fields['id'] . ",
							'{$fields['account_number']}',
							'{$fields['transaction_control_number']}',
							'{$fields['transaction_control_number_file']}',
							'{$fields['name']}',
							'{$fields['memo']}',
							'" . implode(' ', array(
								$fields['remarks_1'],
								$fields['remarks_2'],
								$fields['remarks_3'],
								$fields['remarks_4']
							)) . "',
							" . ($fields['has_remarks'] ? 1 : 0) . ",
							'{$fields['action_code']}',
							'{$fields['department_code']}',
							" . ($fields['followup_date'] != '' ? "'" . databaseDate($fields['followup_date']) . "'" : "null") . ",
							'{$fields['invoice_number']}',
							'{$fields['followup_initials']}',
							'{$fields['priority_code']}',
							" . ($fields['is_client_responsibility'] ? 1 : 0) . ",
							" . ($fields['should_be_deleted'] ? 1 : 0) . ",
							'" . databaseDate($fields['created']) . "'
						)
					", false);
				}
			}
			
			// Filter the customer records from the temp table
			if (!empty($this->data))
			{
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['FileNote.followup_date_start']))
				{
					$filters['FileNote.followup_date >='] = databaseDate($filters['FileNote.followup_date_start']);
					unset($filters['FileNote.followup_date_start']);
				}
				if (isset($filters['FileNote.followup_date_end']))
				{
					$filters['FileNote.followup_date <='] = databaseDate($filters['FileNote.followup_date_end']);
					unset($filters['FileNote.followup_date_end']);
				}
				
				$this->Session->write($sessionFilter, $filters);
			}
			else if ($this->Session->check($sessionFilter))
			{
				$filters = $this->Session->read($sessionFilter);
			}
			else
			{
				$filters = array();
			}
			
			//create the temp model
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'FileNote', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			$this->paginate = array(
				'limit' => 20,
				'page' => 1,
				'conditions' => $filters,
				'order' => 'created desc'
			);
			
			$this->{$modelName} = $tempModel;
			$records = $this->paginate($modelName);
			
			$this->set(compact('isPostback', 'records', 'accountNumber'));
		}
		
		/**
		 * Generate a user-specific temp table name
		 */
		function _customerTempTableName($username)
		{
			return 'temp_ord_memo_customer_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Container view for loading ajax_details.
		 * @param int $id The record ID to load.
		 */
		function details($id)
		{
			$this->set(compact('id'));
		}
		
		/**
		 * Load record details.
		 * @param int $id The record ID.
		 */
		function ajax_details($id)
		{
			$this->autoRenderAjax = false;
			
			$this->data = $this->FileNote->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
			
			if ($this->data !== false)
			{
				formatDatesInArray($this->data['FileNote'], array('followup_date', 'created'));
				
				$this->data['FileNote']['remarks'] = implode("\n", array(
					$this->data['FileNote']['remarks_1'],
					$this->data['FileNote']['remarks_2'],
					$this->data['FileNote']['remarks_3'],
					$this->data['FileNote']['remarks_4']
				));
			}
			
			$departments = $this->Department->getCodeList();
			$actionCodes = $this->FileNoteActionCode->getCodeList();
			
			$this->set(compact('departments', 'actionCodes'));
		}
		
		/**
		 * Get the action codes.
		 * @param string The department code to filter the action codes with.
		 */
		function json_getActionCodes($departmentCode = null)
		{
			$codes = $this->FileNoteActionCode->getCodeList($departmentCode);
			$this->set('json', compact('codes'));
		}
		
		/**
		 * Find eFN records for a specified filters.
		 */
		function module_summary()
		{
			//this can take a while to run
			set_time_limit(0);
			
			//figure out the dynamic model name for the model that will be used to grab the cached data from MySQL
			$db = ConnectionManager::getDataSource('default');
			$table = $this->_summaryTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			$isPostback = !empty($this->data) || !empty($this->params['named']);
			
			if (!empty($this->data))
			{
				//create the temp table in MySQL
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						original_id int not null,
						account_number varchar(6) not null,
						transaction_control_number varchar(6) not null,
						transaction_control_number_file varchar(1) not null,
						name varchar(30) not null,
						memo varchar(50) not null,
						remarks varchar(240) not null,
						has_remarks tinyint(1) not null,
						action_code varchar(5) not null,
						department_code varchar(1) not null,
						followup_date date null,
						invoice_number varchar(7) not null,
						followup_initials varchar(3) not null,
						priority_code varchar(3) not null,
						is_client_responsibility tinyint(1) not null,
						should_be_deleted tinyint(1) not null,
						created_by varchar(10) not null,
						created date null
					)
				");
				
				// Filter the customer records from the temp table
				$filters = Set::filter($this->postConditions($this->data));
				
				if (isset($filters['FileNote.action_code']))
				{
					$index = 'J';
				}
				if (isset($filters['FileNote.followup_date_start']))
				{
					$filters['FileNote.followup_date >='] = databaseDate($filters['FileNote.followup_date_start']);
					unset($filters['FileNote.followup_date_start']);
				}
				if (isset($filters['FileNote.followup_date_end']))
				{
					$filters['FileNote.followup_date <='] = databaseDate($filters['FileNote.followup_date_end']);
					unset($filters['FileNote.followup_date_end']);
				}
				if (isset($filters['FileNote.created_date_start']))
				{
					$filters['FileNote.created >='] = databaseDate($filters['FileNote.created_date_start']);
					unset($filters['FileNote.created_date_start']);
					$index = 'F';
				}
				if (isset($filters['FileNote.created_date_end']))
				{
					$filters['FileNote.created <='] = databaseDate($filters['FileNote.created_date_end']);
					unset($filters['FileNote.created_date_end']);
					$index = 'F';
				}
				if (isset($filters['FileNote.created_by']))
				{
					$index = 'D';
				}
				if (isset($filters['FileNote.account_number']))
				{
					$index = 'G';
				}
				if (isset($filters['FileNote.invoice_number']))
				{
					$index = 'E';
				}
				if (isset($filters['FileNote.transaction_control_number']))
				{
					$index = 'B';
				}
				
				$findArray = array(
					'contain' => array(),
					'conditions' => $filters,
				);
				
				if (isset($index))
				{
					$findArray['index'] = $index;
				}
				
				$records = $this->FileNote->find('all', $findArray);
				
				foreach ($records as $record)
				{
					$fields = array_map(array('Sanitize', 'escape'), $record['FileNote']);
					
					$db->query("
						insert into {$table} (
							original_id,
							account_number,
							transaction_control_number,
							transaction_control_number_file,
							name,
							memo,
							remarks,
							has_remarks,
							action_code,
							department_code,
							followup_date,
							invoice_number,
							followup_initials,
							priority_code,
							is_client_responsibility,
							should_be_deleted,
							created_by,
							created
						)
						values (
							" . $fields['id'] . ",
							'{$fields['account_number']}',
							'{$fields['transaction_control_number']}',
							'{$fields['transaction_control_number_file']}',
							'{$fields['name']}',
							'{$fields['memo']}',
							'" . implode(' ', array(
								$fields['remarks_1'],
								$fields['remarks_2'],
								$fields['remarks_3'],
								$fields['remarks_4']
							)) . "',
							" . ($fields['has_remarks'] ? 1 : 0) . ",
							'{$fields['action_code']}',
							'{$fields['department_code']}',
							" . ($fields['followup_date'] != '' ? "'" . databaseDate($fields['followup_date']) . "'" : "null") . ",
							'{$fields['invoice_number']}',
							'{$fields['followup_initials']}',
							'{$fields['priority_code']}',
							" . ($fields['is_client_responsibility'] ? 1 : 0) . ",
							" . ($fields['should_be_deleted'] ? 1 : 0) . ",
							'{$fields['created_by']}',
							'" . databaseDate($fields['created']) . "'
						)
					", false);
				}
			}
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'FileNote', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 20,
					'page' => 1,
					'order' => 'created desc'
				);
				
				$this->{$modelName} = $tempModel;
				$records = $this->paginate($modelName);
			}
			else
			{
				$records = array();
				$departments = $this->Department->getCodeList();
				$actionCodes = $this->FileNoteActionCode->getCodeList();
				
				$this->set(compact('departments', 'actionCodes'));
			}
			
			$this->set(compact('isPostback', 'records'));
		}
		
		/**
		 * Exports the results to CSV.
		 */
		function ajax_exportSummaryResults()
		{
			set_time_limit(0);
			$this->autoRenderAjax = false;
			
			//figure out the table to grab the results from
			$table = $this->_summaryTempTableName($this->Session->read('user')); 
			$modelName = Inflector::classify($table);
			
			//create the model
			$db = ConnectionManager::getDataSource('default');
			$cacheSources = $db->cacheSources;
			$db->cacheSources = false;
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'FileNote', 'table' => $table));
			$db->cacheSources = $cacheSources;
			
			//pull the transactions
			$query = array();
			
			//apply an order if we have one
			if (isset($this->params['named']['sort']))
			{
				$query['order'] = $this->params['named']['sort'];
				
				if (isset($this->params['named']['direction']))
				{
					$query['order'] .= ' ' . $this->params['named']['direction'];
				}
			}
			
			$this->set('records', $model->find('all', $query));
		}
		
		/**
		 * Generate a user-specific temp table name
		 */
		function _summaryTempTableName($username)
		{
			return 'temp_ord_memo_summary_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * 
		 */
		function management()
		{
			
		}
	}
?>