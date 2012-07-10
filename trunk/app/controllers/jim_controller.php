<?php
	Configure::write('debug', 1);
	class JimController extends AppController
	{
		var $uses = array('Transaction', 'Order', 'Carrier', 'Note');
		var $autoRender = false;
		
		// Make sure these don't get run in production
		function beforeRender()
		{
			if (Configure::read('debug') == 0)
			{
				die();
			}
		}
		
		function updateInventorySpecials()
		{
			$i = 0;
			$id = 0;
			
			$inventorySpecial = ClassRegistry::init('InventorySpecialOrder');
			
			$record = $inventorySpecial->find('first', array(
				'contain' => array(),
				'conditions' => array('id >' => $id)
			));
			
			while ($record !== false)
			{
				$i++;
				$id = $record['InventorySpecialOrder']['id'];
				
				$record['InventorySpecialOrder']['department_code'] = 'S';
				$record['InventorySpecialOrder']['mrs_inventory_number'] = $record['InventorySpecialOrder']['manufacturer_code'] . $record['InventorySpecialOrder']['manufacturer_inventory_number'];
				
				$inventorySpecial->create();
				$inventorySpecial->save($record);
				
				$record = $inventorySpecial->find('first', array(
					'contain' => array(),
					'conditions' => array('id >' => $id)
				));
			}
			
			pr($i);
		}
		
		function migrateMemo()
		{
			$oldModel = ClassRegistry::init('FileproMemo');
			$newModel = ClassRegistry::init('InvoiceMemo');
			
			$records = $oldModel->find('all', array(
				'contain' => array(),
				'order' => 'code'
			));
			
			$i = 0;
			
			foreach ($records as $row)
			{
				$saveData[$newModel->alias] = array(
					'code' => $row[$oldModel->alias]['code'],
					'description' => $row[$oldModel->alias]['description'],
					'memo' => implode("\n", array(
						$row[$oldModel->alias]['memo_1'],
						$row[$oldModel->alias]['memo_2'],
						$row[$oldModel->alias]['memo_3'],
						$row[$oldModel->alias]['memo_4'],
						$row[$oldModel->alias]['memo_5']
					))
				);
				
				$newModel->create();
				if ($newModel->save($saveData))
				{
					$i++;
				}
			}
			
			echo "Added {$i} new records.";
		}
		
		// Migrate form codes
		function migrateFormCodes()
		{
			$oldModel = ClassRegistry::init('FileproFormCode');
			$newModel = ClassRegistry::init('ManufacturerFormCode');
			
			$records = $oldModel->find('all', array(
				'contain' => array()
			));
			
			foreach ($records as $row)
			{
				$saveData[$newModel->alias] = array(
					'form_code' => $row[$oldModel->alias]['manufacturer_form_code'],
					'sequence_number' => $row[$oldModel->alias]['sequence_number'],
					'sequence_description' => $row[$oldModel->alias]['sequence_description']
				);
				
				$newModel->create();
				$newModel->save($saveData);
			}
		}
		
		// Migrate profit centers
		function migrateInventoryProfitCenters()
		{
			set_time_limit(0);
			
			$oldModel = ClassRegistry::init('Inventory');
			$newModel = ClassRegistry::init('InventoryProfitCenter');
			
			$id = 0;
			
			$record = $oldModel->find('first', array(
				'contain' => array(),
				'conditions' => array('id >' => $id)
			));
			
			$profitCenters = array('010', '020', '021', '050', '060', '070');
			
			while ($record != false)
			{
				foreach ($profitCenters as $pctr)
				{
					if (is_numeric($record[$oldModel->alias]["stock_level_for_profit_center_{$pctr}"]))
					{
						$saveData[$newModel->alias] = array(
							'inventory_number' => $record[$oldModel->alias]['inventory_number'],
							'profit_center_number' => $pctr,
							'stock_level' => $record[$oldModel->alias]["stock_level_for_profit_center_{$pctr}"]
						);
						
						$newModel->create();
						$newModel->save($saveData);
					}
				}
				
				$id = $record[$oldModel->alias]['id'];
				
				$record = $oldModel->find('first', array(
					'contain' => array(),
					'conditions' => array('id >' => $id)
				));
			}
		}
		
		// Clean empty records out of inventory
		function cleanInventory()
		{
			set_time_limit(0);
			
			$startTime = strtotime('now');
			pr(array(
				'start' => date('Y-m-d H:i:s', $startTime)
			));
			$inventory = ClassRegistry::init('Inventory');
			
			$id = 0;
			$count = 0;
			$deleteArray = array('', 'DELETE');
			
			$currentStart = $inventory->find('first', array(
				'contain' => array(),
				'fields' => array('id', 'inventory_number'),
				'conditions' => array(
					'id >' => $id,
					'inventory_number' => $deleteArray
				)
			));
			
			if ($currentStart !== false)
			{
				$currentEnd = $inventory->find('first', array(
					'contain' => array(),
					'fields' => array('id', 'inventory_number'),
					'conditions' => array(
						'id >' => $currentStart['Inventory']['id'],
						'inventory_number <>' => $deleteArray
					)
				));
			}
			
			while ($currentEnd !== false)
			{
				$start = $currentStart['Inventory']['id'];
				$end = $currentEnd['Inventory']['id'] - 1;
				$id = $currentEnd['Inventory']['id'];
				
				if ($inventory->deleteAll(array('id >=' => $start, 'id <=' => $end)))
//				if (1)
				{
					echo "Deleting from {$start} to {$end}<br/>";
					flush();
					$count += ($end - $start);
				}
				
				$currentStart = $inventory->find('first', array(
					'contain' => array(),
					'fields' => array('id', 'inventory_number'),
					'conditions' => array(
						'id >' => $id,
						'inventory_number' => $deleteArray
					)
				));
			
				if ($currentStart !== false)
				{
					$currentEnd = $inventory->find('first', array(
						'contain' => array(),
						'fields' => array('id', 'inventory_number'),
						'conditions' => array(
							'id >' => $currentStart['Inventory']['id'],
							'inventory_number <>' => $deleteArray
						)
					));
				}
			}
			
			$endTime = strtotime('now');
			
			pr(array(
				'start' => date('Y-m-d H:i:s', $startTime),
				'end' => date('Y-m-d H:i:s', $endTime)
			));
			echo "{$count} records deleted.";
		}
		
		// Reorganize menu
		function migrateMenu()
		{
			$folder = ClassRegistry::init('ApplicationFolder');
			$app = ClassRegistry::init('Application');
			$permission = ClassRegistry::init('RoleApplicationFolder');
			
			// Remove applications that nobody can see
			$data = $app->query("
				select *
				from applications Application
				left join role_applications RoleApplication on RoleApplication.application_id = Application.id
				where RoleApplication.id is null
			");
			
			foreach ($data as $record)
			{
				$app->delete($record[$app->alias]['id']);
			}
			
			// Change DataEntry folder name
			$saveData[$folder->alias] = array(
				'id' => 2,
				'folder_name' => 'CCS'
			);
			$folder->create();
			$folder->save($saveData);
			
			// Move Reports to Claims folder
			$saveData[$folder->alias] = array(
				'id' => 6,
				'parent_id' => 14
			);
			$folder->create();
			$folder->save($saveData);
			
			// Move Purges to Utilities
			$saveData[$folder->alias] = array(
				'id' => 9,
				'parent_id' => 15
			);
			$folder->create();
			$folder->save($saveData);
			
			// Move Default File App
			$saveData[$app->alias] = array(
				'id' => $app->field('id', array('name' => 'Default File')),
				'application_folder_id' => 15
			);
			$app->create();
			$app->save($saveData);
			
			// Move Settings App
			$saveData[$app->alias] = array(
				'id' => $app->field('id', array('name' => 'Settings')),
				'application_folder_id' => 15
			);
			$app->create();
			$app->save($saveData);
			
			// Create Data Files & Master Files folders
			$saveData[$folder->alias] = array(
				'parent_id' => 13,
				'folder_name' => 'Data Files'
			);
			$folder->create();
			if ($folder->save($saveData))
			{
				foreach (array(1, 2, 3) as $roleID)
				{
					$saveData[$permission->alias] = array(
						'application_folder_id' => $folder->id,
						'role_id' => $roleID
					);
					$permission->create();
					$permission->save($saveData);
				}
			}
			$saveData[$folder->alias] = array(
				'parent_id' => 13,
				'folder_name' => 'Master Files'
			);
			$folder->create();
			if ($folder->save($saveData))
			{
				foreach (array(1, 2, 3) as $roleID)
				{
					$saveData[$permission->alias] = array(
						'application_folder_id' => $folder->id,
						'role_id' => $roleID
					);
					$permission->create();
					$permission->save($saveData);
				}
			}
			
			// Move apps to Data Files
			$appNames = array(
				'Client Report' => 'Client Report',
				'Customer Owned Equipment' => 'Customer Owned Equipment',
				'Invoice Report' => 'Invoice Report',
				'Prior Auths' => 'Prior Auths',
				'Rental Report' => 'Rental Equipment Report',
				'Respiratory Report' => 'Respiratory Report',
				'Transaction Report' => 'Transaction Report',
			);
			foreach ($appNames as $appName => $newName)
			{
				$id = $app->field('id', array('name' => $appName));
				
				if ($id === false)
				{
					continue;
				}
				
				$saveData[$app->alias] = array(
					'id' => $id,
					'application_folder_id' => $folder->field('id', array('folder_name' => 'Data Files')),
					'name' => $newName
				);
				$app->create();
				$app->save($saveData);
			}
			
			// Move apps to Data Files
			$appNames = array(
				'Carriers' => 'Carriers',
				'Diagnosis' => 'Diagnoses',
				'General Ledger' => 'General Ledger',
				'Physician' => 'Physicians',
				'Prior Auth Denials' => 'Prior Auth Denial Codes',
				'Vendors' => 'Vendors',
			);
			foreach ($appNames as $appName => $newName)
			{
				$id = $app->field('id', array('name' => $appName));
				
				if ($id === false)
				{
					continue;
				}
				
				$saveData[$app->alias] = array(
					'id' => $id,
					'application_folder_id' => $folder->field('id', array('folder_name' => 'Master Files')),
					'name' => $newName
				);
				$app->create();
				$app->save($saveData);
			}
			
			// Move HCPC folder if it exists
			$hcpcID = $folder->field('id', array('folder_name' => 'HCPC'));
			
			if ($hcpcID !== false)
			{
				$saveData[$folder->alias] = array(
					'id' => $hcpcID,
					'parent_id' => 13
				);
				$folder->create();
				$folder->save($saveData);
			}
		}
		
		function migrateInventorySpecials()
        {
            $oldModel = ClassRegistry::init('FileproSpecial');
            $newModel = ClassRegistry::init('InventorySpecialOrder');

            $data = $oldModel->find('all', array(
                'contain' => array()
            ));

            $i = 0;

            foreach ($data as $row)
            {
                $saveData['InventorySpecialOrder'] = $row[$oldModel->alias];
                $saveData[$newModel->alias]['original_purchase_order_number'] = trim($row[$oldModel->alias]['original_purchase_order_number']);
				
                $newModel->create();
                if ($newModel->save($saveData))
                {
                    $i++;
                }
            }

            pr($i);
        }
		
		function migrateAaaCall()
		{
			set_time_limit(0);

			$callModel = ClassRegistry::init('FileproAaaCall');

			$data = $callModel->find('all', array(
				'contain' => array()
			));

			$i = 0;

			//$this->AaaCall->query('truncate table aaa_calls');

			foreach ($data as $row)
			{
				if ($row[$callModel->alias]['aaa_number'] == null)
				{
					continue;
				}

				$saveData['AaaCall'] = array(
					'aaa_number' => $row[$callModel->alias]['aaa_number'],
					'precall_goal' => $row[$callModel->alias]['precall_goal'],
					'call_date' => databaseDate($row[$callModel->alias]['call_date']),
					'follow_up_thank_you' => ($row[$callModel->alias]['follow_up_thank_you'] == null ? 0 : $row[$callModel->alias]['follow_up_thank_you']),
					'call_type' => $row[$callModel->alias]['call_type'],
					'sales_staff_initials' => $row[$callModel->alias]['sales_staff_initials']
				);

				if ($row[$callModel->alias]['next_call_date'] != '')
				{
					$saveData['AaaCall']['next_call_date'] = databaseDate($row[$callModel->alias]['next_call_date']);
				}
				if ($row[$callModel->alias]['followup_complete_date'] != '')
				{
					$saveData['AaaCall']['followup_complete_date'] = databaseDate($row[$callModel->alias]['followup_complete_date']);
				}

				$this->AaaCall->create();
				if ($this->AaaCall->save($saveData))
				{
					$target = $this->AaaCall->generateTargetUri($this->AaaCall->id);
					$this->Note->saveNote($target, 'call', trim(implode(' ', array(
						$row[$callModel->alias]['call_notes_1'],
						$row[$callModel->alias]['call_notes_2'],
						$row[$callModel->alias]['call_notes_3'],
						$row[$callModel->alias]['call_notes_4'],
						$row[$callModel->alias]['call_notes_5']
					))));
					$this->Note->saveNote($target, 'manager', trim(implode(' ', array(
						$row[$callModel->alias]['manager_assist_1'],
						$row[$callModel->alias]['manager_assist_2']
					))));
					$this->Note->saveNote($target, 'next_call', trim(implode(' ', array(
						$row[$callModel->alias]['next_call_note_1'],
						$row[$callModel->alias]['next_call_note_2']
					))));
				}

				$i++;
			}

			echo "{$i} Records have been created.";
		}

		function migrateMeu()
		{
			$meuModel = ClassRegistry::init('FileproMeu');

			$data = $meuModel->find('all', array(
				'contain' => array()
			));

			$i = 0;

			foreach ($data as $row)
			{
				$saveData['StaffEducation'] = array(
					'username' => $row['FileproMeu']['username'],
					'staff_education_course_id' => $this->StaffEducationCourse->field('id', array('meu_number' => $row['FileproMeu']['meu_number'])),
					'profit_center_number' => $row['FileproMeu']['profit_center_number'],
					'department_code' => $row['FileproMeu']['department_code'],
					'date_completed' => $row['FileproMeu']['date']
				);

				$this->StaffEducation->create();
				$this->StaffEducation->save($saveData);

				$i++;
			}

			echo "{$i} Records have been created.";
		}

		function migrateMeuCourse()
		{
			$meuisModel = ClassRegistry::init('FileproMeuIs');

			$data = $meuisModel->find('all', array(
				'contain' => array()
			));

			$i = 0;

			foreach ($data as $row)
			{
				if (strlen($row['FileproMeuIs']['remarks_1']) > 0 &&
					strlen($row['FileproMeuIs']['remarks_2']) > 0)
				{
					$description = $row['FileproMeuIs']['remarks_1'] . ' ' . $row['FileproMeuIs']['remarks_2'];
				}
				else
				{
					$description = $row['FileproMeuIs']['remarks_1'] . $row['FileproMeuIs']['remarks_2'];
				}

				$saveData['StaffEducationCourse'] = array(
					'meu_number' => $row['FileproMeuIs']['meu_number'],
					'title' => $row['FileproMeuIs']['description'],
					'description' => $description,
					'has_handouts' => ($row['FileproMeuIs']['has_handouts'] ? 1 : 0),
					'presenters' => $row['FileproMeuIs']['presenters'],
					'credit_hours' => $row['FileproMeuIs']['hours'],
					'confirmation_method' => $row['FileproMeuIs']['confirmation_method']
				);

				$this->StaffEducationCourse->create();
				$this->StaffEducationCourse->save($saveData);

				$i++;
			}

			echo "{$i} Records have been created.";
		}
		
		function cleanCarriers()
		{
			pr($this->Carrier->find('count'));
			
			$records = $this->Carrier->find('all', array(
				'contain' => array(),
				'fields' => array(
					'id',
					'carrier_number',
					'name'
				),
				'conditions' => array(
					'carrier_number' => ''
				)
			));
			
			foreach ($records as $row)
			{
				$this->Carrier->delete($row['Carrier']['id']);
			}
			
			pr($this->Carrier->find('count'));
		}
		
		function populateCarrierNotes()
		{
			set_time_limit(0);
			
			$modelName = 'Carrier';
			
			$fields = array(
				'claims' => array(
					'notes_9',
					'notes_10',
					'notes_13',
					'notes_14',
					'notes_15',
					'notes_16',
					'notes_17',
					'notes_18'
				),
				'homecare' => array(
					'notes_12',
					'notes_11',
					'notes_1',
					'notes_2',
					'notes_3',
					'notes_4',
					'notes_5'
				),
				'auth' => array(
					'authorization_information',
					'notes_6',
					'notes_7',
					'notes_8',
					'notes_19',
					'notes_20',
					'notes_21'
				)
			);
			
			$noteModel = ClassRegistry::init($modelName);
			$currentID = 0;
			$i = 0;
			$delimiter = " ";
			
			$record = $noteModel->find('first', array(
				'contain' => array(),
				'conditions' => array('id >' => $currentID)
			));
			
			while ($record !== false)
			{
				$currentID = $record[$modelName]['id'];
				
				$text = '';
				
				foreach ($fields as $noteType => $field)
				{
					$textValues = '';
					
					if (is_array($field))
					{
						$values = array();
						
						foreach ($field as $fieldToJoin)
						{
							$values[] = $record[$modelName][$fieldToJoin];
						}
						
						$textValues = implode($delimiter, $values);
					}
					else
					{
						$textValues = $record[$modelName][$field];
					}
					
					if (trim($textValues) !== '')
					{
						$text .= "****************************************\n";
						$text .= "* " . strtoupper($noteType) . " NOTES FROM UNIX\n";
						$text .= "****************************************\n";
						$text .= $textValues;
						$text .= "\n\n";
					}
				}
				
				$oldNote = $this->Note->getNotes($this->Carrier->generateTargetUri($currentID), 'claims');
				$totalText = isset($oldNote['claims']) ? $oldNote['claims']['note'] . "\n\n" . $text : $text;
				
				if (trim($totalText) !== '')
				{	
					$this->Note->saveNote($noteModel->generateTargetUri($currentID), 'claims', $totalText);
					$i++;
				}
				
				$record = $noteModel->find('first', array(
					'contain' => array(),
					'conditions' => array('id >' => $currentID)
				));
			}
			
			echo "Created {$i} new note records";
		}
		
		function populateCOENotes()
		{
			set_time_limit(0);
			
			$modelName = 'CustomerOwnedEquipment';
			
			$fields = array(
				'general' => array(
					'notes_1',
					'notes_2',
					'notes_3'
				)
			);
			
			$noteModel = ClassRegistry::init($modelName);
			$currentID = 0;
			$i = 0;
			$delimiter = " ";
			
			$record = $noteModel->find('first', array(
				'contain' => array(),
				'conditions' => array('id >' => $currentID)
			));
			
			while ($record !== false)
			{
				$currentID = $record[$modelName]['id'];
				
				foreach ($fields as $noteType => $field)
				{
					$text = '';
					
					if (is_array($field))
					{
						$values = array();
						
						foreach ($field as $fieldToJoin)
						{
							$values[] = $record[$modelName][$fieldToJoin];
						}
						
						$text = implode($delimiter, $values);
					}
					else
					{
						$text = $record[$modelName][$field];
					}
					
					if (trim($text) !== '')
					{
						$this->Note->saveNote($noteModel->generateTargetUri($currentID), $noteType, $text);
						$i++;
					}
				}
				
				$record = $noteModel->find('first', array(
					'contain' => array(),
					'conditions' => array('id >' => $currentID)
				));
			}
			
			echo "Created {$i} new note records";
		}
		
		function checkCustomerCarriers($accountNumber = 'A20094')
		{
			$carrierPointer = $this->Customer->field('carrier_pointer', array('account_number' => $accountNumber));
			
			while ($carrierPointer != 0)
			{
				$record = $this->CustomerCarrier->find('first', array(
					'contain' => array(),
					'fields' => array(
						'carrier_number',
						'carrier_type',
						'is_active',
						'next_record_pointer'
					),
					'conditions' => array('id' => $carrierPointer)
				));
				
				$records[] = $record;
				
				$carrierPointer = $record['CustomerCarrier']['next_record_pointer'];
			}
			pr($records);
		}
		
		function checkInvoices($accountNumber = 'A45800')
		{
			$invoiceCount = $this->Invoice->find('count', array(
				'conditions' => array('account_number' => $accountNumber)
			));
			
			$i = 0;
			$invoicePointer = $this->Customer->field('invoice_pointer', array('account_number' => $accountNumber));
			
			while ($invoicePointer != 0 && $i < 25)
			{
				$record = $this->Invoice->find('first', array(
					'contain' => array(),
					'fields' => array(
						'account_number',
						'invoice_number',
						'date_of_service',
						'billing_date',
						'amount',
						'account_balance',
						'carrier_1_code',
						'carrier_1_balance',
						'carrier_2_code',
						'carrier_2_balance',
						'carrier_3_code',
						'carrier_3_balance',
						'next_record_pointer'
					),
					'conditions' => array('id' => $invoicePointer)
				));
				
				$records[] = $record;
				$i++;
				
				$invoicePointer = $record['Invoice']['next_record_pointer'];
			}
			pr("Showing 25 of {$invoiceCount}");
			pr($records);
		}
		
		function checkTransactions($accountNumber = 'A45800', $invoiceNumber = null)
		{
			$transactionCount = $this->Transaction->find('count', array(
				'conditions' => array('account_number' => $accountNumber)
			));
			
			$i = 0;
			$limit = 1000;
			$transactionPointer = $this->Customer->field('transaction_pointer', array('account_number' => $accountNumber));
			
			while ($transactionPointer != 0 && $i < $limit)
			{
				$record = $this->Transaction->find('first', array(
					'contain' => array(),
					'fields' => array(
						'account_number',
						'invoice_number',
						'transaction_date_of_service',
						'transaction_type',
						'amount',
						'account_balance',
						'carrier_number',
						'carrier_balance_due',
						'next_record_pointer'
					),
					'conditions' => array('id' => $transactionPointer)
				));
				
				if ($record['Transaction']['carrier_number'] == $invoiceNumber || $invoiceNumber == null)
				{
					$records[] = $record;
					$i++;
				}
				
				$transactionPointer = $record['Transaction']['next_record_pointer'];
			}
			
			pr("Showing {$i} of roughly {$transactionCount}");
			pr($records);
		}
		
		// Switch users during testing
		function impersonate($username)
		{
			$this->autoRender = false;
			$this->Session->write('user', $username);
			
			$userModel = ClassRegistry::init('User');
			
			$userRecord = $userModel->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'username' => $this->data['User']['username']
				)
			));
			
			if ($userRecord !== false)
			{
				$this->Session->write('userInfo', $userRecord['User']);
			}
			
			$this->flash("You are now impersonating {$username}", '/');
		}
		
		// Switch roles during testing
		function role($roleID)
		{
			$this->autoRender = false;
			$info = $this->Session->read('userInfo');
			
			$info['role_id'] = $roleID;
			
			$this->Session->write('userInfo', $info);
			
			$this->flash("You are now using role {$roleID}", '/');
		}
		
		// Create a folder in the navigation menu
		function createFolder($name, $parentID)
		{
			$folderModel = ClassRegistry::init('ApplicationFolder');
			
			pr($folderModel->save(array(
				'ApplicationFolder' => array(
					'parent_id' => $parentID,
					'folder_name' => $name
				)
			)));
		}
	}
?>
