<div class="GroupBox FormColumn" style="float: right; min-width: 300px; font-size: 14px; line-height: 20px;">
	<h2>Quick Links</h2>
	<div class="Content">
		<?php 
			$info = $session->read('userInfo');
		?>
		
		<?= $html->link('Client', '/customers/inquiry'); ?><br/>
		<?= $html->link('Rehab', '/orders/work'); ?><br/>
		<?= $html->link('MRS Knowledge Base', 'http://kb.millers.com/login/index_enter?payload=' . $info['kbLoginPayload'], array('target' => '_blank')); ?><br/>
		<?= $html->link('Magnificents', '/navigation/landing/Magnificents'); ?><br/>
		<?= $html->link('AT', '/distributorOrders/reporting'); ?><br/>
		<?= $html->link('AAA', '/aaaReferrals/reporting'); ?><br/>
	</div>
</div>

<img src="/img/millersLogo.gif" alt="Miller's 60 years of Service" style="margin: 25px auto;" />
