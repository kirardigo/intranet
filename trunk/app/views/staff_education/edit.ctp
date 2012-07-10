<?= $javascript->link(array('scriptaculous.js?load=effects,controls'), false); ?>

<script type="text/javascript">
	function closeWindow()
	{
		window.open("","_self");
		window.close();
	}
	
	function validateForm(event)
	{
		valid = true;
		
		$$(".FieldError").invoke("removeClassName", "FieldError");
		
		if ($F("StaffEducationCourseMeuNumber") == "")
		{
			valid = false;
			$("StaffEducationCourseMeuNumber").addClassName("FieldError");
		}
		if ($("StaffSearch") != undefined && $F("StaffSearch") == "")
		{
			valid = false;
			$("StaffSearch").addClassName("FieldError");
		}
		if ($F("StaffEducationDateCompleted") == "")
		{
			valid = false;
			$("StaffEducationDateCompleted").addClassName("FieldError");
		}
		
		//these fields are not always on the screen, but if they are they cannot be blank
		if ($("StaffEducationProfitCenterNumber") != undefined && $F("StaffEducationProfitCenterNumber") == "")
		{
			valid = false;
			$("StaffEducationProfitCenterNumber").addClassName("FieldError");
		}
		if ($("StaffEducationDepartmentCode") != undefined && $F("StaffEducationDepartmentCode") == "")
		{
			valid = false;
			$("StaffEducationDepartmentCode").addClassName("FieldError");
		}
		
		if (!valid)
		{
			event.stop();
			alert("Please fill in the required fields.");
		}
	}
	
	document.observe("dom:loaded", function() {
		<?php if (isset($close) && $close): ?>
			window.opener.document.fire("meu:updated");
			closeWindow();
		<?php endif; ?>
		
		mrs.bindDatePicker("StaffEducationDateCompleted");
		$("CancelButton").observe("click", closeWindow);
		$("StaffEducationCourseMeuNumber").observe("change", function() {
			$("CourseName").update();
		});
		
		new Ajax.Autocompleter("StaffEducationCourseMeuNumber", "StaffEducationCourseAutocomplete", "/ajax/staffEducationCourses/autoComplete", {
			minChars: 2,
			callback: function() {
				return "data[StaffEducationCourse][search]=" + $F("StaffEducationCourseMeuNumber");
			}
		});
		
		if ($("StaffSearch") != undefined)
		{
			new Ajax.Autocompleter("StaffSearch", "StaffFullNameAutocomplete", "/ajax/staff/autoComplete", {
				minChars: 2,
				tokens: [",", ";"]
			});
		}
		
		$("StaffEducationForm").observe("submit", validateForm);
		
		$("StaffEducationCourseMeuNumber").focus();
	});
</script>

<?php
	echo $form->create('', array('url' => "/staffEducation/edit/{$id}", 'id' => 'StaffEducationForm'));
	
	echo $form->input('StaffEducationCourse.meu_number', array(
		'label' => 'Course',
		'class' => 'Text200',
		'after' => '<span id="CourseName" style="margin-left: 20px;">' . ifset($this->data['StaffEducationCourse']['title']) . '</span>'
	));
	echo '<div id="StaffEducationCourseAutocomplete" class="auto_complete" style="display: none;"></div>';
	
	if ($id == null)
	{
		echo $form->input('Staff.search', array(
			'label' => 'Username',
			'class' => 'Text200',
			'value' => ifset($this->data['StaffEducation']['username']),
			'after' => '<div>Use commas to create records for multiple users</div>'
		));
		echo '<div id="StaffFullNameAutocomplete" class="auto_complete" style="display: none;"></div>';
	}
	else
	{
		echo $form->input('Virtual.search', array(
			'label' => 'Username',
			'class' => 'Readonly',
			'readonly' => 'readonly',
			'value' => $this->data['StaffEducation']['username']
		));
	}
	
	echo $form->input('StaffEducation.date_completed', array(
		'type' => 'text',
		'class' => 'Text75'
	));
	
	if ($id != null)
	{
		echo $form->input('StaffEducation.profit_center_number', array(
			'options' => $profitCenters,
			'empty' => true
		));
		
		echo $form->input('StaffEducation.department_code', array(
			'options' => $departments,
			'empty' => true
		));
	}
	
	echo '<br/>';
	
	echo $form->submit('Save', array('id' => 'SaveButton', 'div' => array('class' => 'Horizontal')));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
	
	echo $form->end();
?>