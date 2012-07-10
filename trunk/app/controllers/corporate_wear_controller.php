<?php
	class CorporateWearController extends AppController
	{
		var $uses = array();
		var $pageTitle = 'Corporate Wear';
		var $components = array('Email');
		
		function index() 
		{
			if (!empty($this->data))
			{
				$lines = array();
				
				//grab all products that have a non-zero quantity
				foreach ($this->data['quantity'] as $i => $value)
				{
					if ($value > 0)
					{
						$lines[] = array(
							'product' => $this->data['product_code'][$i], 
							'color' => $this->data['color'][$i], 
							'size' => array_shift(explode('|', $this->data['size'][$i])), 
							'price' => array_pop(explode('|', $this->data['size'][$i])), 
							'quantity' => $value
						);
					}
				}
				
				$user = $this->Session->read('userInfo');
				
				$this->Email->to = (Configure::read('live') == 1) ? 'kaw@millers.com' : 'mrs-appdev@hcd.net';
				$this->Email->from = 'no-reply@millers.com';
				$this->Email->cc = array($user['email']);
				$this->Email->subject = 'Corporate Wear Order Form';
				$this->Email->sendAs = 'html';
				
				$body = '
					<html>
						<head>
							<title>Corporate Wear Order Form</title>
							<style type="text/css">
								h1 {
									margin: 0 0 10px 0;
									font-size: 20px;
								}
								table {
									border-collapse: collapse;
								}
								th {
									text-align: left;
									background-color: #000099;
									color: #ffffff;
								}
							</style>
						</head>
						<body>
							<h1>Corporate Wear Order Form</h1>
							<b>From:</b> ' . "{$user['first_name']} {$user['last_name']}" . '<br />
							<b>Employee ID:</b> ' . $user['username'] . '<br />
							<b>Payment Method:</b> ' . $this->data['payment_method'] . '
							<br/><br/>
							<table width="60%">
								<tr>
									<th>Style</th>
									<th>Color</th>
									<th>Size</th>
									<th>Price</th>
									<th>Qty</th>
								</tr>
				';
				
				// add product lines
				foreach ($lines as $row)
				{
					$body .= "
								<tr>
									<td>{$row['product']}</td>
									<td>{$row['color']}</td>
									<td>{$row['size']}</td>
									<td>{$row['price']}</td>
									<td>{$row['quantity']}</td>
								</tr>
					";
				}
				
				$body .= '
								<tr>
									<td colspan="4" style="font-weight: bold; text-align: right;">Total:</td><td>' . $this->data['subtotal'] . '</td>
								</tr>
							</table>
						</body>
					</html>
				';
				
				$this->Email->send($body);
				
				//flash thank you result to user before redirect
				$this->flash("Thank you. Your order has been placed.", "/", 2);
			}
			
			$this->data['user_id'] = $this->Session->read('user');
		}
	}
?>