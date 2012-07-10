<?php
	class ServiceFlatRate extends AppModel
	{
		var $validate = array(
			'hcpc_code' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The HCPC code is required.'
				),
				'unique' => array(
					'rule' => 'isUnique',
					'message' => 'This HCPC code is already in use.'
				)
			),
			'description' => array(
				'required' => array(
					'rule' => 'notEmpty',
					'message' => 'The description is required.'
				)
			),
			'mrs_flat_rate' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'message' => 'The MRS flat rate must be numeric.'
				)
			),
			'cms_flat_rate' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'allowEmpty' => true,
					'message' => 'The CMS flat rate must be numeric.'
				)
			)				
		);
	}
?>