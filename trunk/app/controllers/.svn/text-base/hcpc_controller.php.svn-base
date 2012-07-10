<?php
	class HcpcController extends AppController
	{
		//set the page title
		var $pageTitle = "HCPC";
		
		//get the models we are using
		var $uses = array(
			'Hcpc', 
			'Lookup',
			'HcpcModifier',
			'HcpcModifierAssociations',
			'HcpcIcd9Crosswalk',
			'HcpcCarrier'
		);
		
		var $helpers = array('Ajax');
		
		/**
		 * Displays all the hcpc records. The default action.
		 */
		function index()
		{
			$postDataName = 'HcpcPost';
			$filterName = 'HcpcFilter';
			$conditions = array();
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Hcpc.initial_date']))
				{
					$conditions['Hcpc.initial_date ='] = databaseDate($conditions['Hcpc.initial_date']);
					unset($conditions['Hcpc.initial_date']);
				}
				if (isset($conditions['Hcpc.discontinued_date']))
				{
					$conditions['Hcpc.discontinued_date ='] = databaseDate($conditions['Hcpc.discontinued_date']);
					unset($conditions['Hcpc.discontinued_date']);
				}
				if (isset($conditions['Hcpc.description']))
				{
					$conditions['Hcpc.description like'] = '%' . $conditions['Hcpc.description'] . '%';
					unset($conditions['Hcpc.description']);
				}
				
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
				'order' => 'code'
			);
			
			$this->set('records', $this->paginate('Hcpc'));
		}
		
		/**
		 * Host action for the view module.
		 * @param string $code The HCPC code to view.
		 */
		function view($code)
		{
			$this->set('code', $code);
		}
		
		/*
		 * Module to view HCPC code detail.
		 * @param string $code The HCPC code to view.
		 */
		function module_view($code)
		{
			$this->data = $this->Hcpc->find('first', array(
				'contain' => array(),
				'conditions' => array('code' => $code)
			));
			
			//format the date fields for nice display
			$this->data['Hcpc']['initial_date'] = formatDate($this->data['Hcpc']['initial_date']);
			$this->data['Hcpc']['discontinued_date'] = formatDate($this->data['Hcpc']['discontinued_date']);
			
			//pull ICD9 crosswalks
			$icd9Records = $this->HcpcIcd9Crosswalk->find('all', array(
				'contain' => array(),
				'conditions' => array('hcpc_code' => $code)
			));
			
			$this->set('icd9Records', $icd9Records);
		}
		
		/**
		 * Displays all the hcpc records for management.
		 */
		function management()
		{
			$postDataName = 'HcpcPostManagement';
			$filterName = 'HcpcFilterManagement';
			$conditions = array();
			
			if (!empty($this->data))
			{
				//filter the results however the user wanted
				$conditions = Set::filter($this->postConditions($this->data));
				
				if (isset($conditions['Hcpc.initial_date']))
				{
					$conditions['Hcpc.initial_date ='] = databaseDate($conditions['Hcpc.initial_date']);
					unset($conditions['Hcpc.initial_date']);
				}
				if (isset($conditions['Hcpc.discontinued_date']))
				{
					$conditions['Hcpc.discontinued_date ='] = databaseDate($conditions['Hcpc.discontinued_date']);
					unset($conditions['Hcpc.discontinued_date']);
				}
				if (isset($conditions['Hcpc.description']))
				{
					$conditions['Hcpc.description like'] = '%' . $conditions['Hcpc.description'] . '%';
					unset($conditions['Hcpc.description']);
				}
				
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
				'order' => 'code'
			);
			
			$this->set('records', $this->paginate('Hcpc'));
		}
				
		function ajax_autoComplete()
		{
			$recentSearch = 'icd9';

			if ($recentSearch == '')
			{
				die();
			}
			
			$matches = $this->Icd9Crosswalk->find('all', array(
				'contain' => array(),
				'fields' => array('id', 'icd9_code'),
				'conditions' => array(
					'icd9_code like' => $recentSearch . '%'
				)
			));
			
			$this->set('output', array(
				'data' => $matches, 
				'id_field' => 'Icd9Crosswalk.id', 
				'value_fields' => array('Icd9Crosswalk.icd9_code'),
				'informal_fields' => array('Icd9Crosswalk.icd9_code')
			));
		}
		
		/**
		 * AJAX action to get a HCPC description.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		code The HCPC code to find the description for.
		 */
		function ajax_description()
		{
			$match = $this->Hcpc->field('description', array('code' => $this->params['form']['code']));
			$this->set('output', $match !== false ? $match : '');
		}
		
		/**
		 * Gets the associated icd9 crosswalk records.
		 */
		function module_icd9_crosswalks($code)
		{
			$this->helpers[] = 'ajax';
		
			//get the icd9 crosswalk values associated with this hcpc record
			$icd9Records = $this->HcpcIcd9Crosswalk->find('all', array(
				'contain' => array(),
				'conditions' => array('hcpc_code' => $code)
			));
			
			$this->set(compact('icd9Records', 'code'));
		}
		
		/**
		 * Gets the associated hcpc carriers records.
		 */
		function module_hcpc_carriers($code)
		{
			//set and return the hcpc results
			$hcpcCarriers = $this->HcpcCarrier->find('all', array(
				'contain' => array(),
				'conditions' => array('hcpc_code' => $code)
			));
			
			//see if we should render the display as read only and if we should show the HCPC code
			$readonly = isset($this->params['named']['readonly']) && $this->params['named']['readonly'];
			$showCode = isset($this->params['named']['showcode']) && $this->params['named']['showcode'];
			
			$this->set(compact('hcpcCarriers', 'code', 'readonly', 'showCode'));
		} 
		
		/**
		 * Get the carrier detail form for a particular Hcpc carrier.
		 * @param int $id The ID of the HCPC carrier record.
		 */
		function ajax_carrierDetail($id)
		{	
			$this->autoRenderAjax = false;
			
			//grab the carrier
			$this->data = $this->HcpcCarrier->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'id' => $id
				)
			));
			
			if ($this->data !== false)
			{
				$carrierModel = ClassRegistry::init('Carrier');
				$this->data['Hcpc']['description'] = $this->Hcpc->field('description', array('code' => $this->data['HcpcCarrier']['hcpc_code']));
				$this->data['Carrier']['name'] = $carrierModel->field('name', array('carrier_number' => $this->data['HcpcCarrier']['carrier_number']));
			}
			
			//format dates for nice views
			$this->data['HcpcCarrier']['initial_date'] = formatDate($this->data['HcpcCarrier']['initial_date']);
			$this->data['HcpcCarrier']['discontinued_date'] = formatDate($this->data['HcpcCarrier']['discontinued_date']);
			$this->data['HcpcCarrier']['updated_date'] = formatDate($this->data['HcpcCarrier']['updated_date']);
			
			//get the hcpc modifier records associated with this carrier
			$modifiers = $this->HcpcModifierAssociations->find('all', array(
				'contain' => array(),
				'conditions' => array(
					'hcpc_code' => $this->data['HcpcCarrier']['hcpc_code'],
					'carrier_number' => $this->data['HcpcCarrier']['carrier_number']
				),
				'order' => 'hcpc_modifier'
			));
			
			//get the modifiers list
			$modifiersList = $this->HcpcModifier->find('all', array(
				'contain' => array(),
				'fields' => array(
					'modifier',
					'description'
				),
				'order' => 'modifier'
			));
			
			//setup an array of our modifiers
			foreach($modifiersList as $modifier)
			{
				$modifierDropDown[$modifier['HcpcModifier']['modifier']] = $modifier['HcpcModifier']['modifier'] . ' - ' . $modifier['HcpcModifier']['description'];
			}
			
			//get the lookups
			$initialReplacement = $this->Lookup->get('initial_replacement');
			array_unshift($initialReplacement, array('' => '-Select one-'));
			
			$rpCodes = $this->Lookup->get('rp_codes');
			array_unshift($rpCodes, array('' => '-Select one-'));
			
			//see if we should render the display as read only
			$readonly = isset($this->params['named']['readonly']) && $this->params['named']['readonly'];
			
			$this->set(compact('modifiers', 'modifierDropDown', 'initialReplacement', 'rpCodes', 'readonly'));
			
			$this->helpers[] = 'ajax';
		}
		
		/**
		 * Gets the current hcpc record.
		 */
		function module_hcpc($code) {
		
			$this->data = $this->Hcpc->find('first', array(
				'contain' => array(),
				'conditions' => array('code' => $code)
			));
			
			//format the date fields for nice display
			$this->data['Hcpc']['initial_date'] = formatDate($this->data['Hcpc']['initial_date']);
			$this->data['Hcpc']['discontinued_date'] = formatDate($this->data['Hcpc']['discontinued_date']);
		
			//get the 6 point classification lookups
			$sixPointClassification = $this->Lookup->get('6_point_classification');
			array_unshift($sixPointClassification, array('' => '-Select one-'));
			
			//grab the pmd lookups
			$pmdClasses = $this->Lookup->get('pmd_class');
			array_unshift($pmdClasses, array('' => '-Select one-'));
			
			$this->set(compact('sixPointClassification', 'pmdClasses'));
		}
		
		/**
		 * Adds a new Hcpc record.
		 */
		function add()
		{
			//the form has data and is being submitted
			if (isset($this->data))
			{
				$this->data['Hcpc']['initial_date'] = trim($this->data['Hcpc']['initial_date']) == '' ? null : databaseDate($this->data['Hcpc']['initial_date']);
				$this->data['Hcpc']['discontinued_date'] = trim($this->data['Hcpc']['discontinued_date']) == '' ? null : databaseDate($this->data['Hcpc']['discontinued_date']);
				
				if ($this->Hcpc->save($this->data))
				{
					$this->redirect('/hcpc/index');
				}
			}
		
			//get the 6 point classification lookups
			$sixPointClassification = $this->Lookup->get('6_point_classification');
			array_unshift($sixPointClassification, array('' => '-Select one-'));
			
			//grab the pmd lookups
			$pmdClasses = $this->Lookup->get('pmd_class');
			array_unshift($pmdClasses, array('' => '-Select one-'));
			
			$this->set(compact('sixPointClassification', 'pmdClasses'));
		}
		
		/**
		 * Edit a Hcpc record and related info.
		 * @param int $id The ID of the hcpc record.
		 */
		function edit($id)
		{
			$this->data = $this->Hcpc->find('first', array(
				'contain' => array(),
				'conditions' => array('id' => $id)
			));
		}
		
		function json_edit($id)
		{
			//form submission
			if (isset($this->data) && isset($this->data['Hcpc']))
			{
				$result = array('success' => true);
				
				//format the dates to database format
				$this->data['Hcpc']['initial_date'] = trim($this->data['Hcpc']['initial_date']) == '' ? null : databaseDate($this->data['Hcpc']['initial_date']);
				$this->data['Hcpc']['discontinued_date'] = trim($this->data['Hcpc']['discontinued_date']) == '' ? null : databaseDate($this->data['Hcpc']['discontinued_date']);
			
				$result['success'] = !!$this->Hcpc->save($this->data);
				$this->set('json', $result);
			}
		}
	}
?>
