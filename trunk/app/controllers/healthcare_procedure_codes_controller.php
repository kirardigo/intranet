<?php
	class HealthcareProcedureCodesController extends AppController
	{
		/**
		 * AJAX action to get a HCPC description.
		 * 
		 * The method expects $this->params['form'] to contain the following variables:
		 * 		code The HCPC code to find the description for.
		 */
		function ajax_description()
		{
			$match = $this->HealthcareProcedureCode->field('description', array('code' => $this->params['form']['code']));
			$this->set('output', $match !== false ? $match : '');
		}
	}
?>