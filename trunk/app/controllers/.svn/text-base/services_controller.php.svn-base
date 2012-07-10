<?php
	/**
	 * This controller hosts any web services that are exposed for any reason.
	 */
	class ServicesController extends AppController
	{
		var $uses = array();
		
		/**
		 * Service to allow a query of any model in the database with any combination of fields.
		 * @param string $model The name of the model to query.
		 * And now a variable number of arguments but always in a pair of two at a time:
		 * @param string $field The field to create a condition on.
		 * @param string $value The value to check for.
		 *
		 * The field can contain a space at the end and then an operator to be able to query >, <, <=, etc. If no 
		 * operator is supplied, equality is assumed.
		 *
		 * To specify a blank string for an argument, pass ~blank~ for the value.
		 *
		 * Examples:
		 * 		/json/services/query/Customer/account_number/A20094 - Find all customers with account number A20094
		 * 		/json/services/query/CustomerCarrier/account_number/A20094/is_active/true - Find all customer carriers for A20094 that are active
		 * 		/json/services/query/ProfitCenter/sales_tax_rate >/6.50 - Find all profit centers with a sales tax rate > 6.50
		 *
		 * The response is a JSON array of objects, where each object is a record that is in the form of a model->find('all') call.
		 */
		function json_query($model)
		{
			//grab the extra arguments for the fields and values
			$args = func_get_args();
			array_shift($args);
			
			//create the model
			$model = ClassRegistry::init($model);
			
			//make sure we have an even pair for each field
			if (count($args) == 0 || count($args) % 2 != 0)
			{
				$this->set('json', array('error' => 'Missing arguments.'));
				return;
			}
			
			$conditions = array();
			
			//turn the arguments into a conditions array
			for ($i = 0; $i < count($args); $i += 2)
			{
				$conditions[$args[$i]] = str_replace('~blank~', '', $args[$i + 1]);
			}
			
			//this JSON can get large so we don't want to try and put it in the header
			$this->set('suppressJsonHeader', true);
			
			//find the matching records
			$this->set('json', $model->find('all', array('conditions' => $conditions, 'contain' => array())));
		}	
		
		/**
		 * Service to get the full schema for a given model.
		 * @param string $model The name of the model to get the schema for.
		 *
		 * The response is a JSON object with a key for each field. The value of each key is another JSON object with the following fields:
		 * 		ordinal - Physical ordinal of the field in a record.
		 * 		position - The physical offset within the record where the field starts.
		 * 		fileproType - The type of the field as defined by filepro.
		 * 		type - The equivalent PHP type.
		 *		null - ignore / do not use
		 *		default - ignore / do not use
		 * 		length - The length of the field.
		 */
		function json_schema($model)
		{
			//create the model and load the schema
			$model = ClassRegistry::init($model);
			$this->set('json', $model->schema());
		}
		
		/**
		 * Service to get a full record count of any model in the database.
		 * @param string $model The name of the model to get the record count for.
		 *
		 * The response is a JSON array with one key called "count" containing the full record count.
		 */
		function json_count($model)
		{
			//create the model and perform the count
			$model = ClassRegistry::init($model);
			$this->set('json', array('count' => $model->find('count')));
		}	
		
		/**
		 * Service to allow a save of any model in the database.
		 * @param string $model The name of the model to save.
		 * 
		 * The action expected posted data to contain the fields and values to set for the record in the model (the post
		 * data should be in the same form as a regular CakePHP form post using the FormHelper).
		 *
		 * To insert a new record, omit posting the id field. To update a record, specify an id.
		 *
		 * The response is a JSON object with the following keys:
		 * 		success - True of false depending on if the save was successful.
		 * 		id - The ID of the record that was inserted/updated.
		 */
		function json_save($model)
		{
			$success = false;
			$id = false;
			
			//make sure we have posted data
			if (!empty($this->data))
			{
				//create the model and save the record
				$model = ClassRegistry::init($model);
				$success = !!$model->save($this->data);
				
				//update the ID
				$id = $success ? $model->id : false;
			}
			
			//let the caller know if the save worked as well as the ID of the record that was inserted/updated
			$this->set('json', array('success' => $success, 'id' => $id, 'passedData' => $this->data, 'errors' => $id === false ? $model->invalidFields() : null));
		}
		
		/**
		 * Test case function for the save() action.
		 */
		function test()
		{
			$this->autoRender = false;
			
			$data = array('Customer' => array(
				'id' => 1,
				'address_1' => '',
				'city' => ''
			));
			
			echo $this->requestAction("/json/services/save/Customer", array('return', 'data' => $data));
		}
	}
?>