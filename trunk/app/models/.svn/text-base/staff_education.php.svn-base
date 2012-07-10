<?php
	class StaffEducation extends AppModel
	{
		var $useTable = 'staff_education';
		
		var $belongsTo = array('StaffEducationCourse');
		
		//username, meu_number, date, description, hours, department_ code, profit_center_number
		var $validate = array(
			'username' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field must be specified.'
				)
			),	
			'staff_education_course_id' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field must be specified.'
				)
			),
			'date_completed' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field must be specified.'
				)
			),
			'department_code' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field must be specified.'
				)
			),
			'profit_center_number' => array(
				'required' => array(
					'rule' => array('minLength', 1),
					'message' => 'This field must be specified.'
				)
			),
		);
	}
?>