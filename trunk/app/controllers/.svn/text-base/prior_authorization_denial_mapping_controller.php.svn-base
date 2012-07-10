<?php
	class PriorAuthorizationDenialMappingController extends AppController
	{
		var $uses = array(
			'PriorAuthorizationDenialMapping',
			'PriorAuthorizationDenial',
			'PriorAuthorization'
		);
		
		/**
		 * Render a table of the associated denials.
		 * @param int $priorAuthorizationID The prior authorization record ID.
		 */
		function ajax_table($priorAuthorizationID)
		{
			$this->autoRenderAjax = false;
			
			$priorAuthorizationNumber = $this->PriorAuthorization->field('authorization_id_number', array('id' => $priorAuthorizationID));
			
			$denialCodes = $this->PriorAuthorizationDenialMapping->find('all', array(
				'contain' => array(),
				'conditions' => array('authorization_id_number' => $priorAuthorizationNumber),
				'index' => 'A'
			));
			
			foreach ($denialCodes as $i => $denialCodeRecord)
			{
				$denialCode = str_pad($denialCodeRecord['PriorAuthorizationDenialMapping']['denial_code'], 3, '0', STR_PAD_LEFT);
				
				$denialRecord = $this->PriorAuthorizationDenial->find('first', array(
					'contain' => array(),
					'conditions' => array('code' => $denialCode),
					'index' => 'A'
				));
				
				$this->data['PriorAuthorizationDenial'][] = array(
					'id' => $denialCodeRecord['PriorAuthorizationDenialMapping']['id'],
					'code' => $denialCode,
					'description' => ($denialRecord === false) ? '' : $denialRecord['PriorAuthorizationDenial']['description']
				);
			}
			
			$this->set(compact('priorAuthorizationID'));
		}
		
		/**
		 * Delete a mapping record.
		 */
		function json_delete($id)
		{
			$this->PriorAuthorizationDenialMapping->delete($id);
			
			$this->set('json', array('success' => 1));
		}
		
		/**
		 * Add a mapping record.
		 * @param int $priorAuthorizationID The ID of the prior authorization record.
		 * @param int $denialCode The denial code.
		 */
		function json_add($priorAuthorizationID, $denialCode)
		{
			$success = 1;
			
			$saveData['PriorAuthorizationDenialMapping'] = array(
				'authorization_id_number' => $priorAuthorizationID,
				'denial_code' => $denialCode
			);
			
			$this->PriorAuthorizationDenialMapping->save($saveData);
			
			$this->set('json', array('success' => $success));
		}
	}
?>