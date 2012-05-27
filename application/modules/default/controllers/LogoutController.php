<?php

class LogoutController extends Zend_Controller_Action
{

    public function indexAction()
    {
    	$redirector = $this->_helper->getHelper('Redirector');
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $storage = Zend_Auth::getInstance()->getIdentity();
        	Zend_Auth::getInstance()->clearIdentity();
        	return $redirector->setGotoRoute(array(), 'default');
        } else {
        	return $redirector->setGoto('index','index');
        }
    }

    public function destroy()
    {
        $storage = new Zend_Session_Namespace('Default');
        $storage->unsetAll();
    }
    
}