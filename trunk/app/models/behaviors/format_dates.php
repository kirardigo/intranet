<?php
	/**
	 * Behavior for models that uses schema information to automatically format
	 * date fields correctly after finds and before saves.
	 */
	class FormatDatesBehavior extends ModelBehavior
	{
		/**
		 * This function gets triggered from the AppModel afterFind callback.
		 * @param object $model The model that the function is being called for.
		 * @param array $results The original result array for a given model.
		 * @return array The array of formatted model data.
		 */
		function formatDates(&$model, $results)
		{
			$schema = $model->schema();
			
			// Loop through results and format all present date fields
			foreach ($results as $fieldName => $fieldValue)
			{
				// We need the isset because FU05 schema does not contain id field
				if (isset($schema[$fieldName]['type']) && $schema[$fieldName]['type'] == 'date')
				{
					$results[$fieldName] = formatDate($fieldValue);
				}
			}
			
			return $results;
		}
		
		/**
		 * Callback implementation to massage data before saving.
		 * @param object $model model using this behavior
		 * @return bool Indicates success or failure.
		 */
		function beforeSave(&$model)
		{
			$schema = $model->schema();
			
			foreach ($model->data[$model->alias] as $fieldName => $fieldValue)
			{
				// We need the isset because FU05 schema does not contain id field
				if (isset($schema[$fieldName]['type']) && $schema[$fieldName]['type'] == 'date')
				{
					if ($fieldValue == '')
					{
						$model->data[$model->alias][$fieldName] = null;
					}
					else
					{
						$model->data[$model->alias][$fieldName] = databaseDate($fieldValue);
					}
				}
			}
			
			return true;
		}
	}
?>