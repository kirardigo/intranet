<?php
	class AaaCallsController extends AppController
	{
		var $pageTitle = 'Aaa Calls';
	
		var $uses = array(
			'AaaCall',
			'AaaReferral',
			'County',
			'Department',
			'Lookup',
			'Note'
		);
				
		/**
		 * List the AAA Call records.
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
			
			//the user posted a new search
			if (!empty($this->data))
			{
				//create the temp table in MySQL
				$db->query("drop table if exists {$table}");
				$db->query("
					create table {$table} (
						id int not null auto_increment primary key,
						original_id int not null,
						aaa_number varchar(6) not null,
						precall_goal varchar(50) not null,
						call_date date null,
						follow_up_thank_you bool not null,
						call_type varchar(1) not null,
						sales_staff_initials varchar(3) not null,
						facility_name varchar(30) not null,
						profit_center_number varchar(3) not null,
						homecare_salesman varchar(3) not null,
						homecare_market_code varchar(3) not null,
						next_call_date date null,
						followup_complete_date date null
					)
				");
				
				//create our search conditions
				$conditions = array();
				$filters = Set::filter($this->postConditions(array('AaaCall' => $this->data['AaaCall'])));
				
				// Set the filters
				if (isset($filters['AaaCall.call_date_start']))
				{
					$filters['AaaCall.call_date >='] = databaseDate($filters['AaaCall.call_date_start']);
					unset($filters['AaaCall.call_date_start']);
				}
				if (isset($filters['AaaCall.call_date_end']))
				{
					$filters['AaaCall.call_date <='] = databaseDate($filters['AaaCall.call_date_end']);
					unset($filters['AaaCall.call_date_end']);
				}
				if (isset($filters['AaaCall.next_call_date_start']))
				{
					$filters['AaaCall.next_call_date >='] = databaseDate($filters['AaaCall.next_call_date_start']);
					unset($filters['AaaCall.next_call_date_start']);
				}
				if (isset($filters['AaaCall.next_call_date_end']))
				{
					$filters['AaaCall.next_call_date <='] = databaseDate($filters['AaaCall.next_call_date_end']);
					unset($filters['AaaCall.next_call_date_end']);
				}
				
				if (isset($filters['AaaCall.completed']))
				{
					if ($filters['AaaCall.completed'] == 0)
					{
						$filters['AaaCall.followup_complete_date'] = null;
					}
					else if ($filters['AaaCall.completed'] == 1)
					{
						$filters['AaaCall.followup_complete_date !='] = null;
					}
					
					unset($filters['AaaCall.completed']);
				}
				
				$conditions = array_merge($conditions, $filters);
				
				$findArray = array(
					'contain' => array(),
					'fields' => array(
						'id',
						'aaa_number',
						'precall_goal',
						'call_date',
						'follow_up_thank_you',
						'call_type',
						'sales_staff_initials',
						'next_call_date',
						'followup_complete_date'
					),
					'conditions' => $conditions
				);
				
				if (count(Set::filter($this->postConditions($this->data))) == 0)
				{
					$records = array();
				}
				else
				{
					$records = $this->AaaCall->find('all', $findArray);
				}

				$aaaRecords = $this->_buildFinalResultSet($records, $this->data);
				
				//insert into the temp table
				foreach ($records as $record)
				{
					$fields = array_map(array('Sanitize', 'escape'), $record['AaaCall']);
					
					$db->query("
						insert into {$table} (
							original_id,
							aaa_number,
							precall_goal,
							call_date,
							follow_up_thank_you,
							call_type,
							sales_staff_initials,
							next_call_date,
							followup_complete_date,
							facility_name,
							profit_center_number,
							homecare_salesman,
							homecare_market_code
						)
						values (
							{$fields['id']},
							'{$fields['aaa_number']}',
							'{$fields['precall_goal']}',
							'" . databaseDate($fields['call_date']) . "',
							" . ($fields['follow_up_thank_you'] ? 1 : 0) . ",
							'{$fields['call_type']}',
							'{$fields['sales_staff_initials']}',
							" . ($fields['next_call_date'] != null ? "'" . databaseDate($fields['next_call_date']) . "'" : "null") . ",
							" . ($fields['followup_complete_date'] != null ? "'" . databaseDate($fields['followup_complete_date']) . "'" : "null") . ", 
							'" . Sanitize::escape(ifset($aaaRecords[$fields['aaa_number']]['facility_name'])) . "',
							'" . Sanitize::escape(ifset($aaaRecords[$fields['aaa_number']]['profit_center_number'])) . "',
							'" . Sanitize::escape(ifset($aaaRecords[$fields['aaa_number']]['homecare_salesman'])) . "',
							'" . Sanitize::escape(ifset($aaaRecords[$fields['aaa_number']]['homecare_market_code'])) . "'
						)
					", false);
				}
			}
			
			if ($isPostback)
			{
				//create the temp model
				$cacheSources = $db->cacheSources;
				$db->cacheSources = false;
				$tempModel = ClassRegistry::init(array('class' => $modelName, 'alias' => 'AaaCall', 'table' => $table));
				$db->cacheSources = $cacheSources;
				
				$this->paginate = array(
					'limit' => 25,
					'page' => 1,
					'order' => 'call_date desc'
				);
				
				//paginate the current page
				$this->{$modelName} = $tempModel;
				$records = $this->paginate($modelName);
				
				$this->set('records', $records);
			}
			
			$profitCenters = $this->Lookup->get('profit_centers', true, true);
			$marketCodes = $this->Lookup->get('aaa_market_codes', true, true);
			
			$this->helpers[] = 'ajax';
			$this->set(compact('records', 'profitCenters', 'isPostback', 'marketCodes'));
		}
		
		/**
		 * Private method to generate a unique table name that can be used to store the cached results for the summary module.
		 * @param string $username The users username.
		 * @return string The unique table name.
		 */
		function _summaryTempTableName($username)
		{
			return 'temp_aaa_call_summary_u' . strtolower(Inflector::slug($username));
		}
		
		/**
		 * Edit a AAA Call record.
		 * @param int $id The ID of the record to edit or null to create.
		 */
		function edit($id = null)
		{
			$noteRecord = array();
			
			if (isset($this->data))
			{	
				pr($this->data);
				exit;
				$this->data['AaaCall']['call_date'] = databaseDate($this->data['AaaCall']['call_date']);
				$this->data['AaaCall']['next_call_date'] = databaseDate($this->data['AaaCall']['next_call_date']);
				$this->data['AaaCall']['followup_complete_date'] = databaseDate($this->data['AaaCall']['followup_complete_date']);
				
				if ($this->data['AaaCall']['next_call_date'] == '')
				{
					$this->data['AaaCall']['next_call_date'] = null;
				}
				
				if ($this->data['AaaCall']['followup_complete_date'] == '')
				{
					$this->data['AaaCall']['followup_complete_date'] = null;
				}
				
				if ($this->AaaCall->save($this->data))
				{
					$id = $this->AaaCall->id;
					$uri = $this->AaaCall->generateTargetUri($id);
					
					if (isset($this->data['Note']['call']['note']))
					{
						$this->Note->saveNote($uri, 'call', $this->data['Note']['call']['note']);
					}
					
					if (isset($this->data['Note']['manager']['note']))
					{
						$this->Note->saveNote($uri, 'manager', $this->data['Note']['manager']['note']);	
					}
					
					if (isset($this->data['Note']['next_call']['note']))
					{
						$this->Note->saveNote($uri, 'next_call', $this->data['Note']['next_call']['note']);
					}
					
					$this->set('close', true);			
				}
				
				$noteRecord = $this->Note->getNotes($this->AaaCall->generateTargetUri($id));
			}
			else
			{
				$this->data = $this->AaaCall->find('first', array(
					'contain' => array(),
					'conditions' => array('id' => $id)
				));
				
				if ($this->data !== false)
				{
					$this->data['AaaReferral']['facility_name'] = $this->AaaReferral->field('facility_name', array('aaa_number' => $this->data['AaaCall']['aaa_number']));
					$this->data['AaaCall']['call_date'] = formatDate($this->data['AaaCall']['call_date']);
					$noteRecord = $this->Note->getNotes($this->AaaCall->generateTargetUri($id));
				}
			}
			
			//get the call type list
			$callTypes = $this->Lookup->get('aaa_call_types');
			
			$this->set(compact('id', 'callTypes', 'noteRecord'));
			
			pr($this->data);
		}
		
		/**
		 * Exports the summary results to CSV.
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
			$model = ClassRegistry::init(array('class' => $modelName, 'alias' => 'AaaCall', 'table' => $table));
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
		 * Perform post processing on the result set.
		 * @param array $records The array or results to process.
		 * @return array The array of cached AAA records.
		 */
		function _buildFinalResultSet(&$records, &$data)
		{
			$cached = array();
			
			foreach ($records as $key => $record)
			{
				if (isset($filtered[$record['AaaCall']['aaa_number']]))
				{
					unset($records[$key]);
					continue;
				}
				
				if (isset($cached[$record['AaaCall']['aaa_number']]))
				{
					continue;
				}
				
				$aaa = $this->AaaReferral->find('first', array(
					'contain' => array(),
					'fields' => array(
						'aaa_number',
						'AaaReferral.facility_name',
						'AaaReferral.county_code',
						'AaaReferral.homecare_salesman',
						'AaaReferral.homecare_market_code'
					),
					'conditions' => array('aaa_number' => $record['AaaCall']['aaa_number'])
				));
				
				if ($aaa === false)
				{
					$filtered[$record['AaaCall']['aaa_number']] = 1;
					unset($records[$key]);
					continue;
				}
				else
				{
					$aaa['AaaReferral']['profit_center_number'] = $this->County->field('default_profit_center', array('code' => $aaa['AaaReferral']['county_code']));
					
					//remove non-matching records if we have a filter to apply
					if (!empty($data))
					{
						if ($data['AaaReferral']['profit_center_number'] != ''
							&& $data['AaaReferral']['profit_center_number'] != $aaa['AaaReferral']['profit_center_number'])
						{
							$filtered[$record['AaaCall']['aaa_number']] = 1;
							unset($records[$key]);
							continue;
						}
						
						if ($data['AaaReferral']['homecare_salesman'] != ''
							&& strtoupper($data['AaaReferral']['homecare_salesman']) != strtoupper($aaa['AaaReferral']['homecare_salesman']))
						{
							$filtered[$record['AaaCall']['aaa_number']] = 1;
							unset($records[$key]);
							continue;
						}
						
						if ($data['AaaReferral']['homecare_market_code'] != ''
							&& $data['AaaReferral']['homecare_market_code'] != $aaa['AaaReferral']['homecare_market_code'])
						{
							$filtered[$record['AaaCall']['aaa_number']] = 1;
							unset($records[$key]);
							continue;
						}
					}
					
					$cached[$record['AaaCall']['aaa_number']] = $aaa['AaaReferral'];
				}
			}
			
			return $cached;
		}
	}
?>