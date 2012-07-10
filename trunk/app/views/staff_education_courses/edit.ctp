<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("staffeducationcourse:updated", {
				id: $F("StaffEducationCourseId")
			});
			closeWindow();
		<?php endif; ?>
		
		$("CancelButton").observe("click", closeWindow);
		
		$("StaffEducationCourseCourseType").observe("change", function(event) {
			if ($F("StaffEducationCourseCourseType") == "MED")
			{
				$("StaffEducationCourseMeuNumber").up("div").show();
				$("StaffEducationCourseMeuNumber").focus();
			}
			else
			{
				$("StaffEducationCourseMeuNumber").up("div").hide();
				$("StaffEducationCourseCreditHours").focus();
			}
		});
		
		$("StaffEducationCourseCourseType").focus();
	});
</script>

<?php
	echo $form->create('', array('url' => "/staffEducationCourses/edit/{$id}"));
	
	echo $form->hidden('StaffEducationCourse.id');
	
	if ($id !== null)
	{
		echo $form->input('StaffEducationCourse.course_type', array(
			'value' => $staffCourseTypes[$this->data['StaffEducationCourse']['course_type']],
			'readonly' => 'readonly',
			'class' => 'Text100 ReadOnly'
		));
		
		echo $form->input('StaffEducationCourse.meu_number', array(
			'class' => 'Text75 ReadOnly',
			'readonly' => 'readonly'
		));
	}
	else
	{
		echo $form->input('StaffEducationCourse.course_type', array(
			'options' => $staffCourseTypes
		));
		
		echo $form->input('StaffEducationCourse.meu_number', array(
			'class' => 'Text75',
			'div' => array('style' => 'display: none;')
		));
	}
	
	echo $form->input('StaffEducationCourse.credit_hours', array(
		'class' => 'Text50'
	));
	echo $form->input('StaffEducationCourse.title', array(
		'class' => 'Text500'
	));
	echo $form->input('StaffEducationCourse.description', array(
		'class' => 'StandardTextArea'
	));
	echo $form->input('StaffEducationCourse.has_handouts', array(
		'options' => array('N' => 'No', 'Y' => 'Yes'),
		'empty' => false
	));
	echo $form->input('StaffEducationCourse.presenters', array(
		'class' => 'Text400'
	));
	echo $form->input('StaffEducationCourse.confirmation_method', array(
		'options' => $confirmationMethods,
		'empty' => true
	));
	echo '<br/>';
	
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	
	echo $form->end();
?>