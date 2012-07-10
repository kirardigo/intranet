<?= $html->docType('xhtml-trans') ?>
<html>
	<head>
		<?php echo $html->charset(); ?>
		<title><?php echo $page_title; ?></title>

		<?php if (Configure::read() == 0 && !isset($dontRedirect)) { ?>
			<meta http-equiv="Refresh" content="<?php echo $pause; ?>;url=<?php echo $url; ?>"/>
		<?php } ?>
		
		<?= $html->css('default') ?>
	</head>
	<body>
		<div id="Container">
			<div id="Header">
				<div id="Logo">
					<a href="/"><img src="/img/eMRSLogo.png" alt="eMRS"></a>
				</div>
				<div id="TagLine">
					Miller's Rental & Sales Enterprise Applications
				</div>
			</div>
			<div id="Content">
				<div id="FlashDialog">
					<div id="FlashTitle"><h1>Information</h1></div>
					<div id="FlashContent">
						<?php if (isset($dontRedirect)): ?>
							<p><?php echo ifset($message, ifset($content_for_layout, '')); ?></p>
						<?php else: ?>
							<p><a href="<?php echo $url; ?>"><?php echo ifset($message, ifset($content_for_layout, '')); ?></a></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>