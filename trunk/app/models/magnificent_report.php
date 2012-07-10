<?php
	/**
	 * This model is based on a view that combines data from several tables for reports.
	 */
	class MagnificentReport extends AppModel
	{
		var $actsAs = array('FormatDates');
		
		var $belongsTo = array('MillersFamilyValue');
		
		/**
		 * Callback implementation to massage data when retrieving records.
		 * We need this because when we order by certain parameters the data is returned
		 * with the original model names rather than the model name of the view.
		 * @param array $results The results that are returned from the find.
		 * @return array The modified result array.
		 */
		function afterFind($results)
		{
			foreach ($results as $key => $result)
			{
				foreach ($result as $model => $fields)
				{
					foreach ($fields as $field => $value)
					{
						$final[$key]['MagnificentReport'][$field] = $value;
					}
				}
			}
			
			return isset($final) ? $final : false;
		}
	}
?>