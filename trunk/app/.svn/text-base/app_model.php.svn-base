<?php
	class AppModel extends Model
	{
		var $actsAs = array('Containable');
		
		var $importFile;
		
		/**
		 * Used internally for fu05 models to be able to write via filepro.
		 */
		var $_saveViaFilepro = false;
		var $_deleteViaFilepro = false;
		
		/**
		 * Callback implementation that takes care of the created/modified by fields in the models.
		 */
		function beforeSave()
		{
			//we always set the modified by field
			$fields = array('modified_by');
			
			//if we're creating the record, we need to write the created by field as well
			if (!$this->exists())
			{
				$fields[] = 'created_by';
			}
			
			foreach ($fields as $field)
			{
				//if the model has the field and the user hasn't set it when saving the record, we'll set it to the
				//currently logged in user.
				if ($this->hasField($field) && !isset($this->data[$this->alias][$field]) && class_exists('User'))
				{
					$this->data[$this->alias][$field] = User::current();
				}
			}
			
			return true;
		}
		
		/**
		 * Allows the user to save (create or update) a U05 record by writing it through filepro processing. This allows
		 * filepro indexes to be up to date even on records created or modified from eMRS. To the caller, there is no difference
		 * between calling this method and the regular save() method. 
		 *
		 * One important thing to remember is that for an update, you cannot use the Lockable behavior to lock the record you wish to write
		 * prior to calling this method. Doing so locks the record in eMRS, and since this forwards the write call to a separate filepro
		 * process, that process will be locked out from writing.
		 *
		 * (param definitions taken straight from Cake's model file)
		 * 
		 * @param array $data Data to save.
		 * @param mixed $validate Either a boolean, or an array.
		 * 			If a boolean, indicates whether or not to validate before saving.
		 *			If an array, allows control of validate, callbacks, and fieldList.
		 * @param array $fieldList List of fields to allow to be written.
		 * @return mixed On success Model::$data if its not empty or true, false on failure.
		 */
		function saveViaFilepro($data = null, $validate = true, $fieldList = array())
		{
			try
			{
				if ($this->useDbConfig != 'fu05')
				{
					return false;
				}
			
				$this->_saveViaFilepro = true;
				$success = $this->save($data, $validate, $fieldList);
			}
			catch (Exception $ex)
			{	
				$success = false;
			}
			
			$this->_saveViaFilepro = false;
			return $success;
		}
		
		/**
		 * Allows the user to delete a U05 record by writing it through filepro processing. This allows
		 * filepro indexes to be up to date even on records deleted from eMRS. To the caller, there is no difference
		 * between calling this method and the regular delete() method. 
		 *
		 * (param definitions taken straight from Cake's model file)
		 *
		 * @param mixed $id ID of record to delete.
		 * @param boolean $cascade Set to true to delete records that depend on this record.
		 * @return boolean True on success.
		 */
		function deleteViaFilepro($id = null, $cascade = true)
		{
			try
			{
				if ($this->useDbConfig != 'fu05')
				{
					return false;
				}
			
				$this->_deleteViaFilepro = true;
				$success = $this->delete($id, $cascade);
			}
			catch (Exception $ex)
			{	
				$success = false;
			}
			
			$this->_deleteViaFilepro = false;
			return $success;
		}
		
		/**
		 * Callback implementation to massage data when retrieving records.
		 * @param array $results The results that are returned from the find.
		 * @return array The modified result array.
		 */
		function afterFind($results)
		{
			foreach ($results as $key => $record)
			{
				foreach ($record as $modelName => $modelData)
				{
					if ($modelName != '0')
					{
						$currentModel = ClassRegistry::init($modelName);
						
						// This will ensure that models with the FormatDates behavior will always
						// have their data massaged even if they are linked as an associated model,
						// which does not normally trigger callback functions.
						if ($currentModel->Behaviors->enabled('FormatDates'))
						{
							$results[$key][$modelName] = $currentModel->formatDates($modelData);
						}
					}
				}
			}
			
			return $results;
		}
		
		/**
		 * Overridden. This has been overridden to allow the special belongsTo syntax
		 * of the FU05 driver. It is largely copy-pasted from the Model class, but with
		 * one extra check (see the FU05 driver for more info).
		 */
		function escapeField($field = null, $alias = null) 
		{
			if (empty($alias)) 
			{
				$alias = $this->alias;
			}
			
			if (empty($field)) 
			{
				$field = $this->primaryKey;
			}
			
			$db =& ConnectionManager::getDataSource($this->useDbConfig);
			
			//handle the special fu05 belongsTo syntax
			if ($this->useDbConfig == 'fu05' && is_array($field))
			{
				$field = $field['field'];
			}
			
			if (strpos($field, $db->name($alias)) === 0) 
			{
				return $field;
			}
			
			return $db->name($alias . '.' . $field);
		}
		
		/**
		 * Generates a target URI usable for note targets. We use the class name because
		 * the table names from FU05 and filePro are not always descriptive.
		 * @param integer $id The ID of the record to generate the URI for.
		 * @return string A target URI.
		 */
		function generateTargetUri($id)
		{
			return $this->name . '_' . $id;
		}
		
		/**
		 * Returns the contents of a single field given the supplied conditions, in the
		 * supplied order.
		 *
		 * @param string $name Name of field to get
		 * @param array $conditions SQL conditions (defaults to NULL)
		 * @param string $order SQL ORDER BY fragment
		 * @param string $index Filepro index to use.
		 * @return string field contents, or false if not found
		 * @access public
		 * @link http://book.cakephp.org/view/453/field
		 */
		function field($name, $conditions = null, $order = null, $index = null)
		{
			if ($conditions === null && $this->id !== false) {
				$conditions = array($this->alias . '.' . $this->primaryKey => $this->id);
			}
			if ($this->recursive >= 1) {
				$recursive = -1;
			} else {
				$recursive = $this->recursive;
			}
			
			$findArray = array(
				'contain' => array(),
				'fields' => array($name),
				'conditions' => $conditions,
			);
			
			if ($order != null)
			{
				$findArray['order'] = $order;
			}
			
			if ($index != null)
			{
				$findArray['index'] = $index;
			}	
			
			if ($data = $this->find('first', $findArray)) {
				if (strpos($name, '.') === false) {
					if (isset($data[$this->alias][$name])) {
						return $data[$this->alias][$name];
					}
				} else {
					$name = explode('.', $name);
					if (isset($data[$name[0]][$name[1]])) {
						return $data[$name[0]][$name[1]];
					}
				}
				if (isset($data[0]) && count($data[0]) > 0) {
					$name = key($data[0]);
					return $data[0][$name];
				}
			} else {
				return false;
			}
		}
	}
?>