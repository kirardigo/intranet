<?php
	class HomeController extends AppController
	{
		var $uses = array('User');
		var $components = array('Authentication', 'Cookie');
		var $pageTitle = 'eMRS Home';
		var $kbEncryptionKey = 'D073F1F1-BC52-13bb-9A03-123CE3F94F74';
		
		/** Landing home page **/
		function index() {}
		
		/**
		 * Login action to authenticate a user.
		 */
		function login()
		{
			$this->pageTitle = 'eMRS Login';

			if (!empty($this->data))
			{
				//was this a programmatic login?
				$automated = isset($this->params['requested']);
				
				//try to authenticate
				if ($automated || $this->Authentication->authenticate($this->data['User']['username'], $this->data['User']['password']))
				{
					//if we're good, save their username to session
					$this->Session->write('user', $this->data['User']['username']);
					
					$userRecord = $this->User->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'username' => $this->data['User']['username']
						)
					));
					
					if ($userRecord !== false)
					{
						$payload = null;
						
						//encrypt user/pass for KB publisher link in case they go there for a silent login and add it to the session data
						//(when automated, we can grab the encrypted data from the cookie)
						if (!$automated)
						{
							App::import('Vendor', 'crypt/rc4');
							$encryptor = new Crypt_RC4();
							$encryptor->setKey($this->kbEncryptionKey);
							$payload = $this->data['User']['username'] . '|' . $this->data['User']['password'];
							$payload = base64_encode($encryptor->crypt($payload));
						}
						else
						{
							$payload = $this->Cookie->read('kbPayload');
						}
						
						$userRecord['User']['kbLoginPayload'] = $payload;
						$this->Session->write('userInfo', $userRecord['User']);
												
						//if this was automated, we're done
						if ($automated)
						{
							return true;
						}
						
						if ($this->data['User']['remember_me'])
						{
							$this->Cookie->write('user', $this->data['User']['username'], true, '+3 months');
							$this->Cookie->write('kbPayload', $userRecord['User']['kbLoginPayload'], true, '+3 months');
						}
						
						$url = '/';
						
						//redirect them to the page they came from, if any
						if ($this->Session->check('originalUrl'))
						{
							$url .= $this->Session->read('originalUrl');
							$this->Session->delete('originalUrl');
						}
						
						$this->redirect($url);
					}
					else
					{
						$this->Session->delete('user');
						$this->Cookie->delete('user');
						$this->Cookie->delete('kbPayload');
						
						if ($automated)
						{
							return false;
						}
					}
				}
				
				$this->set('invalidLogin', true);
			}
		}
		
		/**
		 * Logout action to terminate a session.
		 */
		function logout()
		{
			if ($this->Session->check('user'))
			{
				$this->Session->delete('user');
				$this->Cookie->delete('user');
				$this->Cookie->delete('kbPayload');
			}
			
			$this->redirect('/login');
		}
		
		/**
		 * Action for our "down for maintenance" page.
		 */
		function maintenance() 
		{ 
			$this->layout = 'flash'; 
			$this->set('page_title', 'eMRS Down for Maintenance');
			$this->set('dontRedirect', true);
		}
	}
?>