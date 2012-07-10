<?= $html->docType('xhtml-trans') ?>

<html>
	<head>		
		<title><?= $title_for_layout ?></title>
		
		<?php
			echo $html->css(array('default', 'navigation', 'fastDatePicker'));
			
			echo $javascript->link(array('prototype', 'navigation', 'fastDatePicker', 'utility', 'validation'));
			
			echo $scripts_for_layout;
		?>
	</head>
	<body>
		<div id="Container">
			<div id="Header">
				<div id="Logo">
					<a href="/"><img src="/img/eMRSLogo.png" alt="eMRS" /></a>
				</div>
				<div id="TagLine">
					Miller's Enterprise Applications
				</div>
				<div id="Navigation">
					<?php if ($session->check('user')): ?>
						<a href="#" id="NavigationButton"><img src="/img/buttonApplications.png" alt="Applications Menu" /></a>
						<script type="text/javascript">new Navigation("NavigationButton");</script>
					<?php endif; ?>
					<div class="ApplicationTitle"><h1><?= $application_title ?></h1></div>
				</div>
			</div>
			<div id="Content">
				<?= $content_for_layout ?>
			</div>
			<div id="Footer">
				Web technologies provided by <a href="http://www.hcd.net" target="_blank">Himebaugh Consulting, Inc.</a>
			</div>
		</div>
	</body>
</html>