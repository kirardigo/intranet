<?php
	Configure::write('debug', 2);
	class BrianController extends AppController
	{
		var $components = array('DefaultFile', 'Rsh', 'Pdf');
		var $helpers = array('Permission');
		var $uses = array(
			'Document',
			'Setting', 
			'Physician', 
			'ApplicationFolder', 
			'RoleApplication', 
			'RoleApplicationFolder', 
			'Customer', 
			'Carrier', 
			'CustomerCarrier', 
			'Invoice', 
			'TransactionJournal', 
			'TransactionQueue', 
			'Transaction', 
			'Rental',
			'GeneralLedger',
			'FileNote',
			'OrderFilepro',
			'ProfitCenter',
			'County',
			'PriorAuthorization',
			'CarrierProviderNumber'
		);
		
		var $autoRender = false;
		
		function beforeFilter()
		{
			if (Configure::read('debug') == 0)
			{
//				die();
			}
			
			parent::beforeFilter();
		}
		
		function crystal()
		{
			if ($_GET["param1"] == "1")
			{
				echo '<?xml version="1.0" encoding="utf-8"?>
					<root>
						<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
							<xs:element name="parent">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="child" type="xs:string"/>
										<xs:element name="field2" type="xs:string"/>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
						</xs:schema>
						<parent>
							<child>text</child>
							<field2>abc</field2>
						</parent>
						<parent>
							<child>text 2</child>
							<field2>def</field2>
						</parent>
						<parent>
							<child>text 3</child>
							<field2>hij</field2>
						</parent>
					</root>
				';
			}
			else
			{
				echo '<?xml version="1.0" encoding="utf-8"?>
					<root>
						<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
							<xs:element name="parent">
								<xs:complexType>
									<xs:sequence>
										<xs:element name="child" type="xs:string"/>
										<xs:element name="field2" type="xs:string"/>
									</xs:sequence>
								</xs:complexType>
							</xs:element>
						</xs:schema>
						<parent>
							<child>text 4</child>
							<field2>abc</field2>
						</parent>
						<parent>
							<child>text 5</child>
							<field2>def</field2>
						</parent>
						<parent>
							<child>text 6</child>
							<field2>hij</field2>
						</parent>
					</root>
				';
			}
		}
		
		function indexTests()
		{
			set_time_limit(300);
			
			$index = $this->PriorAuthorization->find('all', array(
				'fields' => array('id', 'carrier_number'),
				'conditions' => array(
                    'carrier_number >=' => 'BU81'
                ),
                'index' => 'E'
			));
			
			$noIndex = $this->PriorAuthorization->find('all', array(
				'fields' => array('id', 'carrier_number'),
				'conditions' => array(
                    'carrier_number >=' => 'BU81'
                )
			));
			
			pr(array(count($index), count($noIndex)));
		}
		
		function efnStuff()
        {
            set_time_limit(0);

            $byName = $this->FileNote->find('all', array(
                'fields' => array('id', 'created_by', 'created'),
                'conditions' => array(
                    'created >=' => '2011-08-15',
                    'created <=' => '2011-08-20',
                    'created_by' => 'tse'
                ),
                'index' => 'D',
                'order' => 'id'
            ));

            $byDate = $this->FileNote->find('all', array(
                'fields' => array('id', 'created_by', 'created'),
                'conditions' => array(
                    'created >=' => '2011-08-15',
                    'created <=' => '2011-08-20',
                    'created_by' => 'tse'
                ),
                'index' => 'F'
            ));

            pr(array(count($byName), count($byDate)));

            pr(Set::flatten($byName));
            pr(Set::flatten($byDate));
        }
		
		function efnStuff2()
        {

            $byName = $this->FileNote->find('all', array(
                'fields' => array('id', 'created_by', 'created'),
                'conditions' => array(
                    'created' => '2011-01-24',
                    'created_by' => 'tse'
                ),
                'index' => 'D',
                'order' => 'id'
            ));

            $byDate = $this->FileNote->find('all', array(
                'fields' => array('id', 'created_by', 'created'),
                'conditions' => array(
                    'created' => '2011-01-24',
                    'created_by' => 'tse'
                ),
                'index' => 'F',
                'order' => 'id'
            ));

            pr(array(count($byName), count($byDate)));

            pr(Set::flatten($byName));
            pr(Set::flatten($byDate));
        }

		function efnStuff3()
        {
            set_time_limit(0);

            Configure::write('brian', 1);
            $byDate = $this->FileNote->find('all', array(
                'fields' => array('id', 'created_by', 'created'),
                'conditions' => array(
                    'created' => '2011-01-24',
                //  'created <=' => '2011-02-15',
                    'created_by' => 'tse'
                ),
                'index' => 'F'
            ));
        }
		
		function foo()
		{
			return 10;
		}

		function debug($account = 'A20094', $profitCenter = '020')
		{
			pr($this->requestAction("/brian/foo"));
			$b = $this->requestAction("/modules/customerCarriers/forCustomer/A20094/checkForData:1");
			
			Configure::write('debug', 1);
			pr($b ? 'yep' : 'nope');
			
			pr(array('test'));
			return;
			
			$this->autoRender = true;
			
			/*
			$p = ClassRegistry::init('Permission');
			
			if (!$p->check(2, 'General.test'))
			{
				pr('Role doesnt have access');
			}
			
			try
			{
				$p->demand(2, 'General.test');
			}
			catch (Exception $ex)
			{
				pr($ex->getMessage());
			}
			*/
			if (!$this->checkPermission('General.test'))
			{
				pr('nope');
			}
			//$this->demandPermission('General.test');
		}
		
		function importHcpc($table = 'none')
		{
			if ($table == 'hcpc' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpc', 
					'alias' => 'BjnHcpc', 
					'table' => 'HCPC',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('Hcpc');
				
				foreach ($data as $row)
				{
					$row['BjnHcpc']['id'] = false;
					$row['BjnHcpc']['is_serialized'] = $row['BjnHcpc']['is_serialized'] == null ? false : $row['BjnHcpc']['is_serialized'];
					$row['BjnHcpc']['is_active'] = $row['BjnHcpc']['is_active'] == null ? false : $row['BjnHcpc']['is_active'];
					$row['BjnHcpc']['initial_date'] = $row['BjnHcpc']['initial_date'] == '' ? null : databaseDate($row['BjnHcpc']['initial_date']);
					$row['BjnHcpc']['discontinued_date'] = $row['BjnHcpc']['discontinued_date'] == '' ? null : databaseDate($row['BjnHcpc']['discontinued_date']);
					$row['BjnHcpc']['pmd_class'] = preg_match('/Group[1-5]/i', $row['BjnHcpc']['pmd_class']) ? $row['BjnHcpc']['pmd_class'] : '';
					
					$destination->create();
					$destination->save(array('Hcpc' => $row['BjnHcpc']));
				}
			}
			
			if ($table == 'hcpc_carrier' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpc', 
					'alias' => 'BjnHcpc', 
					'table' => 'HCPC',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcCarrier');
				
				/*
				pr(array_unique(Set::extract('/BjnHcpc/allowed_units', $data)));
				die();
				*/
				
				//go through each HCPC code and create carriers per the rules below per Peggy
				foreach ($data as $row)
				{
					$row['BjnHcpc']['initial_date'] = $row['BjnHcpc']['initial_date'] == '' ? null : databaseDate($row['BjnHcpc']['initial_date']);
					$row['BjnHcpc']['discontinued_date'] = $row['BjnHcpc']['discontinued_date'] == '' ? null : databaseDate($row['BjnHcpc']['discontinued_date']);
					$row['BjnHcpc']['odjfs_initial_date'] = $row['BjnHcpc']['odjfs_initial_date'] == '' ? null : databaseDate($row['BjnHcpc']['odjfs_initial_date']);
					$row['BjnHcpc']['odjfs_discontinued_date'] = $row['BjnHcpc']['odjfs_discontinued_date'] == '' ? null : databaseDate($row['BjnHcpc']['odjfs_discontinued_date']);					
					$row['BjnHcpc']['payable_inital_replacement'] = $row['BjnHcpc']['payable_inital_replacement'] == null ? '' : $row['BjnHcpc']['payable_inital_replacement'];
					$row['BjnHcpc']['allowed_units'] = $row['BjnHcpc']['allowed_units'] == null ? '' : $row['BjnHcpc']['allowed_units'];
					$row['BjnHcpc']['odjfs_payable_initial_replacement'] = $row['BjnHcpc']['odjfs_payable_initial_replacement'] == null ? '' : $row['BjnHcpc']['odjfs_payable_initial_replacement'];
					$row['BjnHcpc']['odjfs_allowed_units'] = $row['BjnHcpc']['odjfs_allowed_units'] == null ? '' : $row['BjnHcpc']['odjfs_allowed_units'];
					
					$destination->create();
					$destination->save(array('HcpcCarrier' => array(
						'hcpc_code' => $row['BjnHcpc']['code'],
						'carrier_number' => 'MC20',
						'allowable_sale' => $row['BjnHcpc']['current_allowed_amount_new'],
						'allowable_rent' => $row['BjnHcpc']['current_allowed_amount_rent'],
						'allowable_units' => $row['BjnHcpc']['allowed_units'],
						'is_medicare_covered' => $row['BjnHcpc']['allowed_units'] != 'N/C',
						'initial_replacement' =>  $row['BjnHcpc']['payable_inital_replacement'],
						'previous_allowable_sale' => $row['BjnHcpc']['year_2_allowed_amount_new'],
						'previous_allowable_rent' => $row['BjnHcpc']['year_2_allowed_amount_rent'],
						'initial_date' => $row['BjnHcpc']['initial_date'],
						'discontinued_date' => $row['BjnHcpc']['discontinued_date'],
						'updated_date' => $row['BjnHcpc']['modified']
					)));
					
					if (!empty($row['BjnHcpc']['open_36']))
					{
						$destination->create();
						$destination->save(array('HcpcCarrier' => array(
							'hcpc_code' => $row['BjnHcpc']['code'],
							'carrier_number' => 'MD01',
							//'rp_code' => $row['BjnHcpc']['odjfs_rental_or_purchase_code'],
							'allowable_sale' => $row['BjnHcpc']['odjfs_rental_or_purchase_code'] == 'RO' ? null : $row['BjnHcpc']['odjfs_allowed_amount'],
							'allowable_rent' => $row['BjnHcpc']['odjfs_rental_or_purchase_code'] == 'RP' ? $row['BjnHcpc']['odjfs_allowed_amount'] / 10.0 : ($row['BjnHcpc']['odjfs_rental_or_purchase_code'] == 'RO' ? $row['BjnHcpc']['odjfs_allowed_amount'] : null),
							'initial_date' => $row['BjnHcpc']['odjfs_initial_date'],
							'discontinued_date' => $row['BjnHcpc']['odjfs_discontinued_date'],
							'initial_replacement' =>  $row['BjnHcpc']['odjfs_payable_initial_replacement'],
							'is_medicare_covered' => strtoupper($row['BjnHcpc']['is_medicare_covered']) == 'Y' || strtoupper($row['BjnHcpc']['is_medicare_covered']) == 'H',
							'allowable_units' => $row['BjnHcpc']['odjfs_allowed_units'],
							'updated_date' => $row['BjnHcpc']['modified']
						)));
					}
				}
			}
			
			if ($table == 'hcpc_cb' || $table == 'all')
			{
				ClassRegistry::init('HcpcCompetitiveBid')->save(array('HcpcCompetitiveBid' => array('bid_number' => 1, 'assigned_carrier_number' => 'MCC1')));
				
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpcCbZip', 
					'alias' => 'BjnHcpcCbZip', 
					'table' => 'HCPC_CB_ZIP',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcCompetitiveBidZipCode');
				
				foreach ($data as $row)
				{					
					$destination->create();
					$destination->save(array('HcpcCompetitiveBidZipCode' => array('bid_number' => 1, 'zip_code' => $row['BjnHcpcCbZip']['competitive_bid_zip_code'])));
				}
			}
			
			if ($table == 'hcpc_icd9' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpcIcd9', 
					'alias' => 'BjnHcpcIcd9', 
					'table' => 'HCPC_ICD9',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcIcd9Crosswalk');
				
				foreach ($data as $row)
				{					
					$destination->create();
					$destination->save(array('HcpcIcd9Crosswalk' => array('hcpc_code' => $row['BjnHcpcIcd9']['HCPC Code'], 'icd9_code' => $row['BjnHcpcIcd9']['ICD9 Code w/ decimal pt'])));
				}
			}
			
			if ($table == 'hcpc_message' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpcMessage', 
					'alias' => 'BjnHcpcMessage', 
					'table' => 'HCPC_MESSAGE',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcMessage');
				
				foreach ($data as $row)
				{
					$row['BjnHcpcMessage']['id'] = false;
					
					$destination->create();
					$destination->save(array('HcpcMessage' => $row['BjnHcpcMessage']));
				}
			}
			
			if ($table == 'hcpc_modifier' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpcModifier', 
					'alias' => 'BjnHcpcModifier', 
					'table' => 'HCPC_MODIFIER_MSTR',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcModifier');
				
				foreach ($data as $row)
				{
					foreach (array(array('modifier_description', 'description'), array('date_effective', 'effective_date'), array('date_termination', 'termination_date'), array('modifier_note', 'dmerc_note'), array('modifier_note_mrs', 'mrs_note')) as $translator)
					{
						$row['BjnHcpcModifier'][$translator[1]] = $row['BjnHcpcModifier'][$translator[0]];
					}

					$destination->create();
					$destination->save(array('HcpcModifier' => $row['BjnHcpcModifier']));
				}
			}
			
			if ($table == 'hcpc_modifier_assoc' || $table == 'all')
			{
				$source = ClassRegistry::init(array(
					'class' => 'BjnHcpcModifierAssoc', 
					'alias' => 'BjnHcpcModifierAssoc', 
					'table' => 'HCPC_MODIFIER',
					'ds' => 'filepro'
				));
				
				$data = $source->find('all');
				$destination = ClassRegistry::init('HcpcModifierAssociation');
				
				foreach ($data as $row)
				{				
					$destination->create();
					$destination->save(array('HcpcModifierAssociation' => array('hcpc_code' => $row['BjnHcpcModifierAssoc']['healthcare_procedure_code'], 'hcpc_modifier' => $row['BjnHcpcModifierAssoc']['hcpc_modifier'])));
				}
			}
		}
		
		function buildAppFolderPermissions()
		{
			$perms = $this->RoleApplication->find('all', array(
				'fields' => array('RoleApplication.role_id', 'RoleApplication.application_id', 'Application.application_folder_id'),
				'contain' => array('Application')
			));
			
			$this->RoleApplicationFolder->query("truncate table role_application_folders");
			
			foreach ($perms as $perm)
			{
				$folder = $this->ApplicationFolder->find('first', array(
					'conditions' => array('id' => $perm['Application']['application_folder_id']),
					'contain' => array()
				));
				
				while ($folder !== false)
				{
					$this->RoleApplicationFolder->query("
						insert ignore into role_application_folders (role_id, application_folder_id)
						values ({$perm['RoleApplication']['role_id']}, {$folder['ApplicationFolder']['id']})
					");
					
					$folder = $this->ApplicationFolder->find('first', array(
						'conditions' => array('id' => $folder['ApplicationFolder']['parent_id']),
						'contain' => array()
					));
				}
			}
		}
		
		function sample($modelName, $fullSchema = false)
		{
			$model = ClassRegistry::init($modelName);
			
			if ($fullSchema)
			{
				pr(array('Schema' => ConnectionManager::getDataSource($model->useDbConfig)->describe($model, 'all')));
			}
			else
			{
				pr(array('Schema' => array_combine(
					array_keys($model->schema()),
					Set::format($model->schema(), '(%s) %s', array('{s}.length', '{s}.fileproType'))
				)));
			}
			
			pr(array('BelongsTo' => $model->belongsTo));
			pr(array('Behaviors' => $model->actsAs));
			
			pr(array('Sample Data' => $model->find('all', array(
				'limit' => 5,
				'contain' => array()
			))));
			
			pr(array('Count' => $model->find('count')));
		}
		
		function findByField($model, $field, $value, $showHidden = false)
		{
			$results = ClassRegistry::init($model)->find('all', array('conditions' => array($field => $value), 'contain' => array()));
			
			// This can be quite slow, only use on very small result sets
			if ($showHidden)
			{
				foreach ($results as $recordKey => $record)
				{
					foreach ($record[$model] as $index => $data)
					{
						if ($data === false)
						{
							$results[$recordKey][$model][$index] = '[false]';
						}
						if ($data === null)
						{
							$results[$recordKey][$model][$index] = '[null]';
						}
					}
				}
			}
			
			pr($results);
		}
		
		function indexes($modelName)
		{	
			$model = ClassRegistry::init($modelName);
			$indexes = ConnectionManager::getDataSource($model->useDbConfig)->describe($model, 'indexes');
			
			if ($model->useDbConfig == 'filepro')
			{
				foreach ($indexes as $name => $index)
				{
					$indexes[$name] = Set::format($index->header['sort_info'], '%s (%d)', array('/field_name', '/field_length'));
					$indexes[$name]['supported'] = $index->isSupported ? 'Yes' : 'No';
				}
			}
			
			pr($indexes);
		}
		
		function test($case = 'first')
		{			
			switch ($case)
			{
				case 'all':
				
					pr(Set::extract($this->Physician->find('all'), '{n}.Physician.id'));
					break;
					
				case 'first':
				
					$doctor = $this->Physician->find('first');
					pr($doctor);
					break;
					
				case 'count':
		
					pr($this->Physician->find('count'));
					//pr($this->Physician->find('count', array('conditions' => array('id <' => 50))));
					
					//requires BL
					//pr($this->Physician->find('count', array('conditions' => array('DoctorBilling.city LIKE' => '%OHIO'))));
					//pr($this->Physician->DoctorBilling->find('count', array('conditions' => array('DoctorBilling.city LIKE' => 'CLEVELAND%'))));
					break;
					
				case 'order':
					pr(Set::combine($this->Transaction->find('all', array(
						'conditions' => array('Transaction.account_number' => 'A20094'),
						'order' => 'Invoice.account_number',
						'contain' => array('Invoice')
					)), '{n}.Transaction.id', '{n}.Invoice.account_number'));
					
					break;
				case 'page':
				
					pr($this->Physician->find('all', array(
						'page' => 5,
						'limit' => 10,
						'contain' => array()
					)));
					
					break;
					
				case 'page_order':
				
					pr($this->Physician->find('all', array(
						'page' => 3,
						'limit' => 3,
						'order' => 'id desc',
						'conditions' => array('id <=' => 50, 'name LIKE' => 'M%'),
						'contain' => array()
					)));
					
					break;
					
				case 'conditions':
				
					pr($this->Physician->find('all', array(
						'conditions' => array(
							'national_provider_identification_number' => '1861481350',
							'patient_revenue' => 101
						)
					)));
					
					break;
					
				case 'between':
					pr($this->Physician->find('all', array(
						'conditions' => array(
							'id BETWEEN' => array(1, 10)
						)
					)));
					
					break;
					
				case 'or':
				
					pr($this->Physician->find('all', array(
						'conditions' => array(
							'or' => array(
								'physician_number' => array('AG01'),
								'and' => array(
									'license_number' => '043622',
									'license_number_update_date' => '2001-05-02'
								)
							)
						)
					)));
					
					break;
					
				case 'in':
				
					/*pr($this->Physician->find('all', array(
						'conditions' => array(
							'zip_code' => array('44662', '44710'),
						)
					)));*/
					
					pr($this->Physician->find('all', array(
						'conditions' => array(
							'zip_code <>' => array('44662', '44710'),
						)
					)));
					
					break;
					
				case 'order':
				
					pr($this->Physician->find('all', array(
						'fields' => array('zip_code', 'license_number'),
						'page' => 1,
						'limit' => 100,
						'order' => array('zip_code desc', 'license_number')
					)));
					
					break;
					
				case 'create':
					
					if (!$this->Physician->save(array('Physician' => array('physician_number' => 'ABC', 'open_2' => '40.3', 'patient_revenue' => 50, 'is_fax_certificate_of_medical_necessity_allowed' => true, 'license_number_update_date' => '2010-04-21'))))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->id);
					pr($this->Physician->find('first', array('conditions' => array('id' => $this->Physician->id))));
				
					break;
					
				case 'createfp':
				
					if (!$this->Physician->saveViaFilepro(array('Physician' => array('physician_number' => 'ABC', 'open_2' => '40.3', 'patient_revenue' => 50, 'is_fax_certificate_of_medical_necessity_allowed' => true, 'license_number_update_date' => '2010-04-21'))))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->id);
					pr($this->Physician->find('first', array('conditions' => array('id' => $this->Physician->id))));
				
					break;
					
				case 'update':
					
					if (!$this->Physician->save(array('Physician' => array('id' => 8260, 'physician_number' => 'B005', 'open_4' => 760, 'is_fax_certificate_of_medical_necessity_allowed' => true))))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->id);
					pr($this->Physician->find('first', array('conditions' => array('id' => $this->Physician->id))));
				
					break;
					
				case 'updatefp':
					
					if (!$this->Physician->saveViaFilepro(array('Physician' => array('id' => 9430, 'phone_number' => '(330) 123-4567'))))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->id);
					pr($this->Physician->find('first', array('conditions' => array('id' => $this->Physician->id))));
				
					break;
				
				case 'updateAll': 
				
					$conditions = array('id <' => 10);
					
					if (!$this->Physician->updateAll(array('open_2' => 'testbruce'), $conditions))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
					
					$conditions = array('id <' => 10, 'patient_revenue' => 102);
					
					if (!$this->Physician->updateAll(array('open_2' => 'bruce102'), $conditions))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
				
					break;
					
				case 'delete':
				
					$id = 9644;
					
					pr($this->Physician->find('first', array('conditions' => array('id' => $id))));
					
					$this->Physician->id = $id;
					
					if (!$this->Physician->delete())
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('first', array('conditions' => array('id' => $id))) === false);
					
					break;
					
				case 'deletefp':
				
					$id = 9663;
					
					pr($this->Physician->find('first', array('conditions' => array('id' => $id))));
					
					$this->Physician->id = $id;
					
					if (!$this->Physician->deleteViaFilepro())
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('first', array('conditions' => array('id' => $id))) === false);
					
					break;
					
				case 'deleteAll':
				
					$conditions = array('id >' => 7660);
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
					
					if (!$this->Physician->deleteAll($conditions))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
				
					//should not work
					if (!$this->Physician->deleteAll(array()))
					{
						echo 'whew!';
					}
					
					break;
					
				case 'deletefpAll':
				
					$conditions = array('id >' => 9643);
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
					
					if (!$this->Physician->deleteAll($conditions))
					{
						echo 'uh-oh';
					}
					
					pr($this->Physician->find('all', array('conditions' => $conditions)));
				
					//should not work
					if (!$this->Physician->deleteAll(array()))
					{
						echo 'whew!';
					}
					
					break;
					
				case 'belongs': 
				
					set_time_limit(60);
					
					//Requires BL
					
					/*
					pr($this->Physician->find('all', array(
						'limit' => 5
					)));
					
					echo '<hr />';
					
					pr($this->Physician->find('all', array(
						'limit' => 5, 
						'contain' => array()
					)));
					
					echo '<hr />';
					*/
										
					pr($this->Physician->find('all', array(
						'fields' => array('Physician.id', 'Physician.billing_pointer', 'DoctorBilling.city'),
						'conditions' => array('DoctorBilling.city LIKE' => '%OHIO'),
						'limit' => 5,
						'order' => array('DoctorBilling.city desc'),
						'contain' => array('DoctorBilling')
					)));
					
				case 'chain':
				
					pr(Set::flatten($this->Carrier->find('all', array(
						'conditions' => array('carrier_number' => '0006'),
						'fields' => array('carrier_number')
					))));	
					
					echo '<hr />';
					
					echo '?';
					
					pr($this->Customer->find('all', array(
						'fields' => array('Customer.id', 'Customer.account_number'),
						'chains' => array(
							'CustomerCarrier' => array(
								'fields' => array('carrier_number', 'claim_number', 'carrier_type', 'is_active', 'Carrier.address_1'),
								'conditions' => array('CustomerCarrier.is_active' => 'Y', 'CustomerCarrier.carrier_type' => array('N', 'S')),
								'contain' => array('Carrier'),
								'order' => array('carrier_number')
							),
							'Transaction' => array('limit' => 1),
							'Invoice'
						),
						'limit' => 5
					)));
					
					echo '<hr />';
					
					pr($this->Customer->find('all', array(
						'fields' => array('Customer.id', 'Customer.account_number'),
						'conditions' => array('Customer.id' => array(10, 11, 12, 13)),
						'chains' => array(
							'CustomerCarrier' => array(
								'fields' => array('carrier_number', 'claim_number', 'carrier_type', 'is_active', 'Carrier.address_1'),
								'conditions' => array('CustomerCarrier.is_active' => 'Y', 'CustomerCarrier.carrier_type' => array('N', 'S')),
								'contain' => array('Carrier'),
								'order' => array('carrier_number')
							),
							'Transaction' => array(
								'conditions' => array('account_number' => 'N11341'),
								'limit' => 1,
								'required' => false
							)
						)
					)));
					
					break;
				
				case 'like':
				
					//pr($this->Physician->find('all', array('conditions' => array('name LIKE' => 'KING%'))));
					
					//pr($this->Physician->find('all', array('conditions' => array('name LIKE' => '%MARIE'))));
					
					pr($this->Physician->find('all', array('conditions' => array('name LIKE' => '%KING%'))));
					
					//pr($this->Physician->find('all', array('conditions' => array('zip_code LIKE' => '4412%'), 'order' => array('zip_code'))));
					
					//pr($this->Physician->find('all', array('conditions' => array('zip_code LIKE' => '%662'), 'order' => array('zip_code'))));
					
					break;
				
				case 'index':
				
					$start = microtime(true);

					pr($this->Customer->find('all', array(
						'conditions' => array(
							'account_number' => 'A20094'
						)
					)));
					
					echo (microtime(true) - $start) . ' seconds';
					
					break;
				
				case 'id_index':
				
					//in dbo_fu05.php, comment out the part that optimizes on id searches to see the
					//differences in time
				
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id' => 6000)));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id' => array(6000, 6539))));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id >' => 7500)));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id >=' => 7500)));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id <' => 10)));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					$start = microtime(true);
					$this->Physician->find('all', array('conditions' => array('id <=' => 10)));
					echo (microtime(true) - $start) . ' seconds';
					echo '<hr />';
					
					break;
					
				case 'defrag':
					
					$this->Physician->defrag();
					
					break;
				
				case 'ifnull':
				
					pr(Set::extract($this->Customer->find('all', array(
						'order' => 'ifnull(Customer.account_number, \'ZZZ\') desc',
						'limit' => 10
					)), '{n}.Customer.account_number'));
					/*
					pr(Set::format($this->Customer->find('all', array(
						'conditions' => array('Customer.name LIKE' => 'A%'),
						'order' => 'ifnull(Customer.zip_code, \'\')',
						'limit' => 10,
					)), '%s - %s', array('{n}.Customer.account_number', '{n}.Customer.zip_code')));
					*/
					break;
				case 'unchained_create':
					$id = $this->Customer->field('id', array('account_number' => 'A20094'));
					$this->CustomerCarrier->create();
					$this->CustomerCarrier->addToChain($id, array('CustomerCarrier' => array('carrier_number' => 'BJN', 'account_number' => 'A20094', 'carrier_type' => 'N')));
					
					//$this->CustomerCarrier->deleteAll(array('account_number' => 'A20094', 'carrier_number' => 'BJN'), true, true);
					break;
				case 'unchained_find':
				
					pr($this->CustomerCarrier->find('all', array(
						'conditions' => array('account_number' => 'A20094'),
						'contain' => array(),
						'unchainedIndex' => 'account_number'
					)));
				
					break;
			}
		}
		
		/**
		 * Creates a new index for an FU05 file.
		 */
		function createIndex($modelName, $field)
		{
			set_time_limit(0);
			Cache::clear();
			$model = ClassRegistry::init($modelName);
			
			if ($model->Behaviors->enabled('Indexable'))
			{
				$model->createIndex($field);
			}
			else
			{
				echo "Couldn't create index. Model is not indexable.";
			}
			
			echo 'Done';
		}
		
		function rebuildIndexes($modelToUse = null, $field = null, $unchained = false)
		{
			set_time_limit(0);
			Cache::clear();
			
			$models = array();
			
			if ($modelToUse != null)
			{
				$models[] = $modelToUse;
			}
			else
			{
				$models = Configure::listObjects('model');
			}
			
			foreach ($models as $modelName)
			{
				$model = ClassRegistry::init($modelName);
				
				if ($model->Behaviors->enabled('Indexable'))
				{
					echo "Rebuilding {$modelName}...";
					$model->rebuildIndexes($field);
					echo "Done.<br />";
				}
				else
				{
					echo "Couldn't rebuild indexes for {$modelName}. Model is not indexable.<br />";
				}
			}
			
			echo 'Done';
		}
		
		function renameMaps()
		{
			set_time_limit(0);
			$debug = false;
			
			//go grab the tab-delimited file
			$spreadsheet = '/tmp/map_files';
			$mapPath = '/u/apps/appl/filepro';
			$data = array_filter(explode("\n", file_get_contents($spreadsheet)));
			$group = array();
			
			//pop off the header row
			array_shift($data);
			
			foreach ($data as $i => $line)
			{
				//extract the row into a hash
				$fields = array_combine(
					array('new_name', 'description', 'ordinal', 'old_name', 'length', 'type', 'file'), 
					explode("\t", $line)
				);
				
				//rip out carriage returns
				$fields['file'] = preg_replace('/\r/', '', $fields['file']);
				
				//clean up Excel's escaping of quotes and quoted values if there's a comma in a TAB DELIMITED FILE (so stupid)
				$fields['old_name'] = preg_replace('/""/', '"', preg_replace('/^"(.*)"$/', '\1', $fields['old_name']));
				
				//clean up the type for decimals since Peggy had them inconsistent with how they're in the map
				if (is_numeric($fields['type']))
				{
					$fields['type'] = preg_replace(array('/^0./', '/^0$/'), array('.', '.0'), $fields['type']);
				}
				
				//if we're before the last line, and the fu05 file hasn't changed, just keep appending to our group
				if ($i < count($data) - 1 && (empty($group) || $group[count($group) - 1]['file'] == $fields['file']))
				{
					$group[] = $fields;
				}
				else
				{
					//otherwise it's a new group so it's time to process the file
					
					//if this is the last row, we need to add it to the current group
					if ($i == count($data) - 1)
					{
						$group[] = $fields;
					}
					
					$file = $group[0]['file'];
					$names = Set::extract('/new_name', $group);
					
					//de-dupe new column names by appending _N where N is a unique, incrementing number
					for ($i = count($names) - 1; $i > 0; $i--)
					{
						$dupes = count(array_filter(
							array_slice($names, 0, $i), 
							create_function('$value', "return \$value == '{$names[$i]}';")
						));
						
						if ($dupes)
						{
							$group[$i]['new_name'] = $group[$i]['new_name'] . '_' . ($dupes + 1);
						}
					}
					
					//extract the fields out indexed by position with the values being the index in the group.
					//This lets us process the fields in order
					$order = array_combine(Set::extract('/ordinal', $group), array_keys($group));
					$lastKey = array_pop(array_keys($order));
					
					//if we have all of the fields, the last key of the array should be equal to the value
					//of the last element + 1 (since the group array is zero indexed)
					if ($lastKey != $order[$lastKey] + 1)
					{
						echo "Missing fields for file {$file}. Skipping.<br />";
					}
					else
					{
						//read the map file for the table
						$map = array_filter(explode("\n", file_get_contents($mapPath . DS . $file . DS . 'map')));
						$valid = true;
						
						//put the header into our output buffer
						$output = array(array_shift($map));
						
						//U05 (Alien) map file have an extra line in the header
						if (preg_match('/^Alien/i', $output[0]))
						{
							$output[] = array_shift($map);
						}
						
						if (count($group) != count($map))
						{
							echo 'Field counts do not match spreadsheet (' . count($group) . ") in file {$file} (" . count($map) . '). Skipping.<br />';
						}
						else
						{
							//go through each field and make sure we have an equivalent match in the existing map
							foreach ($order as $fieldNumber => $groupIndex)
							{
								$existingDefinition = array_combine(
									array('field', 'length', 'type', 'junk'),
									array_map('trim', explode(':', $map[$groupIndex]))
								);
								
								$newDefinition = $group[$groupIndex];
								
								if ($existingDefinition['field'] != $newDefinition['old_name'])
								{
									echo "Incorrect field ({$fieldNumber}) name in file {$file} - '{$existingDefinition['field']}' in file does not match '{$newDefinition['old_name']}' in spreadsheet. Skipping.<br />";
									$valid = false;
									break;
								}							
								else if ($existingDefinition['length'] != $newDefinition['length'])
								{
									echo "Incorrect field ({$fieldNumber}) length in file {$file} - '{$existingDefinition['length']}' in file does not match '{$newDefinition['length']}' in spreadsheet. Skipping.<br />";
									$valid = false;
									break;
								}
								else if (strcasecmp($existingDefinition['type'], $newDefinition['type']) != 0)
								{
									echo "Incorrect field ({$fieldNumber}) type in file {$file} - '{$existingDefinition['type']}' in file does not match '{$newDefinition['type']}' in spreadsheet. Skipping.<br />";
									$valid = false;
									break;
								}
							}
							
							if ($valid)
							{
								echo "File {$file} is valid. Processing...<br />";
								
								//cool - good record, now we'll re-write the map with new field names
								foreach ($order as $fieldNumber => $groupIndex)
								{
									$newDefinition = $group[$groupIndex];
									$newName = $newDefinition['new_name'];
									
									//be sure to apply the group from the old field if we have one
													 
									if (preg_match('/^([a-z][0-9]+\))/i', $newDefinition['old_name'], $matches))
									{
										$newName = $matches[1] . ' ' . $newName;
									}

									$output[] = $newName . ':' . (trim($newDefinition['length']) == '' ? '' : str_pad($newDefinition['length'], 3, ' ', STR_PAD_LEFT)) . ':' . $newDefinition['type'] . ':';
								}
								
								if (!$debug)
								{
									copy($mapPath . DS . $file . DS . 'map', $mapPath . DS . $file . DS . 'map.bak');
									chmod($mapPath . DS . $file . DS . 'map.bak', 0777);
									file_put_contents($mapPath . DS . $file . DS . 'map', implode("\n", $output));
								}
								else
								{
									uses('Folder');
									new Folder("/tmp/fu05maps/{$file}", true);
									file_put_contents("/tmp/fu05maps/{$file}/map", implode("\n", $output));
								}
							}
						}
					}
					
					//prep the next group
					$group = array($fields);
				}
			}
			
			echo 'Done.';
		}
		
		function fileproTests($test = 'insert', $id = null)
		{
			$driver = ConnectionManager::getDataSource('filepro');
			
			switch ($test)
			{
				case 'insert':
				
					if (!$this->OrderFilepro->save(array('OrderFilepro' => array(
						'zip_code' => '11111',
						'transaction_control_number' => 222222,
						'wheeled_mobility_lmn' => 'P',
						'profit_center_number' => '020',
						'is_printed' => false,
						'is_printed_from_screen' => false,
						'work_in_process' => 'W',
						'referral_cmn' => 333333,
						'county' => 4444,
						'color_frame' => '555555555555555',
						'staff_rehab_technology_supplier' => '666',
						'purch_order_number' => '7777777777',
						'long_term_facility_number' => 888888,
						'parent_number' => '999999',
						'account_number' => 'A20094',
						'physician_number' => '0000',
						'wip_program_number' => 111111,
						'wip_program_alphasort' => '2222222222',
						'inventory_number_item_1' => '33333333333333333',
						'inventory_number_item_2' => '43333333333333333',
						'inventory_number_item_3' => '53333333333333333',
						'inventory_number_item_4' => '63333333333333333',
						'inventory_number_item_5' => '73333333333333333',
						'inventory_number_item_6' => '83333333333333333',
						'inventory_number_item_7' => '93333333333333333',
						'inventory_number_item_8' => '03333333333333333',
						'inventory_number_item_9' => '13333333333333333',
						'inventory_number_item_10' => '23333333333333333',
						'inventory_number_item_11' => '33333333333333333',
						'inventory_number_item_12' => '43333333333333333',
						'inventory_number_item_13' => '53333333333333333',
						'inventory_number_item_14' => '63333333333333333',
						'inventory_number_item_15' => '73333333333333333',
						'inventory_number_item_16' => '83333333333333333',
						'inventory_number_item_17' => '93333333333333333',
						'inventory_number_item_18' => '03333333333333333',
						'inventory_number_item_19' => '13333333333333333',
						'inventory_number_item_20' => '23333333333333333',
						'inventory_number_item_21' => '33333333333333333',
						'inventory_number_item_22' => '43333333333333333',
						'serial_number' => '55555555555555555555',
						'is_count_in_inventory' => false,
						'page_number' => 1,
						'deletion_code' => 'N',
						'message' => '6666',
						'status' => 'Y',
						'is_active' => true,
						'is_prior_authorize' => true,
						'needs_quote_date' => '20110101',
						'quantity_item_1' => 10,
						'is_item_22_e2609_or_e26171' => true
					))))
					{	
						pr('Error saving model!');
					}
					
					$mirror = $driver->createMirrorModel($this->OrderFilepro);
					
					$record = $mirror->find('first', array(
						'fields' => array('id', 'mirror_transaction_success', 'mirror_filepro_record_id'),
						'order' => 'id desc'
					));
					
					pr(Set::flatten($record));
					
					pr(Set::flatten($this->OrderFilepro->find('first', array(
						'conditions' => array('id' => $record['OrderFilepro_MIRROR']['mirror_filepro_record_id'])
					))));
															
					break;
				case 'update':
				
					//load the record
					$record = $this->OrderFilepro->find('first', array('conditions' => array('id' => $id)));
			
					//change it somehow
					if ($this->OrderFilepro->save(array('OrderFilepro' => array('id' => $record['OrderFilepro']['id'], 'inventory_number_item_1' => 'test2'))))
					{
						//load the record again to see what's changed
						$changes = $this->OrderFilepro->find('first', array('conditions' => array('id' => $id)));
						pr(Set::diff(Set::flatten($changes), Set::flatten($record)));
					}
					else
					{
						pr('Error updating model!');
					}
										
					break;
					
				case 'delete':
							
					//TODO - RIGHT HERE - why does this return false when trying to delete a record that doesn't exist?
					if ($this->OrderFilepro->deleteAll(array('id' => $id)))
					{
						$mirror = $driver->createMirrorModel($this->OrderFilepro);	
											
						pr(Set::flatten($mirror->find('first', array(
							'fields' => array('id', 'mirror_transaction_success', 'mirror_filepro_record_id'),
							'order' => 'id desc'
						))));
						
						pr($this->OrderFilepro->find('first', array('conditions' => array('id' => $id))) === false ? 'Deleted' : 'Still There');
					}
					else
					{
						pr('Error deleting model!');
						
						$mirror = $driver->createMirrorModel($this->OrderFilepro);	
						
						pr(Set::flatten($mirror->find('first', array(
							'fields' => array('id', 'mirror_transaction_success', 'mirror_filepro_record_id'),
							'order' => 'id desc'
						))));
					}
					
					break;
				
				case 'encode':	
					//pr(ByteConverter::bin2Number(ByteConverter::number2Bin(1000000, true), true));
					//pr(ByteConverter::bin2String(ByteConverter::string2Bin('abcdefghijklmnopqrstuvwxyz', true), true));
					
					pr($this->OrderFilepro->find('first', array(
						'fields' => array('created', 'created_by', 'modified', 'modified_by'),
						'conditions' => array('id' => 1),
						'contain' => array()
					)));
					
					$driver->_updateRecordHeaders($this->OrderFilepro, 1, '1983-10-06', 240, '2010-11-05', 0);
					
					pr($this->OrderFilepro->find('first', array(
						'fields' => array('created', 'created_by', 'modified', 'modified_by'),
						'conditions' => array('id' => 1),
						'contain' => array()
					)));
					
					break;
				
				case 'verify':
					
					$mirror = $driver->createMirrorModel($this->OrderFilepro);
					pr($driver->_verifyMirror($this->OrderFilepro, $mirror) === true ? 'Y' : 'N');
					break;
					
				case 'increment':
				
					$model = ClassRegistry::init('PriorAuthorizationNumber');
					/*
					$model = ClassRegistry::init(array(
						'class' => 'Bjn', 
						'alias' => 'Bjn', 
						'table' => 'BJN',
						'ds' => 'filepro'
					));
					
					$model->Behaviors->attach('Incrementable', array(
						'fields' => array(
							'x',
							'z' => array('prefixLength' => 1, 'returnIncremented' => true)
						)
					));
					
					pr($model->increment('x'));
					pr($model->increment('z'));
					pr($model->increment('foo'));
					*/
					
					pr($model->increment('authorization_number'));
					break;
			}
		}
		
		function btree()
		{
			set_time_limit(60);
			
			$db = ConnectionManager::getDataSource('filepro');
			/*
			$indexes = $db->describe($this->FileNote, 'indexes');
			$tree = new BPlusTree($indexes['index.D']);
			$tree->prettyDisplay(null, 1);
			pr($indexes['index.D']);
			*/
			
			$credit = ClassRegistry::init('Credit');
			$indexes = $db->describe($credit, 'indexes');
			$tree = new BPlusTree($indexes['index.D']);
			$tree->prettyDisplay(null, 1);
			pr($indexes['index.D']);
			
			die();

			pr(ClassRegistry::init('PriorAuthorizationFileproTest')->find('all', array(
				'fields' => array('id', 'account_num', 'carrier_num', 'transaction_control_number', 'created', 'created_by'),
				'conditions' => array('transaction_control_number' => 479553),
				'index' => 'G'
			)));
			
			/*
			pr($this->FileNote->find('all', array(
				'fields' => array('acount_number', 'transaction_control_number', 'invoice_number', 'name', 'created', 'created_by'),
				'conditions' => array('account_number' => 'A20094'),
				'index' => 'A'
			)));
			*/
			
			/*
			pr($this->OrderFilepro->find('all', array(
				'fields' => array('id', 'account_num', 'program_referral_num', 'created_by'),
				'conditions' => array('account_num like' => 'A100%'),
				'index' => 'F'
			)));
			*/
			
			/*
			pr($this->OrderFilepro->find('all', array(
				'fields' => array('id', 'account_num', 'program_referral_num', 'created_by', 'created'),
				'conditions' => array('created' => '2009-11-23'),
				'index' => 'P'
			)));
			*/
			
			/*
			pr($this->OrderFilepro->find('all', array(
				'fields' => array('id', 'account_num', 'program_referral_number', 'created_by'),
				'conditions' => array('program_referral_number' => 5058),
				'index' => 'B'
			)));
			*/
			
			/*
			pr($this->OrderFilepro->find('all', array(
				'fields' => array('id', 'account_num', 'program_referral_num'),
				'conditions' => array('id' => 19934),
			)));
			*/
			
		}
		
		function simpleTreeTest()
		{
			App::import('Vendor', 'filepro/simple_controller');
			$tree = new BPlusTree(new SimpleController(new AscendingComparer()));
			
			//I'm working off of this example:
			//http://www.scribd.com/doc/18211/B-TREE-TUTORIAL-PPT
			//which has a screen by screen walkthrough that I can verify my results against
			$tree->add("C", 1);
			$tree->add("N", 20);
			$tree->add("G", 354);
			$tree->add("A", 556);
			$tree->add("H", 958);
			$tree->add("E", 48);
			$tree->add("K", 96);
			$tree->add("Q", 495);
			$tree->add("M", 495);
			$tree->add("F", 495);
			$tree->add("W", 495);
			$tree->add("L", 495);
			$tree->add("T", 495);
			$tree->add("Z", 495);
			$tree->add("D", 495);
			$tree->add("P", 495);
			$tree->add("R", 495);
			$tree->add("X", 495);
			$tree->add("Y", 495);
			$tree->add("S", 495);
		
			$tree->prettyDisplay($tree->controller->root);
		}
		
		function port($model)
		{
			$model = ClassRegistry::init($model);
			$table = 'tom_' . $model->useTable;
			$sql = "
				create table {$table} (
					id int not null auto_increment primary key,
			";
			
			$schema = $model->schema();
			
			foreach ($schema as $column => $info)
			{
				$type = '';
				
				switch ($info['type'])
				{
					case 'boolean':
						$type = 'bool null';
						break;
					case 'date':
						$type = 'date null';
						break;
					case 'int':
						$type = 'int null';
						break;
					case 'float':
						$type = 'decimal(15, 4) null';
						break;
					default:
						$type = 'varchar(255) not null';
				}
				
				$sql .= "`{$column}` {$type},\n";
			}
			
			$sql = substr($sql, 0, strlen($sql) - 2);
			$sql .= ')';
			
			ClassRegistry::init('Setting')->query("drop table if exists {$table}");
			ClassRegistry::init('Setting')->query($sql);
			
			$ported = ClassRegistry::init(array(
				'class' => 'Tom' . $model->alias, 
				'alias' => $model->alias, 
				'table' => $table,
				'ds' => 'default'
			));
			
			$records = $model->find('all', array('limit' => 1000, 'contain' => array()));
			
			foreach ($records as $record)
			{
				$ported->create();
				$ported->save($record);
			}
		}
		
		function emcSchema()
		{
			$model = ClassRegistry::init('BillingQueue');
			$this->createEmcTable($model, 'is_good_record bool not null default 1, rental_id int, purchase_id int'); //need to push record_number field to either one of these based on rental_or_purchase field
			
			$model = ClassRegistry::init('Customer');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //relates back to billing queue by account number
			
			$model = ClassRegistry::init('ProfitCenter');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //relates by profit_center_number in customer
			
			$model = ClassRegistry::init('CustomerBilling');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve pointer (not a chain) to customer
			
			$model = ClassRegistry::init('CustomerCarrier');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve chain to customer
			
			$model = ClassRegistry::init('Carrier');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //relates to customer carrier by carrier_number field in both tables
				
			$model = ClassRegistry::init('AaaReferral');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve back to customer via customer_billing.long_term_care_facility_number
			
			$model = ClassRegistry::init('Invoice');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve the invoice based on the invoice in the billing queue record
			
			$model = ClassRegistry::init('Purchase');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve record by looking up record_number from billing queue when type is P
			
			$model = ClassRegistry::init('Rental');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve record by looking up record_number from billing queue when type is R
			
			$model = ClassRegistry::init('Physician');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve this from purchase/rental file physician_equipment_code field. If blank, resolve by CustomerBilling.physician_number
				
			$model = ClassRegistry::init('Inventory');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolve from purchase/rental inventory_number field
			
			$model = ClassRegistry::init('CertificateMedicalNecessityEquipment');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolved by looking up correct account_number, equipment_file (P or R), and equipment_file_record_number (pointer to purchase/rental) fields (all 3 pieces of info come from the billing queue record)
			
			$model = ClassRegistry::init('Oxygen');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolved by looking up correct account_number, equipment_type (P or R), and equipment_record_number (pointer to purchase/rental) fields (all 3 pieces of info come from the billing queue record)
			
			$model = ClassRegistry::init('ExtraNarrative');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolved by looking up correct account_number, transaction_type (P or R), and equipment_file_record_number (pointer to purchase/rental) fields (all 3 pieces of info come from the billing queue record)
			
			$model = ClassRegistry::init('HcpcModifier');
			$this->createEmcTable($model, 'billing_queue_id int not null'); //resolved by looking up cmodifier_1 in billing queue record
		}
		
		function createEmcTable($model, $extraSchema = '')
		{
			$table = 'emc_' . Inflector::underscore($model->alias);
			$sql = "
				create table {$table} (
					`id` int not null auto_increment primary key,
					`record_id` int not null,
					`emc_billing_batch_id` int not null" . ($extraSchema != '' ? (',' . $extraSchema) : '') . ",
			";
			
			$schema = $model->schema();
			
			foreach ($schema as $column => $info)
			{
				$type = '';
				
				if ($column == 'id') { continue; }
				
				switch ($info['type'])
				{
					case 'boolean':
						$type = 'bool null';
						break;
					case 'date':
						$type = 'date null';
						break;
					case 'datetime':
						$type = 'datetime null';
						break;
					case 'text':
						$type = 'text null';
						break;
					case 'int':
					case 'integer':
						$type = 'int null';
						break;
					case 'float':
						$type = 'decimal(' . $info['length'] . ', ' . str_replace('.', '', $info['fileproType']) . ') null';
						break;
					default:
						$type = 'varchar(' . $info['length'] . ') not null';
				}
				
				$sql .= "`{$column}` {$type},\n";
			}
			
			$sql = substr($sql, 0, strlen($sql) - 2);
			$sql .= ')';
			
			ClassRegistry::init('Setting')->query("drop table if exists {$table}");
			ClassRegistry::init('Setting')->query($sql);
		}
	}
?>