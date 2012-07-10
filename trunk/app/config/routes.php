<?php
/* SVN FILE: $Id: routes.php 7945 2008-12-19 02:16:01Z gwoo $ */
/**
 * Short description for file.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision: 7945 $
 * @modifiedby    $LastChangedBy: gwoo $
 * @lastmodified  $Date: 2008-12-18 18:16:01 -0800 (Thu, 18 Dec 2008) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

Router::connect('/', array('controller' => 'home', 'action' => 'index'));
Router::connect('/login', array('controller' => 'home', 'action' => 'login'));
Router::connect('/logout', array('controller' => 'home', 'action' => 'logout'));
Router::connect('/emc/:action/*', array('controller' => 'electronic_medical_claims'));

//map reports to a prefix
Router::connect('/reports/:controller/:action/*', array('prefix' => 'report', 'report' => true));

//map modules to a prefix
Router::connect('/modules/:controller/:action/*', array('prefix' => 'module', 'module' => true));

//map ajax to a prefix
Router::connect('/ajax/:controller/:action/*', array('prefix' => 'ajax', 'ajax' => true));

//map json to a prefix
Router::connect('/json/:controller/:action/*', array('prefix' => 'json', 'json' => true));

?>