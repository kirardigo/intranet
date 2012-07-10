<?php
	class AppController extends Controller
	{
		var $components = array('Session', 'Cookie');
		var $helpers = array('Javascript', 'Html', 'Form', 'Session');
		var $applicationTitle = false;
		
		/**
		 * An array of controller => action routes that are allowed to be executed without logging in.
		 */ 
		var $openRoutes = array(
			'transactionQueue' => 'batchPostSingle',
			'home' => array('login', 'maintenance'),
			'filepro' => array('indexesWithSystemFields'),
			'staff' => array('ajax_kbPublisherInfo'),
			'services' => '*',
			'brian' => '*',
			'jim' => '*',
			'bruce' => '*'
		);
		
		/** 
		 * Used by ajax routes - the controller can set this to false to 
		 * use their own view. Otherwise a shared view is used. The view
		 * expects a variable called "output" to be set by the controller via $this->set().
		 * If the variable is a string, its output will be rendered. If the variable is an array, 
		 * it will be rendered as an unordered list, suitable for an auto-completer. The array
		 * can simply be an array of values, or it can contain the following keys:
		 * 
		 * array(
		 * 		data => array - data that is the result of a Model->find() call.
		 * 		id_field => string - optional, but specifies the name of the Model.field to use for the 'id'
		 * 							 attribute of the <li> tag.
		 * 		id_prefix => string - the prefix to put on the id attribute before each id value.
		 * 		value_fields => array - an array of Model.field names to render for each item.
		 * 		value_format => string - optional format string to apply to the value fields array - use printf syntax.
		 * 		informal_fields => array - optional and will be rendered as an informal span in the list 
		 * 								   item. It works just like the value_fields.
		 * 		informal_format => string - optional format string to apply to the informal fields array - use printf syntax.
		 * 		escape => boolean - used to tell the view whether or not to escape the output. The default is escape fields.
		 * )
		 */
		var $autoRenderAjax = true;
		
		/**
		 * Used by json routes - the controller can set this to false to 
		 * use their own view. Otherwise a shared view is used. The view expects a variable
		 * called "json" to be set by the controller via $this->set(). The variable should be 
		 * an array with named keys. The keys will be used as the json keys.
		 */
		var $autoRenderJson = true;
		
		/**
		 * Overridden. Performs logic before an action is invoked.
		 */
		function beforeFilter()
		{
			//all shells will run under a pseudo-user when the perform a requestAction
			if (defined('CAKEPHP_SHELL'))
			{
				$this->Session->write('user', 'emrs');
				
				$userRecord = ClassRegistry::init('User')->find('first', array(
					'contain' => array(),
					'conditions' => array(
						'username' => $this->Session->read('user')
					)
				));
				
				if ($userRecord !== false)
				{
					$this->Session->write('userInfo', $userRecord['User']);
				}
			}
			
			//make sure the site is online (cake shells we allow through though)
			if (!defined('CAKEPHP_SHELL') && Configure::read('offline') && !($this->params['controller'] == 'home' && $this->params['action'] == 'maintenance'))
			{
				$this->redirect('/home/maintenance');
			}
			
			//require login except for shells and open routes
			if (!defined('CAKEPHP_SHELL') && !$this->Session->check('userInfo') && !$this->_isOpenRoute($this->params['controller'], $this->params['action']))
			{
				$authenticated = false;
				
				//see if the user has the "remember me" cookie, and if so, try and silently authenticate them
				if (($user = $this->Cookie->read('user')) !== null)
				{
					$authenticated = $this->requestAction('/home/login', array('data' => array('User' => array('username' => $user))));
				}
				
				//if the cookie auth didn't pan out, force them to manually login
				if (!$authenticated)
				{
					// Only save the original target if it wasn't an AJAX or JSON request
					if (!isset($this->params['ajax']) && !isset($this->params['json']))
					{
						$this->Session->write('originalUrl', $this->params['url']['url']);
					}
					
					$this->redirect('/login');
				}
			}
			
			//default ajax and module routes to use the ajax layout, and json
			//routes to use the json layout
			if (isset($this->params['ajax']) || isset($this->params['module']))
			{
				$this->layout = 'ajax';
			}
			else if (isset($this->params['json']))
			{
				$this->layout = 'json';
			}
			
			//set the current user ID so models can use it
			if ($this->Session->check('user'))
			{
				App::import('Model', 'User');
				User::current($this->Session->read('user'));
			}
			
			//if this isn't a shell and the user IS logged in, we need to make sure they are authorized to see
			//the action they are trying to get to
			if (!defined('CAKEPHP_SHELL') && $this->Session->check('userInfo'))
			{
				$info = $this->Session->read('userInfo');

				if (!ClassRegistry::init('SecureRoute')->check($info['role_id'], isset($this->params['prefix']) ? $this->params['prefix'] : '', $this->params['controller'], $this->params['action']))
				{
					$this->flash('You do not have access to this page. If you feel you should have access, please contact the administrator.', '/');
				}
			}
		}
		
		/**
		 * Determines if a given controller and action are allowed to be served without a login.
		 * @param string $controller The controller to test.
		 * @param string $action The action in the controller to test.
		 * @return bool Whether or not the route is open (i.e. can be served without logging in).
		 */
		function _isOpenRoute($controller, $action)
		{
			//see if the controller has any open routes
			if (!array_key_exists($controller, $this->openRoutes))
			{
				return false;
			}
			
			//grab the open actions for the controller
			$actions = $this->openRoutes[$controller];
			
			//convert the actions into an array if they aren't already
			if (!is_array($actions))
			{
				$actions = array($actions);
			}
			
			//if all actions are allowed in the controller, let it through
			if (in_array('*', $actions))
			{
				return true;
			}
			
			//otherwise just try and find the action in the list of open routes
			return in_array($action, $actions);
		}
		
		/**
		 * Overridden. Performs logic before each view is rendered.
		 */
		function beforeRender()
		{
			if ($this->applicationTitle == false)
			{
				$this->applicationTitle = $this->pageTitle;
			}
			
			$this->set('application_title', $this->applicationTitle);
		}
		
		/**
		 * Overridden. Allows ajax and json routes to not have to create a view for each action. Instead,
		 * a shared view is used.
		 */
		function render($action = null, $layout = null, $file = null)
		{
			if (isset($this->params['ajax']) && $this->autoRenderAjax)
			{
				return parent::render($action, $layout, '/shared/ajax');
			}
			else if (isset($this->params['json']) && $this->autoRenderJson)
			{
				return parent::render($action, $layout, '/shared/json');
			}

			return parent::render($action, $layout, $file);
		}
		
		/**
		 * Allows any controller to check for a particular permission for the currently logged in user.
		 * @param string $permission The name of the permission to demand. The permission should be in the form
		 * of "domain.permission".
		 * @return boolean True if the user has access, false otherwise.
		 */
		function checkPermission($permission)
		{
			//if the user is logged in...
			if ($this->Session->check('userInfo'))
			{
				//check permission for their role against the desired permission
				$info = $this->Session->read('userInfo');
				return ClassRegistry::init('Permission')->check($info['role_id'], $permission);
			}
			
			//users who aren't logged in can't have access
			return false;
		}
		
		/**
		 * Allows any controller to demand a particular permission for the currently logged in user. If
		 * the user doesn't have access to the permission, they are redirected to the home page via a flash.
		 * @param string $permission The name of the permission to demand. The permission should be in the form
		 * of "domain.permission".
		 */
		function demandPermission($permission)
		{
			//if the user is logged in...
			if ($this->Session->check('userInfo'))
			{
				//demand permission for their role against the desired permission (the demand method
				//will throw a PermissionException if the user doesn't have access. See the Permission model
				//for more info and for the definition of the PermissionException class).
				$info = $this->Session->read('userInfo');
				ClassRegistry::init('Permission')->demand($info['role_id'], $permission);
			}
			else
			{
				//if the user isn't logged in, we throw our own permission exception to get page execution to end.
				
				//we need to load the permission class in case it hasn't been loaded yet to get the PermissionsException declaration
				ClassRegistry::init('Permission');
				throw new PermissionException('User is not logged in!');
			}
		}
		
		/**
		 * Overridden method (from Object) to be able to have the demandPermission method totally stop page 
		 * execution but still be able to do a flash.
		 * @param string $method Name of the method to call.
		 * @param array $params Parameter list to use when calling $method.
		 * @return mixed Returns the result of the method call.
		 */
		function dispatchMethod($method, $params = array())
		{
			try
			{
				switch (count($params)) 
				{
					case 0:
						return $this->{$method}();
					case 1:
						return $this->{$method}($params[0]);
					case 2:
						return $this->{$method}($params[0], $params[1]);
					case 3:
						return $this->{$method}($params[0], $params[1], $params[2]);
					case 4:
						return $this->{$method}($params[0], $params[1], $params[2], $params[3]);
					case 5:
						return $this->{$method}($params[0], $params[1], $params[2], $params[3], $params[4]);
					default:
						return call_user_func_array(array(&$this, $method), $params);
					break;
				}
			}
			catch (PermissionException $ex)
			{
				//if a permission exception was thrown, code was demanding permission for the logged in user and the
				//user did not have access. In that case, we flash the user a message to let them know and redirect to
				//the home page
				$this->flash('You do not have the required permissions to perform the requested action. If you feel you should have access, please contact the administrator.', '/');	
			}
		}
	}
?>