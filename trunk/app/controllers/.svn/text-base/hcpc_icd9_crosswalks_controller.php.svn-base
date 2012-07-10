<?php
	class HcpcIcd9CrosswalksController extends AppController
	{
		/**
		 * Associate diagnosis with Hcpc
		 */
		function json_addDiagnosis($code, $DiagnosisSearch)
		{
			$saveData['HcpcIcd9Crosswalk'] = array( 
				'hcpc_code' => $code,
				'icd9_code' => $DiagnosisSearch
			);
			
			$success = $this->HcpcIcd9Crosswalk->save($saveData);

			$this->set('json', array('success' => ($success !== false), 'id' => $this->HcpcIcd9Crosswalk->id));
		}
		
		/**
		 * Removes the diagnosis.
		 */
		function json_removeDiagnosis($id)
		{
			$success = $this->HcpcIcd9Crosswalk->delete($id);
			
			$this->set('json', array('success' => $success));
		}
	}
?>
