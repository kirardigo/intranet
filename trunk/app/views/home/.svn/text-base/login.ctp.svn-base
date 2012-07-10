<script type="text/javascript">
	document.observe("dom:loaded", function() {
		$("UserUsername").focus();
	});
</script>

<?php
	echo $form->create('User', array('id' => 'Login', 'url' => '/login'));
	
	if (isset($invalidLogin))
	{
		echo '<p class="Exception">Invalid username and/or password.</p>';
	}
	
	echo $form->input('username');
	echo $form->input('password');
	echo '<br />';
	echo $form->input('remember_me', array('type' => 'checkbox', 'label' => array('class' => 'Checkbox')));
	echo '<br />';
	
	echo $form->end('Login');
?>