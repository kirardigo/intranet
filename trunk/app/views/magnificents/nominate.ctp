<?php
	echo $html->css('tabs', false);
	echo $javascript->link(
		array(
			'tabs',
			'scriptaculous.js?load=effects,controls'
		),
		false
	);
?>

<script type="text/javascript">
	function afterUserSelected(text, li)
	{
		var valueText = text.value;
		var strPosition= (valueText.search(",") != -1) ? valueText.search(",") : valueText.length;
		var firstUser = valueText.slice(0, strPosition);
		
		new Ajax.Request("/json/staff/getManager", {
			parameters: { 
				username: firstUser
			},
			onSuccess: function(transport) {
				if (Object.isElement($("MagnificentApprovingRecipientUser")))
				{
					$("MagnificentApprovingRecipientUser").value = transport.headerJSON.manager;
				}
			}			
		});
	}
	
	document.observe("dom:loaded", function() {
		// Fix the autocompleters to work with IE
		mrs.fixAutoCompleter("StaffSearch");
		mrs.fixAutoCompleter("CarbonCopyRecipients");
		
		// Wire up the form buttons
		$("SaveButton").observe("click", function() {
			$("NominateForm").submit();
		});
		$("CancelButton").observe("click", function() {
			location.href = "/magnificents/nominate";
		});
	});
</script>

<style type="text/css">
	#MFVContainer {
		float: right;
		width: 40%;
		border: 1px solid #006118;
		padding: 20px;
	}
	
	#MFVTitle {
		font-weight: bold;
		font-size: 22px;
		color: #006118;
	}
	
	.MFVValue {
		font-weight: bold;
		font-size: 14px;
		color: #006118;
	}
</style>

<?= $html->image('magnificents_small.jpg', array('style' => 'float: right;')); ?>
<h1 class="MagnificentHeader">Award / Nominate</h1>
<br class="ClearBoth" />

<?= $form->create('', array('id' => 'NominateForm', 'url' => 'nominate', 'enctype' => 'multipart/form-data')); ?>

<ul class="TabStrip">
	<li class="Selected"><a href="#">Main</a></li>
</ul>

<div class="TabContainer">
	<div class="TabPage"><!-- Main Tab -->
		<div id="MFVContainer">
			<span id="MFVTitle">Miller's Family Values</span>
			<p>At Miller's we firmly believe in the pledge Let Our Family Take Care
			of Your Family. The Management Team of Miller's commits to the belief
			that all staff is treated as family members and that we will also treat
			each other and our clients as family. The following Family Values are
			incorporated into each of our daily lives and every staff member will
			make them a standard of practice at Miller's.</p>
			
			<span class="MFVValue">Integrity</span>
			<p>We pledge to be honest and to communicate with openness, sincerity,
			and compassion. We will admit our own errors and forgive those of others.</p>
			
			<span class="MFVValue">Respect</span>
			<p>We pledge to treat our colleagues, clients, vendors, and other visitors
			as we wish to be treated - with respect and compassion. We will seek the
			good in everyone, appreciate other views/ideas, and foster business and
			personal growth.</p>
			
			<span class="MFVValue">Service</span>
			<p>We are committed to superior service - to each other as well as our
			clients and referrals. Our clients and their needs will always be upheld as
			our top priority. We will work together with a common purpose and a
			positive attitude.</p>
			
			<span class="MFVValue">Personal Excellence</span>
			<p>We will take pride in Miller's and ourselves. We will demonstrate this
			by maintaining our own personal appearance, buildings/grounds, showrooms,
			vehicles, and individual work areas. We will always stand behind the work
			we do and the service we provide.</p>
		</div>
	<?php
		echo $form->label('Nominated Users ( Separate Multiple Values With Comma )');
		echo $ajax->autoComplete('Staff.search', '/ajax/staff/autoComplete/0/0', array(
			'afterUpdateElement' => 'afterUserSelected',
			'minChars' => 3,
			'style' => 'width: 300px;',
			'tokens' => array(',', ';')
		));
		
		echo $form->label('Nominated By');
		echo '<div style="margin-bottom: 8px;">' . $currentUser . '</div>';
		
		if (!$canApprove)
		{
			echo $form->input('Magnificent.approving_recipient_user', array('label' => 'Intended Approver'));
		}
		
		echo $form->input('Magnificent.is_group_effort', array('label' => array('class' => 'Checkbox', 'text' => 'Part of Monthly Goals?')));
		echo $form->input('Magnificent.value');
		echo $form->input('Magnificent.millers_family_value_id', array('options' => $familyValues, 'empty' => 'Choose'));
		echo $form->input('Magnificent.reason', array('class' => 'Text300'));
		echo $form->input('Magnificent.attachment_file', array('type' => 'file'));
		echo $form->input('Magnificent.narrative', array('class' => 'StandardTextArea', 'label' => 'Supporting Documentation (copy & paste)'));
		
		if ($canApprove)
		{
			echo $form->input('Magnificent.message', array('class' => 'StandardTextArea', 'label' => 'Personalize the E-mail Message'));
			echo $form->label('Carbon Copy E-Mail ( Separate Multiple Values With Comma )');
			echo $ajax->autoComplete('CarbonCopy.recipients', '/ajax/staff/autoComplete/0/1', array(
				'minChars' => 3,
				'style' => 'width: 300px;',
				'tokens' => array(',', ';')
			));
		}
	?>
	</div>
</div>

<?php
	echo $form->button('Save', array('id' => 'SaveButton', 'div' => false));
	echo $form->button('Cancel', array('id' => 'CancelButton'));
 	$form->end();
?>