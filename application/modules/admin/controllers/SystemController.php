<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Admin_SystemController extends Zend_Controller_Action
{
    public function indexAction() 
    {
		$config = $this->_helper->settings('config');
		$this->view->config = $config;
		
		Zend_Debug::dump($config['production']['resources']['frontController']['controllerDirectory']);
    }
}
